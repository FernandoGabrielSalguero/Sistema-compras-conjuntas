<?php
class SveKpiDronesModel
{
    /** @var PDO */
    public PDO $pdo;

    private function getPdo(): PDO
    {
        if (!($this->pdo instanceof PDO)) {
            throw new Exception('PDO no disponible en SveKpiDronesModel (inyectar $pdo desde el controlador).');
        }
        return $this->pdo;
    }

    public function ping(): array
    {
        $pdo = $this->getPdo();
        $stmt = $pdo->query("SELECT 1 AS ok, CURRENT_TIMESTAMP AS server_time");
        $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;

        return [
            'db_ok' => (bool)($row && (int)$row['ok'] === 1),
            'server_time' => $row['server_time'] ?? null
        ];
    }

    // Lista de productores presentes en solicitudes (robusta: incluye productor sin usuario registrado)
    public function obtenerProductores(): array
    {
        $pdo = $this->getPdo();
        $sql = "SELECT DISTINCT ds.productor_id_real AS id,
                COALESCE(ui.nombre, ds.ses_usuario, ds.productor_id_real) AS nombre
                FROM drones_solicitud ds
                LEFT JOIN usuarios u ON u.id_real = ds.productor_id_real
                LEFT JOIN usuarios_info ui ON ui.usuario_id = u.id
                ORDER BY nombre";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Resumen general
    public function resumenTotales(?string $start = null, ?string $end = null, ?string $productor = null, ?string $estado = null): array
    {
        $pdo = $this->getPdo();
        $sql = "SELECT
                    COUNT(*) AS total_solicitudes,
                    SUM(CASE WHEN ds.estado = 'completada' THEN 1 ELSE 0 END) AS completadas_count,
                    SUM(ds.superficie_ha) AS total_superficie_ha,
                    SUM(dsc.total) AS total_monto,
                    COUNT(DISTINCT ds.productor_id_real) AS unique_productores
                FROM drones_solicitud ds
                LEFT JOIN drones_solicitud_costos dsc ON dsc.solicitud_id = ds.id
                WHERE 1=1";
        $params = [];
        if ($start) { $sql .= " AND ds.fecha_visita >= :start"; $params[':start'] = $start; }
        if ($end)   { $sql .= " AND ds.fecha_visita <= :end";   $params[':end'] = $end; }
        if ($productor) { $sql .= " AND ds.productor_id_real = :productor"; $params[':productor'] = $productor; }
        if ($estado) { $sql .= " AND ds.estado = :estado"; $params[':estado'] = $estado; }

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: [];
    }

    // Serie temporal: solicitudes por mes o por fecha
    public function obtenerSolicitudesPorMes(int $months = 6, ?string $start = null, ?string $end = null, ?string $productor = null, ?string $estado = null, string $group = 'month'): array
    {
        $pdo = $this->getPdo();

        if (!$start) {
            $start = (new DateTime())->modify("-" . max(1, $months) . " months")->format('Y-m-01');
        }

        $format = ($group === 'date') ? '%Y-%m-%d' : '%Y-%m';
        $sql = "SELECT DATE_FORMAT(fecha_visita, '{$format}') AS ym, COUNT(*) AS solicitudes_count
                FROM drones_solicitud ds
                WHERE ds.fecha_visita >= :start";
        $params = [':start' => $start];
        if ($end) { $sql .= " AND ds.fecha_visita <= :end"; $params[':end'] = $end; }
        if ($productor) { $sql .= " AND ds.productor_id_real = :productor"; $params[':productor'] = $productor; }
        if ($estado) { $sql .= " AND ds.estado = :estado"; $params[':estado'] = $estado; }

        $sql .= " GROUP BY ym ORDER BY ym ASC";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }






}
