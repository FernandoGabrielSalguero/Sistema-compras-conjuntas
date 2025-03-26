<?php

// Cargar variables de entorno manualmente desde .env
$envPath = __DIR__ . '/../.env';

if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        [$name, $value] = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value, '"');
    }
}

$host = $_ENV['DB_HOST'];
$dbname = $_ENV['DB_NAME'];
$user = $_ENV['DB_USER'];
$pass = $_ENV['DB_PASS'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cuit = $_POST['cuit'];
    $contrasena = $_POST['contrasena'];

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE cuit = :cuit");
    $stmt->bindParam(':cuit', $cuit);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($contrasena, $usuario['contrasena'])) {
        if ($usuario['permiso_ingreso'] === 'Habilitado') {
            session_start();
            $_SESSION['cuit'] = $cuit;
            $_SESSION['rol'] = $usuario['rol'];

            switch ($usuario['rol']) {
                case 'Usuario SVE':
                    header('Location: ../views/sve/dashboard.php');
                    break;
                case 'Usuario Productor':
                    header('Location: ../views/productor/dashboard.php');
                    break;
                case 'Usuario Cooperativa':
                    header('Location: ../views/cooperativa/dashboard.php');
                    break;
                default:
                    echo "Rol no reconocido.";
                    break;
            }
            exit();
        } else {
            echo "Acceso denegado: el usuario no está habilitado.";
        }
    } else {
        echo "Credenciales inválidas.";
    }
}

?>
