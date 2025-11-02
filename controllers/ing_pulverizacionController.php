<?php

declare(strict_types=1);

ini_set('display_errors', '0');
error_reporting(E_ALL);
ob_start();

session_start();
header('Content-Type: application/json; charset=UTF-8');

$mwPath = __DIR__ . '/../middleware/authMiddleware.php';
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
    $rolSesion = $_SESSION['rol'] ?? null;

    $model = new ingPulverizacionModel($pdo);

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? '';

        // Listado de solicitudes para el ingeniero (con filtros)
        if ($action === 'list_ingeniero') {
            if ($rolSesion !== 'ingeniero') {
                http_response_code(403);
                ob_clean();
                echo json_encode(['ok' => false, 'error' => 'Solo ingeniero'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            $page   = max(1, (int)($_GET['page'] ?? 1));
            $size   = min(50, max(1, (int)($_GET['size'] ?? 20)));
            $offset = ($page - 1) * $size;

            $qProd = trim((string)($_GET['q'] ?? ''));           // nombre productor (LIKE)
            $coop  = trim((string)($_GET['coop'] ?? ''));        // cooperativa_id_real exacto o vacío

            $res = $model->listByIngeniero($idReal, $qProd, $coop, $size, $offset);

            http_response_code(200);
            ob_clean();
            echo json_encode([
                'ok'   => true,
                'data' => [
                    'items' => $res['items'],
                    'total' => $res['total'],
                    'page'  => $page
                ]
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        // Cooperativas asociadas al ingeniero (para el filtro)
        if ($action === 'coops_ingeniero') {
            if ($rolSesion !== 'ingeniero') {
                http_response_code(403);
                ob_clean();
                echo json_encode(['ok' => false, 'error' => 'Solo ingeniero']);
                exit;
            }
            $rows = $model->getCoopsByIngeniero($idReal);
            http_response_code(200);
            ob_clean();
            echo json_encode(['ok' => true, 'data' => $rows], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        http_response_code(400);
        ob_clean();
        echo json_encode(['ok' => false, 'error' => 'Acción GET no soportada']);
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
}
