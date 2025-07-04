<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
class UsuarioInformacionModel
{

    private $conn;

    public function __construct()
    {
        global $pdo;
        $this->conn = $pdo;
    }

    public function obtenerRangoCooperativa($cooperativaIdReal)
    {
        $stmt = $this->conn->prepare("SELECT * FROM cooperativas_rangos WHERE cooperativa_id_real = ?");
        $stmt->execute([$cooperativaIdReal]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerProximoIdRealDisponible($inicio, $fin)
    {
        $stmt = $this->conn->prepare("
    SELECT id_real FROM usuarios 
    WHERE id_real REGEXP '^P[0-9]+$'
    ORDER BY id_real ASC
");
        $stmt->execute();
        $usados = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $stmt->execute([$inicio, $fin]);
        $usados = $stmt->fetchAll(PDO::FETCH_COLUMN);

        for ($i = $inicio; $i <= $fin; $i++) {
            $idConPrefijo = 'P' . $i;
            if (!in_array($idConPrefijo, $usados)) {
                return $idConPrefijo;
            }
        }
        return null;
    }

    public function crearUsuarioProductor($usuario, $contrasena, $cuit, $idReal)
    {
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO usuarios (usuario, contrasena, rol, permiso_ingreso, cuit, id_real) VALUES (?, ?, 'productor', 'Habilitado', ?, ?)");
        $stmt->execute([$usuario, $hash, $cuit, $idReal]);
        return $this->conn->lastInsertId();
    }

    public function asociarProductorCooperativa($productorIdReal, $cooperativaIdReal)
    {
        $stmt = $this->conn->prepare("INSERT INTO rel_productor_coop (productor_id_real, cooperativa_id_real) VALUES (?, ?)");
        return $stmt->execute([$productorIdReal, $cooperativaIdReal]);
    }

    public function obtenerProductoresPorCooperativa($cooperativaIdReal)
    {
        $stmt = $this->conn->prepare("
        SELECT u.id AS usuario_id, u.usuario, u.cuit, u.id_real, i.nombre, i.telefono, i.correo, i.direccion
        FROM usuarios u
        JOIN rel_productor_coop rpc ON u.id_real = rpc.productor_id_real
        LEFT JOIN usuarios_info i ON u.id = i.usuario_id
        WHERE rpc.cooperativa_id_real = ?
        ORDER BY u.usuario ASC
    ");
        $stmt->execute([$cooperativaIdReal]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function guardarInfoProductor($usuarioId, $nombre, $telefono, $correo, $direccion)
    {
        // Verificar si ya existe
        $stmt = $this->conn->prepare("SELECT id FROM usuarios_info WHERE usuario_id = ?");
        $stmt->execute([$usuarioId]);

        if ($stmt->fetch()) {
            // Actualizar
            $stmt = $this->conn->prepare("
            UPDATE usuarios_info 
            SET nombre = ?, telefono = ?, correo = ?, direccion = ?
            WHERE usuario_id = ?
        ");
            return $stmt->execute([$nombre, $telefono, $correo, $direccion, $usuarioId]);
        } else {
            // Insertar
            $stmt = $this->conn->prepare("
            INSERT INTO usuarios_info (usuario_id, nombre, telefono, correo, direccion)
            VALUES (?, ?, ?, ?, ?)
        ");
            return $stmt->execute([$usuarioId, $nombre, $telefono, $correo, $direccion]);
        }
    }
}
