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



}
