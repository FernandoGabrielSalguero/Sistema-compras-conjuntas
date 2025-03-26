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
    die("❌ Error de conexión a la base de datos: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cuit = trim(strval($_POST['cuit'] ?? ''));
    $contrasena = trim(strval($_POST['contrasena'] ?? ''));

    if (empty($cuit) || empty($contrasena)) {
        $error = "❌ CUIT y/o contraseña no proporcionados.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE cuit = :cuit LIMIT 1");
        $stmt->execute([':cuit' => $cuit]);

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            // Convertimos ambas contraseñas a texto plano y eliminamos espacios en blanco
            if (trim($usuario['contrasena']) !== $contrasena) {
                $error = "❌ CUIT o contraseña incorrectos.";
            } elseif ($usuario['permiso_ingreso'] !== 'Habilitado') {
                $error = "❌ Su acceso está restringido. Contacte con soporte.";
            } else {
                $_SESSION['usuario'] = $usuario;

                switch ($usuario['rol']) {
                    case 'productor':
                        header('Location: productor_dashboard.php');
                        break;
                    case 'cooperativa':
                        header('Location: cooperativa_dashboard.php');
                        break;
                    case 'SVE':
                        header('Location: sve_dashboard.php');
                        break;
                    default:
                        $error = "❌ Rol desconocido. Contacte con soporte.";
                }

                exit();
            }
        } else {
            $error = "❌ CUIT o contraseña incorrectos.";
        }
    }
}
?>