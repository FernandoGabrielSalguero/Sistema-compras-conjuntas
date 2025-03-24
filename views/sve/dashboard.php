<?php
require_once '../../controllers/auth.php';
verificarAcceso(['SVE']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard SVE</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Bienvenido, <?= $_SESSION["user_name"]; ?> (SVE)</h1>
        
        <div class="card">
            <h2>Estadísticas Generales</h2>
            <p>Visualiza estadísticas de la plataforma y gestiona el sistema.</p>
            <button class="button">Ver Estadísticas</button>
        </div>
    </div>
</body>
</html>
