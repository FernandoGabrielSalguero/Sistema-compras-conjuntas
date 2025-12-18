<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../middleware/authMiddleware.php';
checkAccess('sve');

require_once __DIR__ . '/cargaDatosFincasModel.php';

try {
    if (ob_get_level()) {
        @ob_clean();
    }

    $raw = file_get_contents('php://input');
    $payload = json_decode($raw, true);

    if (!is_array($payload)) {
        $payload = [];
    }

    $action = isset($payload['action']) ? trim((string)$payload['action']) : 'ping';

    $model = new CargaDatosFincasModel();

    switch ($action) {
        case 'ping':
            $data = $model->pingDb();
            echo json_encode([
                'ok' => true,
                'module' => 'fincas',
                'action' => 'ping',
                'data' => $data
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode([
                'ok' => false,
                'error' => 'Acción desconocida en Fincas: ' . $action
            ]);
            break;
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'Error Fincas: ' . $e->getMessage()
    ]);
}
