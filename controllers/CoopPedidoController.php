<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../models/CoopPedidoModel.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? null;

switch ($action) {

    case 'getProductores':
        $id_coop = $_SESSION['id_cooperativa'] ?? null;
        if ($id_coop) {
            echo json_encode(CoopPedidoModel::getProductoresDeCooperativa($id_coop));
        } else {
            echo json_encode(['error' => 'ID de cooperativa no disponible en sesión']);
        }
        break;

    case 'getProductos':
        echo json_encode(CoopPedidoModel::getProductosPorCategoria());
        break;

    case 'guardarPedido':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);
            $pedido = $data['pedido'] ?? [];
            $detalles = $data['detalles'] ?? [];

            $resultado = CoopPedidoModel::guardarPedido($pedido, $detalles);
            echo json_encode($resultado);
        } else {
            echo json_encode(['error' => 'Método no permitido']);
        }
        break;

    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;
}
