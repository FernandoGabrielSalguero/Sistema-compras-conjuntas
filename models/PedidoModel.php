<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../config.php';

class PedidoModel
{

    // Obtener todas las cooperativas
    public static function getCooperativas()
    {
        global $pdo;
        $query = "SELECT id, nombre FROM usuarios WHERE rol = 'cooperativa'";
        $stmt = $pdo->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener los productores vinculados a una cooperativa
    public static function getProductoresPorCooperativa($id_cooperativa)
    {
        global $pdo;
        $query = "
            SELECT u.id, u.nombre
            FROM Relaciones_Cooperativa_Productores r
            JOIN usuarios u ON r.id_productor = u.id
            WHERE r.id_cooperativa = :id_coop
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['id_coop' => $id_cooperativa]);
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

    // Guardar el pedido y sus detalles
    public static function guardarPedido($pedido, $detalles)
    {
        global $pdo;

        try {
            $pdo->beginTransaction();

            // Insertar pedido
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

            // Insertar detalles del pedido
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

    // Obtener todos los pedidos existentes
    public static function obtenerTodosLosPedidos(): array
    {
        global $pdo;
        $query = "SELECT * FROM pedidos ORDER BY fecha_pedido DESC";
        $stmt = $pdo->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener todos los pedidos para mostrar en la tabla
    public static function getPedidos()
    {
        global $pdo;
        $stmt = $pdo->query("SELECT * FROM pedidos ORDER BY fecha_pedido DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Actualizar un pedido existente
    public static function actualizarPedido($id, $ha_cooperativa, $observaciones)
    {
        global $pdo;

        $stmt = $pdo->prepare("
        UPDATE pedidos
        SET ha_cooperativa = :ha, observaciones = :obs
        WHERE id = :id
    ");

        try {
            $stmt->execute([
                'ha' => $ha_cooperativa,
                'obs' => $observaciones,
                'id' => $id
            ]);
            return ['success' => true, 'message' => '✅ Pedido actualizado correctamente.'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Eliminar un pedido y sus detalles
    public static function eliminarPedido($id)
    {
        global $pdo;

        try {
            $pdo->beginTransaction();

            // Eliminar detalles primero
            $stmtDetalles = $pdo->prepare("DELETE FROM detalle_pedidos WHERE pedido_id = :id");
            $stmtDetalles->execute(['id' => $id]);

            // Luego el pedido
            $stmtPedido = $pdo->prepare("DELETE FROM pedidos WHERE id = :id");
            $stmtPedido->execute(['id' => $id]);

            $pdo->commit();
            return ['success' => true, 'message' => '🗑️ Pedido eliminado correctamente.'];
        } catch (Exception $e) {
            $pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public static function actualizarPedidoCompleto($pedido, $detalles)
    {
        global $pdo;

        try {
            $pdo->beginTransaction();

            // Actualizar datos principales
            $stmt = $pdo->prepare("
                UPDATE pedidos
                SET cooperativa = :cooperativa,
                    productor = :productor,
                    persona_facturacion = :persona_facturacion,
                    condicion_facturacion = :condicion_facturacion,
                    afiliacion = :afiliacion,
                    ha_cooperativa = :ha_cooperativa,
                    total_sin_iva = :total_sin_iva,
                    total_iva = :total_iva,
                    factura = :factura,
                    total_pedido = :total_pedido,
                    observaciones = :observaciones
                WHERE id = :id
            ");

            $stmt->execute([
                'id' => $pedido['id'],
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
                'observaciones' => $pedido['observaciones'],
            ]);

            // Borrar detalles previos
            $pdo->prepare("DELETE FROM detalle_pedidos WHERE pedido_id = :id")->execute(['id' => $pedido['id']]);

            // Insertar nuevos detalles
            $stmt = $pdo->prepare("
                INSERT INTO detalle_pedidos (
                    pedido_id, nombre_producto, detalle_producto, 
                    precio_producto, unidad_medida_venta, categoria, subtotal_por_categoria, alicuota
                )
                VALUES (:pedido_id, :nombre_producto, :detalle_producto, :precio_producto, 
                        :unidad_medida_venta, :categoria, :subtotal_por_categoria, :alicuota)
            ");

            foreach ($detalles as $d) {
                $stmt->execute([
                    'pedido_id' => $pedido['id'],
                    'nombre_producto' => $d['nombre_producto'],
                    'detalle_producto' => $d['detalle_producto'],
                    'precio_producto' => $d['precio_producto'],
                    'unidad_medida_venta' => $d['unidad_medida_venta'],
                    'categoria' => $d['categoria'],
                    'subtotal_por_categoria' => $d['subtotal_por_categoria'],
                    'alicuota' => $detalle['alicuota'] ?? 0
                ]);
            }

            $pdo->commit();
            return ['success' => true, 'message' => '✅ Pedido actualizado correctamente.'];
        } catch (Exception $e) {
            $pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public static function getDetallePedido($pedido_id)
    {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM detalle_pedidos WHERE pedido_id = :id");
        $stmt->execute(['id' => $pedido_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
