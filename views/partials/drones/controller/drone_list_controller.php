<?php
// CONTROLLER: lectura + delete
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../../../../lib/Authz_vista.php';
require_once __DIR__ . '/../model/drone_list_model.php';

/** Lectura segura del body (por compatibilidad futura) */
function read_json_body(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

/** Contexto de sesión mínimo para visibilidad */
function session_ctx(): array
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $rol = $_SESSION['rol']      ?? '';
    $idr = $_SESSION['id_real']  ?? '';
    // Notas:
    // - En cooperativas: id_real = "C2" (ejemplo)
    // - En ingenieros:   id_real = "20274967855" (ejemplo)
    return ['rol' => (string)$rol, 'id_real' => (string)$idr];
}

try {
    $model  = new DroneListModel($pdo);
    $action = $_GET['action'] ?? 'list_solicitudes';
    $ctx    = session_ctx();

    if ($action === 'list_solicitudes') {
        $filters = [
            'q'            => isset($_GET['q']) ? trim((string)$_GET['q']) : '',
            'ses_usuario'  => isset($_GET['ses_usuario']) ? trim((string)$_GET['ses_usuario']) : '',
            'piloto'       => isset($_GET['piloto']) ? trim((string)$_GET['piloto']) : '',
            'estado'       => isset($_GET['estado']) ? trim((string)$_GET['estado']) : '',
            'fecha_visita' => isset($_GET['fecha_visita']) ? trim((string)$_GET['fecha_visita']) : '',
            'rango'        => isset($_GET['rango']) ? trim((string)$_GET['rango']) : '',
        ];
        $data = $model->listarSolicitudes($filters, $ctx);
        echo json_encode(['ok' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ===== Listado de rangos (para chips dinámicos) =====
    if ($action === 'list_rangos') {
        $data = $model->listarRangos($ctx);
        echo json_encode(['ok' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ===== NUEVO: JSON profundo de la solicitud =====
    if ($action === 'solicitud_json') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'ID inválido'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $deep = $model->obtenerSolicitudDeep($id, $ctx);
        if (!$deep) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'Solicitud no encontrada'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        echo json_encode(['ok' => true, 'data' => $deep], JSON_UNESCAPED_UNICODE);
        exit;
    }


    if ($action === 'delete_solicitud') {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'error' => 'Método no permitido'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $body = read_json_body();
        $id   = isset($body['id']) ? (int)$body['id'] : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'ID inválido'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $ok = $model->eliminarSolicitud($id, $ctx);
        if (!$ok) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'Solicitud no encontrada'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode(['ok' => true, 'data' => ['id' => $id]], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // funciones para exportar 

    if ($action === 'export_solicitudes') {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'error' => 'Método no permitido'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Solo SVE
        if (strtolower((string)$ctx['rol']) !== 'sve') {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'No autorizado'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Opcionalmente “prepararnos” para lotes grandes en export:
        // (no cambia la salida; ayuda a evitar timeouts en entornos restringidos)
        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');

        $body    = read_json_body();
        $filtros = isset($body['filtros']) && is_array($body['filtros']) ? $body['filtros'] : [];

        $filters = [
            'q'            => isset($filtros['q']) ? trim((string)$filtros['q']) : '',
            'ses_usuario'  => isset($filtros['ses_usuario']) ? trim((string)$filtros['ses_usuario']) : '',
            'piloto'       => isset($filtros['piloto']) ? trim((string)$filtros['piloto']) : '',
            'estado'       => isset($filtros['estado']) ? trim((string)$filtros['estado']) : '',
            'fecha_visita' => isset($filtros['fecha_visita']) ? trim((string)$filtros['fecha_visita']) : '',
        ];

        $data = $model->exportSolicitudes($filters, $ctx);
        echo json_encode(['ok' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
        exit;
    }


    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Acción no soportada'], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error del servidor', 'detail' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
