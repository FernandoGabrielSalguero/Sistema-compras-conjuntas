<?php
declare(strict_types=1);

class DroneCalendarModel
{
    /** @var PDO */
    public PDO $pdo;

    /**
     * Devuelve visitas (una por fila) entre fechas, con nombre del usuario y horas.
     * @return array<int, array<string, mixed>>
     */
    public function getVisitsBetween(string $fromDate, string $toDate): array
    {
        $sql = "
            SELECT
                ds.fecha_visita                        AS fecha,
                TIME_FORMAT(ds.hora_visita_desde, '%H:%i') AS hora_desde,
                TIME_FORMAT(ds.hora_visita_hasta, '%H:%i') AS hora_hasta,
                COALESCE(ui.nombre, u.usuario)        AS nombre
            FROM drones_solicitud ds
            INNER JOIN usuarios u
                ON u.id_real = ds.productor_id_real
            LEFT JOIN usuarios_info ui
                ON ui.usuario_id = u.id
            WHERE ds.fecha_visita BETWEEN :from AND :to
            ORDER BY ds.fecha_visita ASC, ds.hora_visita_desde ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':from', $fromDate);
        $stmt->bindValue(':to', $toDate);
        $stmt->execute();
        /** @var array<int, array<string, mixed>> $rows */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        return $rows;
    }
}
