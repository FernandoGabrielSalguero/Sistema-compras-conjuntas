<?php
require_once __DIR__ . '/../config.php';

class AsociacionModel {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function obtenerProductores() {
        $stmt = $this->db->prepare("
            SELECT u.id_real, u.cuit, i.nombre
            FROM usuarios u
            LEFT JOIN usuarios_info i ON u.id = i.usuario_id
            WHERE u.rol = 'productor'
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerCooperativas() {
        $stmt = $this->db->prepare("
            SELECT id_real, nombre
            FROM usuarios
            LEFT JOIN usuarios_info ON usuarios.id = usuarios_info.usuario_id
            WHERE rol = 'cooperativa'
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
