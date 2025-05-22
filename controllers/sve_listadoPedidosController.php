<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/errores.log');
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/sve_MercadoDigitalModel.php';

$model = new SveMercadoDigitalModel($pdo);

// 🔹 Obtener resumen para tarjetas
if (isset($_GET['resumen']) && $_GET['resumen'] == 1) {
    try {
        $resumen = $model->obtenerResumenPedidos();
        echo json_encode([
            'success' => true,
            'data' => $resumen
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener resumen: ' . $e->getMessage()
        ]);
    }
    exit;
}

// 🔹 Obtener listado de pedidos con paginación y búsqueda
if (isset($_GET['listar']) && $_GET['listar'] == 1) {
    $search = $_GET['search'] ?? '';
    $page = (int) ($_GET['page'] ?? 1);
    $limit = 25;
    $offset = ($page - 1) * $limit;

    try {
        $pedidos = $model->obtenerListadoPedidos($search, $offset, $limit);
        $total = $model->contarPedidosFiltrados($search);

        echo json_encode([
            'success' => true,
            'data' => $pedidos,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener pedidos: ' . $e->getMessage()
        ]);
    }
    exit;
}

// ❌ Si llega acá, no hay endpoint válido
http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Solicitud no válida']);
exit;

// ELIMINAR PEDIDO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = json_decode(file_get_contents("php://input"), true);

    if (isset($json['accion']) && $json['accion'] === 'eliminar_pedido') {
        $id = intval($json['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM pedidos WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar pedido']);
        }

        exit;
    }
}

