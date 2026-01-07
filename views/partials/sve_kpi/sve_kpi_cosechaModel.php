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

    // Lista de contratos (para filtro por contrato)
    public function obtenerContratos(): array
    {
        $pdo = $this->getPdo();
        $sql = "SELECT cm.id AS id, cm.nombre AS nombre
                FROM CosechaMecanica cm
                ORDER BY cm.nombre ASC";
        $stmt = $pdo->query($sql);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    // Lista de cooperativas que participan en contratos
    public function obtenerCooperativas(?int $contratoId = null): array
    {
        $pdo = $this->getPdo();
        // La tabla de participaciones no tiene un id de cooperativa, guardamos el nombre como id
        $sql = "SELECT DISTINCT cp.nom_cooperativa AS nombre, cp.nom_cooperativa AS id
                FROM cosechaMecanica_cooperativas_participacion cp
                WHERE 1=1";
        $params = [];
        if ($contratoId) {
            $sql .= " AND cp.contrato_id = :contrato_id";
            $params[':contrato_id'] = (int)$contratoId;
        }
        $sql .= " ORDER BY nombre";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lista de productores presentes en participaciones
    public function obtenerProductores(?int $contratoId = null): array
    {
        $pdo = $this->getPdo();
        $sql = "SELECT DISTINCT cp.productor AS id, cp.productor AS nombre
                FROM cosechaMecanica_cooperativas_participacion cp
                WHERE 1=1";
        $params = [];
        if ($contratoId) {
            $sql .= " AND cp.contrato_id = :contrato_id";
            $params[':contrato_id'] = (int)$contratoId;
        }
        $sql .= " ORDER BY nombre";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Resumen general: cantidad de contratos, superficie total, produccion estimada, monto estimado (costo_base * superficie)
    public function resumenTotales(?string $start = null, ?string $end = null, ?int $contratoId = null, ?string $cooperativa = null, ?string $productor = null, ?string $estado = null): array
    {
        $pdo = $this->getPdo();

        $sql = "SELECT
                    COUNT(DISTINCT cm.id) AS total_contratos,
                    COALESCE(SUM(cp.superficie), 0) AS total_superficie_ha,
                    COALESCE(SUM(cm.costo_base * cp.superficie), 0) AS total_monto_estimado
                FROM CosechaMecanica cm
                LEFT JOIN cosechaMecanica_cooperativas_participacion cp ON cp.contrato_id = cm.id
                WHERE 1=1";

        $params = [];
        if ($start) {
            $sql .= " AND cm.fecha_apertura >= :start";
            $params[':start'] = $start;
        }
        if ($end) {
            $sql .= " AND cm.fecha_cierre <= :end";
            $params[':end'] = $end;
        }
        if ($contratoId) {
            $sql .= " AND cm.id = :contrato_id";
            $params[':contrato_id'] = (int)$contratoId;
        }
        if ($cooperativa) {
            $sql .= " AND cp.nom_cooperativa = :coop";
            $params[':coop'] = $cooperativa;
        }
        if ($productor) {
            $sql .= " AND cp.productor = :productor";
            $params[':productor'] = $productor;
        }
        if ($estado) {
            $sql .= " AND cm.estado = :estado";
            $params[':estado'] = $estado;
        }

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: [];
    }

    // Serie temporal: contratos (o participaciones) por mes o por fecha
    public function obtenerContratosPorMes(
        int $months = 6,
        ?string $start = null,
        ?string $end = null,
        ?int $contratoId = null,
        ?string $cooperativa = null,
        ?string $productor = null,
        ?string $estado = null,
        string $group = 'month'
    ): array {
        $pdo = $this->getPdo();

        if (!$start) {
            $start = (new DateTime())->modify("-" . max(1, $months) . " months")->format('Y-m-01');
        }

        // fecha_estimada es VARCHAR -> la parseamos; fallback a fecha_apertura para no dejar vacío
        $format = ($group === 'date') ? '%Y-%m-%d' : '%Y-%m';
        $fechaRef = "COALESCE(STR_TO_DATE(NULLIF(cp.fecha_estimada,''), '%Y-%m-%d'), cm.fecha_apertura)";

        $sql = "SELECT
                    DATE_FORMAT({$fechaRef}, '{$format}') AS ym,
                    COUNT(cp.id) AS count_visitas,
                    COUNT(cp.id) AS count_contratos,
                    SUM(cp.superficie) AS total_superficie
                FROM CosechaMecanica cm
                LEFT JOIN cosechaMecanica_cooperativas_participacion cp ON cp.contrato_id = cm.id
                WHERE {$fechaRef} >= :start";

        $params = [':start' => $start];

        if ($end) {
            $sql .= " AND {$fechaRef} <= :end";
            $params[':end'] = $end;
        }
        if ($contratoId) {
            $sql .= " AND cm.id = :contrato_id";
            $params[':contrato_id'] = (int)$contratoId;
        }
        if ($cooperativa) {
            $sql .= " AND cp.nom_cooperativa = :coop";
            $params[':coop'] = $cooperativa;
        }
        if ($productor) {
            $sql .= " AND cp.productor = :productor";
            $params[':productor'] = $productor;
        }
        if ($estado) {
            $sql .= " AND cm.estado = :estado";
            $params[':estado'] = $estado;
        }

        $sql .= " GROUP BY ym ORDER BY ym ASC";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }





}
