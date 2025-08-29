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

// ✅ Lista todos los operativos activos
if (isset($_GET['listar']) && $_GET['listar'] === 'operativos') {
    $stmt = $pdo->query("SELECT id, nombre, fecha_inicio, fecha_cierre FROM operativos WHERE estado = 'abierto' ORDER BY fecha_inicio DESC");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data);
    exit;
}

// ✅ Lista productos de un operativo específico
if (isset($_GET['listar']) && $_GET['listar'] === 'productos_operativo' && isset($_GET['id'])) {
    $operativoId = (int) $_GET['id'];
    $stmt = $pdo->prepare("
        SELECT 
            p.Id as producto_id,
            p.Nombre_producto,
            p.Unidad_Medida_venta,
            p.Precio_producto,
            p.moneda,
            p.alicuota,
            p.categoria,
            p.Detalle_producto
        FROM productos p
        JOIN operativos_productos op ON op.producto_id = p.Id
        WHERE op.operativo_id = ?
        ORDER BY p.categoria, p.Nombre_producto
    ");
    $stmt->execute([$operativoId]);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $agrupados = [];
    foreach ($productos as $p) {
        $categoria = $p['categoria'];
        if (!isset($agrupados[$categoria])) {
            $agrupados[$categoria] = [];
        }
        $agrupados[$categoria][] = $p;
    }

    echo json_encode($agrupados);
    exit;
}