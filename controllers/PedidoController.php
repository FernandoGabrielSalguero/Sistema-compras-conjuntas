<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../models/PedidoModel.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? null;

switch ($action) {
    case 'getCooperativas':
        echo json_encode(PedidoModel::getCooperativas());
        break;

    case 'getProductores':
        $id_coop = $_GET['id'] ?? null;
        if ($id_coop) {
            echo json_encode(PedidoModel::getProductoresPorCooperativa($id_coop));
        } else {
            echo json_encode(['error' => 'ID de cooperativa no proporcionado.']);
        }
        break;

    case 'getProductos':
        echo json_encode(PedidoModel::getProductosPorCategoria());
        break;

    case 'guardarPedido':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);

            $pedido = $data['pedido'] ?? [];
            $detalles = $data['detalles'] ?? [];

            $resultado = PedidoModel::guardarPedido($pedido, $detalles);
            echo json_encode($resultado);
        } else {
            echo json_encode(['error' => 'Método no permitido']);
        }
        break;

    case 'getPedidos':
        echo json_encode(PedidoModel::obtenerTodosLosPedidos());
        break;

    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;

    case 'getPedidosParaEdicion':
        echo json_encode(PedidoModel::getPedidos());
        break;

    case 'actualizarPedido':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);
            $id = $data['id'] ?? null;
            $ha = $data['ha_cooperativa'] ?? null;
            $obs = $data['observaciones'] ?? '';

            if ($id && $ha !== null) {
                echo json_encode(PedidoModel::actualizarPedido($id, $ha, $obs));
            } else {
                echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
        }
        break;

    case 'eliminarPedido':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);
            $id = $data['id'] ?? null;

            if ($id) {
                echo json_encode(PedidoModel::eliminarPedido($id));
            } else {
                echo json_encode(['success' => false, 'error' => 'ID no proporcionado']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
        }
        break;

    case 'actualizarPedidoCompleto':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);

            $pedido = $data['pedido'] ?? [];
            $detalles = $data['detalles'] ?? [];

            if (isset($pedido['id'])) {
                echo json_encode(PedidoModel::actualizarPedidoCompleto($pedido, $detalles));
            } else {
                echo json_encode(['success' => false, 'error' => 'ID faltante para actualización.']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
        }
        break;

    case 'getDetallePedido':
        $id = $_GET['id'] ?? null;
        if ($id) {
            echo json_encode(PedidoModel::getDetallePedido($id));
        } else {
            echo json_encode([]);
        }
        break;
}
