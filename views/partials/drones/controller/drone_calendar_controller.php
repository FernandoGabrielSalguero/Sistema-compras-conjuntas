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

    // Ruteo por action
    $action = $_GET['action'] ?? $_POST['action'] ?? null;

    // META (pilotos/zonas)
    if ($action === 'meta') {
        $pilotos = $model->getPilots();
        $zonas   = $model->getZones();
        respond(['ok'=>true, 'data'=>['pilotos'=>$pilotos,'zonas'=>$zonas]]);
    }

    // CRUD notas
    if ($action === 'note_create') {
        $fecha = (string)($_POST['fecha'] ?? '');
        $texto = (string)($_POST['texto'] ?? '');
        $pilotoId = isset($_POST['piloto_id']) && $_POST['piloto_id'] !== '' ? (int)$_POST['piloto_id'] : null;
        $zona = isset($_POST['zona']) && $_POST['zona'] !== '' ? $_POST['zona'] : null;
        if (!$fecha || !$texto) respond(['ok'=>false,'error'=>'Fecha y texto son requeridos']);
        $actor = $_POST['actor'] ?? null;
        $id = $model->createNote($fecha, $texto, $pilotoId, $zona, $actor);
        respond(['ok'=>true,'data'=>['id'=>$id]]);
    }
    if ($action === 'note_update') {
        $id = (int)($_POST['id'] ?? 0);
        $texto = (string)($_POST['texto'] ?? '');
        if ($id<=0 || $texto==='') respond(['ok'=>false,'error'=>'ID y texto requeridos']);
        $ok = $model->updateNote($id, $texto);
        respond(['ok'=>$ok]);
    }
    if ($action === 'note_delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id<=0) respond(['ok'=>false,'error'=>'ID requerido']);
        $ok = $model->deleteNote($id);
        respond(['ok'=>$ok]);
    }

    // CALENDARIO (mes)
    $year  = isset($_GET['year']) ? (int)$_GET['year'] : null;
    $month = isset($_GET['month']) ? (int)$_GET['month'] : null;

    // Healthcheck si no vienen parámetros
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

    // Filtros opcionales
    $pilotoId = isset($_GET['piloto_id']) && $_GET['piloto_id'] !== '' ? (int)$_GET['piloto_id'] : null;
    $zona     = isset($_GET['zona']) && $_GET['zona'] !== '' ? (string)$_GET['zona'] : null;

    // Rango [primer día del mes, último día del mes]
    $from = sprintf('%04d-%02d-01', $year, $month);
    $to   = date('Y-m-t', strtotime($from));

    $visitas = $model->getVisitsBetween($from, $to, $pilotoId, $zona);
    $notas   = $model->getNotesBetween($from, $to, $pilotoId, $zona);

    // Normalizo
    $visitasNorm = array_map(static function(array $r): array {
        return [
            'fecha'      => $r['fecha'],
            'hora_desde' => $r['hora_desde'] ?? null,
            'hora_hasta' => $r['hora_hasta'] ?? null,
            'nombre'     => $r['nombre'] ?? '—',
            'piloto'     => $r['piloto'] ?? null,
            'zona'       => $r['zona'] ?? null,
        ];
    }, $visitas);

    respond(['ok' => true, 'data' => ['visitas'=>$visitasNorm, 'notas'=>$notas]]);
} catch (Throwable $e) {
    respond(['ok' => false, 'error' => $e->getMessage()]);
}
