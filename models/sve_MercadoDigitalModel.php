<?php
class SveMercadoDigitalModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function listarCooperativas() {
        $stmt = $this->pdo->query("
            SELECT u.id_real, i.nombre
            FROM usuarios u
            JOIN usuarios_info i ON i.usuario_id = u.id
            WHERE u.rol = 'cooperativa'
            ORDER BY i.nombre
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarProductoresPorCooperativa($coop_id) {
        $stmt = $this->pdo->prepare("
            SELECT u.id_real, i.nombre
            FROM rel_productor_coop rel
            JOIN usuarios u ON u.id_real = rel.productor_id_real
            JOIN usuarios_info i ON i.usuario_id = u.id
            WHERE rel.cooperativa_id_real = ?
            ORDER BY i.nombre
        ");
        $stmt->execute([$coop_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
