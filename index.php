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
                    header('Location: /views/ingeniero/dashboard.php');
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
    <link rel="preload" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css" as="style">
    <link rel="stylesheet" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css">
    <script defer src="https://www.fernandosalguero.com/cdn/assets/javascript/framework.js"></script>

</head>

<body class="fs-app bg-neutral-50 min-h-screen flex items-center justify-center no-fouc">
    <div class="card shadow-lg p-6 w-full max-w-md rounded-2xl animate-fade-in">
        <h1 class="h3 text-primary-700 text-center mb-4">Iniciar Sesión</h1>

        <?php if ($error): ?>
            <div class="alert alert-danger mb-3" role="alert" aria-live="assertive">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" novalidate>
            <div class="input-group mb-3">
                <label for="usuario" class="label required">Usuario</label>
                <div class="input-icon">
                    <span class="material-icons" aria-hidden="true">person</span>
                    <input
                        class="input"
                        type="text"
                        name="usuario"
                        id="usuario"
                        required
                        autocomplete="username"
                        aria-describedby="usuario-help">
                </div>
                <small id="usuario-help" class="help-text">Ingresá tu usuario asignado.</small>
            </div>

            <div class="input-group mb-4">
                <label for="contrasena" class="label required">Contraseña</label>
                <div class="input-icon">
                    <span class="material-icons" aria-hidden="true">lock</span>
                    <input
                        class="input"
                        type="password"
                        name="contrasena"
                        id="contrasena"
                        required
                        autocomplete="current-password">
                    <button type="button"
                            class="icon-btn toggle-password"
                            aria-label="Mostrar u ocultar contraseña"
                            aria-pressed="false">
                        <span class="material-icons" aria-hidden="true">visibility</span>
                    </button>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn btn-primary w-full">INGRESAR</button>
            </div>
        </form>
    </div>

    <script>
        // Evitar FOUC simple
        document.documentElement.classList.add('js');
        window.addEventListener('DOMContentLoaded', () => {
            document.querySelector('.no-fouc')?.classList.remove('no-fouc');
        });

        // Visualizador de contraseña accesible
        (function () {
            const btn = document.querySelector('.toggle-password');
            const input = document.getElementById('contrasena');
            if (!btn || !input) return;
            btn.addEventListener('click', () => {
                const isPwd = input.type === 'password';
                input.type = isPwd ? 'text' : 'password';
                btn.setAttribute('aria-pressed', String(isPwd));
                btn.querySelector('.material-icons').textContent = isPwd ? 'visibility_off' : 'visibility';
            });
        })();

        // Logs de consola (solo si existen datos)
        <?php if (!empty($_SESSION)): ?>
            const sessionData = <?= json_encode($_SESSION, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
            console.log("Datos de sesión:", sessionData);
        <?php endif; ?>
        <?php if (!empty($cierre_info)): ?>
            const cierreData = <?= json_encode($cierre_info, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
            console.log("Cierre operativos:", cierreData);
        <?php endif; ?>
    </script>

    <!-- Spinner Global -->
    <script defer src="views/partials/spinner-global.js"></script>
</body>


</html>