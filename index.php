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

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cuit = trim($_POST["cuit"]);
    $password = trim($_POST["password"]);

    if (empty($cuit) || empty($password)) {
        $error = "❌ Todos los campos son obligatorios.";
    } else {
        // Consultar el usuario en la base de datos
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE cuit = :cuit");
        $stmt->bindParam(':cuit', $cuit);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Validar contraseña
            if ($user["contraseña"] === $password) {

                // Verificar si tiene permisos para ingresar
                if ($user["permiso_ingreso"] === "Habilitado") {
                    
                    // Crear variables de sesión
                    $_SESSION["user_id"] = $user["id_productor"];
                    $_SESSION["user_name"] = $user["nombre"];
                    $_SESSION["user_role"] = $user["rol"];
                    
                    // Redirigir al dashboard correspondiente
                    if ($user["rol"] === "Productor") {
                        header("Location: views/productor/dashboard.php");
                    } elseif ($user["rol"] === "Cooperativa") {
                        header("Location: views/cooperativa/dashboard.php");
                    } elseif ($user["rol"] === "SVE") {
                        header("Location: views/sve/dashboard.php");
                    }
                    exit();
                } else {
                    $error = "❌ No tienes permisos para ingresar. Comunícate al siguiente celular: 2616686062.";
                }
            } else {
                $error = "❌ Contraseña incorrecta.";
            }
        } else {
            $error = "❌ CUIT no encontrado.";
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
                <input type="password" name="password" id="password" required>
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
