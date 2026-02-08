<?php

class CentrifugadoresModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function obtenerTodos()
    {
        $stmt = $this->pdo->query("SELECT * FROM serviciosVendimiales_centrifugadores ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM serviciosVendimiales_centrifugadores WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($nombre, $precio, $moneda, $activo)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO serviciosVendimiales_centrifugadores (nombre, precio, moneda, activo) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$nombre, $precio, $moneda, $activo]);
        return $this->pdo->lastInsertId();
    }

    public function actualizar($id, $nombre, $precio, $moneda, $activo)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE serviciosVendimiales_centrifugadores SET nombre = ?, precio = ?, moneda = ?, activo = ? WHERE id = ?"
        );
        return $stmt->execute([$nombre, $precio, $moneda, $activo, $id]);
    }

    public function eliminar($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM serviciosVendimiales_centrifugadores WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
