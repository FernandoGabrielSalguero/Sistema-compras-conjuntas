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

require_once __DIR__ . '/cargaDatosFincasModel.php';

function cuarteles_json_response(int $code, array $payload): void
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
        cuarteles_json_response(500, [
            'ok'    => false,
            'error' => 'PDO no disponible en CargaDatosCuarteles'
        ]);
    }

    $raw = file_get_contents('php://input');
    $payload = json_decode($raw, true);
    if (!is_array($payload)) $payload = [];

    $action = isset($payload['action']) ? trim((string)$payload['action']) : 'ping';

    $model = new CargaDatosCuartelesModel();
    $model->pdo = $pdo;

    switch ($action) {
        case 'ping': {
            $data = $model->pingDb();
            cuarteles_json_response(200, [
                'ok' => true,
                'module' => 'cuarteles',
                'action' => 'ping',
                'data' => $data
            ]);
        }

        case 'schema_check': {
            $data = $model->schemaCheck();
            cuarteles_json_response(200, [
                'ok' => true,
                'module' => 'cuarteles',
                'action' => 'schema_check',
                'data' => $data
            ]);
        }

        case 'ingest_batch': {
            $mode = isset($payload['mode']) ? trim((string)$payload['mode']) : 'simulate';
            if ($mode !== 'simulate' && $mode !== 'commit') {
                cuarteles_json_response(400, ['ok' => false, 'error' => 'mode inválido (simulate|commit).']);
            }

            $rows = isset($payload['rows']) && is_array($payload['rows']) ? $payload['rows'] : [];
            if (!$rows) {
                cuarteles_json_response(400, ['ok' => false, 'error' => 'rows vacío.']);
            }

            $batchIndex = isset($payload['batch_index']) ? (int)$payload['batch_index'] : 0;
            $totalBatches = isset($payload['total_batches']) ? (int)$payload['total_batches'] : 0;

            $data = $model->ingestBatch($rows, $mode);

            cuarteles_json_response(200, [
                'ok' => true,
                'module' => 'cuarteles',
                'action' => 'ingest_batch',
                'meta' => [
                    'mode' => $mode,
                    'batch_index' => $batchIndex,
                    'total_batches' => $totalBatches,
                    'rows' => count($rows),
                ],
                'data' => $data
            ]);
        }

        default:
            cuarteles_json_response(400, [
                'ok' => false,
                'error' => 'Acción desconocida en Cuarteles: ' . $action
            ]);
    }
} catch (Throwable $e) {
    cuarteles_json_response(500, [
        'ok' => false,
        'error' => 'Error Cuarteles: ' . $e->getMessage()
    ]);
}
