<?php
session_start();

// Proteger la sesión
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'SVE') {
    header("Location: ../../index.php");
    exit();
}

$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';

// Conexión a la base de datos
$dotenv = parse_ini_file("../../.env");
$host = $dotenv['DB_HOST'];
$dbname = $dotenv['DB_NAME'];
$username = $dotenv['DB_USER'];
$password = $dotenv['DB_PASS'];

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consultar pedidos
    $stmt = $conn->query("SELECT * FROM pedidos");
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
        body { margin: 0; padding: 0; font-family: Arial, sans-serif; }
        .content { margin-left: 260px; padding: 20px; margin-top: 60px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f4f4f4; }
        tr:hover { background-color: #f1f1f1; }
    </style>
</head>
<body>
    <?php include '../../views/partials/header.php'; ?>
    <?php include '../../views/partials/sidebar.php'; ?>

    <div class="content">
        <h1>Pedidos</h1>

        <table>
            <thead>
                <tr>
                    <th>ID Pedido</th>
                    <th>Productor</th>
                    <th>Cooperativa</th>
                    <th>Fecha de Compra</th>
                    <th>Estado del Pedido</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $pedido): ?>
                    <tr>
                        <td><?php echo $pedido['id']; ?></td>
                        <td><?php echo $pedido['nombre_productor']; ?></td>
                        <td><?php echo $pedido['nombre_cooperativa']; ?></td>
                        <td><?php echo $pedido['fecha_compra']; ?></td>
                        <td><?php echo $pedido['estado_compra']; ?></td>
                        <td>
                            <button>Ver Detalle</button>
                            <button>Modificar Estado</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</body>
</html>
