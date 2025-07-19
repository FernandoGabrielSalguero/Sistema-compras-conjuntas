<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/coop_consolidadoModel.php';

$model = new CoopConsolidadoModel($pdo);

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'cooperativa') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit;
}

$cooperativa_id = $_SESSION['id_real'] ?? null;

try {
    $operativo_id = $_GET['operativo_id'] ?? null;
    $data = $model->obtenerConsolidadoPedidos($cooperativa_id, $operativo_id);
    echo json_encode(['success' => true, 'consolidado' => $data]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
exit;
