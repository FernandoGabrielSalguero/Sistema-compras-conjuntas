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

    // Lista de pilotos
    public function obtenerPilotos(): array
    {
        $pdo = $this->getPdo();
        $stmt = $pdo->query("SELECT id, nombre FROM dron_pilotos WHERE nombre IS NOT NULL ORDER BY nombre");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lista de productores presentes en solicitudes
    public function obtenerProductores(): array
    {
        $pdo = $this->getPdo();
        $sql = "SELECT DISTINCT ds.productor_id_real AS id, ui.nombre
                FROM drones_solicitud ds
                JOIN usuarios u ON u.id_real = ds.productor_id_real
                JOIN usuarios_info ui ON ui.usuario_id = u.id
                ORDER BY ui.nombre";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Resumen general
    public function resumenTotales(?string $start = null, ?string $end = null, ?int $piloto = null, ?string $productor = null, ?string $estado = null): array
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
        if ($piloto) { $sql .= " AND ds.piloto_id = :piloto"; $params[':piloto'] = $piloto; }
        if ($productor) { $sql .= " AND ds.productor_id_real = :productor"; $params[':productor'] = $productor; }
        if ($estado) { $sql .= " AND ds.estado = :estado"; $params[':estado'] = $estado; }

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: [];
    }

    // Serie temporal: solicitudes por mes
    public function obtenerSolicitudesPorMes(int $months = 6, ?string $start = null, ?string $end = null, ?int $piloto = null, ?string $productor = null, ?string $estado = null): array
    {
        $pdo = $this->getPdo();

        if (!$start) {
            $start = (new DateTime())->modify("-" . max(1, $months) . " months")->format('Y-m-01');
        }

        $sql = "SELECT DATE_FORMAT(fecha_visita, '%Y-%m') AS ym, COUNT(*) AS solicitudes_count
                FROM drones_solicitud ds
                WHERE ds.fecha_visita >= :start";
        $params = [':start' => $start];
        if ($end) { $sql .= " AND ds.fecha_visita <= :end"; $params[':end'] = $end; }
        if ($piloto) { $sql .= " AND ds.piloto_id = :piloto"; $params[':piloto'] = $piloto; }
        if ($productor) { $sql .= " AND ds.productor_id_real = :productor"; $params[':productor'] = $productor; }
        if ($estado) { $sql .= " AND ds.estado = :estado"; $params[':estado'] = $estado; }

        $sql .= " GROUP BY ym ORDER BY ym ASC";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Top pilotos por cantidad de solicitudes
    public function topPilotos(int $limit = 10, ?string $start = null, ?string $end = null, ?string $productor = null, ?string $estado = null): array
    {
        $pdo = $this->getPdo();
        $sql = "SELECT ds.piloto_id AS id, COALESCE(dp.nombre, 'Sin piloto') AS nombre, COUNT(*) AS solicitudes_count, SUM(dsc.total) AS total_monto
                FROM drones_solicitud ds
                LEFT JOIN dron_pilotos dp ON dp.id = ds.piloto_id
                LEFT JOIN drones_solicitud_costos dsc ON dsc.solicitud_id = ds.id
                WHERE 1=1";
        $params = [];
        if ($start) { $sql .= " AND ds.fecha_visita >= :start"; $params[':start'] = $start; }
        if ($end)   { $sql .= " AND ds.fecha_visita <= :end";   $params[':end'] = $end; }
        if ($productor) { $sql .= " AND ds.productor_id_real = :productor"; $params[':productor'] = $productor; }
        if ($estado) { $sql .= " AND ds.estado = :estado"; $params[':estado'] = $estado; }

        $sql .= " GROUP BY ds.piloto_id, dp.nombre ORDER BY solicitudes_count DESC LIMIT :limit";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Top productos por usos y monto
    public function topProductos(int $limit = 10, ?string $start = null, ?string $end = null, ?int $piloto = null, ?string $productor = null, ?string $estado = null): array
    {
        $pdo = $this->getPdo();
        $sql = "SELECT dsi.producto_id AS id, dsi.nombre_producto AS nombre_producto, COUNT(*) AS usos_count, SUM(dsi.total_producto_snapshot) AS total_monto
                FROM drones_solicitud_item dsi
                JOIN drones_solicitud ds ON ds.id = dsi.solicitud_id
                WHERE 1=1";
        $params = [];
        if ($start) { $sql .= " AND ds.fecha_visita >= :start"; $params[':start'] = $start; }
        if ($end)   { $sql .= " AND ds.fecha_visita <= :end";   $params[':end'] = $end; }
        if ($piloto) { $sql .= " AND ds.piloto_id = :piloto"; $params[':piloto'] = $piloto; }
        if ($productor) { $sql .= " AND ds.productor_id_real = :productor"; $params[':productor'] = $productor; }
        if ($estado) { $sql .= " AND ds.estado = :estado"; $params[':estado'] = $estado; }

        $sql .= " GROUP BY dsi.producto_id, dsi.nombre_producto ORDER BY usos_count DESC LIMIT :limit";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
