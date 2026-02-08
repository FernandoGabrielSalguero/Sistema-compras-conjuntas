<?php

class ServiciosVendimialesModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function obtenerTodos()
    {
        $stmt = $this->pdo->query("SELECT * FROM serviciosVendimiales_serviciosOfrecidos ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM serviciosVendimiales_serviciosOfrecidos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($nombre, $activo)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO serviciosVendimiales_serviciosOfrecidos (nombre, activo) VALUES (?, ?)"
        );
        $stmt->execute([$nombre, $activo]);
        return $this->pdo->lastInsertId();
    }

    public function actualizar($id, $nombre, $activo)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE serviciosVendimiales_serviciosOfrecidos SET nombre = ?, activo = ? WHERE id = ?"
        );
        return $stmt->execute([$nombre, $activo, $id]);
    }

    public function eliminar($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM serviciosVendimiales_serviciosOfrecidos WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
