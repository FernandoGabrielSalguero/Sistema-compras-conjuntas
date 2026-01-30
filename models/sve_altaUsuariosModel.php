<?php
require_once __DIR__ . '/../config.php';

class UserModel
{
    private $pdo;
    private const ROLES_VALIDOS = [
        'sve',
        'cooperativa',
        'productor',
        'ingeniero',
        'piloto_drone',
        'piloto_tractor',
    ];

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function existeUsuario($usuario)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE usuario = :usuario");
        $stmt->execute(['usuario' => $usuario]);
        return $stmt->fetchColumn() > 0;
    }

    public function crearUsuario($data)
    {
        try {
            if (!in_array($data['rol'] ?? '', self::ROLES_VALIDOS, true)) {
                return ['success' => false, 'message' => 'Rol inválido.'];
            }

            if ($this->existeUsuario($data['usuario'])) {
                return ['success' => false, 'message' => 'El usuario ya está registrado.'];
            }

            // Insertar en tabla usuarios
            $stmt = $this->pdo->prepare("
                INSERT INTO usuarios (usuario, contrasena, rol, permiso_ingreso, cuit, id_real)
                VALUES (:usuario, :contrasena, :rol, :permiso_ingreso, :cuit, :id_real)
            ");
            $stmt->execute([
                'usuario' => $data['usuario'],
                'contrasena' => password_hash($data['contrasena'], PASSWORD_DEFAULT),
                'rol' => $data['rol'],
                'permiso_ingreso' => $data['permiso_ingreso'],
                'cuit' => $data['cuit'],
                'id_real' => $data['id_real']
            ]);

            // Obtener ID insertado
            $usuario_id = $this->pdo->lastInsertId();

            // Insertar datos en usuarios_info usando el mismo valor de "usuario" como "nombre"
            $stmtInfo = $this->pdo->prepare("
    INSERT INTO usuarios_info (usuario_id, nombre, direccion, telefono, correo)
    VALUES (:usuario_id, :nombre, 'Sin dirección', 'Sin teléfono', 'sin-correo@sve.com')
");
            $stmtInfo->execute([
                'usuario_id' => $usuario_id,
                'nombre'     => $data['usuario']
            ]);

            return ['success' => true, 'message' => 'Usuario creado correctamente.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al crear el usuario.'];
        }
    }
}
