<?php
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

    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;
}
