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
            if ($user["contrasena"] === $password) {
                // Verificar si tiene permisos para ingresar
                if ($user["permiso_ingreso"] === "Habilitado") {
                    // Crear variables de sesión
                    $_SESSION["user_id"] = $user["id_productor"];
                    $_SESSION["user_name"] = $user["nombre"];
                    $_SESSION["user_role"] = $user["rol"];

                    // Redirigir al dashboard correspondiente
                    switch ($user["rol"]) {
                        case "Productor":
                            header("Location: views/productor/dashboard.php");
                            break;
                        case "Cooperativa":
                            header("Location: views/cooperativa/dashboard.php");
                            break;
                        case "SVE":
                            header("Location: views/sve/dashboard.php");
                            break;
                        default:
                            $error = "❌ Rol desconocido.";
                    }
                    exit();
                } else {
                    $error = "Usuario no habilitado, por favor, póngase en contacto con el administrador.";
                }
            } else {
                $error = "❌ Contraseña incorrecta.";
            }
        } else {
            $error = "❌ Usuario no encontrado.";
        }
    }
}
?>