<?php

declare(strict_types=1);

ini_set('display_errors', '0');
error_reporting(E_ALL);
ob_start();
session_start();

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../../../../middleware/authMiddleware.php';
require_once __DIR__ . '/step_editModel.php';

checkAccess('ingeniero');

function stepEditJson(int $status, array $payload): void
{
    http_response_code($status);
    ob_clean();
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $ingenieroIdReal = (string)($_SESSION['id_real'] ?? '');
    if ($ingenieroIdReal === '') {
        stepEditJson(403, ['ok' => false, 'error' => 'Sesion invalida']);
    }

    /** @var PDO $pdo */
    $model = new StepEditModel($pdo);

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = (string)($_GET['action'] ?? '');

        switch ($action) {
            case 'operativos':
                stepEditJson(200, ['ok' => true, 'data' => $model->listarOperativosAbiertos()]);

            case 'cooperativas':
                $operativoId = (int)($_GET['operativo_id'] ?? 0);
                stepEditJson(200, ['ok' => true, 'data' => $model->listarCooperativasLivianas($operativoId, $ingenieroIdReal)]);

            case 'productores':
                $operativoId = (int)($_GET['operativo_id'] ?? 0);
                $coopIdReal = (string)($_GET['coop_id_real'] ?? '');
                $q = trim((string)($_GET['q'] ?? ''));
                stepEditJson(200, ['ok' => true, 'data' => $model->listarProductoresLivianos($operativoId, $coopIdReal, $ingenieroIdReal, $q)]);

            case 'form':
                $operativoId = (int)($_GET['operativo_id'] ?? 0);
                $productorIdReal = (string)($_GET['productor_id_real'] ?? '');
                stepEditJson(200, ['ok' => true, 'data' => $model->obtenerFormularioProductor($operativoId, $productorIdReal, $ingenieroIdReal)]);

            case 'avance_cooperativa':
                $operativoId = (int)($_GET['operativo_id'] ?? 0);
                $coopIdReal = (string)($_GET['coop_id_real'] ?? '');
                stepEditJson(200, ['ok' => true, 'data' => $model->obtenerAvanceCooperativa($operativoId, $coopIdReal, $ingenieroIdReal)]);

            case 'avance_productor':
                $operativoId = (int)($_GET['operativo_id'] ?? 0);
                $productorIdReal = (string)($_GET['productor_id_real'] ?? '');
                stepEditJson(200, ['ok' => true, 'data' => $model->obtenerAvanceProductorPublico($operativoId, $productorIdReal, $ingenieroIdReal)]);

            case 'avance':
                $operativoId = (int)($_GET['operativo_id'] ?? 0);
                stepEditJson(200, ['ok' => true, 'data' => $model->obtenerAvanceGeneral($operativoId, $ingenieroIdReal)]);

            case 'estado_productor':
                $operativoId = (int)($_GET['operativo_id'] ?? 0);
                $productorIdReal = (string)($_GET['productor_id_real'] ?? '');
                stepEditJson(200, ['ok' => true, 'data' => $model->obtenerEstadoProductor($operativoId, $productorIdReal, $ingenieroIdReal)]);

            case 'variedades':
                stepEditJson(200, ['ok' => true, 'data' => $model->listarCodigosVariedades()]);

            default:
                stepEditJson(400, ['ok' => false, 'error' => 'Accion invalida']);
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $raw = file_get_contents('php://input') ?: '';
        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            $payload = $_POST;
        }

        $action = (string)($payload['action'] ?? '');
        switch ($action) {
            case 'save_field':
                stepEditJson(200, ['ok' => true, 'data' => $model->guardarCampo($payload, $ingenieroIdReal)]);

            case 'save_productor_estado':
                stepEditJson(200, ['ok' => true, 'data' => $model->guardarEstadoProductor($payload, $ingenieroIdReal)]);

            case 'crear_productor':
                stepEditJson(200, ['ok' => true, 'data' => $model->crearProductorEnCooperativaOperativo($payload, $ingenieroIdReal)]);

            case 'crear_finca':
                stepEditJson(200, ['ok' => true, 'data' => $model->crearFincaProductor($payload, $ingenieroIdReal)]);

            case 'crear_cuartel':
                stepEditJson(200, ['ok' => true, 'data' => $model->crearCuartelEnFinca($payload, $ingenieroIdReal)]);

            case 'archivar_productor':
                stepEditJson(200, ['ok' => true, 'data' => $model->archivarProductor($payload, $ingenieroIdReal)]);

            case 'archivar_finca':
                stepEditJson(200, ['ok' => true, 'data' => $model->archivarFincaProductor($payload, $ingenieroIdReal)]);

            case 'archivar_cuartel':
                stepEditJson(200, ['ok' => true, 'data' => $model->archivarCuartelProductor($payload, $ingenieroIdReal)]);

            default:
                stepEditJson(400, ['ok' => false, 'error' => 'Accion invalida']);
        }
    }

    stepEditJson(405, ['ok' => false, 'error' => 'Metodo no permitido']);
} catch (InvalidArgumentException $e) {
    stepEditJson(422, ['ok' => false, 'error' => $e->getMessage(), 'message' => $e->getMessage()]);
} catch (RuntimeException $e) {
    error_log('[step_editController] ' . $e->getMessage());
    stepEditJson(422, ['ok' => false, 'error' => $e->getMessage(), 'message' => $e->getMessage()]);
} catch (Throwable $e) {
    error_log('[step_editController] ' . $e->getMessage());
    stepEditJson(500, ['ok' => false, 'error' => 'Error interno del relevamiento', 'message' => 'Error interno del relevamiento']);
}
