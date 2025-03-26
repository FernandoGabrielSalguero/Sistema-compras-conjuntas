<?php
// auth.php - Controlador de autenticación

session_start();

// Cargar las variables del archivo .env manualmente
$env_path = __DIR__ . '/../.env';
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
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Error de conexión a la base de datos: " . $e->getMessage());
}

// Obtener datos del formulario
$cuit = $_POST['cuit'] ?? '';
$password = $_POST['password'] ?? '';

if ($cuit === '' || $password === '') {
    header('Location: /index.php?error=Debe ingresar CUIT y contraseña.');
    exit();
}

// Consultar la base de datos
$stmt = $pdo->prepare("SELECT id, rol, password, permiso_ingreso FROM usuarios WHERE cuit = :cuit LIMIT 1");
$stmt->execute([':cuit' => $cuit]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    if ($user['permiso_ingreso'] === 'Habilitado') {
        // Inicio de sesión exitoso
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['rol'];
        header('Location: /views/' . $user['rol'] . '/dashboard.php');
        exit();
    } else {
        header('Location: /index.php?error=Su acceso está deshabilitado.');
        exit();
    }
} else {
    header('Location: /index.php?error=CUIT o contraseña incorrectos.');
    exit();
}
