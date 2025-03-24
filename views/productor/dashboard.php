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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../../views/partials/sidebar.php'; ?>
    <?php include '../../views/partials/header.php'; ?>
    
    <div class="container">
        <div class="card">
            <h2>Bienvenido, <?= $_SESSION["user_name"]; ?> (Productor)</h2>
            <div class="dashboard-grid">
                <div class="card">
                    <h2>Total de Pedidos</h2>
                    <p>25 Pedidos realizados</p>
                </div>
                <div class="card">
                    <h2>Compras Pendientes</h2>
                    <p>5 Compras en proceso</p>
                </div>
                <div class="card">
                    <h2>Historial de Compras</h2>
                    <p>Revisa todas tus compras pasadas</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
