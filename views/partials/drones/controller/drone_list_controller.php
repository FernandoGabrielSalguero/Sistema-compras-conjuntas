<?php
// CONTROLLER: lectura + delete
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
    // acción por defecto
    $action = $_GET['action'] ?? 'list_solicitudes';

    if ($action === 'list_solicitudes') {
        $filters = [
            'q'            => isset($_GET['q']) ? trim($_GET['q']) : '',
            'ses_usuario'  => isset($_GET['ses_usuario']) ? trim($_GET['ses_usuario']) : '',
            'piloto'       => isset($_GET['piloto']) ? trim($_GET['piloto']) : '',
            'estado'       => isset($_GET['estado']) ? trim($_GET['estado']) : '',
            'fecha_visita' => isset($_GET['fecha_visita']) ? trim($_GET['fecha_visita']) : '',
        ];
        $data = $model->listarSolicitudes($filters);
        echo json_encode(['ok' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($action === 'delete_solicitud') {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'error' => 'Método no permitido'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $body = read_json_body();
        $id   = isset($body['id']) ? (int)$body['id'] : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'ID inválido'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $ok = $model->eliminarSolicitud($id);
        if (!$ok) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'Solicitud no encontrada'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode(['ok' => true, 'data' => ['id' => $id]], JSON_UNESCAPED_UNICODE);
        exit;
    }

    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Acción no soportada'], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error del servidor', 'detail' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
