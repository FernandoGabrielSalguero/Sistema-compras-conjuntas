<?php

// Mostrar errores en pantalla (útil en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión y proteger acceso
session_start();

if (!isset($_SESSION['cuit'])) {
    die("⚠️ Acceso denegado. No has iniciado sesión.");
}

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'sve') {
    die("🚫 Acceso restringido: esta página es solo para usuarios SVE.");
}

// Datos del usuario en sesión
$nombre = $_SESSION['nombre'] ?? 'Sin nombre';
$correo = $_SESSION['correo'] ?? 'Sin correo';
$cuit = $_SESSION['cuit'] ?? 'Sin CUIT';
$telefono = $_SESSION['telefono'] ?? 'Sin teléfono';
$observaciones = $_SESSION['observaciones'] ?? 'Sin observaciones';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard SVE</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef2f3;
            padding: 40px;
        }
        .dashboard {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
            max-width: 700px;
            margin: auto;
        }
        h1 {
            color: #673ab7;
            margin-bottom: 20px;
        }
        .info p {
            margin: 8px 0;
            font-size: 16px;
        }
        .logout {
            margin-top: 20px;
        }
        .logout a {
            color: red;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <h1>Bienvenido al Dashboard SVE</h1>
        <div class="info">
            <p><strong>Nombre:</strong> <?= htmlspecialchars($nombre) ?></p>
            <p><strong>Correo:</strong> <?= htmlspecialchars($correo) ?></p>
            <p><strong>CUIT:</strong> <?= htmlspecialchars($cuit) ?></p>
            <p><strong>Teléfono:</strong> <?= htmlspecialchars($telefono) ?></p>
            <p><strong>Observaciones:</strong> <?= htmlspecialchars($observaciones) ?></p>
        </div>

        <div class="logout">
            <a href="/controllers/logout.php">Cerrar sesión</a>
        </div>
    </div>
</body>
</html>
