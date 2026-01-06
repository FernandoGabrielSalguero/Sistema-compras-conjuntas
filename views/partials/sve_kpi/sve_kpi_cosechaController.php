<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ini_set('memory_limit', '512M');
set_time_limit(0);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../middleware/authMiddleware.php';
checkAccess('sve');

require_once __DIR__ . '/sve_kpi_cosechaModel.php';

function sve_kpi_cosecha_json_response(int $code, array $payload): void
{
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    if (ob_get_level()) {
        @ob_clean();
    }

    if (!($pdo instanceof PDO)) {
        sve_kpi_cosecha_json_response(500, [
            'ok' => false,
            'error' => 'PDO no disponible en sve_kpi_cosecha'
        ]);
    }

    $raw = file_get_contents('php://input');
    $payload = json_decode($raw, true);
    if (!is_array($payload)) $payload = [];

    $action = isset($payload['action']) ? trim((string)$payload['action']) : 'ping';

    $model = new SveKpiCosechaModel();
    $model->pdo = $pdo;

    switch ($action) {
        case 'ping': {
            $data = $model->ping();
            sve_kpi_cosecha_json_response(200, [
                'ok' => true,
                'module' => 'sve_kpi_cosecha',
                'action' => 'ping',
                'data' => $data
            ]);
        }

        default:
            sve_kpi_cosecha_json_response(400, [
                'ok' => false,
                'error' => 'Accion desconocida en sve_kpi_cosecha: ' . $action
            ]);
    }
} catch (Throwable $e) {
    sve_kpi_cosecha_json_response(500, [
        'ok' => false,
        'error' => 'Error sve_kpi_cosecha: ' . $e->getMessage()
    ]);
}
