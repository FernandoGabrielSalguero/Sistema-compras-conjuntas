<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configurar duración de sesión en 20 minutos
ini_set('session.gc_maxlifetime', 1200); // 20 minutos
session_set_cookie_params([
    'lifetime' => 1200, // también 20 minutos
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

// Mensaje si viene por expiración
if (isset($_GET['expired']) && $_GET['expired'] == 1) {
    $error = "La sesión expiró por inactividad. Por favor, iniciá sesión nuevamente.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];

    $auth = new AuthModel($pdo);
    $user = $auth->login($usuario, $contrasena);

    if ($user) {

        //Analizamos los operativos cerrados
        require_once __DIR__ . '/views/partials/cierre_operativos.php';
        $cierre_info = cerrarOperativosVencidos($pdo);
        $_SESSION['cierre_info'] = $cierre_info;

        // Datos combinados
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

            <div class="form-group">
                <button type="submit">INGRESAR</button>
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
</body>

</html>