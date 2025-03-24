<?php
require_once '../../controllers/auth.php';
verificarAcceso(['Productor']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Productor</title>
</head>
<body>
    <h1>Bienvenido, <?= $_SESSION["user_name"]; ?> (Productor)</h1>
    <p>Este es el dashboard principal para los productores.</p>
</body>
</html>
