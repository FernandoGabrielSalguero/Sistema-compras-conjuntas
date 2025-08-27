<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');

// Middleware (si lo usás también en controllers)
$mwPath = __DIR__ . '/../middleware/authMiddleware.php';
if (file_exists($mwPath)) {
    require_once $mwPath;
    if (function_exists('checkAccess')) {
        try { checkAccess('productor'); } catch (Throwable $e) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'Acceso denegado']); exit; }
    }
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/prod_dronesModel.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
        exit;
    }

    $raw = file_get_contents('php://input') ?: '';
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'JSON inválido']);
        exit;
    }

    $model = new prodDronesModel($pdo);
    $id = $model->crearSolicitud($data, $_SESSION);

    echo json_encode([
        'ok'      => true,
        'id'      => $id,
        'message' => 'Solicitud registrada correctamente'
    ]);

} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['ok'=>false, 'error'=>$e->getMessage()]);
} catch (RuntimeException $e) {
    http_response_code(403);
    echo json_encode(['ok'=>false, 'error'=>$e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false, 'error'=>'Error interno.','detail'=>$e->getMessage()]);
}
