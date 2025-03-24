<?php
// Activar la visualización de errores en pantalla
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Cargar el archivo .env
require_once 'vendor/autoload.php';
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
  
// Conexión a la base de datos
$host = getenv('DB_HOST');
$dbname = getenv('DB_NAME');
$username = getenv('DB_USER');
$password = getenv('DB_PASS');

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

// Si se envió el formulario de inicio de sesión
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cuit = $_POST['cuit'];
    $password = $_POST['password'];

    // Verificación de credenciales
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE cuit = :cuit AND contraseña = :password");
    $stmt->bindParam(':cuit', $cuit);
    $stmt->bindParam(':password', $password);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if ($user['permiso_ingreso'] === 'Habilitado') {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['rol'] = $user['rol'];

            // Redirigir al dashboard correspondiente
            switch ($user['rol']) {
                case 'Productor':
                    header('Location: /views/productor/dashboard.php');
                    break;
                case 'Cooperativa':
                    header('Location: /views/cooperativa/dashboard.php');
                    break;
                case 'SVE':
                    header('Location: /views/sve/dashboard.php');
                    break;
            }
            exit();
        } else {
            $error_message = "Su acceso está deshabilitado. Por favor, comuníquese al siguiente celular: 2616686062.";
        }
    } else {
        $error_message = "CUIT o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <h2>Iniciar Sesión</h2>
        <?php if (isset($error_message)) : ?>
            <div class="error"> <?= $error_message ?> </div>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="cuit">CUIT:</label>
            <input type="text" name="cuit" id="cuit" required>

            <label for="password">Contraseña:</label>
            <input type="password" name="password" id="password" required>

            <button type="submit">Iniciar Sesión</button>
        </form>
    </div>
</body>
</html>