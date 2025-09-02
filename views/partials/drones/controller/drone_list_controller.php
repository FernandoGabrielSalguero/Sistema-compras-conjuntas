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

        case 'list_stock': {
                $q = isset($_GET['q']) ? trim($_GET['q']) : '';
                $items = $model->listarStockProductos($q);
                echo json_encode(['ok' => true, 'data' => ['items' => $items]]);
                break;
            }

        case 'upsert_producto': {
                $raw = file_get_contents('php://input') ?: '';
                $json = json_decode($raw, true) ?: [];
                $d = $json['data'] ?? $json ?? [];
                $sid = isset($d['solicitud_id']) ? (int)$d['solicitud_id'] : 0;
                if ($sid <= 0) {
                    http_response_code(400);
                    echo json_encode(['ok' => false, 'error' => 'solicitud_id inválido']);
                    break;
                }
                try {
                    $out = $model->upsertProductoSolicitud($sid, $d);
                    echo json_encode(['ok' => true] + $out);
                } catch (InvalidArgumentException $e) {
                    http_response_code(422);
                    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
                }
                break;
            }

        case 'delete_producto': {
                $raw = file_get_contents('php://input') ?: '';
                $json = json_decode($raw, true) ?: [];
                $sid = isset($json['solicitud_id']) ? (int)$json['solicitud_id'] : 0;
                $pid = isset($json['id']) ? (int)$json['id'] : 0;
                if ($sid <= 0 || $pid <= 0) {
                    http_response_code(400);
                    echo json_encode(['ok' => false, 'error' => 'Parámetros inválidos']);
                    break;
                }
                $ok = $model->eliminarProductoSolicitud($pid, $sid);
                echo json_encode(['ok' => (bool)$ok]);
                break;
            }

            case 'save_all': {
        $raw = file_get_contents('php://input') ?: '';
        $json = json_decode($raw, true) ?: [];

        $sol = $json['solicitud'] ?? [];
        $prods = $json['productos'] ?? [];

        $sid = isset($sol['id']) ? (int)$sol['id'] : 0;
        if ($sid <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'ID de solicitud inválido']);
            break;
        }

        try {
            $out = $model->guardarTodo($sid, $sol, is_array($prods) ? $prods : []);
            echo json_encode(['ok' => true, 'data' => $out]);
        } catch (InvalidArgumentException $e) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }
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
