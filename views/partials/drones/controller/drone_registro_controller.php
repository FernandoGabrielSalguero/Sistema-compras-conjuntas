<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../model/drone_registro_model.php';

$model = new DroneRegistroModel();
$model->pdo = $pdo;

// Router simple
$action = $_GET['action'] ?? '';
try {
    if ($action === 'list') {
        $q = isset($_GET['q']) ? trim((string)$_GET['q']) : null;
        $estado = isset($_GET['estado']) ? trim((string)$_GET['estado']) : null;
        $data = $model->getSolicitudesList($q ?: null, $estado ?: null);
        echo json_encode(['ok'=>true, 'data'=>$data], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if ($action === 'detail') {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) { echo json_encode(['ok'=>false,'message'=>'ID inválido']); exit; }
        $data = $model->getRegistroDetalle($id);
        if (!$data) { echo json_encode(['ok'=>false,'message'=>'No encontrado']); exit; }
        echo json_encode(['ok'=>true,'data'=>$data], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Healthcheck por defecto
    $connected = ($model instanceof DroneRegistroModel) && ($pdo instanceof PDO);
    echo json_encode([
        'ok'      => $connected,
        'message' => $connected ? 'Controlador y modelo conectados correctamente Registro' : 'Falla de wiring (revisá require y $pdo)',
        'checks'  => ['modelClass' => get_class($model), 'pdo' => $pdo instanceof PDO],
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'message'=>'Error: '.$e->getMessage()]);
}
