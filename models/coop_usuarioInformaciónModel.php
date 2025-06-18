<?php
class UsuarioInformacionModel {

    private $conn;

    public function __construct() {
        global $pdo;
        $this->conn = $pdo;
    }

    public function obtenerRangoCooperativa($cooperativaIdReal) {
        $stmt = $this->conn->prepare("SELECT * FROM cooperativas_rangos WHERE cooperativa_id_real = ?");
        $stmt->execute([$cooperativaIdReal]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerProximoIdRealDisponible($inicio, $fin) {
        $stmt = $this->conn->prepare("SELECT id_real FROM usuarios WHERE id_real BETWEEN ? AND ? ORDER BY id_real ASC");
        $stmt->execute([$inicio, $fin]);
        $usados = $stmt->fetchAll(PDO::FETCH_COLUMN);

        for ($i = $inicio; $i <= $fin; $i++) {
            if (!in_array($i, $usados)) {
                return $i;
            }
        }
        return null;
    }

    public function crearUsuarioProductor($usuario, $contrasena, $cuit, $idReal) {
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO usuarios (usuario, contrasena, rol, permiso_ingreso, cuit, id_real) VALUES (?, ?, 'productor', 'Habilitado', ?, ?)");
        $stmt->execute([$usuario, $hash, $cuit, $idReal]);
        return $this->conn->lastInsertId();
    }

    public function asociarProductorCooperativa($productorIdReal, $cooperativaIdReal) {
        $stmt = $this->conn->prepare("INSERT INTO rel_productor_coop (productor_id_real, cooperativa_id_real) VALUES (?, ?)");
        return $stmt->execute([$productorIdReal, $cooperativaIdReal]);
    }
}
