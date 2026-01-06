<?php
class SveKpiCosechaModel
{
    /** @var PDO */
    public PDO $pdo;

    private function getPdo(): PDO
    {
        if (!($this->pdo instanceof PDO)) {
            throw new Exception('PDO no disponible en SveKpiCosechaModel (inyectar $pdo desde el controlador).');
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

    // Lista de cooperativas que participan en contratos
    public function obtenerCooperativas(): array
    {
        $pdo = $this->getPdo();
        // La tabla de participaciones no tiene un id de cooperativa, guardamos el nombre como id
        $sql = "SELECT DISTINCT cp.nom_cooperativa AS nombre, cp.nom_cooperativa AS id
                FROM cosechaMecanica_cooperativas_participacion cp
                ORDER BY nombre";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lista de productores presentes en participaciones
    public function obtenerProductores(): array
    {
        $pdo = $this->getPdo();
        $sql = "SELECT DISTINCT cp.productor AS id, cp.productor AS nombre
                FROM cosechaMecanica_cooperativas_participacion cp
                ORDER BY nombre";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Resumen general: cantidad de contratos, superficie total, produccion estimada, monto estimado (costo_base * superficie)
    public function resumenTotales(?string $start = null, ?string $end = null, ?string $cooperativa = null, ?string $productor = null, ?string $estado = null): array
    {
        $pdo = $this->getPdo();

        $sql = "SELECT
                    COUNT(DISTINCT cm.id) AS total_contratos,
                    SUM(cp.superficie) AS total_superficie_ha,
                    SUM(cp.prod_estimada) AS total_prod_estimada,
                    SUM(cm.costo_base * cp.superficie) AS total_monto_estimado
                FROM CosechaMecanica cm
                LEFT JOIN cosechaMecanica_cooperativas_participacion cp ON cp.contrato_id = cm.id
                WHERE 1=1";

        $params = [];
        if ($start) { $sql .= " AND cm.fecha_apertura >= :start"; $params[':start'] = $start; }
        if ($end)   { $sql .= " AND cm.fecha_cierre <= :end";   $params[':end'] = $end; }
        if ($cooperativa) { $sql .= " AND cp.nom_cooperativa = :coop"; $params[':coop'] = $cooperativa; }
        if ($productor) { $sql .= " AND cp.productor = :productor"; $params[':productor'] = $productor; }
        if ($estado) { $sql .= " AND cm.estado = :estado"; $params[':estado'] = $estado; }

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: [];
    }

    // Serie temporal: contratos (o participaciones) por mes o por fecha
    public function obtenerContratosPorMes(int $months = 6, ?string $start = null, ?string $end = null, ?string $cooperativa = null, ?string $productor = null, string $group = 'month'): array
    {
        $pdo = $this->getPdo();

        if (!$start) {
            $start = (new DateTime())->modify("-" . max(1, $months) . " months")->format('Y-m-01');
        }

        // Usamos la fecha_estimada de participacion si existe, sino fecha_apertura del contrato
        $format = ($group === 'date') ? '%Y-%m-%d' : '%Y-%m';
        $sql = "SELECT DATE_FORMAT(COALESCE(NULLIF(cp.fecha_estimada,''), cm.fecha_apertura), '{$format}') AS ym, COUNT(*) AS count_contratos, SUM(cp.superficie) AS total_superficie
                FROM CosechaMecanica cm
                LEFT JOIN cosechaMecanica_cooperativas_participacion cp ON cp.contrato_id = cm.id
                WHERE COALESCE(NULLIF(cp.fecha_estimada,''), cm.fecha_apertura) >= :start";

        $params = [':start' => $start];
        if ($end) { $sql .= " AND COALESCE(NULLIF(cp.fecha_estimada,''), cm.fecha_apertura) <= :end"; $params[':end'] = $end; }
        if ($cooperativa) { $sql .= " AND cp.nom_cooperativa = :coop"; $params[':coop'] = $cooperativa; }
        if ($productor) { $sql .= " AND cp.productor = :productor"; $params[':productor'] = $productor; }

        $sql .= " GROUP BY ym ORDER BY ym ASC";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Breakdown por estado de contrato
    public function contratosPorEstado(?string $start = null, ?string $end = null, ?string $cooperativa = null, ?string $productor = null): array
    {
        $pdo = $this->getPdo();
        $sql = "SELECT cm.estado, COUNT(DISTINCT cm.id) AS count
                FROM CosechaMecanica cm
                LEFT JOIN cosechaMecanica_cooperativas_participacion cp ON cp.contrato_id = cm.id
                WHERE 1=1";
        $params = [];
        if ($start) { $sql .= " AND cm.fecha_apertura >= :start"; $params[':start'] = $start; }
        if ($end)   { $sql .= " AND cm.fecha_cierre <= :end";   $params[':end'] = $end; }
        if ($cooperativa) { $sql .= " AND cp.nom_cooperativa = :coop"; $params[':coop'] = $cooperativa; }
        if ($productor) { $sql .= " AND cp.productor = :productor"; $params[':productor'] = $productor; }

        $sql .= " GROUP BY cm.estado ORDER BY count DESC";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Top cooperativas por superficie o cantidad de participaciones
    public function topCooperativas(int $limit = 10, ?string $start = null, ?string $end = null, ?string $productor = null): array
    {
        $pdo = $this->getPdo();
        $sql = "SELECT cp.nom_cooperativa AS id, cp.nom_cooperativa AS nombre, COUNT(*) AS participaciones, SUM(cp.superficie) AS total_superficie, SUM(cm.costo_base * cp.superficie) AS total_monto
                FROM cosechaMecanica_cooperativas_participacion cp
                JOIN CosechaMecanica cm ON cm.id = cp.contrato_id
                WHERE 1=1";
        $params = [];
        if ($start) { $sql .= " AND cm.fecha_apertura >= :start"; $params[':start'] = $start; }
        if ($end)   { $sql .= " AND cm.fecha_cierre <= :end";   $params[':end'] = $end; }
        if ($productor) { $sql .= " AND cp.productor = :productor"; $params[':productor'] = $productor; }

        $sql .= " GROUP BY cp.nom_cooperativa ORDER BY total_superficie DESC LIMIT :limit";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Top productores por superficie o produccion estimada
    public function topProductores(int $limit = 10, ?string $start = null, ?string $end = null, ?string $cooperativa = null): array
    {
        $pdo = $this->getPdo();
        $sql = "SELECT cp.productor AS id, cp.productor AS nombre, COUNT(*) AS participaciones, SUM(cp.superficie) AS total_superficie, SUM(cp.prod_estimada) AS total_prod_estimada
                FROM cosechaMecanica_cooperativas_participacion cp
                JOIN CosechaMecanica cm ON cm.id = cp.contrato_id
                WHERE 1=1";
        $params = [];
        if ($start) { $sql .= " AND cm.fecha_apertura >= :start"; $params[':start'] = $start; }
        if ($end)   { $sql .= " AND cm.fecha_cierre <= :end";   $params[':end'] = $end; }
        if ($cooperativa) { $sql .= " AND cp.nom_cooperativa = :coop"; $params[':coop'] = $cooperativa; }

        $sql .= " GROUP BY cp.productor ORDER BY total_superficie DESC LIMIT :limit";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
