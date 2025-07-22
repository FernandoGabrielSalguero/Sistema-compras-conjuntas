<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('session.gc_maxlifetime', 1200);
session_set_cookie_params([
    'lifetime' => 1200,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/models/AuthModel.php';

$error = '';

if (isset($_GET['expired']) && $_GET['expired'] == 1) {
    $error = "La sesión expiró por inactividad. Por favor, iniciá sesión nuevamente.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];

    $auth = new AuthModel($pdo);
    $user = $auth->login($usuario, $contrasena);

    if ($user) {
        require_once __DIR__ . '/views/partials/cierre_operativos.php';
        $cierre_info = cerrarOperativosVencidos($pdo);
        $_SESSION['cierre_info'] = $cierre_info;

        $_SESSION['usuario'] = $user['usuario'];
        $_SESSION['rol'] = $user['rol'];
        $_SESSION['nombre']    = $user['nombre'] ?? '';
        $_SESSION['correo']    = $user['correo'] ?? '';
        $_SESSION['telefono']  = $user['telefono'] ?? '';
        $_SESSION['direccion'] = $user['direccion'] ?? '';
        $_SESSION['id_real'] = $user['id_real'];
        $_SESSION['cuit'] = $user['cuit'];
        $_SESSION['LAST_ACTIVITY'] = time();

        switch ($user['rol']) {
            case 'cooperativa':
                header('Location: /views/cooperativa/coop_dashboard.php');
                break;
            case 'productor':
                header('Location: /views/productor/dashboard.php');
                break;
            case 'sve':
                header('Location: /views/sve/sve_dashboard.php');
                break;
            case 'ingeniero':
                header('Location: /views/ingeniero/dashboard.php');
                break;
        }
        exit;
    } else {
        $error = "Usuario o contraseña inválidos o permiso no habilitado.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- CDN de tu framework -->
    <link rel="stylesheet" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css">
    <script src="https://www.fernandosalguero.com/cdn/assets/javascript/framework.js" defer></script>

    <!-- Google Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>

<body class="flex center middle full-height bg-light">

    <div class="card p-4 radius-12 shadow-soft max-w-400 w-full">
        <h1 class="text-center text-xl color-primary mb-3">Iniciar Sesión</h1>

        <?php if ($error): ?>
            <div class="alert alert-danger mb-2 text-center"><?= $error ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="usuario">Usuario</label>
                <input type="text" name="usuario" id="usuario" class="input" required>
            </div>

            <div class="form-group relative">
                <label for="contrasena">Contraseña</label>
                <input type="password" name="contrasena" id="contrasena" class="input pr-10" required>
                <span class="material-icons absolute top-50 right-10 translate-middle-y cursor-pointer color-primary toggle-password" title="Mostrar/Ocultar contraseña">visibility</span>
            </div>

            <button type="submit" class="btn btn-primary w-full mt-3">INGRESAR</button>
        </form>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const togglePassword = document.querySelector(".toggle-password");
            const passwordField = document.getElementById("contrasena");

            togglePassword.addEventListener("click", () => {
                const isPassword = passwordField.type === "password";
                passwordField.type = isPassword ? "text" : "password";
                togglePassword.textContent = isPassword ? "visibility_off" : "visibility";
            });

            <?php if (!empty($_SESSION)): ?>
                console.log("Datos de sesión:", <?= json_encode($_SESSION, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>);
            <?php endif; ?>

            <?php if (!empty($cierre_info)): ?>
                console.log("Cierre operativos:", <?= json_encode($cierre_info, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>);
            <?php endif; ?>
        });
    </script>

    <!-- Spinner Global (opcional) -->
    <script src="views/partials/spinner-global.js"></script>
</body>

</html>
