<?php
class CargaDatosFamiliaModel
{
    /** @var PDO */
    public PDO $pdo;
    private array $columnsCache = [];

    private function getPdo(): PDO
    {
        if (!($this->pdo instanceof PDO)) {
            throw new Exception(
                'PDO no disponible en CargaDatosFamiliaModel (inyectar $pdo desde el controlador).'
            );
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
        $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);

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

        // Si viene algo raro, no lo escribimos.
        return null;
    }

    private function normalizeDateYmd($v): ?string
    {
        $s = $this->normalizeStr($v);
        if ($s === null) return null;

        // YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) return $s;

        // DD/MM/YYYY o DD-MM-YYYY
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $s, $m)) {
            $d = (int)$m[1];
            $mo = (int)$m[2];
            $y = (int)$m[3];
            if ($y < 1900 || $y > 2100) return null;
            if ($mo < 1 || $mo > 12) return null;
            if ($d < 1 || $d > 31) return null;
            return sprintf('%04d-%02d-%02d', $y, $mo, $d);
        }

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

        if (!$cols) {
            throw new Exception("Insert vacío en $table (no hay columnas válidas).");
        }

        $sql = "INSERT INTO `$table` (" . implode(',', $cols) . ") VALUES (" . implode(',', $ph) . ")";
        $st = $pdo->prepare($sql);
        $st->execute($vals);
        return (int)$pdo->lastInsertId();
    }

    private function upsertByKey(string $table, array $keyWhere, array $data): array
    {
        // Devuelve: ['id' => int, 'created' => bool]
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
        $this->updateById($table, $id, $data);
        return ['id' => $id, 'created' => false];
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
        return $campos; // “campos escritos” (aprox: campos con valor no nulo enviados)
    }

    public function schemaCheck(): array
    {
        // Solo reporta faltantes. El SQL se entrega por separado (fuera del código).
        $expected = [
            'usuarios' => ['usuario', 'contrasena', 'rol', 'permiso_ingreso', 'cuit', 'razon_social', 'id_real'],
            'usuarios_info' => ['usuario_id', 'nombre', 'telefono', 'correo', 'fecha_nacimiento', 'categorizacion', 'tipo_relacion', 'zona_asignada'],
            'productores_contactos_alternos' => ['productor_id', 'contacto_preferido', 'celular_alternativo', 'telefono_fijo', 'mail_alternativo'],
            'info_productor' => ['productor_id', 'anio', 'acceso_internet', 'vive_en_finca', 'tiene_otra_finca', 'condicion_cooperativa', 'anio_asociacion', 'actividad_principal', 'actividad_secundaria', 'porcentaje_aporte_vitivinicola'],
            'prod_colaboradores' => ['productor_id', 'anio', 'hijos_sobrinos_participan', 'mujeres_tc', 'hombres_tc', 'mujeres_tp', 'hombres_tp', 'prob_hijos_trabajen'],
            'prod_hijos' => ['productor_id', 'anio', 'motivo_no_trabajar', 'nom_hijo_1', 'fecha_nacimiento_1', 'sexo1', 'nivel_estudio1', 'nom_hijo_2', 'fecha_nacimiento_2', 'sexo2', 'nivel_estudio2', 'nom_hijo_3', 'fecha_nacimiento_3', 'sexo3', 'nivel_estudio3'],
            'rel_productor_coop' => ['productor_id_real', 'cooperativa_id_real'],
            'prod_fincas' => ['codigo_finca', 'productor_id_real'],
            'rel_productor_finca' => ['productor_id', 'productor_id_real', 'finca_id'],
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
            if ($missCols) {
                $missing[] = ['table' => $table, 'missing' => $missCols];
            }
        }

        return ['missing' => $missing];
    }

    public function ingestBatch(array $rows, string $mode, int $anio): array
    {
        $pdo = $this->getPdo();

        $stats = [
            'productores_creados' => 0,
            'productores_actualizados' => 0,
            'cooperativas_creadas' => 0,
            'fincas_creadas' => 0,
            'rel_prod_coop_creadas' => 0,
            'rel_prod_finca_creadas' => 0,
            'campos_escritos' => 0,
            'errores' => []
        ];

        $cacheUserIdByReal = [];
        $cacheFincaIdByCodigo = [];
        $cacheCoopRealExists = [];

        // Para no contar 2+ veces el mismo productor como "actualizado" dentro del batch
        $updatedUserIds = [];

        $pdo->beginTransaction();

        try {
            foreach ($rows as $i => $r) {
                if (!is_array($r)) continue;

                try {
                    // Headers normalizados desde PapaParse: id_pp, cooperativa, codigo_finca, etc.
                    $idReal = $this->normalizeStr($r['id_pp'] ?? $r['id_real'] ?? null);
                    if (!$idReal) {
                        $stats['errores'][] = ['fila' => $i + 1, 'error' => 'Falta ID PP (id_pp).'];
                        continue;
                    }

                    $coopReal = $this->normalizeStr($r['cooperativa'] ?? null);
                    $codigoFinca = $this->normalizeStr($r['codigo_finca'] ?? null);

                    // Validaciones según tu esquema (varchar(20))
                    if (strlen($idReal) > 20) {
                        throw new Exception('ID PP (id_real) excede 20 caracteres: ' . $idReal);
                    }
                    if ($coopReal !== null && strlen($coopReal) > 20) {
                        throw new Exception('Cooperativa (id_real) excede 20 caracteres: ' . $coopReal);
                    }
                    if ($codigoFinca !== null && strlen($codigoFinca) > 20) {
                        throw new Exception('codigo_finca excede 20 caracteres: ' . $codigoFinca);
                    }


                    // ===== 1) PRODUCTOR (usuarios) =====
                    $userId = null;
                    $createdProducer = false;

                    if (isset($cacheUserIdByReal[$idReal])) {
                        $userId = (int)$cacheUserIdByReal[$idReal];
                    } else {
                        $u = $this->fetchOne("SELECT id FROM usuarios WHERE id_real = ? LIMIT 1", [$idReal]);
                        if ($u) {
                            $userId = (int)$u['id'];
                        } else {
                            $cuit = $this->normalizeInt($r['cuit'] ?? null);
                            if ($cuit === null) {
                                $stats['errores'][] = ['fila' => $i + 1, 'id_pp' => $idReal, 'error' => 'No existe el productor y falta CUIT para crearlo.'];
                                continue;
                            }

                            $razon = $this->normalizeStr($r['razon_social'] ?? null);

                            $tmpPassPlain = bin2hex(random_bytes(8));
                            $tmpPassHash = password_hash($tmpPassPlain, PASSWORD_DEFAULT);

                            $newId = $this->insert('usuarios', [
                                'usuario' => $idReal,
                                'contrasena' => $tmpPassHash,
                                'rol' => 'productor',
                                'permiso_ingreso' => 'Habilitado',
                                'cuit' => $cuit,
                                'razon_social' => $razon,
                                'id_real' => $idReal,
                            ]);

                            $userId = $newId;
                            $createdProducer = true;
                            $stats['productores_creados']++;
                        }

                        $cacheUserIdByReal[$idReal] = $userId;
                    }

                    // updates en usuarios (solo si vienen valores)
                    $camposUsuarios = 0;
                    $camposUsuarios += $this->updateById('usuarios', $userId, [
                        'razon_social' => $this->normalizeStr($r['razon_social'] ?? null),
                        'cuit' => $this->normalizeInt($r['cuit'] ?? null),
                    ]);

                    // ===== 2) usuarios_info (perfil) =====
                    $ui = $this->fetchOne("SELECT id FROM usuarios_info WHERE usuario_id = ? LIMIT 1", [$userId]);
                    if (!$ui) {
                        $this->insert('usuarios_info', [
                            'usuario_id' => $userId,
                            'zona_asignada' => 'Sin asignar',
                        ]);
                        $ui = $this->fetchOne("SELECT id FROM usuarios_info WHERE usuario_id = ? LIMIT 1", [$userId]);
                    }

                    $categ = $this->normalizeStr($r['categorizacion'] ?? null);
                    if ($categ !== null && !in_array($categ, ['A', 'B', 'C'], true)) $categ = null;

                    $camposUsuariosInfo = $this->updateById('usuarios_info', (int)$ui['id'], [
                        'nombre' => $this->normalizeStr($r['nombre'] ?? null),
                        'fecha_nacimiento' => $this->normalizeDateYmd($r['fecha_nacimiento'] ?? null),
                        'tipo_relacion' => $this->normalizeStr($r['tipo_relacion'] ?? null),
                        'telefono' => $this->normalizeStr($r['telefono'] ?? null),
                        'correo' => $this->normalizeStr($r['correo'] ?? null),
                        'categorizacion' => $categ,
                    ]);

                    // ===== 3) productores_contactos_alternos =====
                    $pca = $this->fetchOne("SELECT id FROM productores_contactos_alternos WHERE productor_id = ? ORDER BY id DESC LIMIT 1", [$userId]);
                    if (!$pca) {
                        $this->insert('productores_contactos_alternos', [
                            'productor_id' => $userId
                        ]);
                        $pca = $this->fetchOne("SELECT id FROM productores_contactos_alternos WHERE productor_id = ? ORDER BY id DESC LIMIT 1", [$userId]);
                    }

                    $camposPca = $this->updateById('productores_contactos_alternos', (int)$pca['id'], [
                        'contacto_preferido' => $this->normalizeStr($r['contacto_preferido'] ?? null),
                        'celular_alternativo' => $this->normalizeStr($r['celular_alternativo'] ?? null),
                        'telefono_fijo' => $this->normalizeStr($r['telefono_fijo'] ?? null),
                        'mail_alternativo' => $this->normalizeStr($r['mail_alternativo'] ?? null),
                    ]);

                    // ===== 4) info_productor (por año) =====
                    $ip = $this->upsertByKey('info_productor', ['productor_id' => $userId, 'anio' => $anio], [
                        // En insert también puede entrar con NULLs
                    ]);
                    $camposIp = $this->updateById('info_productor', (int)$ip['id'], [
                        'acceso_internet' => $this->normalizeEnumSiNoNsnc($r['acceso_internet'] ?? null),
                        'vive_en_finca' => $this->normalizeEnumSiNoNsnc($r['vive_en_finca'] ?? null),
                        'tiene_otra_finca' => $this->normalizeEnumSiNoNsnc($r['tiene_otra_finca'] ?? null),
                        'condicion_cooperativa' => $this->normalizeStr($r['condicion_cooperativa'] ?? null),
                        'anio_asociacion' => $this->normalizeInt($r['anio_asociacion'] ?? null),
                        'actividad_principal' => $this->normalizeStr($r['actividad_principal'] ?? null),
                        'actividad_secundaria' => $this->normalizeStr($r['actividad_secundaria'] ?? null),
                        'porcentaje_aporte_vitivinicola' => $this->normalizeDecimal($r['porcentaje_aporte_vitivinicola'] ?? null),
                    ]);

                    // ===== 5) prod_colaboradores (por año) =====
                    $pc = $this->upsertByKey('prod_colaboradores', ['productor_id' => $userId, 'anio' => $anio], []);
                    $camposPc = $this->updateById('prod_colaboradores', (int)$pc['id'], [
                        'hijos_sobrinos_participan' => $this->normalizeEnumSiNoNsnc($r['hijos_sobrinos_participan'] ?? null),
                        'mujeres_tc' => $this->normalizeInt($r['mujeres_tc'] ?? null),
                        'hombres_tc' => $this->normalizeInt($r['hombres_tc'] ?? null),
                        'mujeres_tp' => $this->normalizeInt($r['mujeres_tp'] ?? null),
                        'hombres_tp' => $this->normalizeInt($r['hombres_tp'] ?? null),
                        'prob_hijos_trabajen' => $this->normalizeStr($r['prob_hijos_trabajen'] ?? null),
                    ]);

                    // ===== 6) prod_hijos (por año) =====
                    $ph = $this->upsertByKey('prod_hijos', ['productor_id' => $userId, 'anio' => $anio], []);
                    $camposPh = $this->updateById('prod_hijos', (int)$ph['id'], [
                        'motivo_no_trabajar' => $this->normalizeStr($r['motivo_no_trabajar'] ?? null),

                        'nom_hijo_1' => $this->normalizeStr($r['nom_hijo_1'] ?? null),
                        'fecha_nacimiento_1' => $this->normalizeDateYmd($r['fecha_nacimiento_1'] ?? null),
                        'sexo1' => $this->normalizeStr($r['sexo1'] ?? null),
                        'nivel_estudio1' => $this->normalizeStr($r['nivel_estudio1'] ?? null),

                        'nom_hijo_2' => $this->normalizeStr($r['nom_hijo_2'] ?? null),
                        'fecha_nacimiento_2' => $this->normalizeDateYmd($r['fecha_nacimiento_2'] ?? null),
                        'sexo2' => $this->normalizeStr($r['sexo2'] ?? null),
                        'nivel_estudio2' => $this->normalizeStr($r['nivel_estudio2'] ?? null),

                        'nom_hijo_3' => $this->normalizeStr($r['nom_hijo_3'] ?? null),
                        'fecha_nacimiento_3' => $this->normalizeDateYmd($r['fecha_nacimiento_3'] ?? null),
                        'sexo3' => $this->normalizeStr($r['sexo3'] ?? null),
                        'nivel_estudio3' => $this->normalizeStr($r['nivel_estudio3'] ?? null),
                    ]);

                    // ===== 7) Cooperativa + relación =====
                    if ($coopReal) {
                        if (!isset($cacheCoopRealExists[$coopReal])) {
                            $coop = $this->fetchOne("SELECT id FROM usuarios WHERE id_real = ? LIMIT 1", [$coopReal]);
                            if (!$coop) {
                                // No tenemos CUIT de cooperativa en CSV -> creamos placeholder (cuit=0, ingreso deshabilitado)
                                $tmpPassPlain = bin2hex(random_bytes(8));
                                $tmpPassHash = password_hash($tmpPassPlain, PASSWORD_DEFAULT);

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
                            $cacheCoopRealExists[$coopReal] = true;
                        }

                        $rel = $this->fetchOne(
                            "SELECT id FROM rel_productor_coop WHERE productor_id_real = ? AND cooperativa_id_real = ? LIMIT 1",
                            [$idReal, $coopReal]
                        );
                        if (!$rel) {
                            $this->insert('rel_productor_coop', [
                                'productor_id_real' => $idReal,
                                'cooperativa_id_real' => $coopReal
                            ]);
                            $stats['rel_prod_coop_creadas']++;
                        }
                    }

                    // ===== 8) Finca + relación =====
                    // codigo_finca puede venir como "123-456-789" (múltiples fincas).
                    if ($codigoFinca) {
                        // Split por "-" tolerando espacios alrededor.
                        $parts = preg_split('/\s*-\s*/', (string)$codigoFinca) ?: [];
                        $codes = [];

                        foreach ($parts as $p) {
                            $c = $this->normalizeStr($p);
                            if ($c === null) continue;

                            // Validación según tu esquema (varchar(20))
                            if (strlen($c) > 20) {
                                throw new Exception('codigo_finca excede 20 caracteres: ' . $c);
                            }

                            $codes[] = $c;
                        }

                        // Unificar / evitar duplicados dentro de la misma fila
                        $codes = array_values(array_unique($codes));

                        foreach ($codes as $cf) {
                            $fincaId = null;

                            if (isset($cacheFincaIdByCodigo[$cf])) {
                                $fincaId = (int)$cacheFincaIdByCodigo[$cf];
                            } else {
                                $f = $this->fetchOne("SELECT id FROM prod_fincas WHERE codigo_finca = ? LIMIT 1", [$cf]);
                                if ($f) {
                                    $fincaId = (int)$f['id'];
                                } else {
                                    $fincaId = $this->insert('prod_fincas', [
                                        'codigo_finca' => $cf,
                                        'productor_id_real' => $idReal,
                                    ]);
                                    $stats['fincas_creadas']++;
                                }
                                $cacheFincaIdByCodigo[$cf] = $fincaId;
                            }

                            $rf = $this->fetchOne(
                                "SELECT id FROM rel_productor_finca WHERE productor_id = ? AND finca_id = ? LIMIT 1",
                                [$userId, $fincaId]
                            );
                            if (!$rf) {
                                $this->insert('rel_productor_finca', [
                                    'productor_id' => $userId,
                                    'productor_id_real' => $idReal,
                                    'finca_id' => $fincaId,
                                ]);
                                $stats['rel_prod_finca_creadas']++;
                            }
                        }
                    }


                    $camposTotal = $camposUsuarios + $camposUsuariosInfo + $camposPca + $camposIp + $camposPc + $camposPh;
                    $stats['campos_escritos'] += $camposTotal;

                    if ($camposTotal > 0 && !$createdProducer) {
                        if (!isset($updatedUserIds[$userId])) {
                            $updatedUserIds[$userId] = true;
                            $stats['productores_actualizados']++;
                        }
                    }
                } catch (Throwable $rowE) {
                    $stats['errores'][] = [
                        'fila' => $i + 1,
                        'id_pp' => $this->normalizeStr($r['id_pp'] ?? $r['id_real'] ?? null),
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

            // limitar tamaño de respuesta
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
