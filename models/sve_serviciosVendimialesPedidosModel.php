<?php

class ServiciosVendimialesPedidosModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function obtenerTodos()
    {
        $sql = "
            SELECT
                p.*,
                so.nombre AS servicio_nombre,
                c.nombre AS centrifugadora_nombre,
                f.aceptado AS contrato_aceptado,
                f.firmado_en AS contrato_firmado_en,
                f.firmado_por AS contrato_firmado_por
            FROM serviciosVendimiales_pedidos p
            LEFT JOIN serviciosVendimiales_serviciosOfrecidos so
                ON so.id = p.servicioAcontratar
            LEFT JOIN serviciosVendimiales_centrifugadores c
                ON c.id = p.equipo_centrifugadora
            LEFT JOIN (
                SELECT f1.*
                FROM serviciosVendimiales_pedido_contrato_firma f1
                INNER JOIN (
                    SELECT pedido_id, MAX(id) AS max_id
                    FROM serviciosVendimiales_pedido_contrato_firma
                    GROUP BY pedido_id
                ) f2 ON f1.pedido_id = f2.pedido_id AND f1.id = f2.max_id
            ) f ON f.pedido_id = p.id
            ORDER BY p.id DESC
        ";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
