<?php
class sveConsolidadoModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function obtenerConsolidadoPedidos($operativo_id = null, $cooperativa_id = null): array
    {
        $sql = "
        SELECT 
            o.nombre AS operativo,
            ui.nombre AS nombre_cooperativa,
            p.Nombre_producto AS producto,
            p.Unidad_Medida_venta AS unidad,
            SUM(dp.cantidad) AS cantidad_total
        FROM pedidos ped
        INNER JOIN detalle_pedidos dp ON ped.id = dp.pedido_id
        INNER JOIN productos p ON dp.producto_id = p.Id
        INNER JOIN operativos o ON ped.operativo_id = o.id
        LEFT JOIN usuarios u ON ped.cooperativa = u.id_real
        LEFT JOIN usuarios_info ui ON ui.usuario_id = u.id
        WHERE 1 = 1
    ";

        $params = [];

        if ($operativo_id) {
            $sql .= " AND o.id = :operativo_id";
            $params['operativo_id'] = $operativo_id;
        }

        if ($cooperativa_id) {
            $sql .= " AND ped.cooperativa = :cooperativa_id";
            $params['cooperativa_id'] = $cooperativa_id;
        }

        $sql .= " GROUP BY o.id, p.Id, ped.cooperativa ORDER BY o.fecha_inicio DESC, p.Nombre_producto ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // descargar pedidos
    public function obtenerPedidosConDetalle($operativo_id = null, $cooperativa_id = null): array
    {
        $sql = "
        SELECT 
            ped.id AS pedido_id,
            ped.cooperativa AS cooperativa_id_real,
            coop_info.nombre AS nombre_cooperativa,
            ped.productor AS productor_id_real,
            prod_info.nombre AS nombre_productor,
            ped.fecha_pedido,
            prod.cuit AS cuit_productor,
            ped.persona_facturacion,
            ped.condicion_facturacion,
            ped.afiliacion,
            ped.ha_cooperativa,
            ped.total_sin_iva,
            ped.total_iva,
            ped.total_pedido,
            ped.observaciones,
            ped.operativo_id,

            u.rol,
            u.cuit AS cuit_cooperativa,
            u.id_real,

            -- Datos de la cooperativa
            coop_info.direccion AS direccion_coop,
            coop_info.telefono AS telefono_coop,
            coop_info.correo AS correo_coop,

            -- Datos del productor (NUEVO)
            prod_info.direccion AS direccion_productor,
            prod_info.telefono AS telefono_productor,
            prod_info.correo AS correo_productor,

            -- Detalle del producto
            dp.nombre_producto,
            dp.detalle_producto,
            dp.precio_producto,
            dp.unidad_medida_venta,
            dp.categoria,
            dp.producto_id,
            dp.cantidad,
            dp.alicuota

        FROM pedidos ped
        LEFT JOIN detalle_pedidos dp ON dp.pedido_id = ped.id

        -- Cooperativa
        LEFT JOIN usuarios u ON u.id_real = ped.cooperativa
        LEFT JOIN usuarios_info coop_info ON coop_info.usuario_id = u.id

        -- Productor
        LEFT JOIN usuarios prod ON prod.id_real = ped.productor
        LEFT JOIN usuarios_info prod_info ON prod_info.usuario_id = prod.id

        WHERE 1 = 1
    ";

        $params = [];

        if (!empty($operativo_id)) {
            $sql .= " AND ped.operativo_id = :operativo_id";
            $params['operativo_id'] = $operativo_id;
        }

        if (!empty($cooperativa_id)) {
            $sql .= " AND ped.cooperativa = :cooperativa_id";
            $params['cooperativa_id'] = $cooperativa_id;
        }

        $sql .= " ORDER BY ped.fecha_pedido DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ⬇️ Pegar dentro de la clase sveConsolidadoModel
    public function obtenerMetricas($operativo_id = null, $cooperativa_id = null): array
    {
        // Filtros y params
        $where = " WHERE 1=1 ";
        $params = [];
        if (!empty($operativo_id)) {
            $where .= " AND ped.operativo_id = :operativo_id";
            $params['operativo_id'] = $operativo_id;
        }
        if (!empty($cooperativa_id)) {
            $where .= " AND ped.cooperativa = :cooperativa_id";
            $params['cooperativa_id'] = $cooperativa_id;
        }

        // 1) Totales a nivel PEDIDOS (evita duplicados de importe)
        $sqlTotalesPedidos = "
        SELECT 
            COUNT(*) AS total_pedidos,
            COALESCE(SUM(ped.total_pedido), 0) AS total_facturado
        FROM pedidos ped
        $where
    ";
        $stmt1 = $this->pdo->prepare($sqlTotalesPedidos);
        $stmt1->execute($params);
        $totalesPedidos = $stmt1->fetch(PDO::FETCH_ASSOC) ?: ['total_pedidos' => 0, 'total_facturado' => 0];

        // 2) Totales a nivel DETALLE (unidades + productos distintos)
        $sqlTotalesDetalle = "
        SELECT 
            COALESCE(SUM(dp.cantidad), 0) AS total_unidades,
            COALESCE(COUNT(DISTINCT dp.producto_id), 0) AS productos_distintos
        FROM pedidos ped
        LEFT JOIN detalle_pedidos dp ON dp.pedido_id = ped.id
        $where
    ";
        $stmt2 = $this->pdo->prepare($sqlTotalesDetalle);
        $stmt2->execute($params);
        $totalesDetalle = $stmt2->fetch(PDO::FETCH_ASSOC) ?: ['total_unidades' => 0, 'productos_distintos' => 0];

        // 3) Detalle por COOPERATIVA (sin duplicar importes)
        //    Subconsulta por pedido para sumar unidades por pedido y luego agrupar por coop.
        $subPedidos = "
        SELECT 
            ped.id,
            ped.cooperativa,
            ped.total_pedido,
            (
                SELECT COALESCE(SUM(dp2.cantidad),0)
                FROM detalle_pedidos dp2
                WHERE dp2.pedido_id = ped.id
            ) AS unidades
        FROM pedidos ped
        $where
    ";

        $sqlDetalleCoop = "
        SELECT
            t.cooperativa AS cooperativa_id_real,
            ui.nombre AS nombre_cooperativa,
            COUNT(*) AS pedidos,
            COALESCE(SUM(t.unidades),0) AS unidades,
            COALESCE(SUM(t.total_pedido),0) AS total_facturado
        FROM ($subPedidos) t
        LEFT JOIN usuarios u ON u.id_real = t.cooperativa
        LEFT JOIN usuarios_info ui ON ui.usuario_id = u.id
        GROUP BY t.cooperativa, ui.nombre
        ORDER BY total_facturado DESC, unidades DESC
    ";

        $stmt3 = $this->pdo->prepare($sqlDetalleCoop);
        $stmt3->execute($params);
        $detalleCoop = $stmt3->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return [
            'resumen' => [
                'total_pedidos'       => (int)($totalesPedidos['total_pedidos'] ?? 0),
                'total_unidades'      => (int)($totalesDetalle['total_unidades'] ?? 0),
                'productos_distintos' => (int)($totalesDetalle['productos_distintos'] ?? 0),
                'total_facturado'     => (float)($totalesPedidos['total_facturado'] ?? 0),
            ],
            'detalle_por_cooperativa' => $detalleCoop
        ];
    }
}
