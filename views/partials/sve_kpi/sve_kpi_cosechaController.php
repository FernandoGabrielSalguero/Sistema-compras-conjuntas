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

        case 'kpis': {
                $limit = isset($payload['limit']) ? (int)$payload['limit'] : 6;
                $months = isset($payload['months']) ? (int)$payload['months'] : 6;
                $start_date = !empty($payload['start_date']) ? trim((string)$payload['start_date']) : null;
                $end_date = !empty($payload['end_date']) ? trim((string)$payload['end_date']) : null;
                $contrato_id = isset($payload['contrato_id']) && $payload['contrato_id'] !== '' ? (int)$payload['contrato_id'] : null;
                $cooperativa = isset($payload['cooperativa']) && $payload['cooperativa'] !== '' ? trim((string)$payload['cooperativa']) : null;
                $productor = isset($payload['productor']) && $payload['productor'] !== '' ? trim((string)$payload['productor']) : null;
                $estado = isset($payload['estado']) && $payload['estado'] !== '' ? trim((string)$payload['estado']) : null;
                $group_by = isset($payload['group_by']) && in_array($payload['group_by'], ['month', 'date']) ? $payload['group_by'] : 'month';

                $data = [
                    // (aunque los gráficos de barras se eliminaron de la UI, mantenemos estos datos por compatibilidad)
                    'top_cooperativas' => $model->topCooperativas($limit, $start_date, $end_date, $contrato_id, $productor),
                    'top_productores' => $model->topProductores($limit, $start_date, $end_date, $contrato_id, $cooperativa),

                    // KPIs: prod_estimada = SUM(cp.prod_estimada), monto = SUM(cp.superficie * cm.costo_base)
                    'resumen' => $model->resumenTotales($start_date, $end_date, $contrato_id, $cooperativa, $productor, $estado),

                    // Serie: visitas (participaciones) por fecha_estimada (o fallback)
                    'por_mes' => $model->obtenerContratosPorMes($months, $start_date, $end_date, $contrato_id, $cooperativa, $productor, $estado, $group_by),

                    'por_estado' => $model->contratosPorEstado($start_date, $end_date, $contrato_id, $cooperativa, $productor)
                ];


                // incluir listas para poblar selects en la UI
                $data['contratos'] = $model->obtenerContratos();
                $data['cooperativas'] = $model->obtenerCooperativas($contrato_id);
                $data['productores'] = $model->obtenerProductores($contrato_id);


                sve_kpi_cosecha_json_response(200, [
                    'ok' => true,
                    'module' => 'sve_kpi_cosecha',
                    'action' => 'kpis',
                    'data' => $data
                ]);
            }

        case 'cooperativas': {
                $contrato_id = isset($payload['contrato_id']) && $payload['contrato_id'] !== '' ? (int)$payload['contrato_id'] : null;
                $rows = $model->obtenerCooperativas($contrato_id);
                sve_kpi_cosecha_json_response(200, [
                    'ok' => true,
                    'module' => 'sve_kpi_cosecha',
                    'action' => 'cooperativas',
                    'data' => $rows
                ]);
            }


        case 'productores': {
                $contrato_id = isset($payload['contrato_id']) && $payload['contrato_id'] !== '' ? (int)$payload['contrato_id'] : null;
                $rows = $model->obtenerProductores($contrato_id);
                sve_kpi_cosecha_json_response(200, [
                    'ok' => true,
                    'module' => 'sve_kpi_cosecha',
                    'action' => 'productores',
                    'data' => $rows
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
