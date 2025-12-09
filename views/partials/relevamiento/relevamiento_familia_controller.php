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

$model = new RelevamientoFamiliaModel($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ======== GUARDAR (JSON) ========
    header('Content-Type: application/json; charset=UTF-8');

    $productorIdReal = isset($_POST['productor_id_real']) ? (string)$_POST['productor_id_real'] : '';

    try {
        if ($productorIdReal === '') {
            throw new InvalidArgumentException('productor_id_real es requerido');
        }

        $model->guardarDatosFamiliaPorProductorIdReal($productorIdReal, $_POST);

        echo json_encode([
            'ok'     => true,
            'message' => 'Datos de familia guardados correctamente',
        ]);
    } catch (Throwable $e) {
        error_log('[relevamiento_familia::POST] ' . $e->getMessage());
        error_log($e->getTraceAsString());

        http_response_code(500);
        echo json_encode([
            'ok'    => false,
            'error' => $e->getMessage(),
        ]);
    }
    exit;
}

// ======== CARGAR (GET, HTML parcial) ========
$productorIdReal = isset($_GET['productor_id_real']) ? (string)$_GET['productor_id_real'] : '';

$datosFamilia  = null;
$errorBackend  = null;

try {
    if ($productorIdReal !== '') {
        $datosFamilia = $model->getDatosFamiliaPorProductorIdReal($productorIdReal);
    }
} catch (Throwable $e) {
    error_log('[relevamiento_familia::GET] ' . $e->getMessage());
    error_log($e->getTraceAsString());
    $errorBackend = $e->getMessage();
}

// Siempre devolvemos HTML (200 OK). Si hubo error, la vista lo muestra.
include __DIR__ . '/relevamiento_familia_view.php';
