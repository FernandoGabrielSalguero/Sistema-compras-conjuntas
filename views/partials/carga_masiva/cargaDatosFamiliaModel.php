<?php
class CargaDatosFamiliaModel
{
    public function pingDb(): array
    {
        require_once __DIR__ . '/../../../config.php';
        global $pdo;

        if (!isset($pdo)) {
            throw new Exception('PDO no inicializado (revisar config.php).');
        }

        $stmt = $pdo->query("SELECT 1 AS ok, CURRENT_TIMESTAMP AS server_time");
        $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;

        return [
            'db_ok' => (bool)($row && (int)$row['ok'] === 1),
            'server_time' => $row['server_time'] ?? null
        ];
    }
}
