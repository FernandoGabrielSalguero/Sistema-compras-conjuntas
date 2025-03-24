<?php
require_once '../../controllers/auth.php';
verificarAcceso(['Productor']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Productor</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../views/partials/sidebar.php'; ?>
    <?php include '../../views/partials/header.php'; ?>
    
    <div class="container">
        <div class="card">
            <h2>Historial de Pedidos</h2>
            <p>Aquí podrás ver un listado de tus pedidos realizados.</p>
            <button class="button">Ver Pedidos</button>
        </div>
    </div>
</body>
</html>
