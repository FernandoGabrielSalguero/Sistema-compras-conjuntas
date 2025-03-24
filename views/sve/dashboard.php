<?php
require_once '../../controllers/auth.php';
verificarAcceso(['SVE']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard SVE</title>
</head>
<body>
    <h1>Bienvenido, <?= $_SESSION["user_name"]; ?> (SVE)</h1>
    <p>Este es el dashboard principal para el usuario administrador SVE.</p>
</body>
</html>
