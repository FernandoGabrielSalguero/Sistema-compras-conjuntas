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

        if (!$id_coop) {
            error_log("❌ ID de cooperativa no disponible en sesión");
            echo json_encode([]);
            exit;
        }

        $productores = CoopPedidoModel::getProductoresDeCooperativa($id_coop);
        echo json_encode($productores);
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

    case 'getPedidosPorCooperativa':
        $id_coop = $_SESSION['id_cooperativa'] ?? null;
        if ($id_coop) {
            echo json_encode(CoopPedidoModel::getPedidosPorCooperativa($id_coop));
        } else {
            echo json_encode(['error' => 'ID de cooperativa no disponible en sesión']);
        }
        break;

    case 'eliminarPedido':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['id'])) {
                echo json_encode(['success' => false, 'error' => 'Falta el ID del pedido.']);
                exit;
            }

            $resultado = CoopPedidoModel::eliminarPedido($data['id']);
            echo json_encode($resultado);
        } else {
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
        }
        break;

    case 'editarPedido':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);

            $id = $data['id'] ?? null;
            $obs = $data['observaciones'] ?? '';
            $hectareas = $data['ha_cooperativa'] ?? null;
            $detalles = $data['detalles'] ?? [];
            $persona_facturacion = $data['persona_facturacion'] ?? '';
            $condicion_facturacion = $data['condicion_facturacion'] ?? '';
            $afiliacion = $data['afiliacion'] ?? '';


            if (!$id) {
                echo json_encode(['success' => false, 'error' => 'ID faltante']);
                exit;
            }

            $res = CoopPedidoModel::actualizarPedido(
                $id,
                $obs,
                $hectareas,
                $detalles,
                $persona_facturacion,
                $condicion_facturacion,
                $afiliacion
            );
            echo json_encode($res);
        } else {
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
        }
        break;


    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;


    case 'getPedidos':
        $id_cooperativa = $_SESSION['id_cooperativa'] ?? null;
        if (!$id_cooperativa) {
            echo json_encode(['success' => false, 'message' => 'Cooperativa no identificada']);
            exit;
        }

        require_once '../models/CoopPedidoModel.php';
        $model = new CoopPedidoModel();
        $pedidos = $model->getPedidosPorCooperativa($id_cooperativa);
        echo json_encode($pedidos);
        break;

    case 'getDetallesPedido':
        if (!isset($_GET['id'])) {
            echo json_encode(['error' => 'ID faltante']);
            exit;
        }
        $id = $_GET['id'];
        $detalles = CoopPedidoModel::getDetallesPorPedido($id);
        echo json_encode($detalles);
        break;


    case 'agregarProductoAPedido':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);
            $pedido_id = $data['pedido_id'] ?? null;
            $producto_id = $data['producto_id'] ?? null;
            $cantidad = $data['cantidad'] ?? null;

            if (!$pedido_id || !$producto_id || !$cantidad) {
                echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
                exit;
            }

            $res = CoopPedidoModel::agregarProductoAPedido($pedido_id, $producto_id, $cantidad);
            echo json_encode($res);
        } else {
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
        }
        break;
}
