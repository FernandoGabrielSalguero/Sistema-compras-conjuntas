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
}
