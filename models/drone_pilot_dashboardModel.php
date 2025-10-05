<?php
class DronePilotDashboardModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Solicitudes asignadas al piloto con el nombre del productor.
     * JOIN: drones_solicitud.productor_id_real -> usuarios.id_real -> usuarios_info.nombre
     */
    public function getSolicitudesByPilotoId(int $pilotoId): array
    {
        $sql = "SELECT 
                    s.id,
                    s.productor_id_real,
                    COALESCE(ui.nombre, u.usuario, s.productor_id_real) AS productor_nombre,
                    s.superficie_ha,
                    s.fecha_visita,
                    s.hora_visita_desde,
                    s.hora_visita_hasta,
                    s.dir_localidad
                FROM drones_solicitud s
                LEFT JOIN usuarios u           ON u.id_real = s.productor_id_real
                LEFT JOIN usuarios_info ui      ON ui.usuario_id = u.id
                WHERE s.piloto_id = :piloto_id
                ORDER BY s.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':piloto_id', $pilotoId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
