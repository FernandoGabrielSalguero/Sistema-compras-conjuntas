<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../middleware/authMiddleware.php';
require_once __DIR__ . '/../models/sve_facturacionModel.php';

checkAccess('sve');

function jsonResponse(bool $success, $data = null, ?string $message = null, int $code = 200): void
{
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$model = new SveFacturacionModel($pdo);
$action = $_GET['action'] ?? $_POST['action'] ?? 'estado';

try {
    if ($action === 'estado') {
        jsonResponse(true, ['modulo' => $model->obtenerEstadoModulo()]);
    }

    if ($action === 'fincas') {
        $items = $model->obtenerFincasParticipantes();
        jsonResponse(true, [
            'items' => $items,
            'totales' => $model->obtenerTotales($items),
        ]);
    }

    jsonResponse(false, null, 'Accion no soportada.', 400);
} catch (Throwable $e) {
    jsonResponse(false, null, 'Error interno: ' . $e->getMessage(), 500);
}
