<?php
// views/partials/drones/controller/drone_list_controller.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
$DEBUG = isset($_GET['debug']) || (isset($_POST['debug']) && $_POST['debug']);

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
                if ($DEBUG) {
                    error_log('[SVE] get_solicitud id=' . $id . ' => ' . json_encode($detalle, JSON_UNESCAPED_UNICODE));
                }
                if (!$detalle) {
                    http_response_code(404);
                    echo json_encode(['ok' => false, 'error' => 'Solicitud no encontrada'], JSON_UNESCAPED_UNICODE);
                    break;
                }
                echo json_encode(['ok' => true, 'data' => $detalle], JSON_UNESCAPED_UNICODE);
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
                    if ($DEBUG) {
                        error_log('[SVE] upsert_producto sid=' . $sid . ' payload=' . json_encode($d, JSON_UNESCAPED_UNICODE));
                    }
                    $out = $model->upsertProductoSolicitud($sid, $d);
                    if ($DEBUG) {
                        error_log('[SVE] upsert_producto result=' . json_encode($out, JSON_UNESCAPED_UNICODE));
                    }
                    echo json_encode(['ok' => true] + $out, JSON_UNESCAPED_UNICODE);
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
                    if ($DEBUG) {
                        error_log('[SVE] save_all sid=' . $sid . ' solicitud=' . json_encode($sol, JSON_UNESCAPED_UNICODE));
                        error_log('[SVE] save_all productos=' . json_encode($prods, JSON_UNESCAPED_UNICODE));
                    }
                    $out = $model->guardarTodo($sid, $sol, is_array($prods) ? $prods : []);
                    if ($DEBUG) {
                        error_log('[SVE] save_all result=' . json_encode($out, JSON_UNESCAPED_UNICODE));
                    }
                    echo json_encode(['ok' => true, 'data' => $out], JSON_UNESCAPED_UNICODE);
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
    if ($DEBUG) {
        error_log('[SVE] ERROR 500: ' . $e->getMessage());
    }
    echo json_encode(['ok' => false, 'error' => 'Error del servidor', 'detail' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
