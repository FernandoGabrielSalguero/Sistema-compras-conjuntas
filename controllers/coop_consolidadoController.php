<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/coop_consolidadoModel.php';

$model = new CoopDashboardModel($pdo);

// Autenticación básica por rol
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'cooperativa') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit;
}

$cooperativa_id = $_SESSION['id_real'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $data = $model->obtenerOperativosConParticipacion($cooperativa_id);
        echo json_encode(['success' => true, 'operativos' => $data]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $operativo_id = $input['operativo_id'] ?? null;
    $participa = $input['participa'] ?? 'no';

    if (!$operativo_id) {
        echo json_encode(['success' => false, 'message' => 'Faltan datos.']);
        exit;
    }

    try {
        $model->guardarParticipacion($operativo_id, $cooperativa_id, $participa);
        echo json_encode(['success' => true, 'message' => 'Participación actualizada.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()]);
    }
    exit;
}
