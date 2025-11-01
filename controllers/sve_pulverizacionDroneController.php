<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/sve_pulverizacionDroneModel.php';

$droneModel = new DroneModel();

$action = $_GET['action'] ?? $_POST['action'] ?? null;

try {
    switch ($action) {
        case 'list_solicitudes':
            $filters = [
                'q'      => isset($_GET['q']) ? trim((string)$_GET['q']) : '',
                'fecha'  => isset($_GET['fecha']) ? trim((string)$_GET['fecha']) : '',
                'estado' => isset($_GET['estado']) ? trim((string)$_GET['estado']) : '',
                'limit'  => isset($_GET['limit']) ? (int)$_GET['limit'] : 25,
                'offset' => isset($_GET['offset']) ? (int)$_GET['offset'] : 0,
            ];
            if ($filters['limit'] <= 0)  $filters['limit'] = 25;
            if ($filters['limit'] > 100) $filters['limit'] = 100;
            if ($filters['offset'] < 0)  $filters['offset'] = 0;

            $data = $droneModel->listarSolicitudes($filters);
            echo json_encode(['ok' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
            break;

        case 'get_solicitud':
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'ID inválido']);
                break;
            }
            $det = $droneModel->obtenerSolicitud($id);
            if (empty($det)) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'error' => 'Solicitud no encontrada']);
                break;
            }
            echo json_encode(['ok' => true, 'data' => $det]);
            break;

        case 'get_categorias':
            echo json_encode(['ok' => true, 'data' => $droneModel->obtenerCategorias()]);
            break;

        case 'update_solicitud':
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'ID inválido']);
                break;
            }

            $payload = [
                'estado'             => $_POST['estado'] ?? null,
                'motivo_cancelacion' => $_POST['motivo_cancelacion'] ?? null,
                'responsable'        => $_POST['responsable'] ?? null,
                'piloto'             => $_POST['piloto'] ?? null,
                'fecha_visita'       => $_POST['fecha_visita'] ?? null,
                'hora_visita'        => $_POST['hora_visita'] ?? null,
                'volumen_ha'         => $_POST['volumen_ha'] ?? null,
                'velocidad_vuelo'    => $_POST['velocidad_vuelo'] ?? null,
                'alto_vuelo'         => $_POST['alto_vuelo'] ?? null,
                'tamano_gota'        => $_POST['tamano_gota'] ?? null,
                'obs_piloto'         => $_POST['obs_piloto'] ?? null,
            ];

            if (($payload['estado'] ?? '') === 'cancelado' && empty($payload['motivo_cancelacion'])) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Debe indicar motivo de cancelación']);
                break;
            }

            $ok = $droneModel->actualizarSolicitud($id, $payload);
            echo json_encode(['ok' => (bool)$ok], JSON_UNESCAPED_UNICODE);
            break;

        case 'add_producto':
            $sid    = (int)($_POST['solicitud_id'] ?? 0);
            $tipo   = trim($_POST['tipo']   ?? '');
            $fuente = trim($_POST['fuente'] ?? '');
            $marca  = trim($_POST['marca']  ?? '');

            if ($sid <= 0 || $tipo === '' || $fuente === '') {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Datos de producto incompletos']);
                break;
            }
            $ok = $droneModel->agregarProducto($sid, $tipo, $fuente, $marca);
            echo json_encode(['ok' => (bool)$ok], JSON_UNESCAPED_UNICODE);
            break;

        default:
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Acción no soportada']);
            break;
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'Error del servidor',
        'detail' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
