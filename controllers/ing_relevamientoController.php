<?php

declare(strict_types=1);

ini_set('display_errors', '0');
error_reporting(E_ALL);
ob_start();

session_start();
header('Content-Type: application/json; charset=UTF-8');

$mwPath = __DIR__ . '/../middleware/authMiddleware.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/ing_relevamientoModel.php';
require_once $mwPath;

checkAccess('ingeniero');

function asBool($value): bool
{
    return in_array(strtolower(trim((string)$value)), ['1', 'true', 'si', 'yes', 'on'], true);
}

try {
    $idReal = $_SESSION['id_real'] ?? null;
    if (!$idReal) {
        http_response_code(403);
        ob_clean();
        echo json_encode(['ok' => false, 'error' => 'Sesion invalida']);
        exit;
    }

    /** @var PDO $pdo */
    $model = new ingRelevamientoModel($pdo);

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'cooperativas':
                $coops = $model->getCoopsByIngeniero((string)$idReal);
                http_response_code(200);
                ob_clean();
                echo json_encode(['ok' => true, 'data' => $coops]);
                exit;

            case 'productores':
                $coopIdReal = (string)($_GET['coop_id_real'] ?? '');
                $includeArchived = asBool($_GET['include_archived'] ?? '0');

                if ($coopIdReal === '') {
                    http_response_code(400);
                    ob_clean();
                    echo json_encode(['ok' => false, 'error' => 'Parametro coop_id_real es requerido']);
                    exit;
                }

                $productores = $model->getProductoresByCooperativa($coopIdReal, (string)$idReal, $includeArchived);
                http_response_code(200);
                ob_clean();
                echo json_encode(['ok' => true, 'data' => $productores]);
                exit;

            case 'variedades':
                $variedades = $model->listarCodigosVariedades();
                http_response_code(200);
                ob_clean();
                echo json_encode(['ok' => true, 'data' => $variedades]);
                exit;

            case 'resumen_activos_productor':
                $productorIdReal = (string)($_GET['productor_id_real'] ?? '');
                $includeArchived = asBool($_GET['include_archived'] ?? '0');

                if ($productorIdReal === '') {
                    http_response_code(400);
                    ob_clean();
                    echo json_encode(['ok' => false, 'error' => 'Parametro productor_id_real es requerido']);
                    exit;
                }

                $resumen = $model->getResumenActivosProductor($productorIdReal, (string)$idReal, $includeArchived);
                http_response_code(200);
                ob_clean();
                echo json_encode(['ok' => true, 'data' => $resumen]);
                exit;

            case 'dump_tablas_productor':
                $productorIdReal = (string)($_GET['productor_id_real'] ?? '');
                $includeArchived = asBool($_GET['include_archived'] ?? '0');

                if ($productorIdReal === '') {
                    http_response_code(400);
                    ob_clean();
                    echo json_encode(['ok' => false, 'error' => 'Parametro productor_id_real es requerido']);
                    exit;
                }

                $dump = $model->getDumpTablasProductor($productorIdReal, (string)$idReal, $includeArchived);
                http_response_code(200);
                ob_clean();
                echo json_encode(['ok' => true, 'data' => $dump]);
                exit;

            default:
                http_response_code(400);
                ob_clean();
                echo json_encode(['ok' => false, 'error' => 'Accion invalida']);
                exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = (string)($_POST['action'] ?? '');

        switch ($action) {
            case 'crear_productor':
                $coopIdReal = (string)($_POST['coop_id_real'] ?? '');
                $usuario = trim((string)($_POST['usuario'] ?? ''));
                $cuit = trim((string)($_POST['cuit'] ?? ''));

                $nuevo = $model->crearProductorEnCooperativa($coopIdReal, (string)$idReal, $usuario, $cuit);
                http_response_code(200);
                ob_clean();
                echo json_encode(['ok' => true, 'data' => $nuevo]);
                exit;

            case 'crear_finca':
                $productorIdReal = (string)($_POST['productor_id_real'] ?? '');
                $codigoFinca = trim((string)($_POST['codigo_finca'] ?? ''));
                $nombreFinca = trim((string)($_POST['nombre_finca'] ?? ''));

                $nueva = $model->crearFincaProductor($productorIdReal, (string)$idReal, $codigoFinca, $nombreFinca);
                http_response_code(200);
                ob_clean();
                echo json_encode(['ok' => true, 'data' => $nueva]);
                exit;

            case 'crear_cuartel':
                $productorIdReal = (string)($_POST['productor_id_real'] ?? '');
                $fincaId = (int)($_POST['finca_id'] ?? 0);
                $variedad = trim((string)($_POST['variedad'] ?? ''));
                $superficieHa = trim((string)($_POST['superficie_ha'] ?? ''));

                $nuevo = $model->crearCuartelEnFinca($productorIdReal, (string)$idReal, $fincaId, $variedad, $superficieHa);
                http_response_code(200);
                ob_clean();
                echo json_encode(['ok' => true, 'data' => $nuevo]);
                exit;

            case 'archivar_productor':
                $productorIdReal = (string)($_POST['productor_id_real'] ?? '');
                $model->archivarProductor($productorIdReal, (string)$idReal);
                http_response_code(200);
                ob_clean();
                echo json_encode(['ok' => true]);
                exit;

            case 'desarchivar_productor':
                $productorIdReal = (string)($_POST['productor_id_real'] ?? '');
                $model->desarchivarProductor($productorIdReal, (string)$idReal);
                http_response_code(200);
                ob_clean();
                echo json_encode(['ok' => true]);
                exit;

            case 'archivar_finca':
                $productorIdReal = (string)($_POST['productor_id_real'] ?? '');
                $fincaId = (int)($_POST['finca_id'] ?? 0);
                $model->archivarFincaProductor($fincaId, $productorIdReal, (string)$idReal);
                http_response_code(200);
                ob_clean();
                echo json_encode(['ok' => true]);
                exit;

            case 'desarchivar_finca':
                $productorIdReal = (string)($_POST['productor_id_real'] ?? '');
                $fincaId = (int)($_POST['finca_id'] ?? 0);
                $model->desarchivarFincaProductor($fincaId, $productorIdReal, (string)$idReal);
                http_response_code(200);
                ob_clean();
                echo json_encode(['ok' => true]);
                exit;

            case 'archivar_cuartel':
                $productorIdReal = (string)($_POST['productor_id_real'] ?? '');
                $cuartelId = (int)($_POST['cuartel_id'] ?? 0);
                $model->archivarCuartelProductor($cuartelId, $productorIdReal, (string)$idReal);
                http_response_code(200);
                ob_clean();
                echo json_encode(['ok' => true]);
                exit;

            case 'desarchivar_cuartel':
                $productorIdReal = (string)($_POST['productor_id_real'] ?? '');
                $cuartelId = (int)($_POST['cuartel_id'] ?? 0);
                $model->desarchivarCuartelProductor($cuartelId, $productorIdReal, (string)$idReal);
                http_response_code(200);
                ob_clean();
                echo json_encode(['ok' => true]);
                exit;

            // Compatibilidad con acciones anteriores
            case 'eliminar_finca_productor':
                $productorIdReal = (string)($_POST['productor_id_real'] ?? '');
                $fincaId = (int)($_POST['finca_id'] ?? 0);
                $model->archivarFincaProductor($fincaId, $productorIdReal, (string)$idReal);
                http_response_code(200);
                ob_clean();
                echo json_encode(['ok' => true]);
                exit;

            case 'eliminar_cuartel_productor':
                $productorIdReal = (string)($_POST['productor_id_real'] ?? '');
                $cuartelId = (int)($_POST['cuartel_id'] ?? 0);
                $model->archivarCuartelProductor($cuartelId, $productorIdReal, (string)$idReal);
                http_response_code(200);
                ob_clean();
                echo json_encode(['ok' => true]);
                exit;

            default:
                http_response_code(400);
                ob_clean();
                echo json_encode(['ok' => false, 'error' => 'Accion invalida']);
                exit;
        }
    }

    http_response_code(405);
    ob_clean();
    echo json_encode(['ok' => false, 'error' => 'Metodo no permitido']);
    exit;
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    ob_clean();
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    exit;
} catch (Throwable $e) {
    error_log('[ing_relevamientoController] ' . $e->getMessage());
    error_log($e->getTraceAsString());

    http_response_code(500);
    ob_clean();
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
    exit;
}
