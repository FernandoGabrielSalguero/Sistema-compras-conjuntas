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

// 🔸 EDITAR PEDIDO
if (isset($json['accion']) && $json['accion'] === 'editar_pedido') {
    $pedidoId = intval($json['id']);
    $productos = $json['productos'] ?? [];

    if (!$pedidoId || empty($productos)) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        exit;
    }

    try {
        // 1. Actualizar encabezado del pedido
        $stmt = $pdo->prepare("UPDATE pedidos SET 
            persona_facturacion = ?, 
            condicion_facturacion = ?, 
            afiliacion = ?, 
            ha_cooperativa = ?, 
            observaciones = ?, 
            total_sin_iva = ?, 
            total_iva = ?, 
            total_pedido = ?
            WHERE id = ?");
        
        $totalSinIva = 0;
        $totalIva = 0;
        
        foreach ($productos as $prod) {
            $sub = floatval($prod['precio']) * intval($prod['cantidad']);
            $ivaCalc = $sub * (floatval($prod['alicuota']) / 100);
            $totalSinIva += $sub;
            $totalIva += $ivaCalc;
        }

        $stmt->execute([
            $json['persona_facturacion'],
            $json['condicion_facturacion'],
            $json['afiliacion'],
            $json['hectareas'],
            $json['observaciones'],
            $totalSinIva,
            $totalIva,
            $totalSinIva + $totalIva,
            $pedidoId
        ]);

        // 2. Eliminar productos anteriores
        $pdo->prepare("DELETE FROM detalle_pedidos WHERE pedido_id = ?")->execute([$pedidoId]);

        // 3. Insertar los nuevos productos
        $stmtProd = $pdo->prepare("INSERT INTO detalle_pedidos 
            (pedido_id, producto_id, nombre_producto, categoria, unidad_medida_venta, cantidad, precio_producto, alicuota)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($productos as $p) {
            $stmtProd->execute([
                $pedidoId,
                intval($p['id']),
                $p['nombre'],
                $p['categoria'],
                $p['unidad'],
                intval($p['cantidad']),
                floatval($p['precio']),
                floatval($p['alicuota'])
            ]);
        }

        echo json_encode(['success' => true]);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al actualizar pedido: ' . $e->getMessage()]);
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

// ❌ Si llega acá, no hay endpoint válido
http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Solicitud no válida']);
exit;
