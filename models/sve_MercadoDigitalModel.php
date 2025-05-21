<?php
class SveMercadoDigitalModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function listarCooperativas()
    {
        $stmt = $this->pdo->query("
            SELECT u.id_real, i.nombre
            FROM usuarios u
            JOIN usuarios_info i ON i.usuario_id = u.id
            WHERE u.rol = 'cooperativa'
            ORDER BY i.nombre
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarProductoresPorCooperativa($coop_id)
    {
        $stmt = $this->pdo->prepare("
            SELECT u.id_real, i.nombre
            FROM rel_productor_coop rel
            JOIN usuarios u ON u.id_real = rel.productor_id_real
            JOIN usuarios_info i ON i.usuario_id = u.id
            WHERE rel.cooperativa_id_real = ?
            ORDER BY i.nombre
        ");
        $stmt->execute([$coop_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerProductosAgrupadosPorCategoria()
    {
        $stmt = $this->pdo->query("
        SELECT 
            categoria, 
            Id as producto_id,
            Nombre_producto,
            Unidad_Medida_venta,
            Precio_producto,
            alicuota
        FROM productos
        ORDER BY categoria, Nombre_producto
    ");

        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $agrupados = [];
        foreach ($productos as $p) {
            $categoria = $p['categoria'];
            if (!isset($agrupados[$categoria])) {
                $agrupados[$categoria] = [];
            }
            $agrupados[$categoria][] = $p;
        }

        return $agrupados;
    }

    public function guardarPedidoConDetalles($data)
    {
        $this->pdo->beginTransaction();

        // 1. Insertar pedido principal
        $stmt = $this->pdo->prepare("
        INSERT INTO pedidos (cooperativa, productor, fecha_pedido, persona_facturacion, condicion_facturacion, afiliacion, observaciones, total_sin_iva, total_iva, total_pedido)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

        $total_sin_iva = $data['totales']['sin_iva'] ?? 0;
        $total_iva = $data['totales']['iva'] ?? 0;
        $total_con_iva = $data['totales']['con_iva'] ?? 0;

        $stmt->execute([
            $data['cooperativa'],
            $data['productor'],
            $data['fecha_pedido'],
            $data['persona_facturacion'],
            $data['condicion_facturacion'],
            $data['afiliacion'],
            $data['observaciones'],
            $total_sin_iva,
            $total_iva,
            $total_con_iva
        ]);

        $pedido_id = $this->pdo->lastInsertId();

        // 2. Insertar productos
        foreach ($data['productos'] as $producto) {
            $stmtProd = $this->pdo->prepare("
            INSERT INTO detalle_pedidos (pedido_id, producto_id, nombre_producto, detalle_producto, precio_producto, unidad_medida_venta, categoria, cantidad, alicuota)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

            $stmtProd->execute([
                $pedido_id,
                $producto['id'],
                $producto['nombre'],
                $producto['detalle'],
                $producto['precio'],
                $producto['unidad'],
                $producto['categoria'],
                $producto['cantidad'],
                $producto['alicuota']
            ]);
        }

        $this->pdo->commit();
        return $pedido_id;
    }
}
