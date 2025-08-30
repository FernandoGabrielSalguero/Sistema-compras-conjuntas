<?php
// views/partials/drones/controller/drone_list_controller.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// Config (root del proyecto) y Modelo del módulo
require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../model/drone_list_model.php';

try {
    $model  = new DroneListModel($pdo);
    $action = $_GET['action'] ?? $_POST['action'] ?? 'list_solicitudes';

    switch ($action) {

        case 'list_solicitudes': {
            // Filtros GET (para tipeo en vivo)
            $filters = [
                'q'            => isset($_GET['q']) ? trim($_GET['q']) : '',
                'ses_usuario'  => isset($_GET['ses_usuario']) ? trim($_GET['ses_usuario']) : '',
                'piloto'       => isset($_GET['piloto']) ? trim($_GET['piloto']) : '',
                'estado'       => isset($_GET['estado']) ? trim($_GET['estado']) : '',
                'fecha_visita' => isset($_GET['fecha_visita']) ? trim($_GET['fecha_visita']) : '',
            ];
            $data = $model->listarSolicitudes($filters);
            echo json_encode(['ok' => true, 'data' => $data]);
            break;
        }

        case 'get_solicitud': {
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'ID inválido']);
                break;
            }
            $detalle = $model->obtenerSolicitud($id);
            if (!$detalle) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'error' => 'Solicitud no encontrada']);
                break;
            }
            echo json_encode(['ok' => true, 'data' => $detalle]);
            break;
        }

        case 'update_solicitud': {
    // Soportar JSON o x-www-form-urlencoded
    $input = $_POST;
    if (empty($input)) {
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        if (is_array($json)) $input = $json;
    }

    $data = $input['data'] ?? $input ?? [];
    $id = isset($data['id']) ? (int)$data['id'] : 0;
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'ID inválido']);
        break;
    }

    $ok = $model->actualizarSolicitud($id, $data);
    echo json_encode(['ok' => (bool)$ok]);
    break;
}


        default: {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Acción no soportada']);
            break;
        }
    }

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error del servidor', 'detail' => $e->getMessage()]);
}
