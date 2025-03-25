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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/5.3.45/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <style>
        body { background-color: #F3F4F6; font-family: Arial, sans-serif; }
        .content { padding: 20px; margin-left: 260px; }
        table { width: 100%; margin-top: 20px; background: white; border-radius: 8px; overflow: hidden; }
        th, td { padding: 10px; text-align: left; }
        th { background-color: #4A90E2; color: white; }
        tr:nth-child(even) { background-color: #F5F5F5; }
        .actions { display: flex; gap: 10px; justify-content: center; }
        .actions button { background: none; cursor: pointer; font-size: 1.2em; padding: 5px; color: #4A90E2; }
        .actions button:hover { transform: scale(1.1); color: #673AB7; }
        .btn { padding: 10px 20px; background-color: #4A90E2; color: white; border: none; border-radius: 4px; cursor: pointer; }
    </style>
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
            <tr>
                <td><?php echo $pedido['productor']; ?></td>
                <td><?php echo $pedido['cooperativa']; ?></td>
                <td><?php echo $pedido['rol']; ?></td>
                <td><?php echo $pedido['fecha_compra']; ?></td>
                <td>$<?php echo number_format($pedido['valor_total'], 2); ?></td>
                <td>
                    <select>
                        <option>Pedido recibido</option>
                        <option>Pedido cancelado</option>
                        <option>Pedido OK pendiente de factura</option>
                        <option>Pedido OK FACTURADO</option>
                        <option>Pedido pendiente de retito</option>
                        <option>Pedido en camino al productor</option>
                        <option>Pedido en camino a la cooperativa.</option>
                    </select>
                </td>
                <td><?php echo $pedido['factura'] ?? 'Pendiente de factura'; ?></td>
                <td class="actions">
                    <button><i class="mdi mdi-eye"></i></button>
                    <button><i class="mdi mdi-upload"></i></button>
                    <button><i class="mdi mdi-delete"></i></button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    function showSnackbar(message) {
        const snackbar = document.createElement('div');
        snackbar.className = 'snackbar show';
        snackbar.innerText = message;
        document.body.appendChild(snackbar);
        setTimeout(() => snackbar.remove(), 10000);
    }
</script>

</body>
</html>
