<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/sve_consolidadoModel.php';

$model = new sveConsolidadoModel($pdo);

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'sve') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'operativos') {
    try {
        $stmt = $pdo->query("SELECT id, nombre FROM operativos ORDER BY fecha_inicio DESC");
        $operativos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'operativos' => $operativos]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener operativos: ' . $e->getMessage()]);
    }
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'cooperativas') {
    try {
        $stmt = $pdo->query("SELECT DISTINCT cooperativa AS id, cooperativa AS nombre FROM pedidos ORDER BY cooperativa ASC");
        $cooperativas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'cooperativas' => $cooperativas]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener cooperativas: ' . $e->getMessage()]);
    }
    exit;
}

// Obtener consolidado
try {
    $operativo_id = $_GET['operativo_id'] ?? null;
    $cooperativa_id = $_GET['cooperativa_id'] ?? null;

    $data = $model->obtenerConsolidadoPedidos($operativo_id, $cooperativa_id);
    echo json_encode(['success' => true, 'consolidado' => $data]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

