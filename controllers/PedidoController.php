<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../models/PedidoModel.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? null;

// if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
//     http_response_code(405);
//     echo json_encode(['success' => false, 'error' => 'Método no permitido']);
//     exit;
// }
 
header('Content-Type: application/json');

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
        
    // case 'actualizarPedidoCompleto':
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    //         $json = file_get_contents("php://input");
    //         $data = json_decode($json, true);

    //         if (!$data) {
    //             echo json_encode(['success' => false, 'error' => 'JSON inválido.']);
    //             exit;
    //         }

    //         $pedido = $data['pedido'] ?? [];
    //         $detalles = $data['detalles'] ?? [];

    //         if (isset($pedido['id'])) {
    //             echo json_encode(PedidoModel::actualizarPedidoCompleto($pedido, $detalles));
    //         } else {
    //             echo json_encode(['success' => false, 'error' => 'ID faltante para actualización.']);
    //         }
    //     } else {
    //         echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    //     }
    //     break;

case 'actualizarPedidoCompleto':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
        $json = file_get_contents("php://input");
        $data = json_decode($json, true);

        if (!$data) {
            echo json_encode(['success' => false, 'error' => 'JSON inválido', 'raw' => $json]);
            exit;
        }

        $pedido = $data['pedido'] ?? [];
        $detalles = $data['detalles'] ?? [];

        if (!isset($pedido['id'])) {
            echo json_encode(['success' => false, 'error' => 'ID faltante']);
            exit;
        }

        // 👇 DEBUG temporal
        echo json_encode([
            'success' => true,
            'debug' => [
                'pedido' => $pedido,
                'detalles' => $detalles
            ]
        ]);
        exit;

        // Luego de confirmar que los datos llegan bien, podés comentar el bloque anterior y ejecutar esto:
        // echo json_encode(PedidoModel::actualizarPedidoCompleto($pedido, $detalles));
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
