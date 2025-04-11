<?php
require_once __DIR__ . '/../config.php';

class PedidoModel {

    public static function getCooperativas() {
        global $pdo;
        $query = "SELECT id, nombre FROM usuarios WHERE rol = 'cooperativa'";
        $stmt = $pdo->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getProductoresPorCooperativa($id_cooperativa) {
        global $db;
        $stmt = $db->prepare("
            SELECT u.id, u.nombre
            FROM Relaciones_Cooperativa_Productores r
            JOIN usuarios u ON r.id_productor = u.id
            WHERE r.id_cooperativa = ?
        ");
        $stmt->bind_param("i", $id_cooperativa);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function getProductosPorCategoria() {
        global $db;
        $query = "SELECT * FROM productos ORDER BY categoria";
        $result = $db->query($query);
        $productos = [];

        while ($row = $result->fetch_assoc()) {
            $cat = $row['categoria'];
            if (!isset($productos[$cat])) {
                $productos[$cat] = [];
            }
            $productos[$cat][] = $row;
        }

        return $productos;
    }

    public static function guardarPedido($pedido, $detalles) {
        global $db;
        $db->begin_transaction();

        try {
            $stmt = $db->prepare("
                INSERT INTO pedidos (
                    cooperativa, productor, fecha_pedido, persona_facturacion, 
                    condicion_facturacion, afiliacion, ha_cooperativa, 
                    total_sin_iva, total_iva, factura, total_pedido, observaciones
                )
                VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "iisssidddds",
                $pedido['cooperativa'],
                $pedido['productor'],
                $pedido['persona_facturacion'],
                $pedido['condicion_facturacion'],
                $pedido['afiliacion'],
                $pedido['ha_cooperativa'],
                $pedido['total_sin_iva'],
                $pedido['total_iva'],
                $pedido['factura'],
                $pedido['total_pedido'],
                $pedido['observaciones']
            );

            $stmt->execute();
            $pedido_id = $db->insert_id;

            foreach ($detalles as $detalle) {
                $stmt = $db->prepare("
                    INSERT INTO detalle_pedidos (
                        pedido_id, nombre_producto, detalle_producto, 
                        precio_producto, unidad_medida_venta, categoria, subtotal_por_categoria
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->bind_param(
                    "issdssd",
                    $pedido_id,
                    $detalle['nombre_producto'],
                    $detalle['detalle_producto'],
                    $detalle['precio_producto'],
                    $detalle['unidad_medida_venta'],
                    $detalle['categoria'],
                    $detalle['subtotal_por_categoria']
                );
                $stmt->execute();
            }

            $db->commit();
            return ['success' => true];

        } catch (Exception $e) {
            $db->rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
