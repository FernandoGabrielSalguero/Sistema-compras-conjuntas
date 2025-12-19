<?php
class CargaDatosCuartelesModel
{
    /** @var PDO */
    public PDO $pdo;
    private array $columnsCache = [];

    private function getPdo(): PDO
    {
        if (!($this->pdo instanceof PDO)) {
            throw new Exception('PDO no disponible en CargaDatosCuartelesModel (inyectar $pdo desde el controlador).');
        }
        return $this->pdo;
    }

    public function pingDb(): array
    {
        $pdo = $this->getPdo();
        $stmt = $pdo->query("SELECT 1 AS ok, CURRENT_TIMESTAMP AS server_time");
        $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;

        return [
            'db_ok' => (bool)($row && (int)$row['ok'] === 1),
            'server_time' => $row['server_time'] ?? null
        ];
    }

    private function getTableColumns(string $table): array
    {
        if (isset($this->columnsCache[$table])) return $this->columnsCache[$table];

        $pdo = $this->getPdo();
        $stmt = $pdo->prepare("
            SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
        ");
        $stmt->execute([$table]);
        $cols = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

        $set = [];
        foreach ($cols as $c) $set[(string)$c] = true;

        $this->columnsCache[$table] = $set;
        return $set;
    }

    private function hasColumn(string $table, string $col): bool
    {
        $cols = $this->getTableColumns($table);
        return isset($cols[$col]);
    }

    private function normalizeStr($v): ?string
    {
        if ($v === null) return null;
        $s = trim((string)$v);
        return $s === '' ? null : $s;
    }

    private function normalizeInt($v): ?int
    {
        $s = $this->normalizeStr($v);
        if ($s === null) return null;
        $s = preg_replace('/[^\d\-]/', '', $s);
        if ($s === '' || $s === '-') return null;
        return (int)$s;
    }

    private function normalizeDecimal($v): ?string
    {
        $s = $this->normalizeStr($v);
        if ($s === null) return null;
        $s = str_replace([' ', '%'], '', $s);
        $s = str_replace(',', '.', $s);
        if (!is_numeric($s)) return null;
        return (string)$s;
    }

    private function normalizeEnumSiNoNsnc($v): ?string
    {
        $s = mb_strtolower((string)($v ?? ''));
        $s = trim($s);
        if ($s === '') return null;

        if (in_array($s, ['si', 'sí', 's', '1', 'true', 'x'], true)) return 'si';
        if (in_array($s, ['no', 'n', '0', 'false'], true)) return 'no';
        if (in_array($s, ['nsnc', 'n/s', 'ns/nc', 'no_sabe', 'nose', 'no sabe'], true)) return 'nsnc';

        return null;
    }

    private function fetchOne(string $sql, array $params = []): ?array
    {
        $pdo = $this->getPdo();
        $st = $pdo->prepare($sql);
        $st->execute($params);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ? $row : null;
    }

    private function execStmt(string $sql, array $params = []): int
    {
        $pdo = $this->getPdo();
        $st = $pdo->prepare($sql);
        $st->execute($params);
        return (int)$st->rowCount();
    }

    private function insert(string $table, array $data): int
    {
        $pdo = $this->getPdo();

        $cols = [];
        $ph = [];
        $vals = [];

        foreach ($data as $k => $v) {
            if (!$this->hasColumn($table, (string)$k)) continue;
            $cols[] = "`$k`";
            $ph[] = "?";
            $vals[] = $v;
        }

        if (!$cols) throw new Exception("Insert vacío en $table (no hay columnas válidas).");

        $sql = "INSERT INTO `$table` (" . implode(',', $cols) . ") VALUES (" . implode(',', $ph) . ")";
        $st = $pdo->prepare($sql);
        $st->execute($vals);
        return (int)$pdo->lastInsertId();
    }

    private function updateById(string $table, int $id, array $data): int
    {
        $sets = [];
        $vals = [];
        $campos = 0;

        foreach ($data as $k => $v) {
            if ($v === null) continue;
            if (!$this->hasColumn($table, (string)$k)) continue;
            $sets[] = "`$k` = ?";
            $vals[] = $v;
            $campos++;
        }

        if (!$sets) return 0;

        $vals[] = $id;
        $this->execStmt("UPDATE `$table` SET " . implode(', ', $sets) . " WHERE id = ? LIMIT 1", $vals);
        return $campos;
    }

    private function upsertByKey(string $table, array $keyWhere, array $data): array
    {
        $where = [];
        $params = [];
        foreach ($keyWhere as $k => $v) {
            $where[] = "`$k` = ?";
            $params[] = $v;
        }

        $row = $this->fetchOne("SELECT id FROM `$table` WHERE " . implode(' AND ', $where) . " LIMIT 1", $params);
        if (!$row) {
            $id = $this->insert($table, array_merge($keyWhere, $data));
            return ['id' => $id, 'created' => true];
        }

        $id = (int)$row['id'];
        if ($data) $this->updateById($table, $id, $data);
        return ['id' => $id, 'created' => false];
    }

    public function schemaCheck(): array
    {
        $expected = [
            'usuarios' => ['id_real', 'usuario', 'contrasena', 'rol', 'permiso_ingreso', 'cuit', 'razon_social'],

            'prod_fincas' => ['codigo_finca', 'productor_id_real', 'nombre_finca'],

            'prod_cuartel' => [
                'id_responsable_real',
                'cooperativa_id_real',
                'codigo_finca',
                'nombre_finca',
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
                'finca_id'
            ],

            'prod_cuartel_rendimientos' => [
                'cuartel_id',
                'rend_2020_qq_ha',
                'rend_2021_qq_ha',
                'rend_2022_qq_ha',
                'ing_2023_kg',
                'rend_2023_qq_ha',
                'ing_2024_kg',
                'rend_2024_qq_ha',
                'ing_2025_kg',
                'rend_2025_qq_ha',
                'rend_promedio_5anios_qq_ha'
            ],

            'prod_cuartel_riesgos' => [
                'cuartel_id',
                'tiene_seguro_agricola',
                'porcentaje_dano_granizo',
                'heladas_dano_promedio_5anios',
                'presencia_freatica',
                'plagas_no_convencionales'
            ],

            'prod_cuartel_limitantes' => [
                'cuartel_id',
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
                'ciclo_agricola2_2'
            ],
        ];

        $missing = [];

        foreach ($expected as $table => $cols) {
            $tableCols = $this->getTableColumns($table);
            if (!$tableCols) {
                $missing[] = ['table' => $table, 'missing' => $cols];
                continue;
            }
            $missCols = [];
            foreach ($cols as $c) {
                if (!isset($tableCols[$c])) $missCols[] = $c;
            }
            if ($missCols) $missing[] = ['table' => $table, 'missing' => $missCols];
        }

        return ['missing' => $missing];
    }

    public function ingestBatch(array $rows, string $mode): array
    {
        $pdo = $this->getPdo();

        $stats = [
            'cooperativas_creadas' => 0,
            'fincas_creadas' => 0,
            'fincas_actualizadas' => 0,
            'cuarteles_creados' => 0,
            'cuarteles_actualizados' => 0,
            'rendimientos_actualizados' => 0,
            'riesgos_actualizados' => 0,
            'limitantes_actualizados' => 0,
            'campos_escritos' => 0,
            'errores' => []
        ];

        $cacheCoopExists = [];
        $cacheFincaByCodigo = [];     // codigo_finca => ['id'=>int, 'productor_id_real'=>string|null]
        $cacheCuartelIdByKey = [];    // key => cuartel_id

        $updatedCuartelIds = [];
        $updatedRendByCuartel = [];
        $updatedRiesByCuartel = [];
        $updatedLimByCuartel = [];

        $pdo->beginTransaction();

        try {
            foreach ($rows as $i => $r) {
                if (!is_array($r)) continue;

                try {
                    $codigoFinca = $this->normalizeStr($r['codigo_finca'] ?? null);
                    $nombreFinca = $this->normalizeStr($r['nombre_finca'] ?? null);

                    $codigoCuartel = $this->normalizeStr($r['codigo_cuartel'] ?? null);

                    // cooperativa_id_real es opcional para ACTUALIZAR (si viene, se usa).
                    // Para CREAR un cuartel nuevo, se requerirá más abajo.
                    $coopReal = $this->normalizeStr($r['cooperativa_id_real'] ?? ($r['cooperativa'] ?? null));

                    // Opcional (solo se escribe si viene)
                    $idResponsableReal = $this->normalizeStr(
                        $r['id_responsable_real'] ?? $r['productor_id_real'] ?? $r['id_pp'] ?? $r['id_real'] ?? null
                    );

                    if (!$codigoFinca) {
                        $stats['errores'][] = ['fila' => $i + 1, 'error' => 'Falta codigo_finca.'];
                        continue;
                    }
                    if (!$codigoCuartel) {
                        $stats['errores'][] = ['fila' => $i + 1, 'codigo_finca' => $codigoFinca, 'error' => 'Falta codigo_cuartel.'];
                        continue;
                    }

                    if (strlen($codigoFinca) > 20) throw new Exception('codigo_finca excede 20 caracteres: ' . $codigoFinca);
                    if (strlen($codigoCuartel) > 20) throw new Exception('codigo_cuartel excede 20 caracteres: ' . $codigoCuartel);
                    if ($coopReal !== null && strlen($coopReal) > 20) throw new Exception('cooperativa_id_real excede 20 caracteres: ' . $coopReal);
                    if ($idResponsableReal !== null && strlen($idResponsableReal) > 20) throw new Exception('id_responsable_real excede 20 caracteres: ' . $idResponsableReal);

                    // ===== 1) Asegurar cooperativa (usuarios) SOLO si viene cooperativa_id_real =====
                    if ($coopReal !== null) {
                        if (!isset($cacheCoopExists[$coopReal])) {
                            $u = $this->fetchOne("SELECT id FROM usuarios WHERE id_real = ? LIMIT 1", [$coopReal]);
                            if (!$u) {
                                $tmpPassHash = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
                                $this->insert('usuarios', [
                                    'usuario' => $coopReal,
                                    'contrasena' => $tmpPassHash,
                                    'rol' => 'cooperativa',
                                    'permiso_ingreso' => 'Deshabilitado',
                                    'cuit' => 0,
                                    'razon_social' => $coopReal,
                                    'id_real' => $coopReal,
                                ]);
                                $stats['cooperativas_creadas']++;
                            }
                            $cacheCoopExists[$coopReal] = true;
                        }
                    }

                    // ===== 2) Buscar finca EXISTENTE por codigo_finca (prod_fincas) =====
                    // Regla: para esta actualización, si la finca NO existe, no se crea (se reporta error).
                    $fincaId = null;

                    if (isset($cacheFincaByCodigo[$codigoFinca])) {
                        $fincaId = (int)$cacheFincaByCodigo[$codigoFinca]['id'];
                    } else {
                        $f = $this->fetchOne("SELECT id FROM prod_fincas WHERE codigo_finca = ? LIMIT 1", [$codigoFinca]);
                        if (!$f) {
                            $stats['errores'][] = [
                                'fila' => $i + 1,
                                'codigo_finca' => $codigoFinca,
                                'codigo_cuartel' => $codigoCuartel,
                                'error' => 'La finca no existe en prod_fincas para codigo_finca=' . $codigoFinca
                            ];
                            continue;
                        }
                        $fincaId = (int)$f['id'];
                        $cacheFincaByCodigo[$codigoFinca] = ['id' => $fincaId, 'productor_id_real' => null];
                    }

                    // ===== 3) Upsert cuartel (prod_cuartel) =====
                    // Regla: la actualización se basa en codigo_finca (ya validado contra prod_fincas)
                    // y codigo_cuartel. Primero intentamos por finca_id (más robusto), y si no, fallback por codigo_finca.
                    $key = $codigoFinca . '|' . $codigoCuartel;
                    $cuartelId = $cacheCuartelIdByKey[$key] ?? null;

                    if (!$cuartelId) {
                        $rowCu = $this->fetchOne(
                            "SELECT id FROM prod_cuartel WHERE finca_id = ? AND codigo_cuartel = ? LIMIT 1",
                            [(int)$fincaId, $codigoCuartel]
                        );

                        if (!$rowCu) {
                            $rowCu = $this->fetchOne(
                                "SELECT id FROM prod_cuartel WHERE codigo_finca = ? AND codigo_cuartel = ? LIMIT 1",
                                [$codigoFinca, $codigoCuartel]
                            );
                        }

                        if ($rowCu) {
                            $cuartelId = (int)$rowCu['id'];
                        } else {
                            // Para CREAR un cuartel nuevo exigimos cooperativa_id_real (si no, no podemos completar el registro de forma segura)
                            if ($coopReal === null) {
                                $stats['errores'][] = [
                                    'fila' => $i + 1,
                                    'codigo_finca' => $codigoFinca,
                                    'codigo_cuartel' => $codigoCuartel,
                                    'error' => 'No existe el cuartel y no viene cooperativa_id_real para poder crearlo.'
                                ];
                                continue;
                            }

                            $cuartelId = $this->insert('prod_cuartel', [
                                'id_responsable_real' => $idResponsableReal,
                                'cooperativa_id_real' => $coopReal,
                                'codigo_finca' => $codigoFinca,
                                'nombre_finca' => $nombreFinca,
                                'codigo_cuartel' => $codigoCuartel,

                                'variedad' => $this->normalizeStr($r['variedad'] ?? null),
                                'numero_inv' => $this->normalizeStr($r['numero_inv'] ?? null),
                                'sistema_conduccion' => $this->normalizeStr($r['sistema_conduccion'] ?? null),
                                'superficie_ha' => $this->normalizeDecimal($r['superficie_ha'] ?? null),
                                'porcentaje_cepas_produccion' => $this->normalizeDecimal($r['porcentaje_cepas_produccion'] ?? null),
                                'forma_cosecha_actual' => $this->normalizeStr($r['forma_cosecha_actual'] ?? null),
                                'porcentaje_malla_buen_estado' => $this->normalizeDecimal($r['porcentaje_malla_buen_estado'] ?? null),
                                'edad_promedio_encepado_anios' => $this->normalizeInt($r['edad_promedio_encepado_anios'] ?? null),
                                'estado_estructura_sistema' => $this->normalizeStr($r['estado_estructura_sistema'] ?? null),
                                'labores_mecanizables' => $this->normalizeStr($r['labores_mecanizables'] ?? null),

                                'finca_id' => (int)$fincaId,
                            ]);
                            $stats['cuarteles_creados']++;
                        }

                        $cacheCuartelIdByKey[$key] = $cuartelId;
                    }

                    // updates en prod_cuartel (si vienen valores)
                    $cuUpdate = [
                        'id_responsable_real' => $idResponsableReal,
                        'nombre_finca' => $nombreFinca,

                        'variedad' => $this->normalizeStr($r['variedad'] ?? null),
                        'numero_inv' => $this->normalizeStr($r['numero_inv'] ?? null),
                        'sistema_conduccion' => $this->normalizeStr($r['sistema_conduccion'] ?? null),
                        'superficie_ha' => $this->normalizeDecimal($r['superficie_ha'] ?? null),
                        'porcentaje_cepas_produccion' => $this->normalizeDecimal($r['porcentaje_cepas_produccion'] ?? null),
                        'forma_cosecha_actual' => $this->normalizeStr($r['forma_cosecha_actual'] ?? null),
                        'porcentaje_malla_buen_estado' => $this->normalizeDecimal($r['porcentaje_malla_buen_estado'] ?? null),
                        'edad_promedio_encepado_anios' => $this->normalizeInt($r['edad_promedio_encepado_anios'] ?? null),
                        'estado_estructura_sistema' => $this->normalizeStr($r['estado_estructura_sistema'] ?? null),
                        'labores_mecanizables' => $this->normalizeStr($r['labores_mecanizables'] ?? null),

                        'finca_id' => (int)$fincaId,
                    ];

                    // Si viene cooperativa_id_real, la actualizamos; si no viene, no tocamos el valor existente.
                    if ($coopReal !== null) {
                        $cuUpdate['cooperativa_id_real'] = $coopReal;
                    }

                    $camposCu = $this->updateById('prod_cuartel', (int)$cuartelId, $cuUpdate);


                    if ($camposCu > 0) {
                        $stats['campos_escritos'] += $camposCu;
                        if (!isset($updatedCuartelIds[$cuartelId])) {
                            $updatedCuartelIds[$cuartelId] = true;
                            $stats['cuarteles_actualizados']++;
                        }
                    }

                    // ===== 4) Rendimientos (1-1 por cuartel_id) =====
                    $rend = $this->upsertByKey('prod_cuartel_rendimientos', ['cuartel_id' => (int)$cuartelId], []);
                    $camposR = $this->updateById('prod_cuartel_rendimientos', (int)$rend['id'], [
                        'rend_2020_qq_ha' => $this->normalizeDecimal($r['rend_2020_qq_ha'] ?? null),
                        'rend_2021_qq_ha' => $this->normalizeDecimal($r['rend_2021_qq_ha'] ?? null),
                        'rend_2022_qq_ha' => $this->normalizeDecimal($r['rend_2022_qq_ha'] ?? null),
                        'ing_2023_kg' => $this->normalizeDecimal($r['ing_2023_kg'] ?? null),
                        'rend_2023_qq_ha' => $this->normalizeDecimal($r['rend_2023_qq_ha'] ?? null),
                        'ing_2024_kg' => $this->normalizeDecimal($r['ing_2024_kg'] ?? null),
                        'rend_2024_qq_ha' => $this->normalizeDecimal($r['rend_2024_qq_ha'] ?? null),
                        'ing_2025_kg' => $this->normalizeDecimal($r['ing_2025_kg'] ?? null),
                        'rend_2025_qq_ha' => $this->normalizeDecimal($r['rend_2025_qq_ha'] ?? null),
                        'rend_promedio_5anios_qq_ha' => $this->normalizeDecimal($r['rend_promedio_5anios_qq_ha'] ?? null),
                    ]);

                    if ($camposR > 0) {
                        $stats['campos_escritos'] += $camposR;
                        if (!isset($updatedRendByCuartel[$cuartelId])) {
                            $updatedRendByCuartel[$cuartelId] = true;
                            $stats['rendimientos_actualizados']++;
                        }
                    }

                    // ===== 5) Riesgos (1-1 por cuartel_id) =====
                    $ries = $this->upsertByKey('prod_cuartel_riesgos', ['cuartel_id' => (int)$cuartelId], []);
                    $camposRi = $this->updateById('prod_cuartel_riesgos', (int)$ries['id'], [
                        'tiene_seguro_agricola' => $this->normalizeEnumSiNoNsnc($r['tiene_seguro_agricola'] ?? null),
                        'porcentaje_dano_granizo' => $this->normalizeDecimal($r['porcentaje_dano_granizo'] ?? null),
                        'heladas_dano_promedio_5anios' => $this->normalizeDecimal($r['heladas_dano_promedio_5anios'] ?? null),
                        'presencia_freatica' => $this->normalizeStr($r['presencia_freatica'] ?? null),
                        'plagas_no_convencionales' => $this->normalizeStr($r['plagas_no_convencionales'] ?? null),
                    ]);

                    if ($camposRi > 0) {
                        $stats['campos_escritos'] += $camposRi;
                        if (!isset($updatedRiesByCuartel[$cuartelId])) {
                            $updatedRiesByCuartel[$cuartelId] = true;
                            $stats['riesgos_actualizados']++;
                        }
                    }

                    // ===== 6) Limitantes (1-1 por cuartel_id) =====
                    $lim = $this->upsertByKey('prod_cuartel_limitantes', ['cuartel_id' => (int)$cuartelId], []);
                    $camposL = $this->updateById('prod_cuartel_limitantes', (int)$lim['id'], [
                        'limitantes_suelo' => $this->normalizeStr($r['limitantes_suelo'] ?? null),
                        'observaciones' => $this->normalizeStr($r['observaciones'] ?? null),

                        'categoria_1' => $this->normalizeStr($r['categoria_1'] ?? null),
                        'limitante_1' => $this->normalizeStr($r['limitante_1'] ?? null),
                        'inversion_accion1_1' => $this->normalizeStr($r['inversion_accion1_1'] ?? null),
                        'obs_inversion_accion1_1' => $this->normalizeStr($r['obs_inversion_accion1_1'] ?? null),
                        'ciclo_agricola1_1' => $this->normalizeStr($r['ciclo_agricola1_1'] ?? null),
                        'inversion_accion2_1' => $this->normalizeStr($r['inversion_accion2_1'] ?? null),
                        'obs_inversion_accion2_1' => $this->normalizeStr($r['obs_inversion_accion2_1'] ?? null),
                        'ciclo_agricola2_1' => $this->normalizeStr($r['ciclo_agricola2_1'] ?? null),

                        'categoria_2' => $this->normalizeStr($r['categoria_2'] ?? null),
                        'limitante_2' => $this->normalizeStr($r['limitante_2'] ?? null),
                        'inversion_accion1_2' => $this->normalizeStr($r['inversion_accion1_2'] ?? null),
                        'obs_inversion_accion1_2' => $this->normalizeStr($r['obs_inversion_accion1_2'] ?? null),
                        'ciclo_agricola1_2' => $this->normalizeStr($r['ciclo_agricola1_2'] ?? null),
                        'inversion_accion2_2' => $this->normalizeStr($r['inversion_accion2_2'] ?? null),
                        'obs_inversion_accion2_2' => $this->normalizeStr($r['obs_inversion_accion2_2'] ?? null),
                        'ciclo_agricola2_2' => $this->normalizeStr($r['ciclo_agricola2_2'] ?? null),
                    ]);

                    if ($camposL > 0) {
                        $stats['campos_escritos'] += $camposL;
                        if (!isset($updatedLimByCuartel[$cuartelId])) {
                            $updatedLimByCuartel[$cuartelId] = true;
                            $stats['limitantes_actualizados']++;
                        }
                    }
                } catch (Throwable $rowE) {
                    $stats['errores'][] = [
                        'fila' => $i + 1,
                        'codigo_finca' => $this->normalizeStr($r['codigo_finca'] ?? null),
                        'codigo_cuartel' => $this->normalizeStr($r['codigo_cuartel'] ?? null),
                        'error' => $rowE->getMessage()
                    ];
                    continue;
                }
            }

            if ($mode === 'simulate') {
                $pdo->rollBack();
            } else {
                $pdo->commit();
            }

            if (count($stats['errores']) > 200) {
                $stats['errores'] = array_slice($stats['errores'], 0, 200);
            }

            return $stats;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }
}
