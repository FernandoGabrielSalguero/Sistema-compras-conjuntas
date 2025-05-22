<?php
// Silenciar errores en pantalla, pero seguir registrándolos en logs
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/errores.log');
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/sve_MercadoDigitalModel.php';

$model = new SveMercadoDigitalModel($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    file_put_contents(__DIR__ . '/../debug_payload.log', print_r($data, true));

    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['accion']) || $data['accion'] !== 'guardar_pedido') {
        echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
        exit;
    }

    try {
        $resultado = $model->guardarPedidoConDetalles($data);
        echo json_encode(['success' => true, 'message' => 'Pedido guardado con éxito', 'pedido_id' => $resultado]);
    } catch (Exception $e) {
        http_response_code(500);
        error_log("🧨 Error al guardar pedido: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error al guardar el pedido: ' . $e->getMessage()
        ]);
    }
    exit;
}

if (isset($_GET['listar']) && $_GET['listar'] === 'cooperativas') {
    $data = $model->listarCooperativas();
    echo json_encode($data);
    exit;
}

if (isset($_GET['listar']) && $_GET['listar'] === 'productores' && isset($_GET['coop_id'])) {
    $data = $model->listarProductoresPorCooperativa($_GET['coop_id']);
    echo json_encode($data);
    exit;
}

if (isset($_GET['listar']) && $_GET['listar'] === 'productos_categorizados') {
    $data = $model->obtenerProductosAgrupadosPorCategoria();
    echo json_encode($data);
    exit;
}
