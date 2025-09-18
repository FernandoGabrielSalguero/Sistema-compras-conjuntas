<?php
// CONTROLLER: sólo lectura + filtros (sin drawer / sin detalle / sin update)
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../model/drone_list_model.php';

/** Lectura segura del body (por compatibilidad futura) */
function read_json_body(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

try {
    $model  = new DroneListModel($pdo);
    // por defecto traemos el listado
    $action = $_GET['action'] ?? 'list_solicitudes';

    if ($action !== 'list_solicitudes') {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Acción no soportada en esta vista'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // list_solicitudes
    $filters = [
        'q'            => isset($_GET['q']) ? trim($_GET['q']) : '',
        'ses_usuario'  => isset($_GET['ses_usuario']) ? trim($_GET['ses_usuario']) : '',
        'piloto'       => isset($_GET['piloto']) ? trim($_GET['piloto']) : '',
        'estado'       => isset($_GET['estado']) ? trim($_GET['estado']) : '',
        'fecha_visita' => isset($_GET['fecha_visita']) ? trim($_GET['fecha_visita']) : '',
    ];
    $data = $model->listarSolicitudes($filters);
    echo json_encode(['ok' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error del servidor', 'detail' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
