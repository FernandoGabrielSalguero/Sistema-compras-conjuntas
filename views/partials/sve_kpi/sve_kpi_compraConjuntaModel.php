<?php
class SveKpiCompraConjuntaModel
{
    /** @var PDO */
    public PDO $pdo;

    private function getPdo(): PDO
    {
        if (!($this->pdo instanceof PDO)) {
            throw new Exception('PDO no disponible en SveKpiCompraConjuntaModel (inyectar $pdo desde el controlador).');
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







    // Resumen general de pedidos (acepta filtros por fecha y cooperativa)
    public function resumenTotales(?string $start = null, ?string $end = null, ?int $cooperativa = null, ?int $productor = null, ?int $operativo = null): array
    {
        $pdo = $this->getPdo();
        $sql = "SELECT
                COUNT(*) AS total_pedidos,
                SUM(total_pedido) AS total_monto,
                AVG(total_pedido) AS avg_monto,
                COUNT(DISTINCT p.productor) AS unique_productores,
                COUNT(DISTINCT p.cooperativa) AS unique_cooperativas
             FROM pedidos p
             WHERE 1=1";
        $params = [];
        if ($start) { $sql .= " AND p.fecha_pedido >= :start"; $params[':start'] = $start; }
        if ($end)   { $sql .= " AND p.fecha_pedido <= :end";   $params[':end']   = $end; }
        if ($cooperativa) { $sql .= " AND p.cooperativa = :coop"; $params[':coop'] = $cooperativa; }
        if ($productor) { $sql .= " AND p.productor = :productor"; $params[':productor'] = $productor; }
        if ($operativo) { $sql .= " AND p.operativo_id = :operativo"; $params[':operativo'] = $operativo; }

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: [];
    }

    // Serie temporal: pedidos por mes (acepta months o rango de fechas y cooperativa)
    public function obtenerPedidosPorMes(int $months = 6, ?string $start = null, ?string $end = null, ?int $cooperativa = null, ?int $productor = null, ?int $operativo = null): array
    {
        $pdo = $this->getPdo();

        if (!$start) {
            $start = (new DateTime())->modify("-" . max(1, $months) . " months")->format('Y-m-01');
        }

        $sql = "SELECT DATE_FORMAT(fecha_pedido, '%Y-%m') AS ym, COUNT(*) AS pedidos_count, SUM(total_pedido) AS total_monto
             FROM pedidos p
             WHERE p.fecha_pedido >= :start";
        $params = [':start' => $start];
        if ($end) { $sql .= " AND p.fecha_pedido <= :end"; $params[':end'] = $end; }
        if ($cooperativa) { $sql .= " AND p.cooperativa = :coop"; $params[':coop'] = $cooperativa; }
        if ($productor) { $sql .= " AND p.productor = :productor"; $params[':productor'] = $productor; }
        if ($operativo) { $sql .= " AND p.operativo_id = :operativo"; $params[':operativo'] = $operativo; }

        $sql .= " GROUP BY ym ORDER BY ym ASC";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lista de cooperativas (id, nombre)
    public function obtenerCooperativas(): array
    {
        $pdo = $this->getPdo();
        $stmt = $pdo->query("SELECT u.id_real AS id, i.nombre FROM usuarios u JOIN usuarios_info i ON i.usuario_id = u.id WHERE u.rol = 'cooperativa' ORDER BY i.nombre");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lista de productores asociados o con pedidos por cooperativa
    public function obtenerProductoresPorCooperativa(int $cooperativa_id): array
    {
        $pdo = $this->getPdo();
        $stmt = $pdo->prepare(
            "SELECT DISTINCT u.id_real AS id, i.nombre
             FROM pedidos p
             JOIN usuarios u ON u.id_real = p.productor
             JOIN usuarios_info i ON i.usuario_id = u.id
             WHERE p.cooperativa = :coop
             ORDER BY i.nombre"
        );
        $stmt->execute([':coop' => $cooperativa_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lista de operativos
    public function obtenerOperativos(): array
    {
        $pdo = $this->getPdo();
        $stmt = $pdo->query("SELECT id, nombre, fecha_inicio, fecha_cierre FROM operativos ORDER BY fecha_inicio DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
