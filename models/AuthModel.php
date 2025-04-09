<?php

require_once __DIR__ . '/../config.php';

class AuthModel {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function login($cuit, $contrasena) {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE cuit = :cuit AND contrasena = :contrasena AND permiso_ingreso = 'Habilitado'");
        $stmt->execute(['cuit' => $cuit, 'contrasena' => $contrasena]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
