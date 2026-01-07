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

require_once __DIR__ . '/sve_kpi_dronesModel.php';

function sve_kpi_drones_json_response(int $code, array $payload): void
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
        sve_kpi_drones_json_response(500, [
            'ok' => false,
            'error' => 'PDO no disponible en sve_kpi_drones'
        ]);
    }

    $raw = file_get_contents('php://input');
    $payload = json_decode($raw, true);
    if (!is_array($payload)) $payload = [];

    $action = isset($payload['action']) ? trim((string)$payload['action']) : 'ping';

    $model = new SveKpiDronesModel();
    $model->pdo = $pdo;

    switch ($action) {
        case 'ping': {
                $data = $model->ping();
                sve_kpi_drones_json_response(200, [
                    'ok' => true,
                    'module' => 'sve_kpi_drones',
                    'action' => 'ping',
                    'data' => $data
                ]);
            }

        case 'kpis': {
                $limit = isset($payload['limit']) ? (int)$payload['limit'] : 6;
                $months = isset($payload['months']) ? (int)$payload['months'] : 6;
                $start_date = !empty($payload['start_date']) ? trim((string)$payload['start_date']) : null;
                $end_date = !empty($payload['end_date']) ? trim((string)$payload['end_date']) : null;
                $productor = isset($payload['productor']) && $payload['productor'] !== '' ? trim((string)$payload['productor']) : null;
                $estado = isset($payload['estado']) && $payload['estado'] !== '' ? trim((string)$payload['estado']) : null;
                $group_by = isset($payload['group_by']) && in_array($payload['group_by'], ['month', 'date']) ? $payload['group_by'] : 'month';

                $data = [
                    'top_products' => $model->topProductos($limit, $start_date, $end_date, $productor, $estado),
                    'resumen' => $model->resumenTotales($start_date, $end_date, $productor, $estado),
                    'por_mes' => $model->obtenerSolicitudesPorMes($months, $start_date, $end_date, $productor, $estado, $group_by)
                ];

                // incluir productores para poblar selects en la UI
                $data['productores'] = $model->obtenerProductores();

                sve_kpi_drones_json_response(200, [
                    'ok' => true,
                    'module' => 'sve_kpi_drones',
                    'action' => 'kpis',
                    'data' => $data
                ]);
            }

        case 'productores': {
                $rows = $model->obtenerProductores();
                sve_kpi_drones_json_response(200, [
                    'ok' => true,
                    'module' => 'sve_kpi_drones',
                    'action' => 'productores',
                    'data' => $rows
                ]);
            }

        default:
            sve_kpi_drones_json_response(400, [
                'ok' => false,
                'error' => 'Accion desconocida en sve_kpi_drones: ' . $action
            ]);
    }
} catch (Throwable $e) {
    sve_kpi_drones_json_response(500, [
        'ok' => false,
        'error' => 'Error sve_kpi_drones: ' . $e->getMessage()
    ]);
}
