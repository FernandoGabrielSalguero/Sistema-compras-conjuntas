<?php
class CoopConsolidadoModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }
    
public function obtenerConsolidadoPedidos($cooperativa_id)
{
    $stmt = $this->pdo->prepare("
        SELECT 
            o.nombre AS operativo,
            p.Nombre_producto AS producto,
            p.Unidad_Medida_venta AS unidad,
            SUM(dp.cantidad) AS cantidad_total
        FROM pedidos ped
        INNER JOIN detalle_pedidos dp ON ped.id = dp.pedido_id
        INNER JOIN productos p ON dp.producto_id = p.Id
        INNER JOIN operativos o ON ped.operativo_id = o.id
        WHERE ped.cooperativa = :coop
        GROUP BY o.id, p.Id
        ORDER BY o.fecha_inicio DESC, p.Nombre_producto ASC
    ");
    $stmt->execute(['coop' => $cooperativa_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}
