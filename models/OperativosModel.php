<?php
require_once __DIR__ . '/../config.php';


class OperativosModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function existeNombre($nombre) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM operativos WHERE nombre = ?");
        $stmt->execute([$nombre]);
        return $stmt->fetchColumn() > 0;
    }

    public function crearOperativo($nombre, $fecha_inicio, $fecha_cierre) {
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $fecha_creacion = date('Y-m-d H:i:s');

        $stmt = $this->pdo->prepare("INSERT INTO operativos (nombre, fecha_inicio, fecha_cierre, created_at) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nombre, $fecha_inicio, $fecha_cierre, $fecha_creacion]);
        return $this->pdo->lastInsertId();
    }

    public function guardarCooperativas($operativo_id, $ids) {
        $stmt = $this->pdo->prepare("INSERT INTO operativos_cooperativas (operativo_id, cooperativa_id) VALUES (?, ?)");
        foreach ($ids as $id) {
            $stmt->execute([$operativo_id, $id]);
        }
    }

    public function guardarProductores($operativo_id, $ids) {
        $stmt = $this->pdo->prepare("INSERT INTO operativos_productores (operativo_id, productor_id) VALUES (?, ?)");
        foreach ($ids as $id) {
            $stmt->execute([$operativo_id, $id]);
        }
    }

    public function guardarProductos($operativo_id, $ids) {
        $stmt = $this->pdo->prepare("INSERT INTO operativos_productos (operativo_id, producto_id) VALUES (?, ?)");
        foreach ($ids as $id) {
            $stmt->execute([$operativo_id, $id]);
        }
    }
}
