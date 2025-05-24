<?php
// Mostrar errores en pantalla (útil en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../models/sve_MercadoDigitalModel.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'sve') {
    die("Acceso denegado");
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <link rel="stylesheet" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css">

    <title>Editar Pedido</title>

</head>

<body>

<P>ESTE ES EL MODAL PARA EDITAR EL PEDIDO</P>

</body>

</html>