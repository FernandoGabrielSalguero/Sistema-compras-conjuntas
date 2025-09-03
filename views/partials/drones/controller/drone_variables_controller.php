<?php
declare(strict_types=1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../model/drone_variables_model.php';

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
        out(['ok'=>true, 'message'=>'Variables API OK']);
    }

    $allowed = ['patologias','produccion','costo_hectarea'];
    if (!in_array($entity, $allowed, true)) {
        out(['ok'=>false,'error'=>'Entidad inválida (use patologias|produccion|costo_hectarea)'], 400);
    }

    if ($method === 'GET') {
        if ($action === 'list') {
            // Para costo_hectarea devolvemos fila única (si existe)
            if ($entity === 'costo_hectarea') {
                $row = $model->getCostoHectarea();
                out(['ok'=>true, 'data'=>$row ? [$row] : []]);
            }
            $q = trim((string)($_GET['q'] ?? ''));
            $inactivos = (($_GET['inactivos'] ?? '0') === '1');
            $data = $model->list($entity, $q, $inactivos);
            out(['ok'=>true, 'data'=>$data]);
        }

        if ($action === 'get') {
            if ($entity === 'costo_hectarea') {
                $row = $model->getCostoHectarea();
                out(['ok'=>true, 'data'=>$row]);
            }
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) out(['ok'=>false,'error'=>'ID inválido'], 400);
            $row = $model->get($entity, $id);
            if (!$row) out(['ok'=>false,'error'=>'No encontrado'], 404);
            out(['ok'=>true,'data'=>$row]);
        }

        out(['ok'=>false,'error'=>'Acción GET no soportada'], 400);
    }

    // POST (sin CSRF)
    $raw = file_get_contents('php://input');
    $json = json_decode($raw ?: '[]', true) ?? [];

    if ($action === 'create' || $action === 'update') {
        if ($entity === 'costo_hectarea') {
            $costo  = (float)($json['costo'] ?? $_POST['costo'] ?? -1);
            $moneda = trim((string)($json['moneda'] ?? $_POST['moneda'] ?? 'Pesos'));
            if (!is_finite($costo) || $costo < 0) out(['ok'=>false,'error'=>'Costo inválido'], 422);
            if ($moneda === '' || mb_strlen($moneda) > 20) out(['ok'=>false,'error'=>'Moneda inválida (1-20)'], 422);
            $ok = $model->setCostoHectarea($costo, $moneda);
            if ($ok) out(['ok'=>true,'data'=>['id'=>1,'costo'=>$costo,'moneda'=>$moneda]]);
            out(['ok'=>false,'error'=>'No se pudo actualizar'], 500);
        }

        // Patologías/Producción
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
        if ($entity === 'costo_hectarea') {
            out(['ok'=>false,'error'=>'Acción no permitida para costo_hectarea'], 400);
        }
        $id = isset($json['id']) ? (int)$json['id'] : (isset($_POST['id'])?(int)$_POST['id']:0);
        if ($id <= 0) out(['ok'=>false,'error'=>'ID inválido'], 422);

        $row = $model->get($entity, $id);
        if (!$row) out(['ok'=>false,'error'=>'No encontrado'], 404);
        $to = ($row['activo'] === 'si') ? false : true;
        $model->setActivo($entity, (int)$row['id'], $to);
        out(['ok'=>true, 'data'=>['id'=>$id, 'activo'=>$to ? 'si':'no']]);
    }

    out(['ok'=>false,'error'=>'Acción POST no soportada'], 400);

} catch (Throwable $e) {
    @file_put_contents(__DIR__ . '/../../../../log_backend.txt', '['.date('c')."] drone_variables: ".$e->getMessage().PHP_EOL, FILE_APPEND);
    out(['ok'=>false,'error'=>'Error inesperado'], 500);
}
