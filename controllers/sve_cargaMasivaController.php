<?php
declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

require_once __DIR__ . '/../middleware/authMiddleware.php';
checkAccess('sve');

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../models/sve_cargaMasivaModel.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'ok' => false,
        'error' => 'Metodo no permitido.'
    ]);
    exit;
}

$raw = file_get_contents('php://input');
$payload = json_decode((string)$raw, true);

if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'error' => 'Payload JSON invalido.'
    ]);
    exit;
}

$action = (string)($payload['action'] ?? '');
$rows = $payload['rows'] ?? null;
$cooperativaIdReal = trim((string)($payload['cooperativa_id_real'] ?? ''));

if (!in_array($action, ['preview', 'simulate', 'apply', 'apply_batch'], true)) {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'error' => 'Accion invalida.'
    ]);
    exit;
}

if (!is_array($rows)) {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'error' => 'Debes enviar rows como arreglo.'
    ]);
    exit;
}

if ($cooperativaIdReal === '') {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'error' => 'Debes indicar cooperativa_id_real.'
    ]);
    exit;
}

try {
    $model = new CargaMasivaModel();

    if ($action === 'preview') {
        $data = $model->previewFromRows($rows, $cooperativaIdReal);
    } elseif ($action === 'simulate') {
        $data = $model->simulateStrictFromRows($rows, $cooperativaIdReal);
    } elseif ($action === 'apply_batch') {
        $finalize = !empty($payload['finalize']);
        $allCsvCuits = is_array($payload['all_csv_cuits'] ?? null) ? $payload['all_csv_cuits'] : [];
        $allCsvRows = is_array($payload['all_csv_rows'] ?? null) ? $payload['all_csv_rows'] : [];
        $data = $model->applyFromRows($rows, $cooperativaIdReal, [
            'skip_revisado_no' => !$finalize,
            'all_csv_cuits' => $finalize ? $allCsvCuits : null,
            'all_csv_rows' => $finalize ? $allCsvRows : null,
        ]);
    } else {
        $data = $model->applyFromRows($rows, $cooperativaIdReal);
    }

    echo json_encode([
        'ok' => true,
        'action' => $action,
        'data' => $data,
    ]);
    exit;
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
    ]);
    exit;
} catch (Throwable $e) {
    $detail = $e->getMessage();
    if (function_exists('mb_substr')) {
        $detail = mb_substr($detail, 0, 180);
    } else {
        $detail = substr($detail, 0, 180);
    }
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'Error interno procesando carga masiva.',
        'detail' => $detail,
    ]);
    exit;
}
