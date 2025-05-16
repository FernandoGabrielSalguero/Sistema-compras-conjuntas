<?php
require_once __DIR__ . '/../config.php';

class UserModel {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function existeUsuario($usuario) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM usuarios WHERE usuario = :usuario");
        $stmt->execute(['usuario' => $usuario]);
        return $stmt->fetchColumn() > 0;
    }

    public function crearUsuario($data) {
        if ($this->existeUsuario($data['usuario'])) {
            return ['success' => false, 'message' => 'El usuario ya está registrado.'];
        }

        // Insertar en tabla usuarios
        $stmt = $this->db->prepare("
            INSERT INTO usuarios (usuario, contrasena, rol, permiso_ingreso)
            VALUES (:usuario, :contrasena, :rol, :permiso_ingreso)
        ");
        $stmt->execute([
            'usuario' => $data['usuario'],
            'contrasena' => $data['contrasena'], // texto plano por ahora
            'rol' => $data['rol'],
            'permiso_ingreso' => $data['permiso_ingreso'],
        ]);

        // Obtener el ID generado
        $usuarioId = $this->db->lastInsertId();

        // Insertar en tabla usuarios_info
        $stmtInfo = $this->db->prepare("
            INSERT INTO usuarios_info (usuario_id, id_real)
            VALUES (:usuario_id, :id_real)
        ");
        $stmtInfo->execute([
            'usuario_id' => $usuarioId,
            'id_real' => $data['id_real'],
        ]);

        return ['success' => true, 'message' => 'Usuario creado correctamente.'];
    }
}
