<?php
require_once __DIR__ . '/../config.php';

class UserModel {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function existeCuit($cuit) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM usuarios WHERE cuit = :cuit");
        $stmt->execute(['cuit' => $cuit]);
        return $stmt->fetchColumn() > 0;
    }

    public function crearUsuario($data) {
        if ($this->existeCuit($data['cuit'])) {
            return ['success' => false, 'message' => 'El CUIT ya está registrado.'];
        }

        $stmt = $this->db->prepare("
            INSERT INTO usuarios 
            (cuit, contrasena, rol, permiso_ingreso, nombre, correo, telefono, id_cooperativa, id_productor, direccion, id_finca_asociada, observaciones)
            VALUES 
            (:cuit, :contrasena, :rol, :permiso_ingreso, :nombre, :correo, :telefono, :id_cooperativa, :id_productor, :direccion, :id_finca_asociada, :observaciones)
        ");

        $stmt->execute([
            'cuit' => $data['cuit'],
            'contrasena' => $data['contrasena'],
            'rol' => $data['rol'],
            'permiso_ingreso' => $data['permiso'],
            'nombre' => $data['nombre'],
            'correo' => $data['email'],
            'telefono' => $data['telefono'],
            'id_cooperativa' => $data['id_cooperativa'],
            'id_productor' => $data['id_productor'],
            'direccion' => $data['direccion'],
            'id_finca_asociada' => $data['finca_asociada'],
            'observaciones' => $data['observaciones'],
        ]);

        return ['success' => true, 'message' => 'Usuario creado correctamente.'];
    }
}
