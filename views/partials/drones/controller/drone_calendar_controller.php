<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../model/drone_calendar_model.php';

function respond(array $payload): never {
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    if (!($pdo instanceof PDO)) {
        respond(['ok' => false, 'error' => 'PDO no disponible']);
    }

    $model = new DroneCalendarModel();
    $model->pdo = $pdo;

    // Si no vienen parámetros, devolvemos healthcheck
    $year  = isset($_GET['year']) ? (int)$_GET['year'] : null;
    $month = isset($_GET['month']) ? (int)$_GET['month'] : null;

    if (!$year || !$month) {
        $connected = ($model instanceof DroneCalendarModel);
        respond([
            'ok' => $connected,
            'message' => $connected ? 'Controlador y modelo conectados correctamente Calendario' : 'Falla de wiring',
            'checks' => ['modelClass' => get_class($model), 'pdo' => $pdo instanceof PDO],
        ]);
    }

    // Sanitización básica (rango razonable)
    if ($year < 2000 || $year > 2100 || $month < 1 || $month > 12) {
        respond(['ok' => false, 'error' => 'Parámetros de fecha inválidos']);
    }

    // Rango [primer día del mes, último día del mes]
    $from = sprintf('%04d-%02d-01', $year, $month);
    $to   = date('Y-m-t', strtotime($from));

    $rows = $model->getVisitsBetween($from, $to);

    // Normalizo fecha a YYYY-MM-DD
    $data = array_map(static function(array $r): array {
        return [
            'fecha'      => $r['fecha'],
            'hora_desde' => $r['hora_desde'] ?? null,
            'hora_hasta' => $r['hora_hasta'] ?? null,
            'nombre'     => $r['nombre'] ?? '—',
        ];
    }, $rows);

    respond(['ok' => true, 'data' => $data]);
} catch (Throwable $e) {
    respond(['ok' => false, 'error' => $e->getMessage()]);
}
