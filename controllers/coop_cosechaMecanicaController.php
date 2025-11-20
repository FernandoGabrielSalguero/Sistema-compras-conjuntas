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

// Listar productores asociados a la cooperativa
if ($action === 'listar_productores') {
    if (!$cooperativa_id) {
        echo json_encode([
            'success' => false,
            'message' => 'No se encontró la cooperativa en la sesión.'
        ]);
        exit;
    }

    try {
        $productores = $model->obtenerProductoresPorCooperativa($cooperativa_id);

        echo json_encode([
            'success' => true,
            'data'    => $productores
        ]);
    } catch (Throwable $e) {
        error_log('Error listar_productores: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'No se pudieron obtener los productores.'
        ]);
    }
    exit;
}

// Guardar participación de productores en un operativo
if ($action === 'guardar_participacion') {
    $contratoId = isset($_POST['contrato_id']) ? (int) $_POST['contrato_id'] : 0;
    $filasJson  = $_POST['filas'] ?? '[]';
    $filas      = json_decode($filasJson, true);

    if ($contratoId <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de contrato inválido.'
        ]);
        exit;
    }

    if (!is_array($filas) || empty($filas)) {
        echo json_encode([
            'success' => false,
            'message' => 'No se recibieron productores para guardar.'
        ]);
        exit;
    }

    $nomCooperativa = $_SESSION['nombre'] ?? 'Cooperativa';

    try {
        $model->guardarParticipaciones($contratoId, $nomCooperativa, $filas);

        echo json_encode([
            'success' => true,
            'message' => 'Participación guardada correctamente.'
        ]);
    } catch (Throwable $e) {
        error_log('Error guardar_participacion: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'No se pudo guardar la participación.'
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
