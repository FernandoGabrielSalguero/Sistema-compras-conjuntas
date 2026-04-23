<?php


ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../middleware/authMiddleware.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/sve_relevamientoModel.php';

checkAccess('sve');

/** @var PDO $pdo */
$model = new SveRelevamientoModel($pdo);

$action = (string)($_GET['action'] ?? $_POST['action'] ?? 'list');

try {
    if ($action === 'resumen') {
        $q = trim((string)($_GET['q'] ?? $_POST['q'] ?? ''));

        $data = $model->obtenerResumenCooperativas($q);

        echo json_encode([
            'ok' => true,
            'data' => $data,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($action === 'list') {
        $q = trim((string)($_GET['q'] ?? $_POST['q'] ?? ''));
        $coopIdReal = trim((string)($_GET['coop_id_real'] ?? $_POST['coop_id_real'] ?? ''));

        $page = (int)($_GET['page'] ?? $_POST['page'] ?? 1);
        $perPage = (int)($_GET['per_page'] ?? $_POST['per_page'] ?? 20);

        if ($page < 1) {
            $page = 1;
        }
        $perPage = max(1, min(100, $perPage));

        $result = $model->obtenerListadoProductores($coopIdReal, $q, $page, $perPage);
        $total = $result['total'];
        $totalPages = (int) max(1, (int) ceil($total / $perPage));

        echo json_encode([
            'ok' => true,
            'data' => $result['rows'],
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
            ],
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'error' => 'Accion no soportada',
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'Error interno del servidor',
        'detail' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}
