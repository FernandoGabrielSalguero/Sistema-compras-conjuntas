<?php

declare(strict_types=1);

final class DroneStockModel
{
    /** @var PDO */
    public PDO $pdo;

    /** Listado de productos con patologías asociadas (ids y nombres) */
    public function listProducts(): array
    {
        $sql = "
            SELECT
                p.id,
                p.nombre,
                p.detalle,
                p.principio_activo,
                p.cantidad_deposito,
                p.costo_hectarea,
                p.tiempo_carencia,
                p.activo,
                COALESCE(GROUP_CONCAT(DISTINCT dsp.patologia_id ORDER BY dsp.patologia_id SEPARATOR ','), '') AS pat_ids,
                COALESCE(GROUP_CONCAT(DISTINCT dp.nombre ORDER BY dp.nombre SEPARATOR '||'), '') AS pat_names
            FROM dron_productos_stock p
            LEFT JOIN dron_productos_stock_patologias dsp ON dsp.producto_id = p.id
            LEFT JOIN dron_patologias dp ON dp.id = dsp.patologia_id
            GROUP BY p.id
            ORDER BY p.nombre ASC";
        $st = $this->pdo->query($sql);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return array_map(function (array $r) {
            return [
                'id' => (int)$r['id'],
                'nombre' => $r['nombre'],
                'detalle' => $r['detalle'],
                'principio_activo' => $r['principio_activo'],
                'cantidad_deposito' => (int)$r['cantidad_deposito'],
                'costo_hectarea' => isset($r['costo_hectarea']) ? (float)$r['costo_hectarea'] : 0.0,
                'tiempo_carencia' => $r['tiempo_carencia'] ?? null,
                'activo' => $r['activo'] ?? 'si',
                'patologias_ids' => $r['pat_ids'] !== '' ? array_map('intval', explode(',', $r['pat_ids'])) : [],
                'patologias_nombres' => $r['pat_names'] !== '' ? explode('||', $r['pat_names']) : [],
            ];
        }, $rows);
    }


    /** Patologías activas para poblar selects */
    public function getPatologias(): array
    {
        $st = $this->pdo->prepare("SELECT id, nombre FROM dron_patologias WHERE activo = 'si' ORDER BY nombre ASC");
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Crea producto + relaciones (max 6) */
    public function createProduct(string $nombre, ?string $detalle, ?string $principio, int $cantidad, array $patIds, float $costo, string $activo, ?string $tiempo_carencia): int
    {
        $this->pdo->beginTransaction();
        try {
            $sql = "INSERT INTO dron_productos_stock (nombre, detalle, principio_activo, cantidad_deposito, costo_hectarea, activo, tiempo_carencia)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $st = $this->pdo->prepare($sql);
            $st->execute([$nombre, $detalle, $principio, $cantidad, $costo, $activo, $tiempo_carencia]);
            $id = (int)$this->pdo->lastInsertId();

            $this->upsertPatologias($id, $patIds);
            $this->pdo->commit();
            return $id;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }


    /** Actualiza producto + relaciones (max 6) */
    public function updateProduct(int $id, string $nombre, ?string $detalle, ?string $principio, int $cantidad, array $patIds, float $costo, string $activo, ?string $tiempo_carencia): bool
    {
        $this->pdo->beginTransaction();
        try {
            $sql = "UPDATE dron_productos_stock
                       SET nombre = ?, detalle = ?, principio_activo = ?, cantidad_deposito = ?, costo_hectarea = ?, activo = ?, tiempo_carencia = ?
                     WHERE id = ?";
            $st = $this->pdo->prepare($sql);
            $st->execute([$nombre, $detalle, $principio, $cantidad, $costo, $activo, $tiempo_carencia, $id]);

            $this->upsertPatologias($id, $patIds);
            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }


    /** Elimina producto (cascade borra relaciones) */
    public function deleteProduct(int $id): bool
    {
        $st = $this->pdo->prepare("DELETE FROM dron_productos_stock WHERE id = ?");
        return $st->execute([$id]);
    }

    /** Reemplaza relaciones por el set provisto, limitado a 6 */
    private function upsertPatologias(int $productoId, array $patIds): void
    {
        // Normalizar/validar ids
        $patIds = array_values(array_unique(array_map('intval', $patIds)));
        if (count($patIds) > 6) {
            $patIds = array_slice($patIds, 0, 6);
        }

        // Validar existencia de patologías
        if ($patIds) {
            $in = implode(',', array_fill(0, count($patIds), '?'));
            $chk = $this->pdo->prepare("SELECT id FROM dron_patologias WHERE id IN ($in)");
            $chk->execute($patIds);
            $valid = array_map('intval', $chk->fetchAll(PDO::FETCH_COLUMN));
            $patIds = array_values(array_intersect($patIds, $valid));
        }

        // Limpiar existentes
        $del = $this->pdo->prepare("DELETE FROM dron_productos_stock_patologias WHERE producto_id = ?");
        $del->execute([$productoId]);

        // Insertar nuevas
        if ($patIds) {
            $ins = $this->pdo->prepare("INSERT INTO dron_productos_stock_patologias (producto_id, patologia_id) VALUES (?, ?)");
            foreach ($patIds as $pid) {
                $ins->execute([$productoId, $pid]);
            }
        }
    }
}
