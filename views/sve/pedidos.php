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
</head>
<body>
<div class="content">
    <h2>Pedidos</h2>

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
                    <select onchange="updateStatus(<?php echo $pedido['id']; ?>, this.value)">
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
                <td>
                    <button onclick="deleteOrder(<?php echo $pedido['id']; ?>)"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    function updateStatus(idPedido, nuevoEstado) {
        fetch('actualizar_estado.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `idPedido=${idPedido}&nuevoEstado=${nuevoEstado}`
        }).then(response => response.text())
          .then(data => alert(data));
    }

    function deleteOrder(idPedido) {
        if (confirm("¿Estás seguro de eliminar este pedido?")) {
            fetch('eliminar_pedido.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `idPedido=${idPedido}`
            }).then(response => response.text())
              .then(data => alert(data));
        }
    }
</script>
</body>
</html>
