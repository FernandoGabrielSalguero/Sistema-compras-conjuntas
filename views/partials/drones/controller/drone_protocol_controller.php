<?php

declare(strict_types=1);

ini_set('display_errors', '0'); // producción
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../model/drone_protocol_model.php';

function respond(array $payload): void
{
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        respond(['ok' => false, 'error' => 'Falla de conexión a base de datos']);
    }
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $model = new DroneProtocolModel();
    $model->pdo = $pdo;

    $action = $_GET['action'] ?? '';

    if ($action === 'list') {
        $nombre = isset($_GET['nombre']) ? trim((string)$_GET['nombre']) : '';
        $estado = isset($_GET['estado']) ? trim((string)$_GET['estado']) : '';
        $data = $model->listarSolicitudes($nombre, $estado);
        respond(['ok' => true, 'data' => $data]);
    }

    if ($action === 'detail') {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            respond(['ok' => false, 'error' => 'ID inválido']);
        }
        $data = $model->obtenerProtocolo($id);
        if (!$data) {
            respond(['ok' => false, 'error' => 'Protocolo no encontrado']);
        }
        // Añadimos el id de solicitud para el front
        $data['solicitud_id'] = $id;
        respond(['ok' => true, 'data' => $data]);
    }

    if ($action === 'update_recetas') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            respond(['ok' => false, 'error' => 'Método no permitido']);
        }
        $payload = json_decode(file_get_contents('php://input'), true);
        $rows = $payload['recetas'] ?? null;
        if (!is_array($rows) || !count($rows)) {
            respond(['ok' => false, 'error' => 'Payload de recetas vacío o inválido']);
        }
        // Normalizamos tipos
        $clean = [];
        foreach ($rows as $r) {
            $rid = isset($r['receta_id']) ? (int)$r['receta_id'] : 0;
            if ($rid <= 0) {
                continue;
            }
            $clean[] = [
                'receta_id' => $rid,
                'dosis' => array_key_exists('dosis', $r) ? (is_null($r['dosis']) ? null : (string)$r['dosis']) : null,
                'orden_mezcla' => array_key_exists('orden_mezcla', $r) ? (is_null($r['orden_mezcla']) ? null : (int)$r['orden_mezcla']) : null,
                'notas' => array_key_exists('notas', $r) ? (is_null($r['notas']) ? null : (string)$r['notas']) : null,
            ];
        }
        if (!$clean) {
            respond(['ok' => false, 'error' => 'No hay recetas válidas para actualizar']);
        }
        try {
            $model->actualizarRecetas($clean);
            respond(['ok' => true, 'updated' => count($clean)]);
        } catch (Throwable $e) {
            respond(['ok' => false, 'error' => 'Error al actualizar recetas: ' . $e->getMessage()]);
        }
    }

    if ($action === 'update_parametros') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            respond(['ok' => false, 'error' => 'Método no permitido']);
        }
        $json = json_decode(file_get_contents('php://input'), true);
        $sid = isset($json['solicitud_id']) ? (int)$json['solicitud_id'] : 0;
        $p   = $json['parametros'] ?? [];
        if ($sid <= 0) {
            respond(['ok' => false, 'error' => 'Solicitud inválida']);
        }
        if (!is_array($p)) {
            respond(['ok' => false, 'error' => 'Parámetros inválidos']);
        }

        try {
            $model->pdo->beginTransaction();
            $model->upsertParametros($sid, $p);
            // hectáreas viven en drones_solicitud
            if (array_key_exists('superficie_ha', $p)) {
                $model->actualizarHectareas($sid, $p['superficie_ha']);
            }
            $model->pdo->commit();
            respond(['ok' => true]);
        } catch (Throwable $e) {
            $model->pdo->rollBack();
            respond(['ok' => false, 'error' => 'Error al actualizar parámetros: ' . $e->getMessage()]);
        }
    }

    // Healthcheck por defecto
    $connected = ($model instanceof DroneProtocolModel) && ($pdo instanceof PDO);
    respond([
        'ok'      => $connected,
        'message' => $connected
            ? 'Controlador y modelo conectados correctamente Protocolo'
            : 'Falla de wiring (revisá require y $pdo)',
        'checks'  => [
            'modelClass' => get_class($model),
            'pdo'        => $pdo instanceof PDO,
        ],
    ]);
} catch (Throwable $e) {
    respond(['ok' => false, 'error' => 'Excepción: ' . $e->getMessage()]);
}
