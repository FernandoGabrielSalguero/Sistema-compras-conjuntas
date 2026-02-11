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
                pr.nombre AS producto_nombre,
                c.nombre AS centrifugadora_nombre,
                f.aceptado AS contrato_aceptado,
                f.firmado_en AS contrato_firmado_en,
                f.firmado_por AS contrato_firmado_por
            FROM serviciosVendimiales_pedidos p
            LEFT JOIN serviciosVendimiales_serviciosOfrecidos so
                ON so.id = p.servicioAcontratar
            LEFT JOIN serviciosVendimiales_productos pr
                ON pr.id = p.producto_id
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

    public function obtenerCooperativas()
    {
        $stmt = $this->pdo->query(
            "SELECT id_real, razon_social, usuario, cuit
             FROM usuarios
             WHERE rol = 'cooperativa'
             ORDER BY razon_social ASC, usuario ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM serviciosVendimiales_pedidos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($data)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO serviciosVendimiales_pedidos
             (cooperativa, nombre, cargo, servicioAcontratar, producto_id, volumenAproximado, unidad_volumen,
              fecha_entrada_equipo, equipo_centrifugadora, estado, observaciones)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $data['cooperativa'],
            $data['nombre'],
            $data['cargo'],
            $data['servicioAcontratar'],
            $data['producto_id'],
            $data['volumenAproximado'],
            $data['unidad_volumen'],
            $data['fecha_entrada_equipo'],
            $data['equipo_centrifugadora'],
            $data['estado'],
            $data['observaciones']
        ]);

        return $this->pdo->lastInsertId();
    }

    public function actualizar($id, $data)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE serviciosVendimiales_pedidos
             SET cooperativa = ?, nombre = ?, cargo = ?, servicioAcontratar = ?, producto_id = ?, volumenAproximado = ?,
                 unidad_volumen = ?, fecha_entrada_equipo = ?, equipo_centrifugadora = ?, estado = ?, observaciones = ?
             WHERE id = ?"
        );

        return $stmt->execute([
            $data['cooperativa'],
            $data['nombre'],
            $data['cargo'],
            $data['servicioAcontratar'],
            $data['producto_id'],
            $data['volumenAproximado'],
            $data['unidad_volumen'],
            $data['fecha_entrada_equipo'],
            $data['equipo_centrifugadora'],
            $data['estado'],
            $data['observaciones'],
            $id
        ]);
    }

    public function eliminar($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM serviciosVendimiales_pedidos WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
