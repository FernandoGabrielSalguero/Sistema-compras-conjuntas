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

require_once __DIR__ . '/sve_kpi_compraConjuntaModel.php';

function sve_kpi_compra_conjunta_json_response(int $code, array $payload): void
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
        sve_kpi_compra_conjunta_json_response(500, [
            'ok' => false,
            'error' => 'PDO no disponible en sve_kpi_compra_conjunta'
        ]);
    }

    $raw = file_get_contents('php://input');
    $payload = json_decode($raw, true);
    if (!is_array($payload)) $payload = [];

    $action = isset($payload['action']) ? trim((string)$payload['action']) : 'ping';

    $model = new SveKpiCompraConjuntaModel();
    $model->pdo = $pdo;

    switch ($action) {
        case 'ping': {
            $data = $model->ping();
            sve_kpi_compra_conjunta_json_response(200, [
                'ok' => true,
                'module' => 'sve_kpi_compra_conjunta',
                'action' => 'ping',
                'data' => $data
            ]);
        }

        case 'kpis': {
            $limit = isset($payload['limit']) ? (int)$payload['limit'] : 10;
            $months = isset($payload['months']) ? (int)$payload['months'] : 6;
            $start_date = !empty($payload['start_date']) ? trim((string)$payload['start_date']) : null;
            $end_date = !empty($payload['end_date']) ? trim((string)$payload['end_date']) : null;
            $cooperativa = isset($payload['cooperativa']) && $payload['cooperativa'] !== '' ? (int)$payload['cooperativa'] : null;

            $data = [
                'top_products' => $model->obtenerTopProductos($limit, $start_date, $end_date, $cooperativa),
                'top_cooperativas' => $model->obtenerTopCooperativas($limit, $start_date, $end_date),
                'top_productores' => $model->obtenerTopProductores($limit, $start_date, $end_date, $cooperativa),
                'resumen' => $model->resumenTotales($start_date, $end_date, $cooperativa),
                'por_mes' => $model->obtenerPedidosPorMes($months, $start_date, $end_date, $cooperativa)
            ];

            // incluir cooperativas para poblar el select en la UI
            $data['cooperativas'] = $model->obtenerCooperativas();

            sve_kpi_compra_conjunta_json_response(200, [
                'ok' => true,
                'module' => 'sve_kpi_compra_conjunta',
                'action' => 'kpis',
                'data' => $data
            ]);
        }

        case 'cooperativas': {
            $rows = $model->obtenerCooperativas();
            sve_kpi_compra_conjunta_json_response(200, [
                'ok' => true,
                'module' => 'sve_kpi_compra_conjunta',
                'action' => 'cooperativas',
                'data' => $rows
            ]);
        }

        default:
            sve_kpi_compra_conjunta_json_response(400, [
                'ok' => false,
                'error' => 'Accion desconocida en sve_kpi_compra_conjunta: ' . $action
            ]);
    }
} catch (Throwable $e) {
    sve_kpi_compra_conjunta_json_response(500, [
        'ok' => false,
        'error' => 'Error sve_kpi_compra_conjunta: ' . $e->getMessage()
    ]);
}
