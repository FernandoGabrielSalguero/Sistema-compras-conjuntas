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

if (!in_array($action, ['preview', 'apply'], true)) {
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

    $data = $action === 'preview'
        ? $model->previewFromRows($rows, $cooperativaIdReal)
        : $model->applyFromRows($rows, $cooperativaIdReal);

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
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'Error interno procesando carga masiva.',
        'detail' => mb_substr($e->getMessage(), 0, 180),
    ]);
    exit;
}
