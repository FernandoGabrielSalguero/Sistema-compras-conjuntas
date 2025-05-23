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
INSERT INTO pedidos (cooperativa, productor, fecha_pedido, persona_facturacion, condicion_facturacion, afiliacion, ha_cooperativa, observaciones, total_sin_iva, total_iva, total_pedido)
VALUES (?, ?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?)

    ");

        $total_sin_iva = $data['totales']['sin_iva'] ?? 0;
        $total_iva = $data['totales']['iva'] ?? 0;
        $total_con_iva = $data['totales']['con_iva'] ?? 0;

        $stmt->execute([
            $data['cooperativa'],
            $data['productor'],
            $data['persona_facturacion'],
            $data['condicion_facturacion'],
            $data['afiliacion'],
            $data['hectareas'],
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

    // modelo de Listado de pedidos
    // 🔢 Cuenta total de pedidos y resumen de facturas
    public function obtenerResumenPedidos()
    {
        $stmt = $this->pdo->query("
        SELECT 
            COUNT(*) AS total,
            SUM(CASE WHEN factura IS NOT NULL AND factura != '' THEN 1 ELSE 0 END) AS con_factura,
            SUM(CASE WHEN factura IS NULL OR factura = '' THEN 1 ELSE 0 END) AS sin_factura
        FROM pedidos
    ");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 📄 Obtiene listado de pedidos con paginación y búsqueda opcional
    public function obtenerListadoPedidos($search = '', $offset = 0, $limit = 25)
    {
        $sql = "
        SELECT 
            p.*,
            i1.nombre AS nombre_cooperativa,
            i2.nombre AS nombre_productor
        FROM pedidos p
        JOIN usuarios u1 ON u1.id_real = p.cooperativa
        JOIN usuarios_info i1 ON i1.usuario_id = u1.id
        JOIN usuarios u2 ON u2.id_real = p.productor
        JOIN usuarios_info i2 ON i2.usuario_id = u2.id
    ";

        $params = [];

        if (!empty($search)) {
            $sql .= " WHERE i1.nombre LIKE :search OR i2.nombre LIKE :search ";
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY p.id DESC LIMIT :offset, :limit";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val, PDO::PARAM_STR);
        }

        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔁 Cuenta total de resultados para paginación
    public function contarPedidosFiltrados($search = '')
    {
        $sql = "
        SELECT COUNT(*) AS total
        FROM pedidos p
        JOIN usuarios u1 ON u1.id_real = p.cooperativa
        JOIN usuarios_info i1 ON i1.usuario_id = u1.id
        JOIN usuarios u2 ON u2.id_real = p.productor
        JOIN usuarios_info i2 ON i2.usuario_id = u2.id
    ";

        $params = [];

        if (!empty($search)) {
            $sql .= " WHERE i1.nombre LIKE :search OR i2.nombre LIKE :search ";
            $params[':search'] = '%' . $search . '%';
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function actualizarPedido($id, $data)
    {
        $this->pdo->beginTransaction();

        try {
            // 1. Calcular totales
            $total_sin_iva = 0;
            $total_iva = 0;

            foreach ($data['productos'] as $prod) {
                $producto_id = $prod['id'];

                // Si es producto existente en la base
                if ($producto_id) {
                    $stmt = $this->pdo->prepare("SELECT Precio_producto, alicuota, Unidad_Medida_venta, categoria FROM productos WHERE Id = ?");
                    $stmt->execute([$producto_id]);
                    $info = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$info) {
                        throw new Exception("Producto no encontrado: ID $producto_id");
                    }

                    $precio = $info['Precio_producto'];
                    $alicuota = (float)$info['alicuota'];
                    $cantidad = (int)$prod['cantidad'];

                    $sin_iva = $precio * $cantidad;
                    $iva = $sin_iva * ($alicuota / 100);

                    $total_sin_iva += $sin_iva;
                    $total_iva += $iva;
                }
            }

            $total_pedido = $total_sin_iva + $total_iva;

            // 2. Actualizar pedido principal
            $stmt = $this->pdo->prepare("
            UPDATE pedidos SET 
                persona_facturacion = ?, 
                condicion_facturacion = ?, 
                afiliacion = ?, 
                ha_cooperativa = ?, 
                observaciones = ?, 
                total_sin_iva = ?, 
                total_iva = ?, 
                total_pedido = ?
            WHERE id = ?
        ");

            $stmt->execute([
                $data['persona_facturacion'],
                $data['condicion_facturacion'],
                $data['afiliacion'],
                $data['hectareas'],
                $data['observaciones'],
                $total_sin_iva,
                $total_iva,
                $total_pedido,
                $id
            ]);

            // 3. Eliminar productos actuales
            $this->pdo->prepare("DELETE FROM detalle_pedidos WHERE pedido_id = ?")->execute([$id]);

            // 4. Insertar nuevos productos
            foreach ($data['productos'] as $prod) {
                $producto_id = $prod['id'];
                $nombre = $prod['nombre'];
                $cantidad = $prod['cantidad'];

                if ($producto_id) {
                    $stmtProd = $this->pdo->prepare("SELECT * FROM productos WHERE Id = ?");
                    $stmtProd->execute([$producto_id]);
                    $producto = $stmtProd->fetch(PDO::FETCH_ASSOC);

                    if (!$producto) continue;

                    $this->pdo->prepare("
                    INSERT INTO detalle_pedidos (
                        pedido_id, producto_id, nombre_producto, detalle_producto, 
                        precio_producto, unidad_medida_venta, categoria, cantidad, alicuota
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ")->execute([
                        $id,
                        $producto_id,
                        $producto['Nombre_producto'],
                        $producto['Detalle_producto'],
                        $producto['Precio_producto'],
                        $producto['Unidad_Medida_venta'],
                        $producto['categoria'],
                        $cantidad,
                        $producto['alicuota']
                    ]);
                } else {
                    // Producto manual (sin ID real)
                    $this->pdo->prepare("
                    INSERT INTO detalle_pedidos (
                        pedido_id, producto_id, nombre_producto, detalle_producto, 
                        precio_producto, unidad_medida_venta, categoria, cantidad, alicuota
                    ) VALUES (?, NULL, ?, '', 0, '', '', ?, 0)
                ")->execute([
                        $id,
                        $nombre,
                        $cantidad
                    ]);
                }
            }

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
