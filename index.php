<?php


declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/middleware/sessionManager.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/models/AuthModel.php';
require_once __DIR__ . '/models/AuthLogModel.php';

// -------------------------------------------------------------
// Estado inicial
// -------------------------------------------------------------
$error       = '';
$cierre_info = null;

// Mensaje si viene por expiración
if (isset($_GET['expired']) && $_GET['expired'] === '1') {
    $error = 'La sesión expiró por inactividad. Por favor, iniciá sesión nuevamente.';
}

// -------------------------------------------------------------
// Procesamiento del POST (login)
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitización básica (tipado estricto + trimming)
    $usuario    = trim((string)($_POST['usuario'] ?? ''));
    $contrasena = (string)($_POST['contrasena'] ?? '');

    // Contexto de auditoría (Cloudflare o REMOTE_ADDR)
    $ip        = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? ($_SERVER['REMOTE_ADDR'] ?? null);
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

    $auth    = new AuthModel($pdo);
    $authLog = new AuthLogModel($pdo);

    try {
        $user = $auth->login($usuario, $contrasena);

        if ($user) {
            // Log OK
            $authLog->registrar([
                'usuario_input' => $usuario,
                'resultado'     => 'ok',
                'motivo'        => null,
                'ip'            => $ip,
                'user_agent'    => $userAgent,
                'usuario_id'    => $user['id_real'] ?? null,
                'rol'           => $user['rol'] ?? null,
            ]);

            // Analizamos y cerramos operativos vencidos (side effect explícito)
            require_once __DIR__ . '/views/partials/cierre_operativos.php';
            $cierre_info = cerrarOperativosVencidos($pdo);
            $_SESSION['cierre_info'] = $cierre_info;

            // Datos de sesión
            $_SESSION['usuario']         = $user['usuario'] ?? '';
            $_SESSION['rol']             = $user['rol'] ?? '';
            $_SESSION['nombre']          = $user['nombre'] ?? '';
            $_SESSION['correo']          = $user['correo'] ?? '';
            $_SESSION['telefono']        = $user['telefono'] ?? '';
            $_SESSION['direccion']       = $user['direccion'] ?? '';
            $_SESSION['usuario_id']      = $user['id'] ?? null;
            $_SESSION['id_real']         = $user['id_real'] ?? null;
            $_SESSION['cuit']            = $user['cuit'] ?? '';
            $_SESSION['LAST_ACTIVITY']   = time();

            // Refrescar cookie de sesión (helper del middleware)
            if (function_exists('refreshSessionCookie')) {
                refreshSessionCookie();
            }

            // Redirección por rol (fail-safe a "/")
            $destinos = [
                'cooperativa'  => '/views/cooperativa/coop_dashboard.php',
                'productor'    => '/views/productor/prod_dashboard.php',
                'sve'          => '/views/sve/sve_dashboard.php',
                'ingeniero'    => '/views/ingeniero/ing_dashboard.php',
                'piloto_drone' => '/views/drone_pilot/drone_pilot_dashboard.php',
                'piloto_tractor' => '/views/tractor_pilot/tractor_pilot_dashboard.php',
            ];
            $rol = (string)($_SESSION['rol'] ?? '');
            $target = $destinos[$rol] ?? '/';
            header('Location: ' . $target);
            exit;
        }

        // Log ERROR (credenciales/permiso)
        $authLog->registrar([
            'usuario_input' => $usuario,
            'resultado'     => 'error',
            'motivo'        => 'credenciales invalidas o permiso no habilitado',
            'ip'            => $ip,
            'user_agent'    => $userAgent,
            'usuario_id'    => null,
            'rol'           => null,
        ]);
        $error = 'Usuario o contraseña inválidos o permiso no habilitado.';
    } catch (Throwable $e) {
        // Log ERROR (excepción)
        $authLog->registrar([
            'usuario_input' => $usuario,
            'resultado'     => 'error',
            'motivo'        => 'excepcion: ' . mb_substr($e->getMessage(), 0, 180),
            'ip'            => $ip,
            'user_agent'    => $userAgent,
            'usuario_id'    => null,
            'rol'           => null,
        ]);
        $error = 'Ocurrió un error inesperado. Intente nuevamente.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Iniciar Sesión</title>

    <!-- Framework visual del proyecto -->
    <link rel="preconnect" href="https://framework.impulsagroup.com" />
    <link rel="stylesheet" href="https://framework.impulsagroup.com/assets/css/framework.css" />

    <!-- Iconos -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />

    <style>
        :root {
            --sve-primary: #673ab7;
            --sve-primary-600: #5e35b1;
            --sve-gray-50: #f5f5f5;
            --sve-gray-100: #f3f4f6;
            --sve-gray-200: #e5e7eb;
            --sve-gray-500: #6b7280;
            --sve-gray-700: #374151;
            --sve-success: #22c55e;
        }

        html,
        body {
            height: 100%;
        }

        body {
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji";
            background-color: var(--sve-gray-50);
            margin: 0;
            display: grid;
            place-items: center;
        }

        .login-container {
            position: relative;
            background: #fff;
            padding: 28px;
            border-radius: 12px;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.08);
            width: min(100%, 420px);
        }

        .login-container h1 {
            margin: 0 0 18px;
            text-align: center;
            color: var(--sve-primary);
            font-size: 22px;
            font-weight: 700;
        }

        .form-group {
            margin-bottom: 14px;
        }

        .form-group label {
            display: block;
            margin: 0 0 6px;
            color: #555;
            font-size: 14px;
        }

        .input,
        .button {
            width: 100%;
            padding: 10px 12px;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 14px;
        }

        .input {
            border: 1px solid var(--sve-gray-200);
            background: #fff;
        }

        .input:focus {
            border-color: var(--sve-primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(103, 58, 183, 0.12);
        }

        .button {
            border: 0;
            background: var(--sve-primary);
            color: #fff;
            cursor: pointer;
            transition: transform .02s ease, box-shadow .2s ease, background-color .2s ease;
        }

        .button:hover {
            background: var(--sve-primary-600);
        }

        .button:active {
            transform: translateY(1px);
        }

        .error {
            color: #b91c1c;
            margin-bottom: 10px;
            text-align: center;
            font-size: 14px;
        }

        /* Password wrapper */
        .password-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-container .input {
            padding-right: 44px;
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--sve-primary);
            font-size: 22px;
            cursor: pointer;
            user-select: none;
        }

        .menu-button {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 36px;
            height: 36px;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: var(--sve-gray-700);
            display: grid;
            place-items: center;
            cursor: pointer;
        }

        .menu-button:hover {
            background: var(--sve-gray-100);
        }

        .menu {
            position: absolute;
            top: 48px;
            right: 10px;
            background: #fff;
            border: 1px solid var(--sve-gray-200);
            border-radius: 10px;
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.12);
            padding: 6px;
            min-width: 180px;
            z-index: 5;
        }

        .menu.hidden {
            display: none;
        }

        .menu-item {
            width: 100%;
            border: 0;
            background: transparent;
            text-align: left;
            padding: 10px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            color: var(--sve-gray-700);
        }

        .menu-item:hover {
            background: var(--sve-gray-100);
        }

    </style>
</head>

<body>
    <div class="login-container">
        <button class="menu-button" id="menu-button" type="button" aria-haspopup="true" aria-expanded="false" aria-controls="login-menu" title="Opciones">
            <span class="material-icons" aria-hidden="true">more_vert</span>
        </button>
        <div class="menu hidden" id="login-menu" role="menu" aria-labelledby="menu-button">
            <button class="menu-item" id="clear-site-cache" type="button" role="menuitem">Borrar cache</button>
        </div>

        <h1>Iniciar Sesión</h1>

        <?php if ($error !== ''): ?>
            <div class="error"><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form action="" method="POST" novalidate autocomplete="on">
            <div class="form-group">
                <label for="usuario">Usuario</label>
                <input class="input" type="text" name="usuario" id="usuario" required autocomplete="username" />
            </div>

            <div class="form-group">
                <label for="contrasena">Contraseña</label>
                <div class="password-container">
                    <input class="input" type="password" name="contrasena" id="contrasena" required autocomplete="current-password" />
                    <span class="material-icons toggle-password" id="toggle-password" title="Mostrar/Ocultar contraseña" aria-controls="contrasena" aria-pressed="false">visibility</span>
                </div>
            </div>

            <div class="form-group" style="display:flex; gap:8px; align-items:center; justify-content:space-between">
                <button class="button" type="submit">INGRESAR</button>
            </div>
        </form>
    </div>

    <!-- Spinner Global (si existe) -->
    <script src="views/partials/spinner-global.js"></script>
    <!-- Framework JS del proyecto -->
    <script src="https://framework.impulsagroup.com/assets/javascript/framework.js" defer></script>

    <script>
        // =========================================================
        // Módulo: Toggle de contraseña (accesible)
        // =========================================================
        (function() {
            const toggle = document.getElementById('toggle-password');
            const field = document.getElementById('contrasena');
            if (!toggle || !field) return;

            function setState(show) {
                field.type = show ? 'text' : 'password';
                toggle.textContent = show ? 'visibility_off' : 'visibility';
                toggle.setAttribute('aria-pressed', show ? 'true' : 'false');
            }
            toggle.addEventListener('click', () => {
                const show = field.type === 'password';
                setState(show);
            });
        })();

        // =========================================================
        // Módulo: Menú y borrado de cache/almacenamiento del sitio
        // =========================================================
        (function() {
            const menuButton = document.getElementById('menu-button');
            const menu = document.getElementById('login-menu');
            const clearButton = document.getElementById('clear-site-cache');

            if (!menuButton || !menu || !clearButton) return;

            function closeMenu() {
                menu.classList.add('hidden');
                menuButton.setAttribute('aria-expanded', 'false');
            }

            function toggleMenu() {
                const isOpen = !menu.classList.contains('hidden');
                if (isOpen) {
                    closeMenu();
                } else {
                    menu.classList.remove('hidden');
                    menuButton.setAttribute('aria-expanded', 'true');
                }
            }

            menuButton.addEventListener('click', (event) => {
                event.stopPropagation();
                toggleMenu();
            });

            document.addEventListener('click', () => {
                closeMenu();
            });

            async function clearSiteData() {
                try {
                    // Cache Storage
                    if ('caches' in window) {
                        const keys = await caches.keys();
                        await Promise.all(keys.map((key) => caches.delete(key)));
                    }

                    // Service Workers
                    if ('serviceWorker' in navigator) {
                        const regs = await navigator.serviceWorker.getRegistrations();
                        await Promise.all(regs.map((reg) => reg.unregister()));
                    }

                    // Web Storage
                    try {
                        localStorage.clear();
                        sessionStorage.clear();
                    } catch {}

                    // IndexedDB
                    if (indexedDB && indexedDB.databases) {
                        const dbs = await indexedDB.databases();
                        await Promise.all(dbs.map((db) => {
                            if (!db.name) return Promise.resolve();
                            return new Promise((resolve) => {
                                const req = indexedDB.deleteDatabase(db.name);
                                req.onsuccess = () => resolve();
                                req.onerror = () => resolve();
                                req.onblocked = () => resolve();
                            });
                        }));
                    }

                    // Cookies del sitio
                    document.cookie.split(';').forEach((cookie) => {
                        const name = cookie.split('=')[0].trim();
                        if (!name) return;
                        document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/`;
                    });

                    alert('Se limpió el almacenamiento del sitio. Es posible que debas recargar la página.');
                } catch (err) {
                    console.error('[SVE] Error limpiando cache del sitio:', err);
                    alert('No se pudo limpiar el almacenamiento del sitio.');
                }
            }

            clearButton.addEventListener('click', async (event) => {
                event.preventDefault();
                closeMenu();
                const ok = confirm('Esto eliminará datos offline del sitio (cache, service workers, almacenamiento). ¿Continuar?');
                if (!ok) return;
                await clearSiteData();
            });
        })();

        // (Opcional para debug): imprimir sesión/cierre en consola
        // =========================================================
        <?php if (!empty($_SESSION)): ?>
                (function() {
                    try {
                        const sessionData = <?= json_encode($_SESSION, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
                        console.log('[SVE] Datos de sesión:', sessionData);
                    } catch {}
                })();
        <?php endif; ?>

        <?php if (!empty($cierre_info)): ?>
                (function() {
                    try {
                        const cierreData = <?= json_encode($cierre_info, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
                        console.log('[SVE] Cierre operativos:', cierreData);
                    } catch {}
                })();
        <?php endif; ?>
    </script>
</body>

</html>
