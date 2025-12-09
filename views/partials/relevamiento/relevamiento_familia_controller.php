<?php

declare(strict_types=1);

// Mientras depuramos, mostramos errores (podés apagarlo luego si querés)
ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once '../../../config.php';
require_once '../../../middleware/authMiddleware.php';
require_once __DIR__ . '/relevamiento_familia_model.php';

checkAccess('ingeniero');

/** @var PDO $pdo viene desde config.php */

$productorIdReal = isset($_GET['productor_id_real']) ? (string)$_GET['productor_id_real'] : '';

$model = new RelevamientoFamiliaModel($pdo);

$datosFamilia  = null;
$errorBackend  = null;

try {
    if ($productorIdReal !== '') {
        $datosFamilia = $model->getDatosFamiliaPorProductorIdReal($productorIdReal);
    }
} catch (Throwable $e) {
    // Log interno para que puedas revisar en el servidor
    error_log('[relevamiento_familia] ' . $e->getMessage());
    error_log($e->getTraceAsString());
    $errorBackend = $e->getMessage();
}

// Siempre devolvemos HTML (200 OK). Si hubo error, la vista lo muestra.
include __DIR__ . '/relevamiento_familia_view.php';
