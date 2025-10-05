<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../middleware/authMiddleware.php';
require_once __DIR__ . '/../models/drone_pilot_dashboardModel.php';

header('Content-Type: application/json; charset=utf-8');

// Iniciar/validar sesión y acceso de piloto
checkAccess('piloto_drone');

// Obtener identificador del usuario autenticado.
// Soporta diferentes claves de sesión sin romper compatibilidad.
$usuarioId = $_SESSION['usuario_id'] ?? ($_SESSION['id'] ?? null);

$model = new DronePilotDashboardModel($pdo);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_GET['action'] ?? $_POST['action'] ?? null;

function jsonResponse($ok, $data = null, $message = null, $code = 200)
{
    http_response_code($code);
    echo json_encode([
        'ok' => $ok,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (!$usuarioId) {
    jsonResponse(false, null, 'Sesión inválida: faltan credenciales (usuario_id).', 401);
}

try {
    switch ($method) {
        case 'GET':
            if ($action === 'mis_solicitudes') {
                // Valida rol
                if (($_SESSION['rol'] ?? null) !== 'piloto_drone') {
                    jsonResponse(false, null, 'Acceso denegado para este recurso.', 403);
                }
                $solicitudes = $model->getSolicitudesByPilotoId((int)$usuarioId);
                jsonResponse(true, $solicitudes, null, 200);
            }
            // Default: acción desconocida
            jsonResponse(false, null, 'Acción no soportada.', 400);

        default:
            jsonResponse(false, null, 'Método HTTP no permitido.', 405);
    }
} catch (Throwable $e) {
    jsonResponse(false, null, 'Error interno: ' . $e->getMessage(), 500);
}
