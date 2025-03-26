<?php
session_start();

// Si el usuario ya está logueado, redirigirlo a su dashboard correspondiente
if (isset($_SESSION['rol'])) {
    switch ($_SESSION['rol']) {
        case 'Admin':
            header('Location: dashboard_admin.php');
            break;
        case 'Usuario':
            header('Location: dashboard_usuario.php');
            break;
        default:
            header('Location: dashboard_general.php');
            break;
    }
    exit();
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Sistema de Compras SVE</title>
    <link rel="stylesheet" href="styles.css"> <!-- Si tienes un archivo CSS -->
</head>
<body>
    <h2>Login - Sistema de Compras SVE</h2>
    <form action="controllers/auth.php" method="POST">
        <label for="cuit">CUIT:</label>
        <input type="text" id="cuit" name="cuit" required><br><br>

        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required><br><br>

        <input type="submit" value="Ingresar">
    </form>
</body>
</html>