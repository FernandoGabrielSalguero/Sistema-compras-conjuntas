<?php

class CoopServiciosVendimialesModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function obtenerServiciosActivos()
    {
        $stmt = $this->pdo->query(
            "SELECT id, nombre FROM serviciosVendimiales_serviciosOfrecidos WHERE activo = 1 ORDER BY nombre"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerCentrifugadorasActivas()
    {
        $stmt = $this->pdo->query(
            "SELECT id, nombre, precio, moneda FROM serviciosVendimiales_centrifugadores WHERE activo = 1 ORDER BY nombre"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerContratoVigente()
    {
        $stmt = $this->pdo->query(
            "SELECT * FROM serviciosVendimiales_contratos WHERE vigente = 1 ORDER BY id DESC LIMIT 1"
        );
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function crearPedido(array $data)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO serviciosVendimiales_pedidos
            (cooperativa, nombre, cargo, servicioAcontratar, volumenAproximado, unidad_volumen,
             fecha_entrada_equipo, equipo_centrifugadora, estado, observaciones)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $data['cooperativa'],
            $data['nombre'],
            $data['cargo'],
            $data['servicioAcontratar'],
            $data['volumenAproximado'],
            $data['unidad_volumen'],
            $data['fecha_entrada_equipo'],
            $data['equipo_centrifugadora'],
            $data['estado'],
            $data['observaciones']
        ]);

        return $this->pdo->lastInsertId();
    }

    public function registrarFirma(array $data)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO serviciosVendimiales_pedido_contrato_firma
            (pedido_id, contrato_id, aceptado, firmado_por, ip, user_agent, snapshot)
            VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        return $stmt->execute([
            $data['pedido_id'],
            $data['contrato_id'],
            $data['aceptado'],
            $data['firmado_por'],
            $data['ip'],
            $data['user_agent'],
            $data['snapshot']
        ]);
    }

    public function listarPedidosPorCooperativa($cooperativaNombre)
    {
        $sql = "
            SELECT
                p.*,
                so.nombre AS servicio_nombre,
                c.nombre AS centrifugadora_nombre,
                f.aceptado AS contrato_aceptado,
                f.firmado_en AS contrato_firmado_en
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
            WHERE p.cooperativa = ?
            ORDER BY p.id DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$cooperativaNombre]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
