<?php

declare(strict_types=1);

require_once '../../../config.php';
require_once '../../../middleware/authMiddleware.php';
require_once __DIR__ . '/relevamiento_cuarteles_model.php';

checkAccess('ingeniero');

/** @var PDO $pdo viene desde config.php */

$model = new RelevamientoCuartelesModel($pdo);
$includeArchived = isset($_GET['include_archived']) && in_array(strtolower(trim((string)$_GET['include_archived'])), ['1', 'true', 'si', 'yes', 'on'], true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $productorIdReal = isset($_POST['productor_id_real']) ? (string)$_POST['productor_id_real'] : '';
    $cuartelesPayload = isset($_POST['cuarteles']) && is_array($_POST['cuarteles']) ? $_POST['cuarteles'] : [];

    try {
        $model->guardarDatosCuartelesPorProductorIdReal($productorIdReal, $cuartelesPayload);
        echo json_encode(['ok' => true]);
    } catch (Throwable $e) {
        error_log('[relevamiento_cuarteles::POST] ' . $e->getMessage());
        error_log($e->getTraceAsString());
        http_response_code(500);
        echo json_encode([
            'ok' => false,
            'error' => 'Error al guardar datos de cuarteles: ' . $e->getMessage(),
        ]);
    }

    exit;
}

$productorIdReal = isset($_GET['productor_id_real']) ? (string)$_GET['productor_id_real'] : '';
$datosCuarteles = $productorIdReal !== '' ? $model->getDatosCuartelesPorProductorIdReal($productorIdReal, $includeArchived) : ['cuarteles' => []];

include __DIR__ . '/relevamiento_cuarteles_view.php';
