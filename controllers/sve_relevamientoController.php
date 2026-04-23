<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

ob_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../middleware/authMiddleware.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/sve_relevamientoModel.php';

checkAccess('sve');

/** @var PDO $pdo */
$model = new SveRelevamientoModel($pdo);

$action = (string)($_GET['action'] ?? $_POST['action'] ?? 'list');

function jsonResponse(int $status, array $payload): void
{
    http_response_code($status);
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    if ($action === 'resumen') {
        $q = trim((string)($_GET['q'] ?? $_POST['q'] ?? ''));

        $data = $model->obtenerResumenCooperativas($q);

        jsonResponse(200, [
            'ok' => true,
            'data' => $data,
        ]);
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

        jsonResponse(200, [
            'ok' => true,
            'data' => $result['rows'],
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
            ],
        ]);
    }

    if ($action === 'variedades_list') {
        $q = trim((string)($_GET['q'] ?? $_POST['q'] ?? ''));
        $rows = $model->listarCodigosVariedades($q);
        jsonResponse(200, [
            'ok' => true,
            'data' => $rows,
        ]);
    }

    if ($action === 'variedades_create') {
        $codigoVariedad = (int)($_POST['codigo_variedad'] ?? 0);
        $nombreVariedad = trim((string)($_POST['nombre_variedad'] ?? ''));

        if ($codigoVariedad <= 0 || $nombreVariedad === '') {
            jsonResponse(422, [
                'ok' => false,
                'error' => 'Codigo y nombre de variedad son obligatorios',
            ]);
        }

        $row = $model->crearCodigoVariedad($codigoVariedad, $nombreVariedad);
        jsonResponse(200, [
            'ok' => true,
            'data' => $row,
        ]);
    }

    if ($action === 'variedades_update') {
        $id = (int)($_POST['id'] ?? 0);
        $codigoVariedad = (int)($_POST['codigo_variedad'] ?? 0);
        $nombreVariedad = trim((string)($_POST['nombre_variedad'] ?? ''));

        if ($id <= 0 || $codigoVariedad <= 0 || $nombreVariedad === '') {
            jsonResponse(422, [
                'ok' => false,
                'error' => 'ID, codigo y nombre son obligatorios',
            ]);
        }

        $row = $model->actualizarCodigoVariedad($id, $codigoVariedad, $nombreVariedad);
        jsonResponse(200, [
            'ok' => true,
            'data' => $row,
        ]);
    }

    if ($action === 'variedades_delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            jsonResponse(422, [
                'ok' => false,
                'error' => 'ID invalido',
            ]);
        }

        $model->eliminarCodigoVariedad($id);
        jsonResponse(200, [
            'ok' => true,
        ]);
    }

    jsonResponse(400, [
        'ok' => false,
        'error' => 'Accion no soportada',
    ]);
} catch (Throwable $e) {
    if ((int)($e->getCode()) === 23000) {
        jsonResponse(200, [
            'ok' => false,
            'error' => 'El codigo de variedad ya existe',
        ]);
    }
    jsonResponse(500, [
        'ok' => false,
        'error' => 'Error interno del servidor',
        'detail' => $e->getMessage(),
    ]);
}
