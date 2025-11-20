<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/coop_cosechaMecanicaModel.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'cooperativa') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit;
}

$cooperativa_id = $_SESSION['id_real'] ?? null;

$model = new CoopCosechaMecanicaModel($pdo);

$action = $_GET['action'] ?? $_POST['action'] ?? null;

// Listar operativos para la cooperativa
if ($action === 'listar_operativos') {
    try {
        $operativos = $model->obtenerOperativos();

        echo json_encode([
            'success' => true,
            'data'    => $operativos
        ]);
    } catch (Throwable $e) {
        error_log('Error listar_operativos: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'No se pudieron obtener los operativos.'
        ]);
    }
    exit;
}

// Obtener un operativo puntual (opcional, útil para futuras ampliaciones)
if ($action === 'obtener_operativo') {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID inválido.']);
        exit;
    }

    try {
        $operativo = $model->obtenerOperativoPorId($id);

        if (!$operativo) {
            echo json_encode(['success' => false, 'message' => 'Operativo no encontrado.']);
        } else {
            echo json_encode(['success' => true, 'data' => $operativo]);
        }
    } catch (Throwable $e) {
        error_log('Error obtener_operativo: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'No se pudo obtener el operativo.'
        ]);
    }
    exit;
}

// Acción no reconocida
echo json_encode([
    'success' => false,
    'message' => 'Acción no válida.'
]);
exit;
