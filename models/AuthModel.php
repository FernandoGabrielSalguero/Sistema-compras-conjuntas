<?php

require_once __DIR__ . '/../config.php';

class AuthModel {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

public function login($usuario, $contrasena) {
    $sql = "SELECT 
                u.id AS usuario_id,
                u.usuario,
                u.contrasena,
                u.rol,
                u.permiso_ingreso,
                ui.id_real,
                ui.nombre,
                ui.direccion,
                ui.telefono,
                ui.correo
            FROM usuarios u
            JOIN usuarios_info ui ON u.id = ui.usuario_id
            WHERE u.usuario = :usuario
              AND u.contrasena = :contrasena
              AND u.permiso_ingreso = 'Habilitado'";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
        'usuario' => $usuario,
        'contrasena' => $contrasena
    ]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

}
