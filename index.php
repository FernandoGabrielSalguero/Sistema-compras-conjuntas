<?php
// Activar la visualización de errores en pantalla
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Cargar las variables del archivo .env manualmente
$env_path = __DIR__ . '/.env';
if (file_exists($env_path)) {
    $dotenv = parse_ini_file($env_path);
} else {
    die("❌ Error: El archivo .env no se encuentra en la carpeta del proyecto.");
}

// Conexión a la base de datos
$host = $dotenv['DB_HOST'];
$dbname = $dotenv['DB_NAME'];
$username = $dotenv['DB_USER'];
$password = $dotenv['DB_PASS'];

$error = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $error = "❌ Error de conexión a la base de datos: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cuit = trim(strval($_POST['cuit'] ?? ''));
    $contrasena = trim(strval($_POST['contrasena'] ?? ''));

    if (empty($cuit) || empty($contrasena)) {
        $error = "❌ CUIT y/o contraseña no proporcionados.";
    } elseif (!isset($error) || $error === "") {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE cuit = :cuit LIMIT 1");
        $stmt->execute([':cuit' => $cuit]);

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            if (trim($usuario['contrasena']) !== $contrasena) {
                $error = "❌ CUIT o contraseña incorrectos.";
            } elseif ($usuario['permiso_ingreso'] !== 'Habilitado') {
                $error = "❌ Su acceso está restringido. Contacte con soporte.";
            } else {
                $_SESSION['usuario'] = $usuario;

                switch ($usuario['rol']) {
                    case 'productor':
                        header('Location: ./views/productor/productor_dashboard.php');
                        exit();
                    case 'cooperativa':
                        header('Location: views\cooperativa\cooperativa_dashboard.php');
                        exit();
                    case 'SVE':
                        header('Location: views\sve\sve_dashboard.php');
                        exit();
                    default:
                        $error = "❌ Rol desconocido. Contacte con soporte.";
                }
            }
        } else {
            $error = "❌ CUIT o contraseña incorrectos.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Compra Conjunta SVE</title>
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
                <label for="password">Contraseña:</label>
                <input type="password" name="contrasena" id="contrasena" required>
                <span class="toggle-password">👁️</span>
            </div>
            <div class="form-group">
                <button type="submit">Iniciar Sesión</button>
            </div>
        </form>
    </div>

    <script>
        const togglePassword = document.querySelector('.toggle-password');
        const passwordField = document.getElementById('password');

        togglePassword.addEventListener('click', () => {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            togglePassword.textContent = type === 'password' ? '👁️' : '🙈';
        });
    </script>
</body>
</html>
