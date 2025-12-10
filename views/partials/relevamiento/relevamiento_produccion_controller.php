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
