<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Compras Conjuntas SVE</title>
</head>
<body>

<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
?>

<div style="width: 300px; margin: auto; padding-top: 50px;">
    <h1>Iniciar Sesión</h1>
    <?php if (isset($_GET['error'])): ?>
        <div style="color: red; margin-bottom: 10px;">
            <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>
    <form method="POST" action="controllers/auth.php">
        <label for="cuit">CUIT:</label><br>
        <input type="text" name="cuit" required><br><br>
        <label for="contrasena">Contraseña:</label><br>
        <input type="password" name="contrasena" required><br><br>
        <button type="submit">Ingresar</button>
    </form>
</div>

</body>
</html>