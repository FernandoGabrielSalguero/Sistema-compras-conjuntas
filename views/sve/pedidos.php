<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'SVE') {
    header("Location: ../../index.php");
    exit();
}

require '../../views/partials/header.php';
require '../../views/partials/sidebar.php';

// Conexión a la base de datos
$dotenv = parse_ini_file("../../.env");
$host = $dotenv['DB_HOST'];
$dbname = $dotenv['DB_NAME'];
$username = $dotenv['DB_USER'];
$password = $dotenv['DB_PASS'];

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obtener datos de pedidos
    $query = "SELECT p.id, u.nombre AS productor, c.nombre AS cooperativa, u.rol, p.fecha_compra, p.valor_total, p.estado_compra, p.factura 
              FROM pedidos p 
              JOIN usuarios u ON p.id_usuario = u.id 
              LEFT JOIN usuarios c ON u.id_cooperativa_asociada = c.id";
    $stmt = $conn->query($query);
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedidos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #F3F4F6;
            font-family: Arial, sans-serif;
        }
        .content {
            padding: 20px;
            margin-left: 260px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 15px;
            text-align: left;
        }
        th {
            background-color: #4A90E2;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #F5F5F5;
        }
        .actions {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        .actions button {
            border: none;
            background: none;
            cursor: pointer;
        }
        .form-container {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .form-container input, select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            outline: none;
        }
        .btn {
            padding: 10px 20px;
            background-color: #4A90E2;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="content">
    <h2>Pedidos</h2>

    <div class="form-container">
        <input type="text" id="buscarProductor" placeholder="Buscar por Productor">
        <input type="text" id="buscarCooperativa" placeholder="Buscar por Cooperativa">
        <input type="date" id="fechaPedido">
        <button class="btn" onclick="buscarPedidos()">Buscar</button>
    </div>

    <table>
        <thead>
        <tr>
            <th>Productor</th>
            <th>Cooperativa</th>
            <th>Rol</th>
            <th>Fecha de Compra</th>
            <th>Valor Total</th>
            <th>Estado</th>
            <th>Factura</th>
            <th>Acciones</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($pedidos as $pedido): ?>
            <tr id="row-<?php echo $pedido['id']; ?>">
                <td><?php echo $pedido['productor']; ?></td>
                <td><?php echo $pedido['cooperativa']; ?></td>
                <td><?php echo $pedido['rol']; ?></td>
                <td><?php echo $pedido['fecha_compra']; ?></td>
                <td>$<?php echo number_format($pedido['valor_total'], 2); ?></td>
                <td>
                    <select onchange="confirmStateChange(<?php echo $pedido['id']; ?>, this.value)">
                        <?php
                        $estados = ['Pedido recibido', 'Pedido cancelado', 'Pedido OK pendiente de factura',
                            'Pedido OK FACTURADO', 'Pedido pendiente de retito', 'Pedido en camino al productor',
                            'Pedido en camino a la cooperativa.'];
                        foreach ($estados as $estado) {
                            $selected = ($pedido['estado_compra'] === $estado) ? 'selected' : '';
                            echo "<option value='$estado' $selected>$estado</option>";
                        }
                        ?>
                    </select>
                </td>
                <td>
                    <?php if ($pedido['factura']): ?>
                        <a href="../../uploads/facturas/<?php echo $pedido['factura']; ?>" target="_blank">📄 Ver</a>
                    <?php else: ?>
                        Pendiente de factura
                    <?php endif; ?>
                </td>
                <td class="actions">
                    <button onclick="viewDetail(<?php echo $pedido['id']; ?>)"><i class="fas fa-eye"></i></button>
                    <button onclick="uploadInvoice(<?php echo $pedido['id']; ?>)"><i class="fas fa-upload"></i></button>
                    <button onclick="deleteOrder(<?php echo $pedido['id']; ?>)"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    function buscarPedidos() {
        alert("Función de búsqueda en desarrollo.");
    }

    function confirmStateChange(idPedido, nuevoEstado) {
        if (confirm("¿Estás seguro de cambiar el estado del pedido?")) {
            alert("Se actualizará el estado a: " + nuevoEstado);
            // Aquí va la lógica para enviar a la base de datos
        }
    }

    function viewDetail(idPedido) {
        alert("Función de ver detalle para el pedido ID: " + idPedido);
    }

    function uploadInvoice(idPedido) {
        alert("Función de cargar factura para el pedido ID: " + idPedido);
    }

    function deleteOrder(idPedido) {
        if (confirm("¿Estás seguro que deseas eliminar este pedido?")) {
            alert("Eliminando pedido con ID: " + idPedido);
            // Aquí va la lógica para eliminar en la base de datos
        }
    }
</script>
</body>
</html>
