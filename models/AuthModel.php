<?php

require_once __DIR__ . '/../config.php';

class AuthModel {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

public function login($usuario, $contrasenaIngresada) {
    $sql = "SELECT 
                u.*, ui.nombre, ui.direccion, ui.telefono, ui.correo, ui.id_real
            FROM usuarios u
            JOIN usuarios_info ui ON u.id = ui.usuario_id
            WHERE u.usuario = :usuario
              AND u.permiso_ingreso = 'Habilitado'";
    
    $stmt = $this->db->prepare($sql);
    $stmt->execute(['usuario' => $usuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($contrasenaIngresada, $user['contrasena'])) {
        return $user;
    }

    return false;
}

}
