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
            $filtros = [
                'contrato_id' => $_GET['contrato_id'] ?? null,
                'cooperativa' => $_GET['cooperativa'] ?? null,
                'productor' => $_GET['productor'] ?? null,
                'finca_id' => $_GET['finca_id'] ?? null,
            ];
            $data = [
                'items' => $model->obtenerFincasParticipantes($filtros),
                'filtros' => $model->obtenerOpcionesFiltros($filtros),
            ];
            jsonResponse(true, $data);
        }
        if ($action === 'relevamiento') {
            $participacionId = isset($_GET['participacion_id']) ? (int) $_GET['participacion_id'] : 0;
            if ($participacionId <= 0) {
                jsonResponse(false, null, 'participacion_id inválido.', 422);
            }
            $relevamiento = $model->obtenerRelevamientoPorParticipacion($participacionId);
            jsonResponse(true, $relevamiento);
        }
        jsonResponse(false, null, 'Acción no soportada.', 400);
    }

    if ($method === 'POST') {
        if ($action === 'guardar_relevamiento') {
            $participacionId = isset($_POST['participacion_id']) ? (int) $_POST['participacion_id'] : 0;
            if ($participacionId <= 0) {
                jsonResponse(false, null, 'participacion_id inválido.', 422);
            }

            $data = [
                'ancho_callejon' => trim((string) ($_POST['ancho_callejon'] ?? '')),
                'interfilar' => trim((string) ($_POST['interfilar'] ?? '')),
                'estructura_postes' => trim((string) ($_POST['estructura_postes'] ?? '')),
                'estructura_separadores' => trim((string) ($_POST['estructura_separadores'] ?? '')),
                'agua_lavado' => trim((string) ($_POST['agua_lavado'] ?? '')),
                'preparacion_acequias' => trim((string) ($_POST['preparacion_acequias'] ?? '')),
                'preparacion_obstaculos' => trim((string) ($_POST['preparacion_obstaculos'] ?? '')),
                'observaciones' => trim((string) ($_POST['observaciones'] ?? '')),
            ];

            $requeridos = [
                'ancho_callejon',
                'interfilar',
                'estructura_postes',
                'estructura_separadores',
                'agua_lavado',
                'preparacion_acequias',
                'preparacion_obstaculos',
            ];

            foreach ($requeridos as $campo) {
                if ($data[$campo] === '') {
                    jsonResponse(false, null, 'Completá todos los campos obligatorios.', 422);
                }
            }

            $resultado = $model->guardarRelevamiento($participacionId, $data);
            jsonResponse(true, $resultado, 'Relevamiento guardado.');
        }

        jsonResponse(false, null, 'Acción no soportada.', 400);
    }

    jsonResponse(false, null, 'Método HTTP no permitido.', 405);
} catch (Throwable $e) {
    jsonResponse(false, null, 'Error interno: ' . $e->getMessage(), 500);
}
