<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/sve_pulverizacionDroneModel.php';

$droneModel = new DroneModel();

$action = $_GET['action'] ?? $_POST['action'] ?? null;

try {
    switch ($action) {
        case 'list_solicitudes':
            $filters = [
                'q'      => isset($_GET['q']) ? trim($_GET['q']) : '',
                'fecha'  => isset($_GET['fecha']) ? trim($_GET['fecha']) : '',
                'estado' => isset($_GET['estado']) ? trim($_GET['estado']) : '',
                'page'   => isset($_GET['page']) ? (int)$_GET['page'] : 1,
                'limit'  => isset($_GET['limit']) ? (int)$_GET['limit'] : 20,
            ];
            $data = $droneModel->listarSolicitudes($filters);
            echo json_encode(['ok'=>true, 'data'=>$data]);
            break;

        case 'get_solicitud':
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['ok'=>false, 'error'=>'ID inválido']);
                break;
            }
            $det = $droneModel->obtenerSolicitud($id);
            if (empty($det)) {
                http_response_code(404);
                echo json_encode(['ok'=>false, 'error'=>'Solicitud no encontrada']);
                break;
            }
            echo json_encode(['ok'=>true, 'data'=>$det]);
            break;

        case 'get_categorias':
            echo json_encode(['ok'=>true, 'data'=>$droneModel->obtenerCategorias()]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['ok'=>false, 'error'=>'Acción no soportada']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false, 'error'=>'Error del servidor', 'detail'=>$e->getMessage()]);
}
