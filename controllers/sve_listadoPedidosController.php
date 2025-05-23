<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/errores.log');
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/sve_MercadoDigitalModel.php';

$model = new SveMercadoDigitalModel($pdo);

// 🔸 ELIMINAR PEDIDO (manejar primero los POST)
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

// Actualziar pedidos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = json_decode(file_get_contents("php://input"), true);

    // 🔄 EDITAR PEDIDO
    if (isset($json['accion']) && $json['accion'] === 'editar_pedido') {
        $id = intval($json['id'] ?? 0);
        if (!$id || !is_array($json['productos'])) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
            exit;
        }

        try {
            $model->actualizarPedido($id, $json);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()]);
        }

        exit;
    }
}

// agregar productos al pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if ($input['accion'] === 'agregar_producto_pedido') {
        require_once '../../config/conexion.php'; // Asegurate que esto conecta con PDO

        $pedido_id = (int)$input['pedido_id'];
        $producto_id = (int)$input['producto_id'];
        $cantidad = (int)$input['cantidad'];

        if (!$pedido_id || !$producto_id || $cantidad <= 0) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
            exit;
        }

        try {
            // Obtener el producto
            $stmt = $pdo->prepare("SELECT * FROM productos WHERE Id = ?");
            $stmt->execute([$producto_id]);
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$producto) {
                echo json_encode(['success' => false, 'message' => 'Producto no encontrado.']);
                exit;
            }

            // Calcular IVA y totales
            $precio = floatval($producto['Precio_producto']);
            $alicuota = floatval($producto['alicuota']);
            $subtotal = $precio * $cantidad;
            $iva = $subtotal * $alicuota / 100;
            $total = $subtotal + $iva;

            // Insertar en detalle_pedidos
            $stmt = $pdo->prepare("
                INSERT INTO detalle_pedidos (pedido_id, producto_id, nombre_producto, precio_producto, unidad_medida_venta, categoria, cantidad, alicuota)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $pedido_id,
                $producto_id,
                $producto['Nombre_producto'],
                $precio,
                $producto['Unidad_Medida_venta'],
                $producto['categoria'],
                $cantidad,
                $alicuota
            ]);

            // Recalcular totales del pedido
            $stmt = $pdo->prepare("
                SELECT 
                    SUM(precio_producto * cantidad) AS subtotal,
                    SUM((precio_producto * cantidad) * (alicuota / 100)) AS iva
                FROM detalle_pedidos
                WHERE pedido_id = ?
            ");
            $stmt->execute([$pedido_id]);
            $totales = $stmt->fetch(PDO::FETCH_ASSOC);

            $total_sin_iva = round($totales['subtotal'], 2);
            $total_iva = round($totales['iva'], 2);
            $total_pedido = $total_sin_iva + $total_iva;

            // Actualizar tabla pedidos
            $stmt = $pdo->prepare("UPDATE pedidos SET total_sin_iva = ?, total_iva = ?, total_pedido = ? WHERE id = ?");
            $stmt->execute([$total_sin_iva, $total_iva, $total_pedido, $pedido_id]);

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error en servidor: ' . $e->getMessage()]);
        }
        exit;
    }
}


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

// 🔎 Ver pedido completo por ID
if (isset($_GET['ver']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);

    try {
        $stmt = $pdo->prepare("
    SELECT
        p.*,
        i1.nombre AS nombre_cooperativa,
        i2.nombre AS nombre_productor
    FROM pedidos p
    JOIN usuarios u1 ON u1.id_real = p.cooperativa
    JOIN usuarios_info i1 ON i1.usuario_id = u1.id
    JOIN usuarios u2 ON u2.id_real = p.productor
    JOIN usuarios_info i2 ON i2.usuario_id = u2.id
    WHERE p.id = ?
");
        $stmt->execute([$id]);
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pedido) {
            echo json_encode(['success' => false, 'message' => 'Pedido no encontrado']);
        } else {
            // Cargar productos
            $stmtProd = $pdo->prepare("SELECT * FROM detalle_pedidos WHERE pedido_id = ?");
            $stmtProd->execute([$id]);
            $productos = $stmtProd->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data' => $pedido,
                'productos' => $productos
            ]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al consultar el pedido']);
    }
    exit;
}

// Obtener todos los productos
if (isset($_GET['productos']) && $_GET['productos'] == 1) {
    try {
        $productos = $model->obtenerProductosAgrupadosPorCategoria();
        echo json_encode(['success' => true, 'data' => $productos]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener productos']);
    }
    exit;
}

// ❌ Si llega acá, no hay endpoint válido
http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Solicitud no válida']);
exit;
