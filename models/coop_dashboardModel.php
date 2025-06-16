<?php
class CoopDashboardModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

public function obtenerOperativosConParticipacion($cooperativa_id) {
    $stmt = $this->pdo->prepare("
        SELECT o.id, o.nombre, o.descripcion, o.fecha_inicio, o.fecha_cierre,
            COALESCE(p.participa, 'no') as participa
        FROM operativos o
        LEFT JOIN operativos_cooperativas_participacion p
            ON o.id = p.operativo_id AND p.cooperativa_id_real = ?
        WHERE o.estado = 'abierto'
        ORDER BY o.fecha_inicio DESC
    ");
    $stmt->execute([$cooperativa_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    public function guardarParticipacion($operativo_id, $cooperativa_id, $participa)
    {
        $participa = $participa === 'si' ? 'si' : 'no';

        // Verificar si ya existe
        $check = $this->pdo->prepare("
            SELECT id FROM operativos_cooperativas_participacion 
            WHERE operativo_id = ? AND cooperativa_id_real = ?
        ");
        $check->execute([$operativo_id, $cooperativa_id]);
        $existe = $check->fetch();

        if ($existe) {
            $update = $this->pdo->prepare("
                UPDATE operativos_cooperativas_participacion 
                SET participa = ?, fecha_registro = CURRENT_TIMESTAMP
                WHERE operativo_id = ? AND cooperativa_id_real = ?
            ");
            return $update->execute([$participa, $operativo_id, $cooperativa_id]);
        } else {
            $insert = $this->pdo->prepare("
                INSERT INTO operativos_cooperativas_participacion 
                (operativo_id, cooperativa_id_real, participa) 
                VALUES (?, ?, ?)
            ");
            return $insert->execute([$operativo_id, $cooperativa_id, $participa]);
        }
    }
}
