<?php
class OperativosModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function crear($nombre, $fecha_inicio, $fecha_cierre, $estado) {
        $stmt = $this->pdo->prepare("INSERT INTO operativos (nombre, fecha_inicio, fecha_cierre, estado) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nombre, $fecha_inicio, $fecha_cierre, $estado]);
        return $this->pdo->lastInsertId();
    }

    public function actualizar($id, $nombre, $fecha_inicio, $fecha_cierre, $estado) {
        $stmt = $this->pdo->prepare("UPDATE operativos SET nombre = ?, fecha_inicio = ?, fecha_cierre = ?, estado = ? WHERE id = ?");
        return $stmt->execute([$nombre, $fecha_inicio, $fecha_cierre, $estado, $id]);
    }

    public function obtenerTodos() {
        $stmt = $this->pdo->query("SELECT * FROM operativos ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM operativos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
} 
