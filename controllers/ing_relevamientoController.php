<?php

declare(strict_types=1);

ini_set('display_errors', '0');
error_reporting(E_ALL);
ob_start();

session_start();
header('Content-Type: application/json; charset=UTF-8');

$mwPath = __DIR__ . '/../middleware/authMiddleware.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/ing_relevamientoModel.php';
require_once $mwPath;

checkAccess('ingeniero');

try {
    $idReal = $_SESSION['id_real'] ?? null;
    if (!$idReal) {
        http_response_code(403);
        ob_clean();
        echo json_encode(['ok' => false, 'error' => 'Sesión inválida']);
        exit;
    }

    $rolSesion = $_SESSION['rol'] ?? null;

    /** @var PDO $pdo viene desde config.php */
    $model = new ingRelevamientoModel($pdo);

    // Solo respondemos a GET, el resto 405
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'cooperativas':
                // Cooperativas asociadas al ingeniero en sesión
                $coops = $model->getCoopsByIngeniero($idReal);

                http_response_code(200);
                ob_clean();
                echo json_encode([
                    'ok'   => true,
                    'data' => $coops,
                ]);
                exit;

            case 'productores':
                // Productores de una coop específica, limitada al ingeniero
                $coopIdReal = $_GET['coop_id_real'] ?? '';

                if ($coopIdReal === '') {
                    http_response_code(400);
                    ob_clean();
                    echo json_encode([
                        'ok'    => false,
                        'error' => 'Parámetro coop_id_real es requerido',
                    ]);
                    exit;
                }

                $productores = $model->getProductoresByCooperativa($coopIdReal, $idReal);

                http_response_code(200);
                ob_clean();
                echo json_encode([
                    'ok'   => true,
                    'data' => $productores,
                ]);
                exit;

            default:
                http_response_code(400);
                ob_clean();
                echo json_encode([
                    'ok'    => false,
                    'error' => 'Acción inválida',
                ]);
                exit;
        }
    }

    // Si no es GET, método no permitido
    http_response_code(405);
    ob_clean();
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit;
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    ob_clean();
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    exit;
} catch (Throwable $e) {
    // Log para el servidor
    error_log('[ing_relevamientoController] ' . $e->getMessage());
    error_log($e->getTraceAsString());

    // Devolver detalle al frontend mientras depuramos
    http_response_code(500);
    ob_clean();
    echo json_encode([
        'ok'    => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(), // opcional, útil mientras desarrollás
    ]);
    exit;
}
