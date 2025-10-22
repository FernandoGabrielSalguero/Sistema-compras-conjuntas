<?php
declare(strict_types=1);

ini_set('display_errors', '0'); // producción
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../model/drone_protocol_model.php';

function respond(array $payload): void {
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
        if ($id <= 0) { respond(['ok' => false, 'error' => 'ID inválido']); }
        $data = $model->obtenerProtocolo($id);
        if (!$data) { respond(['ok' => false, 'error' => 'Protocolo no encontrado']); }
        respond(['ok' => true, 'data' => $data]);
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
