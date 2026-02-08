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
        $stmt = $this->pdo->query("SELECT * FROM servicios_vendimiales ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM servicios_vendimiales WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($nombre, $descripcion, $estado)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO servicios_vendimiales (nombre, descripcion, estado) VALUES (?, ?, ?)"
        );
        $stmt->execute([$nombre, $descripcion, $estado]);
        return $this->pdo->lastInsertId();
    }

    public function actualizar($id, $nombre, $descripcion, $estado)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE servicios_vendimiales SET nombre = ?, descripcion = ?, estado = ? WHERE id = ?"
        );
        return $stmt->execute([$nombre, $descripcion, $estado, $id]);
    }

    public function eliminar($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM servicios_vendimiales WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
