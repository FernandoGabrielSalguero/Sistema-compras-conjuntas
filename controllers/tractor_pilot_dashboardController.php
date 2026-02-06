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
                'cooperativa_id' => $_GET['cooperativa_id'] ?? null,
                'productor_id' => $_GET['productor_id'] ?? null,
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
            $fincaId = isset($_POST['finca_id']) ? (int) $_POST['finca_id'] : null;
            if ($participacionId <= 0) {
                jsonResponse(false, null, 'participacion_id inválido.', 422);
            }
            if ($fincaId !== null && $fincaId <= 0) {
                $fincaId = null;
            }

            $data = [
                'ancho_callejon_norte' => trim((string) ($_POST['ancho_callejon_norte'] ?? '')),
                'ancho_callejon_sur' => trim((string) ($_POST['ancho_callejon_sur'] ?? '')),
                'interfilar' => trim((string) ($_POST['interfilar'] ?? '')),
                'cantidad_postes' => trim((string) ($_POST['cantidad_postes'] ?? '')),
                'postes_mal_estado' => trim((string) ($_POST['postes_mal_estado'] ?? '')),
                'promedio_callejon' => '',
                'porcentaje_postes_mal_estado' => '',
                'estructura_separadores' => trim((string) ($_POST['estructura_separadores'] ?? '')),
                'agua_lavado' => trim((string) ($_POST['agua_lavado'] ?? '')),
                'preparacion_acequias' => trim((string) ($_POST['preparacion_acequias'] ?? '')),
                'preparacion_obstaculos' => trim((string) ($_POST['preparacion_obstaculos'] ?? '')),
                'observaciones' => trim((string) ($_POST['observaciones'] ?? '')),
            ];

            $norte = (float) $data['ancho_callejon_norte'];
            $sur = (float) $data['ancho_callejon_sur'];
            $totalPostes = (float) $data['cantidad_postes'];
            $postesMal = (float) $data['postes_mal_estado'];

            if ($norte >= 0 && $sur >= 0) {
                $data['promedio_callejon'] = (string) round(($norte + $sur) / 2, 2);
            }

            if ($totalPostes > 0 && $postesMal >= 0) {
                $data['porcentaje_postes_mal_estado'] = (string) round(($postesMal / $totalPostes) * 100, 2);
            }

            $requeridos = [
                'ancho_callejon_norte',
                'ancho_callejon_sur',
                'interfilar',
                'cantidad_postes',
                'postes_mal_estado',
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

            $resultado = $model->guardarRelevamiento($participacionId, $fincaId, $data);
            jsonResponse(true, $resultado, 'Relevamiento guardado.');
        }

        jsonResponse(false, null, 'Acción no soportada.', 400);
    }

    jsonResponse(false, null, 'Método HTTP no permitido.', 405);
} catch (Throwable $e) {
    jsonResponse(false, null, 'Error interno: ' . $e->getMessage(), 500);
}
