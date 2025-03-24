<?php
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Productor') {
    header("Location: ../../index.php");
    exit();
}

$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Productor</title>
</head>
<body>
    <?php include '../../views/partials/header.php'; ?>
    <?php include '../../views/partials/sidebar.php'; ?>

    <div class="content">
        <h1>Dashboard de Productor</h1>
        <p>Aquí se mostrarán las métricas e información relacionada a los productores.</p>
    </div>
</body>
</html>
