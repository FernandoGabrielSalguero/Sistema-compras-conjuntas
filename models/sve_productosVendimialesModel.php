<?php

class ProductosVendimialesModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function obtenerTodos($servicioId = null)
    {
        if ($servicioId) {
            $stmt = $this->pdo->prepare(
                "SELECT p.*, s.nombre AS servicio_nombre
                 FROM serviciosVendimiales_productos p
                 LEFT JOIN serviciosVendimiales_serviciosOfrecidos s
                    ON s.id = p.servicio_id
                 WHERE p.servicio_id = ?
                 ORDER BY p.nombre ASC"
            );
            $stmt->execute([$servicioId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $stmt = $this->pdo->query(
            "SELECT p.*, s.nombre AS servicio_nombre
             FROM serviciosVendimiales_productos p
             LEFT JOIN serviciosVendimiales_serviciosOfrecidos s
                ON s.id = p.servicio_id
             ORDER BY s.nombre ASC, p.nombre ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM serviciosVendimiales_productos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($servicioId, $nombre, $precio, $moneda, $activo)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO serviciosVendimiales_productos (servicio_id, nombre, precio, moneda, activo)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$servicioId, $nombre, $precio, $moneda, $activo]);
        return $this->pdo->lastInsertId();
    }

    public function actualizar($id, $servicioId, $nombre, $precio, $moneda, $activo)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE serviciosVendimiales_productos
             SET servicio_id = ?, nombre = ?, precio = ?, moneda = ?, activo = ?
             WHERE id = ?"
        );
        return $stmt->execute([$servicioId, $nombre, $precio, $moneda, $activo, $id]);
    }

    public function eliminar($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM serviciosVendimiales_productos WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
