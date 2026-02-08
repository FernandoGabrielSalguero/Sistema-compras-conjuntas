<?php

class ContratosVendimialesModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function obtenerTodos()
    {
        $stmt = $this->pdo->query("SELECT * FROM serviciosVendimiales_contratos ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM serviciosVendimiales_contratos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function resetVigentes()
    {
        $this->pdo->prepare("UPDATE serviciosVendimiales_contratos SET vigente = 0")->execute();
    }

    public function crear($nombre, $descripcion, $contenido, $version, $vigente)
    {
        $this->pdo->beginTransaction();
        try {
            if ((int)$vigente === 1) {
                $this->resetVigentes();
            }

            $stmt = $this->pdo->prepare(
                "INSERT INTO serviciosVendimiales_contratos (nombre, descripcion, contenido, version, vigente)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([$nombre, $descripcion, $contenido, $version, $vigente]);
            $id = $this->pdo->lastInsertId();
            $this->pdo->commit();
            return $id;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function actualizar($id, $nombre, $descripcion, $contenido, $version, $vigente)
    {
        $this->pdo->beginTransaction();
        try {
            if ((int)$vigente === 1) {
                $this->resetVigentes();
            }

            $stmt = $this->pdo->prepare(
                "UPDATE serviciosVendimiales_contratos
                 SET nombre = ?, descripcion = ?, contenido = ?, version = ?, vigente = ?
                 WHERE id = ?"
            );
            $ok = $stmt->execute([$nombre, $descripcion, $contenido, $version, $vigente, $id]);
            $this->pdo->commit();
            return $ok;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function eliminar($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM serviciosVendimiales_contratos WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
