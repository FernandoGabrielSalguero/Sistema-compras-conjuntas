<?php
require_once '../../controllers/auth.php';
verificarAcceso(['Cooperativa']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Cooperativa</title>
</head>
<body>
    <h1>Bienvenido, <?= $_SESSION["user_name"]; ?> (Cooperativa)</h1>
    <p>Este es el dashboard principal para las cooperativas.</p>
</body>
</html>
