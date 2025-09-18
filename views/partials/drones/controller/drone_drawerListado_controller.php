<?php
// CONTROLLER del drawer: sólo detalle por ID.
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../model/drone_drawerListado_model.php';

try {
    $model  = new DroneDrawerListadoModel($pdo);
    $action = $_GET['action'] ?? 'get_detalle';

    if ($action !== 'get_detalle') {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Acción no soportada'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'ID inválido'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $detalle = $model->obtenerSolicitudFull($id);
    if (!$detalle) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Solicitud no encontrada'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode(['ok' => true, 'data' => $detalle], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error del servidor', 'detail' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
