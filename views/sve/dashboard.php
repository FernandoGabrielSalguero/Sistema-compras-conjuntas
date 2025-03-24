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
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="../../assets/css/header.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f4;
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }
        .content {
            margin-left: 250px; /* Este margen se ajusta según el ancho del sidebar */
            padding: 20px;
            overflow-y: auto;
            height: calc(100vh - 60px); /* Ajustar la altura según el header */
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
            transition: transform 0.3s;
        }
        .kpi-card h3 {
            margin: 0;
            color: #6C63FF;
            font-size: 20px;
        }
        .kpi-card p {
            font-size: 28px;
            margin: 10px 0;
            color: #333;
        }
    </style>
</head>
<body>
    <?php include '../../views/partials/header.php'; ?>
    <?php include '../../views/partials/sidebar.php'; ?>

    <div class="content">
        <div class="kpi-container">
            <div class="kpi-card">
                <h3>Total de Pedidos</h3>
                <p><?php echo $totalPedidos; ?></p>
            </div>
            <div class="kpi-card">
                <h3>Total de Cooperativas</h3>
                <p><?php echo $totalCooperativas; ?></p>
            </div>
            <div class="kpi-card">
                <h3>Total de Productores</h3>
                <p><?php echo $totalProductores; ?></p>
            </div>
            <div class="kpi-card">
                <h3>Total de Fincas</h3>
                <p><?php echo $totalFincas; ?></p>
            </div>
        </div>
    </div>
</body>
</html>