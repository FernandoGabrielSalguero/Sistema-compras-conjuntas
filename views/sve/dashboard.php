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

    // Obtener los KPI
    $totalPedidos = $conn->query("SELECT COUNT(*) FROM pedidos")->fetchColumn();
    $totalCooperativas = $conn->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'Cooperativa'")->fetchColumn();
    $totalProductores = $conn->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'Productor'")->fetchColumn();
    $totalFincas = $conn->query("SELECT COUNT(*) FROM fincas")->fetchColumn();

    // Obtener la cantidad de pedidos por estado
    $estados = [
        'Pedido recibido', 'Pedido cancelado', 'Pedido OK pendiente de factura', 
        'Pedido OK FACTURADO', 'Pedido pendiente de retito',
        'Pedido en camino al productor', 'Pedido en camino a la cooperativa.'
    ];

    $conteoEstados = [];
    foreach ($estados as $estado) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM pedidos WHERE estado_compra = :estado");
        $stmt->bindParam(':estado', $estado);
        $stmt->execute();
        $conteoEstados[$estado] = $stmt->fetchColumn();
    }

} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard SVE</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f4;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .main-container {
            display: flex;
            flex-grow: 1;
            margin-top: 60px;
        }

        .content {
            padding: 20px;
            flex-grow: 1;
            overflow-y: auto;
        }

        .kpi-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .kpi-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .kpi-card h3 {
            margin: 0;
            color: #6C63FF;
            font-size: 18px;
        }

        .kpi-card p {
            font-size: 24px;
            margin: 10px 0;
            color: #333;
        }

        /* Aseguramos que el sidebar no afecte el contenido */
        #sidebar {
            position: fixed;
            top: 60px; 
            left: 0;
            width: 250px;
            height: calc(100vh - 60px);
            background: white;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow-y: auto;
            padding-top: 20px;
        }

        #header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: white;
            padding: 10px 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            z-index: 10;
        }
    </style>
</head>
<body>

    <!-- Incluir el header y sidebar como componentes -->
    <?php include '../../views/partials/header.php'; ?>

    <div class="main-container">
        <?php include '../../views/partials/sidebar.php'; ?>

        <!-- Contenido principal -->
        <div class="content">
            <div class="kpi-container">
                <div class="kpi-card"><h3>Total de Pedidos</h3><p><?php echo $totalPedidos; ?></p></div>
                <div class="kpi-card"><h3>Total de Cooperativas</h3><p><?php echo $totalCooperativas; ?></p></div>
                <div class="kpi-card"><h3>Total de Productores</h3><p><?php echo $totalProductores; ?></p></div>
                <div class="kpi-card"><h3>Total de Fincas</h3><p><?php echo $totalFincas; ?></p></div>
                
                <!-- Mostrar los estados de pedidos -->
                <?php foreach ($conteoEstados as $estado => $conteo): ?>
                    <div class="kpi-card">
                        <h3><?php echo $estado; ?></h3>
                        <p><?php echo $conteo; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
</body>
</html>
