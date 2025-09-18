<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';

final class InisioSesionModel
{
    private \PDO $conn;

    public function __construct()
    {
        global $pdo;
        $this->conn = $pdo;
    }

    /**
     * @param array{rol?:string,usuario_input?:string,created_at?:string} $filters
     * @return array{rows:array<int,array<string,mixed>>, total:int}
     */
    public function searchLogins(array $filters, int $page, int $perPage): array
    {
        // Sanitización/validación básica
        $where = [];
        $params = [];

        if (!empty($filters['rol'])) {
            $rol = strtolower(trim((string)$filters['rol']));
            $valid = ['ingeniero','cooperativa','productor','sve'];
            if (!in_array($rol, $valid, true)) {
                throw new \InvalidArgumentException('Rol inválido');
            }
            $where[] = 'la.rol = :rol';
            $params[':rol'] = $rol;
        }

        if (!empty($filters['usuario_input'])) {
            $uin = trim((string)$filters['usuario_input']);
            $where[] = 'la.usuario_input LIKE :uin';
            $params[':uin'] = '%' . $uin . '%';
        }

        if (!empty($filters['created_at'])) {
            // formato YYYY-MM-DD
            $fecha = (string)$filters['created_at'];
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                throw new \InvalidArgumentException('Fecha inválida (YYYY-MM-DD)');
            }
            // Comparar por fecha sobre AR: DATE(created_at - INTERVAL 3 HOUR) = :fecha
            $where[] = "DATE(la.created_at - INTERVAL 3 HOUR) = :fch";
            $params[':fch'] = $fecha;
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $offset = max(0, ($page - 1) * $perPage);

        // COUNT total
        $sqlCount = "SELECT COUNT(*) AS c FROM login_auditoria la {$whereSql}";
        $stmtC = $this->conn->prepare($sqlCount);
        foreach ($params as $k => $v) {
            $type = is_int($v) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
            $stmtC->bindValue($k, $v, $type);
        }
        $stmtC->execute();
        $total = (int)($stmtC->fetchColumn() ?: 0);

        // Data
        $sql = "
            SELECT
                la.id,
                la.usuario_input,
                la.usuario_id_real,
                la.rol,
                la.resultado,
                la.motivo,
                la.ip,
                la.user_agent,
                DATE_FORMAT(la.created_at - INTERVAL 3 HOUR, '%Y-%m-%d %H:%i:%s') AS created_at_ar
            FROM login_auditoria la
            {$whereSql}
            ORDER BY la.id DESC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->conn->prepare($sql);
        foreach ($params as $k => $v) {
            $type = is_int($v) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
            $stmt->bindValue($k, $v, $type);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);

        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        return ['rows' => $rows, 'total' => $total];
    }
}
