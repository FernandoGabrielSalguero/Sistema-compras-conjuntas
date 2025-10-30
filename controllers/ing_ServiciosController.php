<?php
declare(strict_types=1);

ini_set('display_errors', '0'); // evitar fuga en prod
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/ing_ServiciosModel.php';

function respond(array $payload, int $code = 200): void {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Autorización (usar middleware en vistas; acá se refuerza)
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ingeniero') {
        respond(['ok' => false, 'error' => 'Acceso denegado.'], 403);
    }

    // Sanitizar inputs
    $action = isset($_GET['action']) ? (string)$_GET['action'] : '';
    $model  = new IngServiciosModel($pdo);

    switch ($action) {
        case 'cooperativas_del_ingeniero': {
            $idReal = isset($_SESSION['id_real']) ? (string)$_SESSION['id_real'] : '';
            if ($idReal === '') {
                respond(['ok' => false, 'error' => 'Ingeniero no identificado'], 400);
            }
            $data = $model->getCooperativasByIngeniero($idReal);
            respond(['ok' => true, 'data' => $data]);
        }

        case 'productores_por_coop': {
            $coop = isset($_GET['cooperativa_id_real']) ? trim((string)$_GET['cooperativa_id_real']) : '';
            if ($coop === '') {
                respond(['ok' => false, 'error' => 'Parámetro cooperativa_id_real requerido'], 400);
            }
            $data = $model->getProductoresByCooperativa($coop);
            respond(['ok' => true, 'data' => $data]);
        }

        default:
            respond(['ok' => false, 'error' => 'Acción no soportada'], 404);
    }
} catch (Throwable $e) {
    error_log('[ing_ServiciosController] ' . $e->getMessage());
    respond(['ok' => false, 'error' => 'Error interno del servidor'], 500);
}
