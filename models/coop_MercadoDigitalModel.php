<?php
class CoopMercadoDigitalModel
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
INSERT INTO pedidos (
    cooperativa, productor, fecha_pedido, persona_facturacion, 
    condicion_facturacion, afiliacion, ha_cooperativa, 
    observaciones, total_sin_iva, total_iva, total_pedido,
    operativo_id
) VALUES (?, ?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?, ?)

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
            $total_con_iva,
            $data['operativo_id'] ?? null
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
    public function obtenerListadoPedidos($search = '', $offset = 0, $limit = 25, $coop_id = null)
    {
        $sql = "
    SELECT 
        p.*,
        i1.nombre AS nombre_cooperativa,
        i2.nombre AS nombre_productor,
        o.estado AS estado_operativo
    FROM pedidos p
    JOIN usuarios u1 ON u1.id_real = p.cooperativa
    JOIN usuarios_info i1 ON i1.usuario_id = u1.id
    JOIN usuarios u2 ON u2.id_real = p.productor
    JOIN usuarios_info i2 ON i2.usuario_id = u2.id
    LEFT JOIN operativos o ON o.id = p.operativo_id
    WHERE p.cooperativa = :coop_id
";

        $params = [':coop_id' => $coop_id];

        if (!empty($search)) {
            $sql .= " AND i2.nombre LIKE :search";
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


    // obtenemos los operativos de la bbdd
    public function obtenerOperativosActivosPorCooperativa($coopId)
    {
        $stmt = $this->pdo->prepare("
        SELECT o.id, o.nombre, o.fecha_inicio, o.fecha_cierre
        FROM operativos o
        INNER JOIN operativos_cooperativas_participacion ocp ON o.id = ocp.operativo_id
        WHERE ocp.cooperativa_id_real = ? 
          AND ocp.participa = 'si'
          AND o.estado = 'abierto'
        ORDER BY o.fecha_inicio DESC
    ");
        $stmt->execute([$coopId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
