<?php
session_start();

// Mostrar errores en pantalla
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Proteger la sesión
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'SVE') {
    header('Location: ../../index.php');
    exit();
}

include '../../views/partials/header.php';
include '../../views/partials/sidebar.php';

// Conexión a la base de datos
$dotenv = parse_ini_file('../../.env');
$host = $dotenv['DB_HOST'];
$dbname = $dotenv['DB_NAME'];
$username = $dotenv['DB_USER'];
$password = $dotenv['DB_PASS'];

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->query("SELECT p.id, p.fecha_compra, p.valor_total, p.estado_compra, u1.nombre AS productor, u2.nombre AS cooperativa, p.factura
                          FROM pedidos p
                          LEFT JOIN usuarios u1 ON p.id_usuario = u1.id
                          LEFT JOIN usuarios u2 ON u1.id_cooperativa_asociada = u2.id");
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos - SVE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
            overflow-y: auto;
            margin-top: 60px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f4f4f4;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .filter-bar {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>
<div class="content">
    <h1>Pedidos</h1>
    <div class="filter-bar">
        <input type="text" placeholder="Buscar por Productor">
        <input type="text" placeholder="Buscar por Cooperativa">
        <input type="date">
    </div>
    <table>
        <thead>
            <tr>
                <th>Productor</th>
                <th>Cooperativa</th>
                <th>Fecha de Compra</th>
                <th>Valor Total</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pedidos as $pedido): ?>
                <tr>
                    <td><?= $pedido['productor']; ?></td>
                    <td><?= $pedido['cooperativa']; ?></td>
                    <td><?= $pedido['fecha_compra']; ?></td>
                    <td>$<?= $pedido['valor_total']; ?></td>
                    <td>
                        <select>
                            <option value="Pedido recibido">Pedido recibido</option>
                            <option value="Pedido cancelado">Pedido cancelado</option>
                            <option value="Pedido OK pendiente de factura">Pedido OK pendiente de factura</option>
                            <option value="Pedido OK FACTURADO">Pedido OK FACTURADO</option>
                            <option value="Pedido pendiente de retito">Pedido pendiente de retito</option>
                            <option value="Pedido en camino al productor">Pedido en camino al productor</option>
                            <option value="Pedido en camino a la cooperativa.">Pedido en camino a la cooperativa.</option>
                        </select>
                    </td>
                    <td>
                        <button title="Ver Detalle"><i class="fa fa-eye"></i></button>
                        <?php if ($pedido['factura']) : ?>
                            <a href="../../uploads/facturas/<?= $pedido['factura']; ?>" target="_blank"><i class="fa fa-file-pdf"></i></a>
                        <?php endif; ?>
                        <button title="Añadir Factura"><i class="fa fa-file-upload"></i></button>
                        <button title="Eliminar Pedido"><i class="fa fa-trash"></i></button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
