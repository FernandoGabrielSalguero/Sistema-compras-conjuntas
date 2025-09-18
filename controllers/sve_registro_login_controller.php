<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/sve_registro_login_model.php';

$inisioSesionModel = new InisioSesionModel();

$action = $_GET['action'] ?? $_POST['action'] ?? null;

try {
    if ($action === 'list') {
        $rol = $_GET['rol'] ?? $_POST['rol'] ?? '';
        $usuario_input = $_GET['usuario_input'] ?? $_POST['usuario_input'] ?? '';
        $created_at = $_GET['created_at'] ?? $_POST['created_at'] ?? '';

        $page = isset($_GET['page']) ? (int)$_GET['page'] : (isset($_POST['page']) ? (int)$_POST['page'] : 1);
        if ($page < 1) { $page = 1; }

        $per = isset($_GET['per_page']) ? (int)$_GET['per_page'] : (isset($_POST['per_page']) ? (int)$_POST['per_page'] : 20);
        // No permitir más de 20 por requisitos visuales
        $perPage = max(1, min(20, $per));

        $filters = [
            'rol' => is_string($rol) ? trim($rol) : '',
            'usuario_input' => is_string($usuario_input) ? trim($usuario_input) : '',
            'created_at' => is_string($created_at) ? trim($created_at) : '',
        ];

        $result = $inisioSesionModel->searchLogins($filters, $page, $perPage);

        $total = $result['total'];
        $totalPages = (int)max(1, (int)ceil($total / $perPage));

        echo json_encode([
            'ok' => true,
            'data' => $result['rows'],
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
            ],
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Acción desconocida
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Acción no soportada'], JSON_UNESCAPED_UNICODE);
} catch (\InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error del servidor'], JSON_UNESCAPED_UNICODE);
}
