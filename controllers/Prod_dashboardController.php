<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// Seguridad y sesión
require_once __DIR__ . '/../middleware/authMiddleware.php';
checkAccess('productor');

// CONFIG global y modelo
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/pro_dashboardModel.php';

$response = static function (bool $ok, $dataOrError = null): void {
    if ($ok) {
        echo json_encode(['ok' => true, 'data' => $dataOrError], JSON_UNESCAPED_UNICODE);
    } else {
        $msg = is_string($dataOrError) ? $dataOrError : 'Operación no realizada';
        echo json_encode(['ok' => false, 'error' => $msg], JSON_UNESCAPED_UNICODE);
    }
    exit;
};

try {
    $model = new ProdDashboardModel();
    $model->pdo = $pdo;

    // --- Resolver usuario_id desde la sesión ---
    // Supuestos:
    // 1) $_SESSION['usuario_id'] existe (int). Si no, intento con id_real -> usuarios.id.
    if (!isset($_SESSION)) { session_start(); }
    $usuarioId = isset($_SESSION['usuario_id']) ? (int) $_SESSION['usuario_id'] : 0;

    if ($usuarioId <= 0 && !empty($_SESSION['id_real'])) {
        // Fallback: buscar por id_real
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE id_real = :idr LIMIT 1");
        $stmt->execute([':idr' => $_SESSION['id_real']]);
        $usuarioId = (int) ($stmt->fetchColumn() ?: 0);
    }

    if ($usuarioId <= 0) {
        $response(false, 'No se pudo identificar al usuario en sesión.');
    }

    // --- Routing simple por método ---
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'GET') {
        $data = $model->getContactoByUsuarioId($usuarioId);
        $response(true, $data);
    }

    if ($method === 'POST') {
        $raw = file_get_contents('php://input');
        $payload = json_decode($raw ?: '', true, 512, JSON_THROW_ON_ERROR);

        $correo = isset($payload['correo']) ? trim((string)$payload['correo']) : '';
        $telefono = isset($payload['telefono']) ? trim((string)$payload['telefono']) : '';

        // Sanitización básica
        if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $response(false, 'Correo inválido.');
        }
        if ($telefono === '' || mb_strlen($telefono) < 6) {
            $response(false, 'Teléfono inválido.');
        }

        $ok = $model->upsertContacto($usuarioId, $correo, $telefono);
        if ($ok) {
            $response(true, ['correo' => $correo, 'telefono' => $telefono, 'completo' => true]);
        }
        $response(false, 'No se pudo guardar la información.');
    }

    // Método no permitido
    http_response_code(405);
    $response(false, 'Método no permitido.');
} catch (Throwable $e) {
    http_response_code(500);
    $response(false, 'Error interno: ' . $e->getMessage());
}
