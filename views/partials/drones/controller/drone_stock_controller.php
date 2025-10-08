<?php

declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// CONFIG global
require_once __DIR__ . '/../../../../config.php';

// MODELO
require_once __DIR__ . '/../model/drone_stock_model.php';

function json_out(bool $ok, $dataOrError): void
{
    echo json_encode(
        $ok ? ['ok' => true, 'data' => $dataOrError] : ['ok' => false, 'error' => (string)$dataOrError],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

try {
    if (!($pdo instanceof PDO)) {
        json_out(false, 'Fallo de conexión a base de datos.');
    }

    $model = new DroneStockModel();
    $model->pdo = $pdo;

    // Compatibilidad: si no viene acción => healthcheck
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    // Soportar JSON body
    $raw = file_get_contents('php://input');
    $body = [];
    if ($raw && isset($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
        $tmp = json_decode($raw, true);
        if (is_array($tmp)) $body = $tmp;
    }

    $action = $_GET['action'] ?? $body['action'] ?? ($_POST['action'] ?? '');

    // Helpers de sanitización
    $str = function ($v): ?string {
        if ($v === null) return null;
        $v = trim((string)$v);
        $v = strip_tags($v);
        return $v === '' ? null : $v;
    };
    $int = function ($v): int {
        return max(0, (int)$v);
    };
    $dec = function ($v): float {
        $s = trim((string)$v);
        if ($s === '') return 0.0;
        // admite "1.234,56" y "1234.56"
        $s = str_replace([' ', "\xc2\xa0"], '', $s);
        if (strpos($s, ',') !== false && strpos($s, '.') !== false) {
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
        } else {
            $s = str_replace(',', '.', $s);
        }
        $f = (float)$s;
        return $f < 0 ? 0.0 : $f;
    };
    $yn = function ($v): string {
        $val = is_bool($v) ? $v : in_array(strtolower((string)$v), ['1', 'true', 'si', 'sí', 'on', 'yes'], true);
        return $val ? 'si' : 'no';
    };
    $arrInt = function ($v): array {
        if (!is_array($v)) return [];
        $out = [];
        foreach ($v as $x) {
            $n = (int)$x;
            if ($n > 0) $out[] = $n;
        }
        return array_values(array_unique($out));
    };


    switch ($action) {
        case 'list':
            if ($method !== 'GET') json_out(false, 'Método no permitido.');
            $items = $model->listProducts();
            json_out(true, ['items' => $items]);
            break;

        case 'patologias':
            if ($method !== 'GET') json_out(false, 'Método no permitido.');
            $items = $model->getPatologias();
            json_out(true, ['items' => $items]);
            break;

        case 'create':
            if ($method !== 'POST') json_out(false, 'Método no permitido.');
            $nombre = $str($body['nombre'] ?? $_POST['nombre'] ?? null);
            if (!$nombre) json_out(false, 'El nombre es obligatorio.');
            $detalle = $str($body['detalle'] ?? $_POST['detalle'] ?? null);
            $principio = $str($body['principio_activo'] ?? $_POST['principio_activo'] ?? null);
            $cantidad = $int($body['cantidad_deposito'] ?? $_POST['cantidad_deposito'] ?? 0);
            $tiempo = $str($body['tiempo_carencia'] ?? $_POST['tiempo_carencia'] ?? null);
            $costo = $dec($body['costo_hectarea'] ?? $_POST['costo_hectarea'] ?? 0);
            $activo = $yn($body['activo'] ?? $_POST['activo'] ?? 'si');
            $pat = $arrInt($body['patologias'] ?? $_POST['patologias'] ?? []);
            if (count($pat) > 6) $pat = array_slice($pat, 0, 6);

            $id = $model->createProduct($nombre, $detalle, $principio, $cantidad, $pat, $costo, $activo, $tiempo);
            json_out(true, ['id' => $id]);
            break;

        case 'update':
            if ($method !== 'POST') json_out(false, 'Método no permitido.');
            $id = (int)($body['id'] ?? $_POST['id'] ?? 0);
            if ($id <= 0) json_out(false, 'ID inválido.');
            $nombre = $str($body['nombre'] ?? $_POST['nombre'] ?? null);
            if (!$nombre) json_out(false, 'El nombre es obligatorio.');
            $detalle = $str($body['detalle'] ?? $_POST['detalle'] ?? null);
            $principio = $str($body['principio_activo'] ?? $_POST['principio_activo'] ?? null);
            $cantidad = $int($body['cantidad_deposito'] ?? $_POST['cantidad_deposito'] ?? 0);
            $tiempo = $str($body['tiempo_carencia'] ?? $_POST['tiempo_carencia'] ?? null);
            $costo = $dec($body['costo_hectarea'] ?? $_POST['costo_hectarea'] ?? 0);
            $activo = $yn($body['activo'] ?? $_POST['activo'] ?? 'si');
            $pat = $arrInt($body['patologias'] ?? $_POST['patologias'] ?? []);
            if (count($pat) > 6) $pat = array_slice($pat, 0, 6);

            $ok = $model->updateProduct($id, $nombre, $detalle, $principio, $cantidad, $pat, $costo, $activo, $tiempo);
            json_out(true, ['updated' => $ok]);
            break;


        case 'delete':
            if ($method !== 'POST') json_out(false, 'Método no permitido.');
            $id = (int)($body['id'] ?? $_POST['id'] ?? 0);
            if ($id <= 0) json_out(false, 'ID inválido.');
            $ok = $model->deleteProduct($id);
            json_out(true, ['deleted' => $ok]);
            break;

        case '':
            // Healthcheck
            $connected = ($model instanceof DroneStockModel) && ($pdo instanceof PDO);
            json_out(true, [
                'message' => $connected
                    ? 'Controlador y modelo conectados correctamente Stock'
                    : 'Falla de wiring (revisá require y $pdo)',
                'checks'  => [
                    'modelClass' => get_class($model),
                    'pdo'        => $pdo instanceof PDO,
                ],
            ]);
            break;

        default:
            json_out(false, 'Acción no reconocida.');
    }
} catch (Throwable $e) {
    json_out(false, 'Excepción: ' . $e->getMessage());
}
