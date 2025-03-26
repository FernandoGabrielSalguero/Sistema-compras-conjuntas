<?php
session_start();

// Cargar las variables de entorno desde el archivo .env
function loadEnv() {
    $lines = file('../.env');
    $env = [];
    foreach ($lines as $line) {
        if (trim($line) === '' || str_starts_with(trim($line), '#')) continue;
        [$key, $value] = explode('=', trim($line), 2);
        $env[$key] = trim($value, '"');
    }
    return $env;
}

$env = loadEnv();

// Crear conexión a la base de datos usando los datos del archivo .env
$conn = new mysqli($env['DB_HOST'], $env['DB_USER'], $env['DB_PASS'], $env['DB_NAME']);

// Verificar la conexión
if ($conn->connect_error) {
    die('Conexión fallida: ' . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cuit = $_POST['cuit'];
    $password = $_POST['password'];

    // Consultar la base de datos
    $query = "SELECT cuit, contrasena, rol, permiso_ingreso FROM usuarios WHERE cuit = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $cuit);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verificar si la contraseña y el permiso de ingreso son válidos
        if (password_verify($password, $user['contrasena']) && $user['permiso_ingreso'] === 'Habilitado') {
            $_SESSION['cuit'] = $user['cuit'];
            $_SESSION['rol'] = $user['rol'];

            // Redirigir al usuario según su rol
            switch ($user['rol']) {
                case 'Admin':
                    header('Location: ../../../views/sve/dashboard.php');
                    break;
                case 'Usuario':
                    header('Location: ../../../views/cooperativa/dashboard.php');
                    break;
                default:
                    header('Location: ../../../views/productor/dashboard.php');
                    break;
            }
            exit();
        } else {
            echo "<script>alert('CUIT o Contraseña incorrectos, o su acceso está deshabilitado.');window.location.href='../index.php';</script>";
        }
    } else {
        echo "<script>alert('CUIT no encontrado en la base de datos.');window.location.href='../index.php';</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    header('Location: ../index.php');
    exit();
}
