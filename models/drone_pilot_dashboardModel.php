<?php
class DronePilotDashboardModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        // Asegura modo estricto de errores
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Retorna solicitudes asignadas a un piloto.
     * Filtra por piloto_id y ordena por created_at desc.
     */
    public function getSolicitudesByPilotoId(int $pilotoId): array
    {
        $sql = "SELECT 
                    s.id,
                    s.productor_id_real,
                    s.estado,
                    s.superficie_ha,
                    s.fecha_visita,
                    s.hora_visita_desde,
                    s.hora_visita_hasta,
                    s.dir_provincia,
                    s.dir_localidad,
                    s.observaciones,
                    DATE_FORMAT(s.created_at, '%Y-%m-%d %H:%i:%s') AS created_at
                FROM drones_solicitud s
                WHERE s.piloto_id = :piloto_id
                ORDER BY s.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':piloto_id', $pilotoId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
