<?php
declare(strict_types=1);

final class DroneVariableModel
{
    public PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    private function tableFor(string $entity): string
    {
        switch ($entity) {
            case 'patologias': return 'dron_patologias';
            case 'produccion': return 'dron_produccion';
            default: throw new InvalidArgumentException('Entidad inválida');
        }
    }

    public function list(string $entity, string $q = '', bool $inactivos = false): array
    {
        $tbl = $this->tableFor($entity);
        $where = '1';
        $params = [];
        if (!$inactivos) { $where .= " AND activo = 'si'"; }
        if ($q !== '') {
            $where .= " AND (nombre LIKE :q OR descripcion LIKE :q)";
            $params[':q'] = '%'.$q.'%';
        }
        $sql = "SELECT id, nombre, descripcion, activo, created_at, updated_at
                FROM {$tbl}
                WHERE {$where}
                ORDER BY nombre ASC";
        $st = $this->pdo->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public function get(string $entity, int $id): ?array
    {
        $tbl = $this->tableFor($entity);
        $st = $this->pdo->prepare("SELECT id, nombre, descripcion, activo, created_at, updated_at FROM {$tbl} WHERE id = :id");
        $st->execute([':id'=>$id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public function create(string $entity, string $nombre, ?string $descripcion): int
    {
        $tbl = $this->tableFor($entity);
        $st = $this->pdo->prepare("INSERT INTO {$tbl} (nombre, descripcion) VALUES (:n, :d)");
        $st->execute([':n'=>$nombre, ':d'=>$descripcion]);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(string $entity, int $id, string $nombre, ?string $descripcion): bool
    {
        $tbl = $this->tableFor($entity);
        $st = $this->pdo->prepare("UPDATE {$tbl} SET nombre = :n, descripcion = :d WHERE id = :id");
        return $st->execute([':n'=>$nombre, ':d'=>$descripcion, ':id'=>$id]);
    }

    public function setActivo(string $entity, int $id, bool $activo): bool
    {
        $tbl = $this->tableFor($entity);
        $st = $this->pdo->prepare("UPDATE {$tbl} SET activo = :a WHERE id = :id");
        return $st->execute([':a'=>$activo ? 'si' : 'no', ':id'=>$id]);
    }
}
