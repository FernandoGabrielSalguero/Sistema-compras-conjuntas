<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../config.php';

class CoopPedidoModel
{
    // Obtener los productores vinculados a la cooperativa logueada
    public static function getProductoresDeCooperativa($id_coop)
    {
        global $pdo;
        $query = "
            SELECT u.id_productor, u.nombre
            FROM Relaciones_Cooperativa_Productores r
            JOIN usuarios u ON r.id_productor = u.id_productor
            WHERE r.id_cooperativa = :id_coop
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['id_coop' => $id_coop]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener todos los productos organizados por categoría
    public static function getProductosPorCategoria()
    {
        global $pdo;
        $query = "SELECT * FROM productos ORDER BY categoria";
        $stmt = $pdo->query($query);
        $productos = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $cat = $row['categoria'];
            if (!isset($productos[$cat])) {
                $productos[$cat] = [];
            }
            $productos[$cat][] = $row;
        }

        return $productos;
    }

    // Guardar pedido realizado por la cooperativa
    public static function guardarPedido($pedido, $detalles)
    {
        global $pdo;

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO pedidos (
                    cooperativa, productor, fecha_pedido, persona_facturacion, 
                    condicion_facturacion, afiliacion, ha_cooperativa, 
                    total_sin_iva, total_iva, factura, total_pedido, observaciones
                )
                VALUES (:cooperativa, :productor, NOW(), :persona_facturacion, :condicion_facturacion, 
                        :afiliacion, :ha_cooperativa, :total_sin_iva, :total_iva, :factura, 
                        :total_pedido, :observaciones)
            ");

            $stmt->execute([
                'cooperativa' => $pedido['cooperativa'],
                'productor' => $pedido['productor'],
                'persona_facturacion' => $pedido['persona_facturacion'],
                'condicion_facturacion' => $pedido['condicion_facturacion'],
                'afiliacion' => $pedido['afiliacion'],
                'ha_cooperativa' => $pedido['ha_cooperativa'],
                'total_sin_iva' => $pedido['total_sin_iva'],
                'total_iva' => $pedido['total_iva'],
                'factura' => $pedido['factura'],
                'total_pedido' => $pedido['total_pedido'],
                'observaciones' => $pedido['observaciones']
            ]);

            $pedido_id = $pdo->lastInsertId();

            $stmt = $pdo->prepare("
                INSERT INTO detalle_pedidos (
                    pedido_id, nombre_producto, detalle_producto, 
                    precio_producto, unidad_medida_venta, categoria, subtotal_por_categoria, alicuota
                )
                VALUES (:pedido_id, :nombre_producto, :detalle_producto, :precio_producto, 
                        :unidad_medida_venta, :categoria, :subtotal_por_categoria, :alicuota)
            ");

            foreach ($detalles as $detalle) {
                $stmt->execute([
                    'pedido_id' => $pedido_id,
                    'nombre_producto' => $detalle['nombre_producto'],
                    'detalle_producto' => $detalle['detalle_producto'],
                    'precio_producto' => $detalle['precio_producto'],
                    'unidad_medida_venta' => $detalle['unidad_medida_venta'],
                    'categoria' => $detalle['categoria'],
                    'subtotal_por_categoria' => $detalle['subtotal_por_categoria'],
                    'alicuota' => $detalle['alicuota'] ?? 0
                ]);
            }

            $pdo->commit();
            return ['success' => true, 'message' => '✅ Pedido guardado correctamente.'];
        } catch (Exception $e) {
            $pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public static function getPedidosPorCooperativa($id_coop)
    {
        global $pdo;
        $query = "
        SELECT 
            p.id, 
            p.fecha_pedido, 
            u.nombre AS productor, 
            p.productor AS productor_id,
            p.total_sin_iva, 
            p.total_iva, 
            p.total_pedido, 
            p.observaciones,
            p.persona_facturacion,
            p.condicion_facturacion,
            p.afiliacion,
            p.ha_cooperativa
        FROM pedidos p
        LEFT JOIN usuarios u ON p.productor = u.id_productor
        WHERE p.cooperativa = :id_coop
        ORDER BY p.fecha_pedido DESC
    ";

        $stmt = $pdo->prepare($query);
        $stmt->execute(['id_coop' => $id_coop]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Eliminar pedidos
    public static function eliminarPedido($id)
    {
        global $pdo;

        try {
            $stmt = $pdo->prepare("DELETE FROM pedidos WHERE id = :id");
            $stmt->execute(['id' => $id]);

            return ['success' => true, 'message' => 'Pedido eliminado correctamente'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // actualziar pedido
    public static function actualizarPedido($id, $observaciones, $ha_cooperativa, $detalles = [], $persona_facturacion, $condicion_facturacion, $afiliacion)
    {
        global $pdo;

        try {
            $pdo->beginTransaction();

            // Actualizar encabezado
            $stmt = $pdo->prepare("
            UPDATE pedidos 
            SET observaciones = :obs,
                ha_cooperativa = :ha,
                persona_facturacion = :pf,
                condicion_facturacion = :cf,
                afiliacion = :af
            WHERE id = :id
        ");

            $stmt->execute([
                'obs' => $observaciones,
                'ha' => $ha_cooperativa,
                'pf' => $persona_facturacion,
                'cf' => $condicion_facturacion,
                'af' => $afiliacion,
                'id' => $id
            ]);

            // Eliminar detalles existentes
            $stmt = $pdo->prepare("DELETE FROM detalle_pedidos WHERE pedido_id = :id");
            $stmt->execute(['id' => $id]);

            // Insertar nuevos detalles
            $stmt = $pdo->prepare("
            INSERT INTO detalle_pedidos (
                pedido_id, nombre_producto, detalle_producto, 
                precio_producto, unidad_medida_venta, categoria, subtotal_por_categoria, alicuota, cantidad
            )
            SELECT 
                :pedido_id, p.nombre_producto, p.detalle_producto,
                p.precio_venta AS precio_producto, p.unidad_medida_venta, p.categoria,
                0 AS subtotal_por_categoria, p.alicuota, :cantidad
            FROM productos p
            WHERE p.id = :producto_id
        ");

            foreach ($detalles as $detalle) {
                $stmt->execute([
                    'pedido_id' => $id,
                    'producto_id' => $detalle['id'],
                    'cantidad' => $detalle['cantidad']
                ]);
            }

            $pdo->commit();
            return ['success' => true, 'message' => '✅ Pedido actualizado con productos'];
        } catch (Exception $e) {
            $pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // obtener detalles del pedido
    public static function getDetallesPorPedido($id_pedido)
    {
        global $pdo;
        $query = "SELECT * FROM detalle_pedidos WHERE pedido_id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['id' => $id_pedido]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function agregarProductoAPedido($pedido_id, $producto_id, $cantidad)
{
    global $pdo;

    try {
        // Validar que no exista ya el producto
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM detalle_pedidos WHERE pedido_id = :pedido_id AND nombre_producto = (SELECT nombre_producto FROM productos WHERE id = :producto_id)");
        $stmtCheck->execute([
            'pedido_id' => $pedido_id,
            'producto_id' => $producto_id
        ]);

        if ($stmtCheck->fetchColumn() > 0) {
            return ['success' => false, 'error' => 'Producto ya existe en el pedido'];
        }

        // Insertar producto al detalle del pedido
        $stmt = $pdo->prepare("INSERT INTO detalle_pedidos (
            pedido_id, nombre_producto, detalle_producto, 
            precio_producto, unidad_medida_venta, categoria, 
            subtotal_por_categoria, alicuota, cantidad
        )
        SELECT 
            :pedido_id, p.nombre_producto, p.detalle_producto,
            p.precio_venta, p.unidad_medida_venta, p.categoria,
            0, p.alicuota, :cantidad
        FROM productos p WHERE p.id = :producto_id");

        $stmt->execute([
            'pedido_id' => $pedido_id,
            'producto_id' => $producto_id,
            'cantidad' => $cantidad
        ]);

        return ['success' => true];
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
}
