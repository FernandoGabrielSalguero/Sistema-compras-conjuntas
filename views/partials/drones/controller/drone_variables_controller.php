<?php
declare(strict_types=1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// CONFIG y Auth
require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../../../../middleware/authMiddleware.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
checkAccess('sve'); // Asumo que sólo SVE administra catálogos

require_once __DIR__ . '/../model/drone_variables_model.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function out(array $payload, int $code = 200): void {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_GET['action'] ?? $_POST['action'] ?? 'health';
$entity = $_GET['entity'] ?? $_POST['entity'] ?? '';

try {
    $model = new DroneVariableModel($pdo);

    if ($action === 'health') {
        out(['ok'=>true, 'message'=>'Variables API OK', 'csrf'=>$_SESSION['csrf_token']]);
    }

    $allowed = ['patologias','produccion'];
    if (!in_array($entity, $allowed, true)) {
        out(['ok'=>false,'error'=>'Entidad inválida (use patologias|produccion)'], 400);
    }

    if ($method === 'GET') {
        if ($action === 'list') {
            $q = trim((string)($_GET['q'] ?? ''));
            $inactivos = (($_GET['inactivos'] ?? '0') === '1');
            $data = $model->list($entity, $q, $inactivos);
            out(['ok'=>true, 'data'=>$data]);
        }

        if ($action === 'get') {
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) out(['ok'=>false,'error'=>'ID inválido'], 400);
            $row = $model->get($entity, $id);
            if (!$row) out(['ok'=>false,'error'=>'No encontrado'], 404);
            out(['ok'=>true,'data'=>$row]);
        }

        out(['ok'=>false,'error'=>'Acción GET no soportada'], 400);
    }

    // POST: require CSRF
    $csrfHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_SERVER['HTTP_X_CSRF'] ?? '';
    $raw = file_get_contents('php://input');
    $json = json_decode($raw ?: '[]', true) ?? [];
    $csrfBody = (string)($json['csrf_token'] ?? $_POST['csrf_token'] ?? '');
    $csrf = $csrfHeader ?: $csrfBody;

    if (!$csrf || !hash_equals($_SESSION['csrf_token'], $csrf)) {
        out(['ok'=>false,'error'=>'CSRF inválido'], 419);
    }

    if ($action === 'create' || $action === 'update') {
        $id = isset($json['id']) ? (int)$json['id'] : (isset($_POST['id'])?(int)$_POST['id']:0);
        $nombre = trim((string)($json['nombre'] ?? $_POST['nombre'] ?? ''));
        $descripcion = trim((string)($json['descripcion'] ?? $_POST['descripcion'] ?? ''));

        if ($nombre === '' || mb_strlen($nombre) > 100) {
            out(['ok'=>false,'error'=>'Nombre requerido (1-100)'], 422);
        }
        if ($descripcion !== '' && mb_strlen($descripcion) > 255) {
            out(['ok'=>false,'error'=>'Descripción demasiado larga (<=255)'], 422);
        }
        $descripcion = ($descripcion === '') ? null : $descripcion;

        if ($action === 'create') {
            $newId = $model->create($entity, $nombre, $descripcion);
            out(['ok'=>true,'data'=>['id'=>$newId]]);
        } else {
            if ($id <= 0) out(['ok'=>false,'error'=>'ID inválido'], 422);
            $model->update($entity, $id, $nombre, $descripcion);
            out(['ok'=>true,'data'=>['id'=>$id]]);
        }
    }

    if ($action === 'delete') {
        $id = isset($json['id']) ? (int)$json['id'] : (isset($_POST['id'])?(int)$_POST['id']:0);
        if ($id <= 0) out(['ok'=>false,'error'=>'ID inválido'], 422);

        // toggle activo (soft delete/reactivar)
        $row = $model->get($entity, $id);
        if (!$row) out(['ok'=>false,'error'=>'No encontrado'], 404);
        $to = ($row['activo'] === 'si') ? false : true;
        $model->setActivo($entity, (int)$row['id'], $to);
        out(['ok'=>true, 'data'=>['id'=>$id, 'activo'=>$to ? 'si':'no']]);
    }

    out(['ok'=>false,'error'=>'Acción POST no soportada'], 400);

} catch (Throwable $e) {
    // Log simple
    @file_put_contents(__DIR__ . '/../../../../log_backend.txt', '['.date('c')."] drone_variables: ".$e->getMessage().PHP_EOL, FILE_APPEND);
    out(['ok'=>false,'error'=>'Error inesperado'], 500);
}
