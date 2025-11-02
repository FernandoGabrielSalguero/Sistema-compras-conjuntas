<?php

declare(strict_types=1);

ini_set('display_errors', '0');
error_reporting(E_ALL);
ob_start();

session_start();
header('Content-Type: application/json; charset=UTF-8');

$mwPath = __DIR__ . '/../middleware/authMiddleware.php';
if (file_exists($mwPath)) {
    require_once $mwPath;
    if (function_exists('checkAccess')) {
        try {
            checkAccess('productor');
        } catch (Throwable $e) {
            http_response_code(403);
            ob_clean();
            echo json_encode(['ok' => false, 'error' => 'Acceso denegado']);
            exit;
        }
    }
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/ing_pulverizacionModel.php';

try {
    $idReal = $_SESSION['id_real'] ?? null;
    if (!$idReal) {
        http_response_code(403);
        ob_clean();
        echo json_encode(['ok' => false, 'error' => 'Sesión inválida']);
        exit;
    }

    $model = new ingPulverizacionModel($pdo);

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? 'list';
        if ($action === 'list') {
            $page = max(1, (int)($_GET['page'] ?? 1));
            $size = min(50, max(1, (int)($_GET['size'] ?? 10)));
            $offset = ($page - 1) * $size;

            $res = $model->listByProductor($idReal, $size, $offset);
            http_response_code(200);
            ob_clean();
            echo json_encode(['ok' => true, 'data' => ['items' => $res['items'], 'total' => $res['total'], 'page' => $page]], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        if ($action === 'detail') {
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) {
                http_response_code(400);
                ob_clean();
                echo json_encode(['ok' => false, 'error' => 'ID inválido']);
                exit;
            }
            $row = $model->detalleById($id, $idReal);
            http_response_code(200);
            ob_clean();
            echo json_encode(['ok' => true, 'data' => $row], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        http_response_code(400);
        ob_clean();
        echo json_encode(['ok' => false, 'error' => 'Acción GET no soportada']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $raw = file_get_contents('php://input') ?: '';
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            http_response_code(400);
            ob_clean();
            echo json_encode(['ok' => false, 'error' => 'JSON inválido']);
            exit;
        }

        if (($data['action'] ?? '') === 'cancel') {
            $id = (int)($data['id'] ?? 0);
            if ($id <= 0) {
                http_response_code(400);
                ob_clean();
                echo json_encode(['ok' => false, 'error' => 'ID inválido']);
                exit;
            }
            $model->cancelar($id, $idReal);
            http_response_code(200);
            ob_clean();
            echo json_encode(['ok' => true, 'message' => 'Cancelado'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        http_response_code(400);
        ob_clean();
        echo json_encode(['ok' => false, 'error' => 'Acción POST no soportada']);
        exit;
    }

    http_response_code(405);
    ob_clean();
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit;
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    ob_clean();
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    exit;
} catch (RuntimeException $e) {
    http_response_code(403);
    ob_clean();
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    ob_clean();
    echo json_encode(['ok' => false, 'error' => 'Error interno.', 'detail' => $e->getMessage()]);
    exit;
}
