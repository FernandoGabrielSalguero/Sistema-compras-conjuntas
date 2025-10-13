<?php
// login.php
// -------------------------------------------------------------
// Página de login con PHP 8+, PDO y MySQL 5.7/8
// - Maneja: validación, auditoría, cierre de operativos vencidos,
//   seteo de sesión y redirección por rol.
// - Frontend: formulario accesible, menú "3 puntos", modo offline,
//   y modal de reseteo de caché.
// -------------------------------------------------------------

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
    <link rel="preconnect" href="https://www.fernandosalguero.com" />
    <link rel="stylesheet" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css" />

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

        /* ===== Menú 3 puntos (esquina superior derecha) ===== */
        .sve-topmenu {
            position: absolute;
            top: 10px;
            right: 10px;
        }

        .sve-menu-trigger {
            width: 36px;
            height: 36px;
            min-width: 36px;
            border-radius: 8px;
            border: 1px solid var(--sve-gray-200);
            background: #fff;
            color: var(--sve-gray-500);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 20px;
            line-height: 1;
        }

        .sve-menu-trigger[data-offline="1"]::after {
            content: "";
            position: absolute;
            top: 6px;
            right: 6px;
            width: 8px;
            height: 8px;
            border-radius: 9999px;
            background: var(--sve-success);
            box-shadow: 0 0 0 2px #fff;
        }

        .sve-menu {
            position: absolute;
            right: 0;
            margin-top: 6px;
            background: #fff;
            border: 1px solid var(--sve-gray-200);
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .12);
            min-width: 260px;
            padding: 6px;
            display: none;
            z-index: 10;
        }

        .sve-menu.open {
            display: block;
        }

        .sve-menu-item {
            width: 100%;
            text-align: left;
            padding: 10px 12px;
            border-radius: 8px;
            border: 0;
            background: #fff;
            color: #111827;
            cursor: pointer;
            font-size: 14px;
        }

        .sve-menu-item:hover {
            background: var(--sve-gray-100);
        }

        /* Estado activo visual para el item de offline */
        #sve-offline-enable-inline[data-active="1"],
        #sve-offline-enable-inline[aria-pressed="true"] {
            background: var(--sve-success) !important;
            color: #fff !important;
            border-color: var(--sve-success) !important;
        }

        /* Modal de reset offline */
        #sve-cache-reset-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .35);
            display: none;
            z-index: 100000;
        }

        #sve-cache-reset-modal {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            max-width: 360px;
            width: 92%;
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .25);
            font-family: system-ui;
        }

        #sve-cache-reset-modal .row {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }

        #sve-cache-reset-modal .btn {
            padding: 6px 10px;
            border-radius: 8px;
            cursor: pointer;
        }

        #sve-cache-reset-modal .btn.cancel {
            border: 1px solid var(--sve-gray-200);
            background: #fff;
        }

        #sve-cache-reset-modal .btn.ok {
            border: 0;
            background: var(--sve-primary);
            color: #fff;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <!-- Menú de opciones (3 puntos) -->
        <div class="sve-topmenu">
            <button type="button" class="sve-menu-trigger" id="sve-menu-trigger" aria-haspopup="true" aria-expanded="false" title="Más opciones">⋮</button>
            <div class="sve-menu" id="sve-menu" role="menu" aria-hidden="true" inert>
                <button type="button" role="menuitem" id="sve-offline-enable-inline" title="Activar acceso sin conexión" aria-label="Activar acceso sin conexión" class="sve-menu-item">
                    ⚡ Activar acceso sin conexión
                </button>
                <button type="button" role="menuitem" id="sve-cache-reset-inline" title="Restablecer versión offline" aria-label="Restablecer versión offline" class="sve-menu-item">
                    ↺ Restablecer versión offline
                </button>
            </div>
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

    <!-- Modal reset offline -->
    <div id="sve-cache-reset-overlay" aria-hidden="true">
        <div id="sve-cache-reset-modal" role="dialog" aria-modal="true" aria-labelledby="reset-offline-title">
            <h3 id="reset-offline-title" style="margin:0 0 8px;font-size:16px;">¿Restablecer la versión offline?</h3>
            <p style="margin:0 0 12px;font-size:14px;line-height:1.4">
                Esto <strong>borra caches</strong>, <strong>storage</strong> y <strong>desregistra</strong> el Service Worker. Se recargará la página.
            </p>
            <div class="row">
                <button class="btn btn-cancelar" id="sve-cancel">Cancelar</button>
                <button class="btn ok" id="sve-confirm">Sí, borrar</button>
            </div>
        </div>
    </div>

    <!-- Spinner Global (si existe) -->
    <script src="views/partials/spinner-global.js"></script>
    <!-- Framework JS del proyecto -->
    <script src="https://www.fernandosalguero.com/cdn/assets/javascript/framework.js" defer></script>

    <script>
        // =========================================================
        // Utilidades de limpieza offline (fallback si offline.js no está)
        // =========================================================
        if (!window.SVE_ClearAll) {
            window.SVE_ClearAll = async function() {
                try {
                    // Caches
                    if (window.caches?.keys) {
                        const keys = await caches.keys();
                        await Promise.all(keys.map(k => caches.delete(k)));
                    }
                    // Storages propios
                    try {
                        localStorage.removeItem('sve_offline_cred');
                    } catch {}
                    try {
                        localStorage.removeItem('sve_offline_session');
                    } catch {}
                    try {
                        sessionStorage.clear();
                    } catch {}
                    // IndexedDB
                    try {
                        if (indexedDB && indexedDB.databases) {
                            const dbs = await indexedDB.databases();
                            await Promise.all(dbs.map(db => db.name && indexedDB.deleteDatabase(db.name)));
                        }
                    } catch {}
                    // Service Workers
                    if ('serviceWorker' in navigator) {
                        const regs = await navigator.serviceWorker.getRegistrations();
                        await Promise.all(regs.map(r => r.unregister()));
                    }
                    console.log('[SVE] Limpieza completa ejecutada');
                } catch (e) {
                    console.warn('[SVE] Error limpiando', e);
                }
            }
        }

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
        // Módulo: Modal de reset offline
        // =========================================================
        (function() {
            const btn = document.getElementById('sve-cache-reset-inline');
            const overlay = document.getElementById('sve-cache-reset-overlay');
            const cancel = document.getElementById('sve-cancel');
            const confirm = document.getElementById('sve-confirm');

            if (!btn || !overlay || !cancel || !confirm) return;

            function openModal() {
                overlay.style.display = 'block';
                overlay.setAttribute('aria-hidden', 'false');
            }

            function closeModal() {
                overlay.style.display = 'none';
                overlay.setAttribute('aria-hidden', 'true');
            }

            btn.addEventListener('click', openModal);
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) closeModal();
            });
            cancel.addEventListener('click', closeModal);
            confirm.addEventListener('click', async () => {
                closeModal();
                await window.SVE_ClearAll();
                location.reload();
            });
        })();

        // =========================================================
        // Módulo: Menú "3 puntos" + estado Offline
        // - Requisitos:
        //   - Cuando el modo offline esté activo, el item muestra
        //     "⚡ Acceso sin conección activado" (pedido literal).
        //   - Indicador verde en el trigger si activo.
        //   - Manejo de foco/esc para accesibilidad.
        // =========================================================
        // =========================================================
        // Módulo: Menú "3 puntos" + estado Offline
        // - Toggle real del modo offline:
        //   * Persiste en localStorage: 'sve_offline_cred'
        //   * Setea/remueve data-active en el botón
        //   * Actualiza texto y badge del trigger
        // - Compatible con offline.js vía MutationObserver.
        // =========================================================
        (function() {
            const trigger = document.getElementById('sve-menu-trigger');
            const menu = document.getElementById('sve-menu');
            const enableItem = document.getElementById('sve-offline-enable-inline');

            if (!trigger || !menu || !enableItem) return;

            function openMenu() {
                menu.classList.add('open');
                trigger.setAttribute('aria-expanded', 'true');
                menu.setAttribute('aria-hidden', 'false');
                menu.removeAttribute('inert');
                const firstItem = menu.querySelector('.sve-menu-item');
                if (firstItem) firstItem.focus();
            }

            function closeMenu() {
                if (menu.contains(document.activeElement)) trigger.focus();
                menu.classList.remove('open');
                trigger.setAttribute('aria-expanded', 'false');
                menu.setAttribute('aria-hidden', 'true');
                menu.setAttribute('inert', '');
            }

            function toggleMenu() {
                menu.classList.contains('open') ? closeMenu() : openMenu();
            }

            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                toggleMenu();
            });
            document.addEventListener('click', (e) => {
                if (!menu.contains(e.target) && e.target !== trigger) closeMenu();
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') closeMenu();
            });
            menu.addEventListener('click', (e) => {
                const item = e.target.closest('.sve-menu-item');
                if (item) {
                    item.blur();
                    closeMenu();
                }
            });

            // ---------- Estado "offline activo" en UI ----------
            function reflectActiveState(on) {
                enableItem.textContent = on ?
                    '⚡ Acceso sin conección activado' :
                    '⚡ Activar acceso sin conexión';

                if (on) {
                    enableItem.setAttribute('data-active', '1');
                    enableItem.setAttribute('aria-pressed', 'true');
                    trigger.setAttribute('data-offline', '1');
                } else {
                    enableItem.removeAttribute('data-active');
                    enableItem.setAttribute('aria-pressed', 'false');
                    trigger.removeAttribute('data-offline');
                }
            }

            // Valor inicial desde localStorage
            reflectActiveState(!!localStorage.getItem('sve_offline_cred'));

            // Toggle manual desde el item del menú (fallback si no está offline.js)
            enableItem.addEventListener('click', (e) => {
                // Evita que el handler del menú intercepte antes de togglear
                e.preventDefault();
                e.stopPropagation();

                const isActive = enableItem.getAttribute('data-active') === '1' || !!localStorage.getItem('sve_offline_cred');

                if (isActive) {
                    // Desactivar offline
                    try {
                        localStorage.removeItem('sve_offline_cred');
                    } catch {}
                    enableItem.removeAttribute('data-active');
                    reflectActiveState(false);
                } else {
                    // Activar offline
                    try {
                        localStorage.setItem('sve_offline_cred', '1');
                    } catch {}
                    enableItem.setAttribute('data-active', '1');
                    reflectActiveState(true);
                }
            });

            // Si offline.js modifica data-active, reflejar cambio
            const obs = new MutationObserver(() => {
                const on = enableItem.getAttribute('data-active') === '1';
                // Sincroniza también con localStorage si difiere
                const lsOn = !!localStorage.getItem('sve_offline_cred');
                if (on && !lsOn) {
                    try {
                        localStorage.setItem('sve_offline_cred', '1');
                    } catch {}
                }
                if (!on && lsOn) {
                    try {
                        localStorage.removeItem('sve_offline_cred');
                    } catch {}
                }
                reflectActiveState(on);
            });
            obs.observe(enableItem, {
                attributes: true,
                attributeFilter: ['data-active']
            });
        })();

        /* Registro opcional de Service Worker al activar offline (si existe sw.js) */
        (function() {
            if (!('serviceWorker' in navigator)) return;
            const enableItem = document.getElementById('sve-offline-enable-inline');
            if (!enableItem) return;

            enableItem.addEventListener('click', async () => {
                const active = enableItem.getAttribute('data-active') === '1';
                if (active) {
                    try {
                        await navigator.serviceWorker.register('/sw.js');
                    } catch (e) {
                        console.warn('[SVE] No se pudo registrar sw.js (opcional).', e);
                    }
                }
            });
        })();

        // =========================================================
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