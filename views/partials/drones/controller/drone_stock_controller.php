<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// CONFIG global
require_once __DIR__ . '/../../../../config.php';

// MODELO de este módulo
require_once __DIR__ . '/../model/drone_calendar_model.php';

// Instancia e inyección de PDO
$model = new DroneCalendarModel();
$model->pdo = $pdo;

// Healthcheck mínimo (para que la vista pueda verificar wiring)
$connected = ($model instanceof DroneCalendarModel) && ($pdo instanceof PDO);

echo json_encode([
    'ok'      => $connected,
    'message' => $connected
        ? 'Controlador y modelo conectados correctamente Calendario'
        : 'Falla de wiring (revisá require y $pdo)',
    // Datos útiles para debug rápido
    'checks'  => [
        'modelClass' => get_class($model),
        'pdo'        => $pdo instanceof PDO,
    ],
]);
