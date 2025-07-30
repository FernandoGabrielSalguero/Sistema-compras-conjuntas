<?php
class sveConsolidadoModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

public function obtenerConsolidadoPedidos($operativo_id = null, $cooperativa_id = null): array {
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
public function obtenerPedidosConDetalle($operativo_id = null, $cooperativa_id = null): array {
    $sql = "
        SELECT 
            ped.id AS pedido_id,
            ped.cooperativa AS cooperativa_id_real,
            coop_info.nombre AS nombre_cooperativa,
            ped.productor AS productor_id_real,
            prod_info.nombre AS nombre_productor,
            ped.fecha_pedido,
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
            u.cuit,
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

}
