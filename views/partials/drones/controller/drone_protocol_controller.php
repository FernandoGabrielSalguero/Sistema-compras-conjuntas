<?php

declare(strict_types=1);

ini_set('display_errors', '0'); // producción
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// Forzar JSON de entrada cuando sea POST
$rawInput = file_get_contents('php://input') ?: '';
$inputJson = json_decode($rawInput, true);


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
        respond(['ok' => true, 'data' => $data]);
    }

    // Guardar cambios (POST JSON)
    if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $solicitudId = (int)($inputJson['solicitud_id'] ?? 0);
        if ($solicitudId <= 0) {
            respond(['ok' => false, 'error' => 'solicitud_id inválido']);
        }

        $paramId = (int)($inputJson['parametros_id'] ?? 0);
        $parametros = (array)($inputJson['parametros'] ?? []);
        $recetas = is_array($inputJson['recetas'] ?? null) ? $inputJson['recetas'] : [];
        $items   = is_array($inputJson['items'] ?? null)   ? $inputJson['items']   : [];

        try {
            $pdo->beginTransaction();

            // Upsert parámetros
            $pid = $model->upsertParametros($solicitudId, $parametros, $paramId);

            // Actualizar recetas
            $afRec = $model->actualizarRecetas($recetas);

            // Actualizar nombre de producto en items (editable)
            $afIt  = $model->actualizarItemsNombre($items);

            $pdo->commit();
            respond(['ok' => true, 'parametros_id' => $pid, 'recetas_afectadas' => $afRec, 'items_afectados' => $afIt]);
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            respond(['ok' => false, 'error' => 'No se pudieron guardar los cambios: ' . $e->getMessage()]);
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
