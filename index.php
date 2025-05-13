<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/models/AuthModel.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cuit = $_POST['cuit'];
    $contrasena = $_POST['contrasena'];

    $auth = new AuthModel($pdo);
    $user = $auth->login($cuit, $contrasena);

    if ($user) {
        $_SESSION['nombre'] = $user['nombre'];
        $_SESSION['correo'] = $user['correo'];
        $_SESSION['cuit'] = $user['cuit'];
        $_SESSION['telefono'] = $user['telefono'];
        $_SESSION['observaciones'] = $user['observaciones'];
        $_SESSION['rol'] = $user['rol'];

        // Campos extras para cooperativa y $productores
        $_SESSION['id_cooperativa'] = $user['id_cooperativa'];
        $_SESSION['id_productor'] = $user['id_productor'];
        $_SESSION['direccion'] = $user['direccion'];
        $_SESSION['id_finca_asociada'] = $user['id_finca_asociada'];

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
        }
        exit;
    } else {
        $error = "CUIT, contraseña inválidos o permiso no habilitado.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
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
        }
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
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
                <label for="cuit">CUIT:</label>
                <input type="text" name="cuit" id="cuit" required>
            </div>
            <div class="form-group password-container">
                <label for="contrasena">Contraseña:</label>
                <input type="password" name="contrasena" id="contrasena" required>
            </div>
            <div class="form-group">
                <button type="submit">INGRESAR</button>
            </div>
        </form>
    </div>

    <script>
        const togglePassword = document.querySelector('.toggle-password');
        const passwordField = document.getElementById('contrasena');

        togglePassword.addEventListener('click', () => {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
        });
    </script>

    <!-- Spinner Global -->
    <script src="views/partials/spinner-global.js"></script>
</body>
</html>
