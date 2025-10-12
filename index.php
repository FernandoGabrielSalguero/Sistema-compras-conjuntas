<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/middleware/sessionManager.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/models/AuthModel.php';
require_once __DIR__ . '/models/AuthLogModel.php';

$error = '';
$cierre_info = null;

// Mensaje si viene por expiración (lo tenías en la versión anterior)
if (isset($_GET['expired']) && $_GET['expired'] == 1) {
    $error = "La sesión expiró por inactividad. Por favor, iniciá sesión nuevamente.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitización básica
    $usuario = isset($_POST['usuario']) ? trim((string)$_POST['usuario']) : '';
    $contrasena = isset($_POST['contrasena']) ? (string)$_POST['contrasena'] : '';

    // Contexto de auditoría
    $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

    $auth = new AuthModel($pdo);
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

            // Analizamos los operativos cerrados
            require_once __DIR__ . '/views/partials/cierre_operativos.php';
            $cierre_info = cerrarOperativosVencidos($pdo);
            $_SESSION['cierre_info'] = $cierre_info;

            // Datos combinados
            $_SESSION['usuario']   = $user['usuario'];
            $_SESSION['rol']       = $user['rol'];
            $_SESSION['nombre']    = $user['nombre'] ?? '';
            $_SESSION['correo']    = $user['correo'] ?? '';
            $_SESSION['telefono']  = $user['telefono'] ?? '';
            $_SESSION['direccion'] = $user['direccion'] ?? '';
            $_SESSION['usuario_id']        = $user['id'];
            $_SESSION['id_real']   = $user['id_real'];
            $_SESSION['cuit']      = $user['cuit'];
            $_SESSION['LAST_ACTIVITY'] = time();
            refreshSessionCookie();


            switch ($user['rol']) {
                case 'cooperativa':
                    header('Location: /views/cooperativa/coop_dashboard.php');
                    break;
                case 'productor':
                    header('Location: /views/productor/prod_dashboard.php');
                    break;
                case 'sve':
                    header('Location: /views/sve/sve_dashboard.php');
                    break;
                case 'ingeniero':
                    header('Location: /views/ingeniero/ing_dashboard.php');
                    break;
                case 'piloto_drone':
                    header('Location: /views/drone_pilot/drone_pilot_dashboard.php');
                    break;
                default:
                    header('Location: /');
                    break;
            }
            exit;
        } else {
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
            $error = "Usuario o contraseña inválidos o permiso no habilitado.";
        }
    } catch (Throwable $e) {
        // Log ERROR (excepción)
        $authLog->registrar([
            'usuario_input' => $usuario,
            'resultado'     => 'error',
            'motivo'        => 'excepcion: ' . substr($e->getMessage(), 0, 180),
            'ip'            => $ip,
            'user_agent'    => $userAgent,
            'usuario_id'    => null,
            'rol'           => null,
        ]);
        $error = "Ocurrió un error inesperado. Intente nuevamente.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .login-container h1 {
            text-align: center;
            color: #673ab7;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
        }

        .form-group input:focus {
            border-color: #673ab7;
            outline: none;
        }

        .form-group button {
            width: 100%;
            padding: 10px;
            background-color: #673ab7;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .form-group button:hover {
            background-color: #5e35b1;
        }

        .error {
            color: red;
            margin-bottom: 10px;
            text-align: center;
        }

        .password-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-container input {
            width: 100%;
            padding: 10px 40px 10px 10px;
            /* padding derecho ajustado para el ícono */
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 6px;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #673ab7;
            font-size: 22px;
            cursor: pointer;
            user-select: none;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h1>Iniciar Sesión</h1>
        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        <form action="" method="POST">

            <div class="form-group">
                <label for="usuario">Usuario:</label>
                <input type="text" name="usuario" id="usuario" required>
            </div>

            <div class="form-group">
                <label for="contrasena">Contraseña:</label>
                <div class="password-container">
                    <input type="password" name="contrasena" id="contrasena" required>
                    <span class="material-icons toggle-password" title="Mostrar/Ocultar contraseña">visibility</span>
                </div>
            </div>

            <div class="form-group" style="display:flex; gap:8px; align-items:center; justify-content:space-between">
                <button type="submit">INGRESAR</button>
                <button type="button" id="sve-cache-reset-inline" title="Restablecer versión offline" aria-label="Restablecer cache" style="width:36px;height:36px;min-width:36px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;color:#6b7280;display:inline-flex;align-items:center;justify-content:center">↺</button>
            </div>

        </form>
    </div>

    <script>
        // visualizador de contraseña
        const togglePassword = document.querySelector('.toggle-password');
        const passwordField = document.getElementById('contrasena');

        togglePassword.addEventListener('click', () => {
            const isPassword = passwordField.type === 'password';
            passwordField.type = isPassword ? 'text' : 'password';
            togglePassword.textContent = isPassword ? 'visibility_off' : 'visibility';
        });

        // imprirmir los datos de la sesion en la consola
        <?php if (!empty($_SESSION)): ?>
            const sessionData = <?= json_encode($_SESSION, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
            console.log("Datos de sesión:", sessionData);
        <?php endif; ?>

        // imprimir los operativos cerrados en la consola
        <?php if (!empty($cierre_info)): ?>
            const cierreData = <?= json_encode($cierre_info, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
            console.log("Cierre operativos:", cierreData);
        <?php endif; ?>
    </script>

    <!-- Spinner Global -->
    <script src="views/partials/spinner-global.js"></script>

    <!-- Botón reset offline (discreto) + modal -->

    <!-- Modal de confirmación para reset offline -->
    <style>
        #sve-cache-reset-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .35);
            display: none;
            z-index: 100000
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
            font-family: system-ui
        }

        #sve-cache-reset-modal .row {
            display: flex;
            gap: 8px;
            justify-content: flex-end
        }

        #sve-cache-reset-modal .btn {
            padding: 6px 10px;
            border-radius: 8px;
            cursor: pointer
        }

        #sve-cache-reset-modal .btn.cancel {
            border: 1px solid #e5e7eb;
            background: #fff
        }

        #sve-cache-reset-modal .btn.ok {
            border: 0;
            background: #7c3aed;
            color: #fff
        }
    </style>
    <div id="sve-cache-reset-overlay">
        <div id="sve-cache-reset-modal">
            <h3 style="margin:0 0 8px;font-size:16px;">¿Restablecer la versión offline?</h3>
            <p style="margin:0 0 12px;font-size:14px;line-height:1.4">
                Esto <strong>borra caches</strong>, <strong>storage</strong> y <strong>desregistra</strong> el Service Worker. Se recargará la página.
            </p>
            <div class="row">
                <button class="btn cancel" id="sve-cancel">Cancelar</button>
                <button class="btn ok" id="sve-confirm">Sí, borrar</button>
            </div>
        </div>
    </div>

    <script>
        // Fallback si offline.js no está presente
        if (!window.SVE_ClearAll) {
            window.SVE_ClearAll = async function() {
                try {
                    const keys = await caches.keys();
                    await Promise.all(keys.map(k => caches.delete(k)));
                    try {
                        localStorage.removeItem('sve_offline_cred');
                    } catch (e) {}
                    try {
                        localStorage.removeItem('sve_offline_session');
                    } catch (e) {}
                    try {
                        sessionStorage.clear();
                    } catch (e) {}
                    try {
                        if (indexedDB && indexedDB.databases) {
                            const dbs = await indexedDB.databases();
                            await Promise.all(dbs.map(db => db.name && indexedDB.deleteDatabase(db.name)));
                        }
                    } catch (e) {}
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

        (function() {
            const btn = document.getElementById('sve-cache-reset-inline');
            const overlay = document.getElementById('sve-cache-reset-overlay');
            const cancel = document.getElementById('sve-cancel');
            const confirmBtn = document.getElementById('sve-confirm');

            function openModal() {
                overlay.style.display = 'block';
            }

            function closeModal() {
                overlay.style.display = 'none';
            }
            if (btn) btn.addEventListener('click', openModal);
            overlay.addEventListener('click', e => {
                if (e.target === overlay) closeModal();
            });
            cancel.addEventListener('click', closeModal);
            confirmBtn.addEventListener('click', async () => {
                closeModal();
                await window.SVE_ClearAll();
                location.reload();
            });
        })();
    </script>

</body>

</html>