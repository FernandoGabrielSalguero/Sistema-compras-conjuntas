<?php
require_once '../../controllers/auth.php';
verificarAcceso(['Cooperativa']);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Cooperativa</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>

    <?php include '../../views/partials/sidebar.php'; ?>
    <?php include '../../views/partials/header.php'; ?>

    <div class="container">
        <h1>Bienvenido, <?= $_SESSION["user_name"]; ?> (Cooperativa)</h1>

        <div class="card">
            <h2>Pedidos de Productores Asociados</h2>
            <p>Aquí podrás ver los pedidos de los productores que pertenecen a tu cooperativa.</p>
            <button class="button">Ver Pedidos</button>
        </div>
    </div>
</body>

</html>