<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

final class CargaMasivaModel
{
    private PDO $pdo;

    private const REQUIRED_COLUMNS = [
        'rol',
        'permiso_ingreso',
        'cuit',
        'razon_social',
        'id_real',
        'nombre',
        'direccion',
        'telefono',
        'correo',
        'fecha_nacimiento',
        'categorizacion',
        'tipo_relacion',
        'zona_asignada',
        'codigo_finca',
        'nombre_finca',
        'variedad',
        'departamento',
        'localidad',
        'calle',
        'numero',
        'latitud',
        'longitud',
        'codigo_cuartel',
        'sistema_conduccion',
        'superficie_ha',
        'porcentaje_cepas_produccion',
        'forma_cosecha_actual',
        'porcentaje_malla_buen_estado',
        'edad_promedio_encepado_anios',
        'estado_estructura_sistema',
        'labores_mecanizables',
        'numero_inv',
    ];

    public function __construct()
    {
        global $pdo;
        if (!$pdo instanceof PDO) {
            throw new RuntimeException('Conexion PDO no disponible.');
        }
        $this->pdo = $pdo;
    }

    public function previewFromRows(array $rawRows, string $cooperativaIdReal): array
    {
        return $this->buildPlan($rawRows, $cooperativaIdReal);
    }

    public function applyFromRows(array $rawRows, string $cooperativaIdReal): array
    {
        $plan = $this->buildPlan($rawRows, $cooperativaIdReal);

        $rows = $plan['normalized_rows'];
        $processable = $plan['processable_rows'];
        $notInCsvIds = $plan['not_in_csv_user_ids'];

        $this->pdo->beginTransaction();

        try {
            $qUpdateUsuario = $this->pdo->prepare(
                'UPDATE usuarios
                 SET rol = :rol,
                     permiso_ingreso = :permiso_ingreso,
                     razon_social = :razon_social,
                     revisado = "Esta revisado"
                 WHERE id = :id'
            );
            $qInsertUsuario = $this->pdo->prepare(
                'INSERT INTO usuarios
                 (usuario, contrasena, rol, permiso_ingreso, cuit, razon_social, id_real, revisado)
                 VALUES
                 (:usuario, :contrasena, :rol, :permiso_ingreso, :cuit, :razon_social, :id_real, "Esta revisado")'
            );

            $qSelectUserInfo = $this->pdo->prepare('SELECT id FROM usuarios_info WHERE usuario_id = :usuario_id LIMIT 1');
            $qInsertUserInfo = $this->pdo->prepare(
                'INSERT INTO usuarios_info
                 (usuario_id, nombre, direccion, telefono, correo, fecha_nacimiento, categorizacion, tipo_relacion, zona_asignada)
                 VALUES
                 (:usuario_id, :nombre, :direccion, :telefono, :correo, :fecha_nacimiento, :categorizacion, :tipo_relacion, :zona_asignada)'
            );
            $qUpdateUserInfo = $this->pdo->prepare(
                'UPDATE usuarios_info
                 SET nombre = :nombre,
                     direccion = :direccion,
                     telefono = :telefono,
                     correo = :correo,
                     fecha_nacimiento = :fecha_nacimiento,
                     categorizacion = :categorizacion,
                     tipo_relacion = :tipo_relacion,
                     zona_asignada = :zona_asignada
                 WHERE usuario_id = :usuario_id'
            );

            $qDeleteOtherCoops = $this->pdo->prepare(
                'DELETE FROM rel_productor_coop
                 WHERE productor_id_real = :productor_id_real
                   AND cooperativa_id_real <> :cooperativa_id_real'
            );
            $qInsertRelCoop = $this->pdo->prepare(
                'INSERT INTO rel_productor_coop (productor_id_real, cooperativa_id_real)
                 SELECT :productor_id_real, :cooperativa_id_real
                 WHERE NOT EXISTS (
                    SELECT 1 FROM rel_productor_coop
                    WHERE productor_id_real = :productor_id_real_chk
                      AND cooperativa_id_real = :cooperativa_id_real_chk
                 )'
            );

            $qSelectFinca = $this->pdo->prepare(
                'SELECT id FROM prod_fincas WHERE productor_id_real = :productor_id_real AND codigo_finca = :codigo_finca LIMIT 1'
            );
            $qInsertFinca = $this->pdo->prepare(
                'INSERT INTO prod_fincas (codigo_finca, productor_id_real, nombre_finca, variedad)
                 VALUES (:codigo_finca, :productor_id_real, :nombre_finca, :variedad)'
            );
            $qUpdateFinca = $this->pdo->prepare(
                'UPDATE prod_fincas
                 SET nombre_finca = :nombre_finca,
                     variedad = :variedad
                 WHERE id = :id'
            );

            $qInsertDir = $this->pdo->prepare(
                'INSERT INTO prod_finca_direccion (finca_id, departamento, localidad, calle, numero, latitud, longitud)
                 VALUES (:finca_id, :departamento, :localidad, :calle, :numero, :latitud, :longitud)
                 ON DUPLICATE KEY UPDATE
                    departamento = VALUES(departamento),
                    localidad = VALUES(localidad),
                    calle = VALUES(calle),
                    numero = VALUES(numero),
                    latitud = VALUES(latitud),
                    longitud = VALUES(longitud)'
            );

            $qInsertRelFinca = $this->pdo->prepare(
                'INSERT INTO rel_productor_finca (productor_id, productor_id_real, finca_id)
                 SELECT :productor_id, :productor_id_real, :finca_id
                 WHERE NOT EXISTS (
                    SELECT 1 FROM rel_productor_finca
                    WHERE productor_id = :productor_id_chk AND finca_id = :finca_id_chk
                 )'
            );

            $qSelectCuartel = $this->pdo->prepare(
                'SELECT id FROM prod_cuartel
                 WHERE cooperativa_id_real = :cooperativa_id_real
                   AND codigo_finca = :codigo_finca
                   AND codigo_cuartel = :codigo_cuartel
                 LIMIT 1'
            );

            $qInsertCuartel = $this->pdo->prepare(
                'INSERT INTO prod_cuartel
                 (id_responsable_real, cooperativa_id_real, codigo_finca, nombre_finca, codigo_cuartel, variedad, numero_inv,
                  sistema_conduccion, superficie_ha, porcentaje_cepas_produccion, forma_cosecha_actual,
                  porcentaje_malla_buen_estado, edad_promedio_encepado_anios, estado_estructura_sistema,
                  labores_mecanizables, finca_id)
                 VALUES
                 (:id_responsable_real, :cooperativa_id_real, :codigo_finca, :nombre_finca, :codigo_cuartel, :variedad, :numero_inv,
                  :sistema_conduccion, :superficie_ha, :porcentaje_cepas_produccion, :forma_cosecha_actual,
                  :porcentaje_malla_buen_estado, :edad_promedio_encepado_anios, :estado_estructura_sistema,
                  :labores_mecanizables, :finca_id)'
            );

            $qUpdateCuartel = $this->pdo->prepare(
                'UPDATE prod_cuartel
                 SET id_responsable_real = :id_responsable_real,
                     nombre_finca = :nombre_finca,
                     variedad = :variedad,
                     numero_inv = :numero_inv,
                     sistema_conduccion = :sistema_conduccion,
                     superficie_ha = :superficie_ha,
                     porcentaje_cepas_produccion = :porcentaje_cepas_produccion,
                     forma_cosecha_actual = :forma_cosecha_actual,
                     porcentaje_malla_buen_estado = :porcentaje_malla_buen_estado,
                     edad_promedio_encepado_anios = :edad_promedio_encepado_anios,
                     estado_estructura_sistema = :estado_estructura_sistema,
                     labores_mecanizables = :labores_mecanizables,
                     finca_id = :finca_id
                 WHERE id = :id'
            );

            $applied = [
                'rows_received' => count($rows),
                'rows_processable' => count($processable),
                'usuarios_created' => 0,
                'usuarios_updated' => 0,
                'usuarios_info_upserted' => 0,
                'rel_productor_coop_adjusted' => 0,
                'fincas_inserted' => 0,
                'fincas_updated' => 0,
                'direcciones_upserted' => 0,
                'rel_productor_finca_inserted' => 0,
                'cuarteles_inserted' => 0,
                'cuarteles_updated' => 0,
                'revisado_to_no' => 0,
            ];
            $createdUsersByCuit = [];

            foreach ($processable as $item) {
                $u = $item['user'];
                $r = $item['row'];

                if ($u === null) {
                    if (isset($createdUsersByCuit[$r['cuit']])) {
                        $u = $createdUsersByCuit[$r['cuit']];
                    } else {
                        $plainTempPassword = bin2hex(random_bytes(16));
                        $qInsertUsuario->execute([
                            ':usuario' => $this->firstNonEmpty($r['id_real'], $r['cuit']),
                            ':contrasena' => password_hash($plainTempPassword, PASSWORD_DEFAULT),
                            ':rol' => $this->firstNonEmpty($r['rol'], 'productor'),
                            ':permiso_ingreso' => $this->firstNonEmpty($r['permiso_ingreso'], 'Habilitado'),
                            ':cuit' => $r['cuit'],
                            ':razon_social' => $r['razon_social'],
                            ':id_real' => $r['id_real'],
                        ]);
                        $newUserId = (int)$this->pdo->lastInsertId();
                        $u = [
                            'id' => $newUserId,
                            'id_real' => (string)$r['id_real'],
                            'rol' => $this->firstNonEmpty($r['rol'], 'productor'),
                            'permiso_ingreso' => $this->firstNonEmpty($r['permiso_ingreso'], 'Habilitado'),
                        ];
                        $createdUsersByCuit[$r['cuit']] = $u;
                        $applied['usuarios_created']++;
                    }
                }

                $qUpdateUsuario->execute([
                    ':id' => $u['id'],
                    ':rol' => $this->firstNonEmpty($r['rol'], $u['rol']) ?? 'productor',
                    ':permiso_ingreso' => $this->firstNonEmpty($r['permiso_ingreso'], $u['permiso_ingreso']) ?? 'Habilitado',
                    ':razon_social' => $r['razon_social'],
                ]);
                $applied['usuarios_updated']++;

                $userInfoParams = [
                    ':usuario_id' => $u['id'],
                    ':nombre' => $r['nombre'],
                    ':direccion' => $r['direccion'],
                    ':telefono' => $r['telefono'],
                    ':correo' => $r['correo'],
                    ':fecha_nacimiento' => $r['fecha_nacimiento'],
                    ':categorizacion' => $r['categorizacion'],
                    ':tipo_relacion' => $r['tipo_relacion'],
                    ':zona_asignada' => $this->nullableString($r['zona_asignada']) ?? '',
                ];

                $qSelectUserInfo->execute([':usuario_id' => $u['id']]);
                $existsInfo = (bool)$qSelectUserInfo->fetchColumn();
                if ($existsInfo) {
                    $qUpdateUserInfo->execute($userInfoParams);
                } else {
                    $qInsertUserInfo->execute($userInfoParams);
                }
                $applied['usuarios_info_upserted']++;

                $qDeleteOtherCoops->execute([
                    ':productor_id_real' => $u['id_real'],
                    ':cooperativa_id_real' => $cooperativaIdReal,
                ]);
                $qInsertRelCoop->execute([
                    ':productor_id_real' => $u['id_real'],
                    ':cooperativa_id_real' => $cooperativaIdReal,
                    ':productor_id_real_chk' => $u['id_real'],
                    ':cooperativa_id_real_chk' => $cooperativaIdReal,
                ]);
                $applied['rel_productor_coop_adjusted']++;

                $qSelectFinca->execute([
                    ':productor_id_real' => $u['id_real'],
                    ':codigo_finca' => $r['codigo_finca'],
                ]);
                $fincaId = $qSelectFinca->fetchColumn();

                if ($fincaId) {
                    $qUpdateFinca->execute([
                        ':id' => (int)$fincaId,
                        ':nombre_finca' => $r['nombre_finca'],
                        ':variedad' => $r['variedad'],
                    ]);
                    $fincaId = (int)$fincaId;
                    $applied['fincas_updated']++;
                } else {
                    $qInsertFinca->execute([
                        ':codigo_finca' => $r['codigo_finca'],
                        ':productor_id_real' => $u['id_real'],
                        ':nombre_finca' => $r['nombre_finca'],
                        ':variedad' => $r['variedad'],
                    ]);
                    $fincaId = (int)$this->pdo->lastInsertId();
                    $applied['fincas_inserted']++;
                }

                $qInsertDir->execute([
                    ':finca_id' => $fincaId,
                    ':departamento' => $r['departamento'],
                    ':localidad' => $r['localidad'],
                    ':calle' => $r['calle'],
                    ':numero' => $r['numero'],
                    ':latitud' => $r['latitud'],
                    ':longitud' => $r['longitud'],
                ]);
                $applied['direcciones_upserted']++;

                $qInsertRelFinca->execute([
                    ':productor_id' => $u['id'],
                    ':productor_id_real' => $u['id_real'],
                    ':finca_id' => $fincaId,
                    ':productor_id_chk' => $u['id'],
                    ':finca_id_chk' => $fincaId,
                ]);
                if ($qInsertRelFinca->rowCount() > 0) {
                    $applied['rel_productor_finca_inserted']++;
                }

                $qSelectCuartel->execute([
                    ':cooperativa_id_real' => $cooperativaIdReal,
                    ':codigo_finca' => $r['codigo_finca'],
                    ':codigo_cuartel' => $r['codigo_cuartel'],
                ]);
                $cuartelId = $qSelectCuartel->fetchColumn();

                $cuartelParams = [
                    ':id_responsable_real' => $u['id_real'],
                    ':cooperativa_id_real' => $cooperativaIdReal,
                    ':codigo_finca' => $r['codigo_finca'],
                    ':nombre_finca' => $r['nombre_finca'],
                    ':codigo_cuartel' => $r['codigo_cuartel'],
                    ':variedad' => $r['variedad'],
                    ':numero_inv' => $r['numero_inv'],
                    ':sistema_conduccion' => $r['sistema_conduccion'],
                    ':superficie_ha' => $r['superficie_ha'],
                    ':porcentaje_cepas_produccion' => $r['porcentaje_cepas_produccion'],
                    ':forma_cosecha_actual' => $r['forma_cosecha_actual'],
                    ':porcentaje_malla_buen_estado' => $r['porcentaje_malla_buen_estado'],
                    ':edad_promedio_encepado_anios' => $r['edad_promedio_encepado_anios'],
                    ':estado_estructura_sistema' => $r['estado_estructura_sistema'],
                    ':labores_mecanizables' => $r['labores_mecanizables'],
                    ':finca_id' => $fincaId,
                ];

                if ($cuartelId) {
                    $cuartelParams[':id'] = (int)$cuartelId;
                    $qUpdateCuartel->execute($cuartelParams);
                    $applied['cuarteles_updated']++;
                } else {
                    $qInsertCuartel->execute($cuartelParams);
                    $applied['cuarteles_inserted']++;
                }
            }

            if (!empty($notInCsvIds)) {
                $placeholders = implode(',', array_fill(0, count($notInCsvIds), '?'));
                $sql = "UPDATE usuarios SET revisado = 'No esta revisado' WHERE id IN ($placeholders)";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(array_values($notInCsvIds));
                $applied['revisado_to_no'] = $stmt->rowCount();
            }

            $this->pdo->commit();

            return [
                'summary' => $plan['summary'],
                'preview_rows' => $plan['preview_rows'],
                'warnings' => $plan['warnings'],
                'applied' => $applied,
            ];
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    private function buildPlan(array $rawRows, string $cooperativaIdReal): array
    {
        $coop = $this->assertCooperativa($cooperativaIdReal);

        if (count($rawRows) === 0) {
            throw new InvalidArgumentException('El CSV no contiene filas.');
        }

        $first = (array)$rawRows[0];
        $missingHeaders = $this->missingHeaders($first);
        if (!empty($missingHeaders)) {
            throw new InvalidArgumentException('Faltan columnas requeridas: ' . implode(', ', $missingHeaders));
        }

        $normalizedRows = [];
        $errors = [];

        foreach ($rawRows as $index => $rawRow) {
            if (!is_array($rawRow)) {
                $errors[] = 'Fila ' . ($index + 2) . ': formato invalido.';
                continue;
            }

            $row = $this->normalizeRow($rawRow);

            if ($row['cuit'] === null) {
                $errors[] = 'Fila ' . ($index + 2) . ': cuit vacio o invalido.';
                continue;
            }

            $row['_csv_line'] = $index + 2;
            $normalizedRows[] = $row;
        }

        if (!empty($errors)) {
            throw new InvalidArgumentException(implode(' | ', $errors));
        }

        $cuitValues = array_values(array_unique(array_map(static fn(array $r): string => (string)$r['cuit'], $normalizedRows)));
        $usersByCuit = $this->fetchUsersByCuit($cuitValues);

        $associatedProducerIds = $this->fetchProducerIdsByCoop($cooperativaIdReal);
        $processable = [];
        $previewRows = [];
        $warnings = [];
        $inCsvUserIds = [];
        $processableByCuit = [];

        foreach ($normalizedRows as $row) {
            $user = $usersByCuit[$row['cuit']] ?? null;

            if ($user === null) {
                if ($row['id_real'] === null) {
                    $previewRows[] = [
                        'linea' => $row['_csv_line'],
                        'cuit' => $row['cuit'],
                        'codigo_finca' => $row['codigo_finca'],
                        'codigo_cuartel' => $row['codigo_cuartel'],
                        'resultado' => 'omitido',
                        'detalle' => 'CUIT no existe y la fila no trae id_real para crear usuario.',
                    ];
                    continue;
                }

                $sameIdReal = $this->findUserByIdReal($row['id_real']);
                if ($sameIdReal !== null && $this->normalizeCuit($sameIdReal['cuit']) !== $row['cuit']) {
                    $previewRows[] = [
                        'linea' => $row['_csv_line'],
                        'cuit' => $row['cuit'],
                        'id_real_usuario' => $row['id_real'],
                        'codigo_finca' => $row['codigo_finca'],
                        'codigo_cuartel' => $row['codigo_cuartel'],
                        'resultado' => 'omitido',
                        'detalle' => 'id_real ya existe con otro CUIT. No se puede crear usuario.',
                    ];
                    continue;
                }

                $previewRows[] = [
                    'linea' => $row['_csv_line'],
                    'cuit' => $row['cuit'],
                    'id_real_usuario' => $row['id_real'],
                    'codigo_finca' => $row['codigo_finca'],
                    'codigo_cuartel' => $row['codigo_cuartel'],
                    'resultado' => 'procesar',
                    'accion_usuario' => 'crear',
                    'accion_finca' => 'insertar_o_actualizar',
                    'accion_cuartel' => 'insertar_o_actualizar',
                    'accion_relacion' => 'crear_o_reasignar',
                ];

                $processable[] = [
                    'row' => $row,
                    'user' => null,
                ];
                $processableByCuit[$row['cuit']] = true;
                continue;
            }

            if (($user['rol'] ?? '') !== 'productor') {
                $previewRows[] = [
                    'linea' => $row['_csv_line'],
                    'cuit' => $row['cuit'],
                    'codigo_finca' => $row['codigo_finca'],
                    'codigo_cuartel' => $row['codigo_cuartel'],
                    'resultado' => 'omitido',
                    'detalle' => 'El CUIT existe pero no es rol productor.',
                ];
                continue;
            }

            $fincaExists = $this->fincaExists($user['id_real'], $row['codigo_finca']);
            $cuartelExists = $this->cuartelExists($cooperativaIdReal, $row['codigo_finca'], $row['codigo_cuartel']);
            $relState = $this->relationState($user['id_real'], $cooperativaIdReal);

            if ($row['id_real'] !== null && $row['id_real'] !== $user['id_real']) {
                $warnings[] = 'CUIT ' . $row['cuit'] . ': id_real CSV (' . $row['id_real'] . ') difiere del usuario existente (' . $user['id_real'] . '). No se modifica id_real.';
            }

            $previewRows[] = [
                'linea' => $row['_csv_line'],
                'cuit' => $row['cuit'],
                'id_real_usuario' => $user['id_real'],
                'codigo_finca' => $row['codigo_finca'],
                'codigo_cuartel' => $row['codigo_cuartel'],
                'resultado' => 'procesar',
                'accion_finca' => $fincaExists ? 'actualizar' : 'insertar',
                'accion_cuartel' => $cuartelExists ? 'actualizar' : 'insertar',
                'accion_relacion' => $relState,
            ];

            $processable[] = [
                'row' => $row,
                'user' => $user,
            ];
            $inCsvUserIds[(int)$user['id']] = (int)$user['id'];
            $processableByCuit[$row['cuit']] = true;
        }

        $notInCsvIds = array_values(array_diff($associatedProducerIds, $inCsvUserIds));

        $summary = [
            'cooperativa' => [
                'id_real' => $coop['id_real'],
                'razon_social' => $coop['razon_social'],
            ],
            'rows_total' => count($normalizedRows),
            'rows_processable' => count($processable),
            'rows_omitted' => count($normalizedRows) - count($processable),
            'usuarios_a_revisado_si' => count($processableByCuit),
            'usuarios_a_revisado_no' => count($notInCsvIds),
        ];

        return [
            'summary' => $summary,
            'preview_rows' => $previewRows,
            'warnings' => array_values(array_unique($warnings)),
            'normalized_rows' => $normalizedRows,
            'processable_rows' => $processable,
            'not_in_csv_user_ids' => $notInCsvIds,
        ];
    }

    private function assertCooperativa(string $cooperativaIdReal): array
    {
        $coopId = trim($cooperativaIdReal);
        if ($coopId === '') {
            throw new InvalidArgumentException('Debes indicar el id_real de la cooperativa.');
        }

        $stmt = $this->pdo->prepare('SELECT id, id_real, razon_social, rol FROM usuarios WHERE id_real = :id_real LIMIT 1');
        $stmt->execute([':id_real' => $coopId]);
        $coop = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$coop) {
            throw new InvalidArgumentException('La cooperativa indicada no existe.');
        }

        if (($coop['rol'] ?? '') !== 'cooperativa') {
            throw new InvalidArgumentException('El id_real indicado no pertenece a una cooperativa.');
        }

        return $coop;
    }

    private function fetchUsersByCuit(array $cuitValues): array
    {
        if (empty($cuitValues)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($cuitValues), '?'));
        $sql = "SELECT id, id_real, rol, permiso_ingreso, razon_social, cuit
                FROM usuarios
                WHERE cuit IN ($placeholders)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($cuitValues);

        $out = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $cuit = $this->normalizeCuit($row['cuit']);
            if ($cuit !== null) {
                $out[$cuit] = $row;
            }
        }
        return $out;
    }

    private function fetchProducerIdsByCoop(string $cooperativaIdReal): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT DISTINCT u.id
             FROM usuarios u
             INNER JOIN rel_productor_coop rpc ON rpc.productor_id_real = u.id_real
             WHERE rpc.cooperativa_id_real = :cooperativa_id_real
               AND u.rol = "productor"'
        );
        $stmt->execute([':cooperativa_id_real' => $cooperativaIdReal]);

        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    private function findUserByIdReal(string $idReal): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, id_real, rol, permiso_ingreso, razon_social, cuit
             FROM usuarios
             WHERE id_real = :id_real
             LIMIT 1'
        );
        $stmt->execute([':id_real' => $idReal]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function fincaExists(string $productorIdReal, string $codigoFinca): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM prod_fincas WHERE productor_id_real = :pid AND codigo_finca = :codigo LIMIT 1');
        $stmt->execute([
            ':pid' => $productorIdReal,
            ':codigo' => $codigoFinca,
        ]);
        return (bool)$stmt->fetchColumn();
    }

    private function cuartelExists(string $cooperativaIdReal, string $codigoFinca, string $codigoCuartel): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM prod_cuartel
             WHERE cooperativa_id_real = :coop
               AND codigo_finca = :codigo_finca
               AND codigo_cuartel = :codigo_cuartel
             LIMIT 1'
        );
        $stmt->execute([
            ':coop' => $cooperativaIdReal,
            ':codigo_finca' => $codigoFinca,
            ':codigo_cuartel' => $codigoCuartel,
        ]);

        return (bool)$stmt->fetchColumn();
    }

    private function relationState(string $productorIdReal, string $cooperativaIdReal): string
    {
        $stmt = $this->pdo->prepare('SELECT cooperativa_id_real FROM rel_productor_coop WHERE productor_id_real = :pid');
        $stmt->execute([':pid' => $productorIdReal]);
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($rows)) {
            return 'crear';
        }

        if (count($rows) === 1 && $rows[0] === $cooperativaIdReal) {
            return 'ok';
        }

        if (in_array($cooperativaIdReal, $rows, true) && count($rows) > 1) {
            return 'limpiar_duplicadas';
        }

        return 'reasignar';
    }

    private function missingHeaders(array $firstRow): array
    {
        $headers = [];
        foreach (array_keys($firstRow) as $k) {
            $headers[] = $this->normalizeHeader((string)$k);
        }

        $missing = [];
        foreach (self::REQUIRED_COLUMNS as $required) {
            if (!in_array($required, $headers, true)) {
                $missing[] = $required;
            }
        }

        return $missing;
    }

    private function normalizeRow(array $rawRow): array
    {
        $r = [];
        foreach ($rawRow as $k => $v) {
            $r[$this->normalizeHeader((string)$k)] = is_string($v) ? trim($v) : $v;
        }

        return [
            'rol' => $this->nullableString($r['rol'] ?? null),
            'permiso_ingreso' => $this->nullableString($r['permiso_ingreso'] ?? null),
            'cuit' => $this->normalizeCuit($r['cuit'] ?? null),
            'razon_social' => $this->nullableString($r['razon_social'] ?? null),
            'id_real' => $this->nullableString($r['id_real'] ?? null),
            'nombre' => $this->nullableString($r['nombre'] ?? null),
            'direccion' => $this->nullableString($r['direccion'] ?? null),
            'telefono' => $this->nullableString($r['telefono'] ?? null),
            'correo' => $this->nullableString($r['correo'] ?? null),
            'fecha_nacimiento' => $this->normalizeDate($r['fecha_nacimiento'] ?? null),
            'categorizacion' => $this->normalizeCategorizacion($r['categorizacion'] ?? null),
            'tipo_relacion' => $this->nullableString($r['tipo_relacion'] ?? null),
            'zona_asignada' => $this->nullableString($r['zona_asignada'] ?? null),
            'codigo_finca' => $this->requiredString($r['codigo_finca'] ?? null),
            'nombre_finca' => $this->nullableString($r['nombre_finca'] ?? null),
            'variedad' => $this->nullableString($r['variedad'] ?? null),
            'departamento' => $this->nullableString($r['departamento'] ?? null),
            'localidad' => $this->nullableString($r['localidad'] ?? null),
            'calle' => $this->nullableString($r['calle'] ?? null),
            'numero' => $this->nullableString($r['numero'] ?? null),
            'latitud' => $this->nullableDecimal($r['latitud'] ?? null),
            'longitud' => $this->nullableDecimal($r['longitud'] ?? null),
            'codigo_cuartel' => $this->requiredString($r['codigo_cuartel'] ?? null),
            'sistema_conduccion' => $this->nullableString($r['sistema_conduccion'] ?? null),
            'superficie_ha' => $this->nullableDecimal($r['superficie_ha'] ?? null),
            'porcentaje_cepas_produccion' => $this->nullableDecimal($r['porcentaje_cepas_produccion'] ?? null),
            'forma_cosecha_actual' => $this->nullableString($r['forma_cosecha_actual'] ?? null),
            'porcentaje_malla_buen_estado' => $this->nullableDecimal($r['porcentaje_malla_buen_estado'] ?? null),
            'edad_promedio_encepado_anios' => $this->nullableInt($r['edad_promedio_encepado_anios'] ?? null),
            'estado_estructura_sistema' => $this->nullableString($r['estado_estructura_sistema'] ?? null),
            'labores_mecanizables' => $this->nullableString($r['labores_mecanizables'] ?? null),
            'numero_inv' => $this->nullableString($r['numero_inv'] ?? null),
        ];
    }

    private function normalizeHeader(string $header): string
    {
        return strtolower(trim($header));
    }

    private function normalizeCuit($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', (string)$value);
        if ($digits === null || $digits === '') {
            return null;
        }

        return $digits;
    }

    private function normalizeDate($value): ?string
    {
        $v = $this->nullableString($value);
        if ($v === null) {
            return null;
        }

        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y'];
        foreach ($formats as $f) {
            $d = DateTime::createFromFormat($f, $v);
            if ($d instanceof DateTime) {
                return $d->format('Y-m-d');
            }
        }

        return null;
    }

    private function normalizeCategorizacion($value): ?string
    {
        $v = strtoupper((string)($this->nullableString($value) ?? ''));
        if ($v === 'A' || $v === 'B' || $v === 'C') {
            return $v;
        }
        return null;
    }

    private function nullableString($value): ?string
    {
        if ($value === null) {
            return null;
        }
        $v = trim((string)$value);
        return $v === '' ? null : $v;
    }

    private function requiredString($value): string
    {
        return (string)($this->nullableString($value) ?? '');
    }

    private function nullableDecimal($value): ?float
    {
        $v = $this->nullableString($value);
        if ($v === null) {
            return null;
        }
        $v = str_replace(',', '.', $v);
        if (!is_numeric($v)) {
            return null;
        }
        return (float)$v;
    }

    private function nullableInt($value): ?int
    {
        $v = $this->nullableString($value);
        if ($v === null) {
            return null;
        }
        if (!preg_match('/^-?\d+$/', $v)) {
            return null;
        }
        return (int)$v;
    }

    private function firstNonEmpty(...$values): ?string
    {
        foreach ($values as $v) {
            $vv = $this->nullableString($v);
            if ($vv !== null) {
                return $vv;
            }
        }
        return null;
    }
}
