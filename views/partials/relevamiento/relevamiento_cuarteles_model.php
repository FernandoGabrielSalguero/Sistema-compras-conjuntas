<?php

class RelevamientoCuartelesModel
{
    private PDO $pdo;

    private const CUARTEL_FIELDS = [
        'codigo_cuartel',
        'variedad',
        'numero_inv',
        'sistema_conduccion',
        'superficie_ha',
        'porcentaje_cepas_produccion',
        'forma_cosecha_actual',
        'porcentaje_malla_buen_estado',
        'edad_promedio_encepado_anios',
        'estado_estructura_sistema',
        'labores_mecanizables',
    ];

    private const LIMITANTES_FIELDS = [
        'limitantes_suelo',
        'observaciones',
        'categoria_1',
        'limitante_1',
        'inversion_accion1_1',
        'obs_inversion_accion1_1',
        'ciclo_agricola1_1',
        'inversion_accion2_1',
        'obs_inversion_accion2_1',
        'ciclo_agricola2_1',
        'categoria_2',
        'limitante_2',
        'inversion_accion1_2',
        'obs_inversion_accion1_2',
        'ciclo_agricola1_2',
        'inversion_accion2_2',
        'obs_inversion_accion2_2',
        'ciclo_agricola2_2',
    ];

    private const RENDIMIENTOS_FIELDS = [
        'rend_2020_qq_ha',
        'rend_2021_qq_ha',
        'rend_2022_qq_ha',
        'ing_2023_kg',
        'rend_2023_qq_ha',
        'ing_2024_kg',
        'rend_2024_qq_ha',
        'ing_2025_kg',
        'rend_2025_qq_ha',
        'rend_promedio_5anios_qq_ha',
    ];

    private const RIESGOS_FIELDS = [
        'tiene_seguro_agricola',
        'porcentaje_dano_granizo',
        'heladas_dano_promedio_5anios',
        'presencia_freatica',
        'plagas_no_convencionales',
    ];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public function getDatosCuartelesPorProductorIdReal(string $productorIdReal): array
    {
        if ($productorIdReal === '') {
            return ['cuarteles' => []];
        }

        $st = $this->pdo->prepare("
            SELECT DISTINCT pc.*
            FROM prod_cuartel pc
            LEFT JOIN prod_fincas pf
              ON pf.id = pc.finca_id
            WHERE pc.id_responsable_real = :prod
               OR pf.productor_id_real = :prod
            ORDER BY pc.codigo_finca ASC, pc.codigo_cuartel ASC, pc.id ASC
        ");
        $st->execute([':prod' => $productorIdReal]);
        $rows = $st->fetchAll() ?: [];

        $out = [];
        foreach ($rows as $cuartel) {
            $cuartelId = (int)($cuartel['id'] ?? 0);
            $out[] = [
                'cuartel' => $cuartel,
                'limitantes' => $this->getRelatedRow('prod_cuartel_limitantes', $cuartelId),
                'rendimientos' => $this->getRelatedRow('prod_cuartel_rendimientos', $cuartelId),
                'riesgos' => $this->getRelatedRow('prod_cuartel_riesgos', $cuartelId),
            ];
        }

        return ['cuarteles' => $out];
    }

    public function guardarDatosCuartelesPorProductorIdReal(string $productorIdReal, array $cuartelesPayload): void
    {
        if ($productorIdReal === '' || empty($cuartelesPayload)) {
            return;
        }

        $this->pdo->beginTransaction();

        try {
            foreach ($cuartelesPayload as $fila) {
                $cuartelId = isset($fila['cuartel_id']) ? (int)$fila['cuartel_id'] : 0;
                if ($cuartelId <= 0 || !$this->cuartelPerteneceAProductor($cuartelId, $productorIdReal)) {
                    continue;
                }

                $this->updateRowById('prod_cuartel', $cuartelId, self::CUARTEL_FIELDS, $fila);
                $this->upsertByCuartelId('prod_cuartel_limitantes', $cuartelId, self::LIMITANTES_FIELDS, $fila);
                $this->upsertByCuartelId('prod_cuartel_rendimientos', $cuartelId, self::RENDIMIENTOS_FIELDS, $fila);
                $this->upsertByCuartelId('prod_cuartel_riesgos', $cuartelId, self::RIESGOS_FIELDS, $fila);
            }

            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function getRelatedRow(string $table, int $cuartelId): array
    {
        if ($cuartelId <= 0) {
            return [];
        }

        $st = $this->pdo->prepare("SELECT * FROM {$table} WHERE cuartel_id = :cid LIMIT 1");
        $st->execute([':cid' => $cuartelId]);
        return $st->fetch() ?: [];
    }

    private function cuartelPerteneceAProductor(int $cuartelId, string $productorIdReal): bool
    {
        $st = $this->pdo->prepare("
            SELECT 1
            FROM prod_cuartel pc
            LEFT JOIN prod_fincas pf
              ON pf.id = pc.finca_id
            WHERE pc.id = :cid
              AND (pc.id_responsable_real = :prod OR pf.productor_id_real = :prod)
            LIMIT 1
        ");
        $st->execute([
            ':cid' => $cuartelId,
            ':prod' => $productorIdReal,
        ]);

        return (bool)$st->fetchColumn();
    }

    private function updateRowById(string $table, int $id, array $fields, array $payload): void
    {
        $sets = [];
        $params = [':id' => $id];

        foreach ($fields as $field) {
            $sets[] = "{$field} = :{$field}";
            $params[":{$field}"] = $this->nullIfEmpty($payload[$field] ?? null);
        }

        $sql = "UPDATE {$table} SET " . implode(', ', $sets) . " WHERE id = :id";
        $st = $this->pdo->prepare($sql);
        $st->execute($params);
    }

    private function upsertByCuartelId(string $table, int $cuartelId, array $fields, array $payload): void
    {
        $st = $this->pdo->prepare("SELECT id FROM {$table} WHERE cuartel_id = :cid LIMIT 1");
        $st->execute([':cid' => $cuartelId]);
        $id = (int)($st->fetchColumn() ?: 0);

        if ($id > 0) {
            $this->updateRowById($table, $id, $fields, $payload);
            return;
        }

        $columns = array_merge(['cuartel_id'], $fields);
        $placeholders = [];
        foreach ($columns as $column) {
            $placeholders[] = ":{$column}";
        }
        $params = [':cuartel_id' => $cuartelId];

        foreach ($fields as $field) {
            $params[":{$field}"] = $this->nullIfEmpty($payload[$field] ?? null);
        }

        $sql = "INSERT INTO {$table} (" . implode(', ', $columns) . ")
                VALUES (" . implode(', ', $placeholders) . ")";
        $insert = $this->pdo->prepare($sql);
        $insert->execute($params);
    }

    private function nullIfEmpty($value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;
        if ($value === '' || $value === null) {
            return null;
        }

        return (string)$value;
    }
}
