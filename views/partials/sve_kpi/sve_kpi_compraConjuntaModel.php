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

    // TOP productos por cantidad y monto (acepta filtros opcionales: fecha inicio/fin y cooperativa)
    public function obtenerTopProductos(int $limit = 10, ?string $start = null, ?string $end = null, ?int $cooperativa = null): array
    {
        $pdo = $this->getPdo();
        $sql = "SELECT dp.producto_id, dp.nombre_producto, SUM(dp.cantidad) AS total_cantidad, SUM(dp.precio_producto * dp.cantidad) AS total_monto
             FROM detalle_pedidos dp
             JOIN pedidos p ON p.id = dp.pedido_id
             WHERE 1=1";

        $params = [];
        if ($start) { $sql .= " AND p.fecha_pedido >= :start"; $params[':start'] = $start; }
        if ($end)   { $sql .= " AND p.fecha_pedido <= :end";   $params[':end']   = $end; }
        if ($cooperativa) { $sql .= " AND p.cooperativa = :coop"; $params[':coop'] = $cooperativa; }

        $sql .= " GROUP BY dp.producto_id, dp.nombre_producto
             ORDER BY total_cantidad DESC
             LIMIT :limit";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // TOP cooperativas por cantidad de pedidos (acepta filtros por fecha)
    public function obtenerTopCooperativas(int $limit = 10, ?string $start = null, ?string $end = null): array
    {
        $pdo = $this->getPdo();
        $sql = "SELECT p.cooperativa AS id, i.nombre AS nombre, COUNT(*) AS pedidos_count, SUM(p.total_pedido) AS total_monto
             FROM pedidos p
             JOIN usuarios u ON u.id_real = p.cooperativa
             JOIN usuarios_info i ON i.usuario_id = u.id
             WHERE 1=1";

        $params = [];
        if ($start) { $sql .= " AND p.fecha_pedido >= :start"; $params[':start'] = $start; }
        if ($end)   { $sql .= " AND p.fecha_pedido <= :end";   $params[':end']   = $end; }

        $sql .= " GROUP BY p.cooperativa, i.nombre
             ORDER BY pedidos_count DESC
             LIMIT :limit";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // TOP productores por cantidad de pedidos (acepta filtros por fecha y cooperativa)
    public function obtenerTopProductores(int $limit = 10, ?string $start = null, ?string $end = null, ?int $cooperativa = null): array
    {
        $pdo = $this->getPdo();
        $sql = "SELECT p.productor AS id, i.nombre AS nombre, COUNT(*) AS pedidos_count, SUM(p.total_pedido) AS total_monto
             FROM pedidos p
             JOIN usuarios u ON u.id_real = p.productor
             JOIN usuarios_info i ON i.usuario_id = u.id
             WHERE 1=1";

        $params = [];
        if ($start) { $sql .= " AND p.fecha_pedido >= :start"; $params[':start'] = $start; }
        if ($end)   { $sql .= " AND p.fecha_pedido <= :end";   $params[':end']   = $end; }
        if ($cooperativa) { $sql .= " AND p.cooperativa = :coop"; $params[':coop'] = $cooperativa; }

        $sql .= " GROUP BY p.productor, i.nombre
             ORDER BY pedidos_count DESC
             LIMIT :limit";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Resumen general de pedidos (acepta filtros por fecha y cooperativa)
    public function resumenTotales(?string $start = null, ?string $end = null, ?int $cooperativa = null): array
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

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: [];
    }

    // Serie temporal: pedidos por mes (acepta months o rango de fechas y cooperativa)
    public function obtenerPedidosPorMes(int $months = 6, ?string $start = null, ?string $end = null, ?int $cooperativa = null): array
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
}
