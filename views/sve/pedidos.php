<?php
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'SVE') {
    header("Location: ../../index.php");
    exit();
}

require_once '../../config/database.php';

// Obtener todos los pedidos de la base de datos
try {
    $stmt = $conn->query("SELECT p.id, u.nombre AS productor, p.estado_compra, p.fecha_compra, p.valor_total, p.factura 
                          FROM pedidos p 
                          JOIN usuarios u ON p.id_usuario = u.id");
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener los pedidos: " . $e->getMessage());
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
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .table-container {
            margin: 80px auto;
            padding: 20px;
            max-width: 1200px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table th, table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #f4f4f4;
            color: #333;
        }

        table td {
            background-color: #fff;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .actions i {
            cursor: pointer;
            padding: 5px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .actions i:hover {
            background-color: #e0e0e0;
        }
    </style>
</head>
<body>
<?php include '../../views/partials/header.php'; ?>
<?php include '../../views/partials/sidebar.php'; ?>

<div class="table-container">
    <h2>Pedidos</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Productor</th>
                <th>Estado</th>
                <th>Fecha de Compra</th>
                <th>Valor Total</th>
                <th>Factura</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pedidos as $pedido): ?>
                <tr>
                    <td><?php echo $pedido['id']; ?></td>
                    <td><?php echo $pedido['productor']; ?></td>
                    <td><?php echo $pedido['estado_compra']; ?></td>
                    <td><?php echo $pedido['fecha_compra']; ?></td>
                    <td><?php echo $pedido['valor_total']; ?></td>
                    <td><?php echo $pedido['factura'] ? '📄 Factura Adjunta' : '❌ No adjunta'; ?></td>
                    <td>
                        <div class="actions">
                            <i class="fas fa-eye" title="Ver Pedido"></i>
                            <i class="fas fa-edit" title="Editar Pedido"></i>
                            <i class="fas fa-upload" title="Subir Factura"></i>
                            <i class="fas fa-trash" title="Eliminar Pedido"></i>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
