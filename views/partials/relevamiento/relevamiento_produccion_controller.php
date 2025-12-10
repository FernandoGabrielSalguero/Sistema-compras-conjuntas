<?php

declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once '../../../config.php';
require_once '../../../middleware/authMiddleware.php';
require_once __DIR__ . '/relevamiento_produccion_model.php';

checkAccess('ingeniero');

/** @var PDO $pdo viene desde config.php */

$model = new RelevamientoProduccionModel($pdo);

// ========== POST: guardar datos de producción ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $productorIdReal = isset($_POST['productor_id_real']) ? (string)$_POST['productor_id_real'] : '';
    $fincasPayload   = isset($_POST['fincas']) && is_array($_POST['fincas']) ? $_POST['fincas'] : [];

    try {
        $model->guardarDatosProduccionPorProductorIdReal($productorIdReal, $fincasPayload);

        echo json_encode([
            'ok' => true,
        ]);
    } catch (Throwable $e) {
        error_log('[relevamiento_produccion::POST] ' . $e->getMessage());
        error_log($e->getTraceAsString());
        http_response_code(500);

        echo json_encode([
            'ok'    => false,
            'error' => 'Error al guardar datos de producción: ' . $e->getMessage(),
        ]);
    }

    exit;
}

// ========== GET: renderizar formulario de producción ==========
$productorIdReal = isset($_GET['productor_id_real']) ? (string)$_GET['productor_id_real'] : '';

$datosProduccion = null;
$errorBackend    = null;

try {
    if ($productorIdReal !== '') {
        $datosProduccion = $model->getDatosProduccionPorProductorIdReal($productorIdReal);
    }
} catch (Throwable $e) {
    error_log('[relevamiento_produccion::GET] ' . $e->getMessage());
    error_log($e->getTraceAsString());
    $errorBackend = $e->getMessage();
}

include __DIR__ . '/relevamiento_produccion_view.php';
