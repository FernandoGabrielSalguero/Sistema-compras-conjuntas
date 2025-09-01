<?php
declare(strict_types=1);

ini_set('display_errors', '0');            // <- MUY IMPORTANTE: no mezclar errores con el JSON
error_reporting(E_ALL);
ob_start();                                // <- buffer de salida para poder limpiar cualquier ruido

session_start();
header('Content-Type: application/json; charset=UTF-8');

// Middleware (si aplica en tu proyecto)
$mwPath = __DIR__ . '/../middleware/authMiddleware.php';
if (file_exists($mwPath)) {
    require_once $mwPath;
    if (function_exists('checkAccess')) {
        try { checkAccess('productor'); } 
        catch (Throwable $e) {
            http_response_code(403);
            ob_clean();
            echo json_encode(['ok'=>false,'error'=>'Acceso denegado']);
            exit;
        }
    }
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/prod_dronesModel.php';

try {
    $model = new prodDronesModel($pdo);

    // --- GET: catálogos ---
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? '';
        if ($action === 'patologias') {
            $items = $model->getPatologiasActivas();
            http_response_code(200);
            ob_clean();
            echo json_encode(['ok'=>true, 'data'=>['items'=>$items]], JSON_UNESCAPED_UNICODE);
            exit;
        }
        if ($action === 'productos') {
            $pid = isset($_GET['patologia_id']) ? (int)$_GET['patologia_id'] : 0;
            if ($pid <= 0) {
                http_response_code(400);
                ob_clean();
                echo json_encode(['ok'=>false,'error'=>'patologia_id inválido']);
                exit;
            }
            $items = $model->getProductosPorPatologia($pid);
            http_response_code(200);
            ob_clean();
            echo json_encode(['ok'=>true, 'data'=>['items'=>$items]], JSON_UNESCAPED_UNICODE);
            exit;
        }
        http_response_code(400);
        ob_clean();
        echo json_encode(['ok'=>false,'error'=>'Acción GET no soportada']);
        exit;
    }

    // --- POST: crear solicitud ---
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        ob_clean();
        echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
        exit;
    }

    $raw  = file_get_contents('php://input') ?: '';
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        http_response_code(400);
        ob_clean();
        echo json_encode(['ok' => false, 'error' => 'JSON inválido']);
        exit;
    }

    $id = $model->crearSolicitud($data, $_SESSION);

    http_response_code(200);
    ob_clean();
    echo json_encode(['ok'=>true,'id'=>$id,'message'=>'Solicitud registrada correctamente'], JSON_UNESCAPED_UNICODE);
    exit;

} catch (InvalidArgumentException $e) {

    http_response_code(400);
    ob_clean();
    echo json_encode(['ok'=>false, 'error'=>$e->getMessage()]);
    exit;

} catch (RuntimeException $e) {
    http_response_code(403);
    ob_clean();
    echo json_encode(['ok'=>false, 'error'=>$e->getMessage()]);
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    ob_clean();
    echo json_encode(['ok'=>false, 'error'=>'Error interno.', 'detail'=>$e->getMessage()]);
    exit;
}
