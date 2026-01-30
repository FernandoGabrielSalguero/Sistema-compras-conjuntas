<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../middleware/authMiddleware.php';
require_once __DIR__ . '/../models/tractor_pilot_dashboardModel.php';

header('Content-Type: application/json; charset=utf-8');
checkAccess('piloto_tractor');

$usuarioId = $_SESSION['usuario_id'] ?? ($_SESSION['id'] ?? null);
if (!$usuarioId) {
    jsonResponse(false, null, 'Sesión inválida: faltan credenciales (usuario_id).', 401);
}

function jsonResponse($ok, $data = null, $message = null, $code = 200)
{
    http_response_code($code);
    echo json_encode(['ok' => $ok, 'message' => $message, 'data' => $data], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$model  = new TractorPilotDashboardModel($pdo);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_GET['action'] ?? $_POST['action'] ?? null;

try {
    if ($method === 'GET') {
        if ($action === 'estado') {
            jsonResponse(true, $model->getEstado());
        }
        if ($action === 'fincas') {
            jsonResponse(true, $model->obtenerFincasParticipantes());
        }
        jsonResponse(false, null, 'Acción no soportada.', 400);
    }

    jsonResponse(false, null, 'Método HTTP no permitido.', 405);
} catch (Throwable $e) {
    jsonResponse(false, null, 'Error interno: ' . $e->getMessage(), 500);
}
