<?php
// CONTROLLER: lectura + actualización
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../model/drone_list_model.php';

function read_json_body(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

try {
    $model  = new DroneListModel($pdo);
    $action = $_GET['action'] ?? $_POST['action'] ?? 'list_solicitudes';

    switch ($action) {

        case 'list_solicitudes': {
                $filters = [
                    'q'            => isset($_GET['q']) ? trim($_GET['q']) : '',
                    'ses_usuario'  => isset($_GET['ses_usuario']) ? trim($_GET['ses_usuario']) : '',
                    'piloto'       => isset($_GET['piloto']) ? trim($_GET['piloto']) : '',
                    'estado'       => isset($_GET['estado']) ? trim($_GET['estado']) : '',
                    'fecha_visita' => isset($_GET['fecha_visita']) ? trim($_GET['fecha_visita']) : '',
                ];
                $data = $model->listarSolicitudes($filters);
                echo json_encode(['ok' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
                break;
            }

        case 'get_solicitud': {
                $id = (int)($_GET['id'] ?? 0);
                if ($id <= 0) {
                    http_response_code(400);
                    echo json_encode(['ok' => false, 'error' => 'ID inválido'], JSON_UNESCAPED_UNICODE);
                    break;
                }
                $detalle = $model->obtenerSolicitudFull($id);
                if (!$detalle) {
                    http_response_code(404);
                    echo json_encode(['ok' => false, 'error' => 'Solicitud no encontrada'], JSON_UNESCAPED_UNICODE);
                    break;
                }
                echo json_encode(['ok' => true, 'data' => $detalle], JSON_UNESCAPED_UNICODE);
                break;
            }

        case 'get_solicitud_full': {
                $id = (int)($_GET['id'] ?? 0);
                if ($id <= 0) {
                    http_response_code(400);
                    echo json_encode(['ok' => false, 'error' => 'ID inválido'], JSON_UNESCAPED_UNICODE);
                    break;
                }
                $detalle = $model->obtenerSolicitudFull($id);
                if (!$detalle) {
                    http_response_code(404);
                    echo json_encode(['ok' => false, 'error' => 'Solicitud no encontrada'], JSON_UNESCAPED_UNICODE);
                    break;
                }
                echo json_encode(['ok' => true, 'data' => $detalle], JSON_UNESCAPED_UNICODE);
                break;
            }

        case 'update_solicitud': {
                $body = read_json_body();
                try {
                    if (!isset($body['id'])) {
                        http_response_code(400);
                        echo json_encode(['ok' => false, 'error' => 'Falta id'], JSON_UNESCAPED_UNICODE);
                        break;
                    }
                    $updatedId = $model->actualizarSolicitud($body);
                    echo json_encode(['ok' => true, 'data' => ['id' => $updatedId]], JSON_UNESCAPED_UNICODE);
                } catch (InvalidArgumentException $e) {
                    http_response_code(400);
                    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
                } catch (Throwable $e) {
                    http_response_code(500);
                    echo json_encode(['ok' => false, 'error' => 'Error al actualizar', 'detail' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
                }
                break;
            }

        case 'list_pilotos': {
                echo json_encode(['ok' => true, 'data' => $model->listPilotos()], JSON_UNESCAPED_UNICODE);
                break;
            }
        case 'list_formas_pago': {
                echo json_encode(['ok' => true, 'data' => $model->listFormasPago()], JSON_UNESCAPED_UNICODE);
                break;
            }
        case 'list_patologias': {
                echo json_encode(['ok' => true, 'data' => $model->listPatologias()], JSON_UNESCAPED_UNICODE);
                break;
            }
        case 'list_productos': {
                echo json_encode(['ok' => true, 'data' => $model->listProductos()], JSON_UNESCAPED_UNICODE);
                break;
            }


        default: {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Acción no soportada'], JSON_UNESCAPED_UNICODE);
                break;
            }
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error del servidor', 'detail' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
