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
    <script src="../../assets/js/sidebar.js"></script>
</head>
<body>
    <?php include '../../views/partials/sidebar.php'; ?>
    <?php include '../../views/partials/header.php'; ?>

    <div class="content">
        <div class="card">
            <h2>Dashboard del Productor</h2>
            <p>Bienvenido, <?= $_SESSION["user_name"]; ?></p>
        </div>
    </div>
</body>
</html>
