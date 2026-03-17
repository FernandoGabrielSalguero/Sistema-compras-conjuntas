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

    private const CUARTEL_LIMITANTES_FIELDS = [
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

    private const CUARTEL_RENDIMIENTOS_FIELDS = [
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

    private const CUARTEL_RIESGOS_FIELDS = [
        'tiene_seguro_agricola',
        'porcentaje_dano_granizo',
        'heladas_dano_promedio_5anios',
        'presencia_freatica',
        'plagas_no_convencionales',
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

    public function simulateStrictFromRows(array $rawRows, string $cooperativaIdReal): array
    {
        $plan = $this->buildPlan($rawRows, $cooperativaIdReal);
        $snapshot = $this->buildSnapshotMapsFromProcessable($plan['processable_rows'] ?? []);
        $cleanup = $this->syncStrictSnapshot(
            $cooperativaIdReal,
            $snapshot['producers'],
            $snapshot['fincas'],
            $snapshot['cuarteles'],
            true
        );

        $blocked = ((int)($plan['summary']['rows_omitted'] ?? 0)) > 0;
        $reason = $blocked
            ? 'Hay filas omitidas en la sabana. La sincronizacion estricta se bloqueara hasta corregirlas.'
            : null;

        return [
            'summary' => $plan['summary'],
            'warnings' => $plan['warnings'],
            'strict_sync' => [
                'blocked' => $blocked,
                'reason' => $reason,
                'rel_productor_coop_to_delete' => (int)($cleanup['rel_productor_coop_deleted'] ?? 0),
                'fincas_to_delete' => (int)($cleanup['fincas_deleted'] ?? 0),
                'cuarteles_to_delete' => (int)($cleanup['cuarteles_deleted'] ?? 0),
            ],
        ];
    }

    public function applyFromRows(array $rawRows, string $cooperativaIdReal, array $options = []): array
    {
        $skipRevisadoNo = !empty($options['skip_revisado_no']);
        $allCsvCuits = is_array($options['all_csv_cuits'] ?? null) ? $options['all_csv_cuits'] : null;
        $allCsvRows = is_array($options['all_csv_rows'] ?? null) ? $options['all_csv_rows'] : null;

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
            $qDeleteSameCoopDup = $this->pdo->prepare(
                'DELETE FROM rel_productor_coop
                 WHERE productor_id_real = :productor_id_real
                   AND cooperativa_id_real = :cooperativa_id_real'
            );
            $qInsertRelCoopSimple = $this->pdo->prepare(
                'INSERT INTO rel_productor_coop (productor_id_real, cooperativa_id_real)
                 VALUES (:productor_id_real, :cooperativa_id_real)'
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

            $qSelectRelFinca = $this->pdo->prepare(
                'SELECT id FROM rel_productor_finca
                 WHERE productor_id = :productor_id AND finca_id = :finca_id
                 LIMIT 1'
            );
            $qInsertRelFinca = $this->pdo->prepare(
                'INSERT INTO rel_productor_finca (productor_id, productor_id_real, finca_id)
                 VALUES (:productor_id, :productor_id_real, :finca_id)'
            );

            $qSelectCuartel = $this->pdo->prepare(
                'SELECT id FROM prod_cuartel
                 WHERE cooperativa_id_real = :cooperativa_id_real
                   AND id_responsable_real = :id_responsable_real
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

            $qResetCuartelLimitantes = $this->pdo->prepare(
                'INSERT INTO prod_cuartel_limitantes (cuartel_id)
                 VALUES (:cuartel_id)
                 ON DUPLICATE KEY UPDATE
                    limitantes_suelo = NULL,
                    observaciones = NULL,
                    categoria_1 = NULL,
                    limitante_1 = NULL,
                    inversion_accion1_1 = NULL,
                    obs_inversion_accion1_1 = NULL,
                    ciclo_agricola1_1 = NULL,
                    inversion_accion2_1 = NULL,
                    obs_inversion_accion2_1 = NULL,
                    ciclo_agricola2_1 = NULL,
                    categoria_2 = NULL,
                    limitante_2 = NULL,
                    inversion_accion1_2 = NULL,
                    obs_inversion_accion1_2 = NULL,
                    ciclo_agricola1_2 = NULL,
                    inversion_accion2_2 = NULL,
                    obs_inversion_accion2_2 = NULL,
                    ciclo_agricola2_2 = NULL'
            );

            $qResetCuartelRendimientos = $this->pdo->prepare(
                'INSERT INTO prod_cuartel_rendimientos (cuartel_id)
                 VALUES (:cuartel_id)
                 ON DUPLICATE KEY UPDATE
                    rend_2020_qq_ha = NULL,
                    rend_2021_qq_ha = NULL,
                    rend_2022_qq_ha = NULL,
                    ing_2023_kg = NULL,
                    rend_2023_qq_ha = NULL,
                    ing_2024_kg = NULL,
                    rend_2024_qq_ha = NULL,
                    ing_2025_kg = NULL,
                    rend_2025_qq_ha = NULL,
                    rend_promedio_5anios_qq_ha = NULL'
            );

            $qResetCuartelRiesgos = $this->pdo->prepare(
                'INSERT INTO prod_cuartel_riesgos (cuartel_id)
                 VALUES (:cuartel_id)
                 ON DUPLICATE KEY UPDATE
                    tiene_seguro_agricola = NULL,
                    porcentaje_dano_granizo = NULL,
                    heladas_dano_promedio_5anios = NULL,
                    presencia_freatica = NULL,
                    plagas_no_convencionales = NULL'
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
                'cuartel_limitantes_null_reset' => 0,
                'cuartel_rendimientos_null_reset' => 0,
                'cuartel_riesgos_null_reset' => 0,
                'revisado_to_no' => 0,
                'rel_productor_coop_deleted' => 0,
                'fincas_deleted' => 0,
                'cuarteles_deleted' => 0,
            ];
            $createdUsersByCuit = [];
            $snapshotProducerIdReals = [];
            $snapshotFincaKeys = [];
            $snapshotCuartelKeys = [];

            foreach ($processable as $item) {
                $u = $item['user'];
                $r = $item['row'];
                $targetIdReal = (string)($item['target_id_real'] ?? $r['id_real'] ?? '');

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
                            ':id_real' => $targetIdReal !== '' ? $targetIdReal : (string)$r['cuit'],
                        ]);
                        $newUserId = (int)$this->pdo->lastInsertId();
                        $u = [
                            'id' => $newUserId,
                            'id_real' => $targetIdReal !== '' ? $targetIdReal : (string)$r['cuit'],
                            'rol' => $this->firstNonEmpty($r['rol'], 'productor'),
                            'permiso_ingreso' => $this->firstNonEmpty($r['permiso_ingreso'], 'Habilitado'),
                        ];
                        $createdUsersByCuit[$r['cuit']] = $u;
                        $applied['usuarios_created']++;
                    }
                }

                $currentIdReal = (string)$u['id_real'];
                if ($targetIdReal !== '' && $targetIdReal !== $currentIdReal) {
                    $this->migrateUserIdReal((int)$u['id'], $currentIdReal, $targetIdReal);
                    $u['id_real'] = $targetIdReal;
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
                $qDeleteSameCoopDup->execute([
                    ':productor_id_real' => $u['id_real'],
                    ':cooperativa_id_real' => $cooperativaIdReal,
                ]);
                $qInsertRelCoopSimple->execute([
                    ':productor_id_real' => $u['id_real'],
                    ':cooperativa_id_real' => $cooperativaIdReal,
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

                $qSelectRelFinca->execute([
                    ':productor_id' => $u['id'],
                    ':finca_id' => $fincaId,
                ]);
                $hasRelFinca = (bool)$qSelectRelFinca->fetchColumn();
                if (!$hasRelFinca) {
                    $qInsertRelFinca->execute([
                        ':productor_id' => $u['id'],
                        ':productor_id_real' => $u['id_real'],
                        ':finca_id' => $fincaId,
                    ]);
                    $applied['rel_productor_finca_inserted']++;
                }

                $qSelectCuartel->execute([
                    ':cooperativa_id_real' => $cooperativaIdReal,
                    ':id_responsable_real' => $u['id_real'],
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
                    $cuartelId = (int)$cuartelId;
                    $qUpdateCuartel->execute([
                        ':id' => $cuartelId,
                        ':id_responsable_real' => $u['id_real'],
                        ':nombre_finca' => $r['nombre_finca'],
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
                    ]);
                    $applied['cuarteles_updated']++;
                } else {
                    $qInsertCuartel->execute($cuartelParams);
                    $cuartelId = (int)$this->pdo->lastInsertId();
                    $applied['cuarteles_inserted']++;
                }

                $qResetCuartelLimitantes->execute([':cuartel_id' => $cuartelId]);
                $qResetCuartelRendimientos->execute([':cuartel_id' => $cuartelId]);
                $qResetCuartelRiesgos->execute([':cuartel_id' => $cuartelId]);
                $applied['cuartel_limitantes_null_reset']++;
                $applied['cuartel_rendimientos_null_reset']++;
                $applied['cuartel_riesgos_null_reset']++;

                $snapshotProducerIdReals[(string)$u['id_real']] = true;
                $snapshotFincaKeys[$this->buildSnapshotFincaKey((string)$u['id_real'], (string)$r['codigo_finca'])] = true;
                $snapshotCuartelKeys[$this->buildSnapshotCuartelKey((string)$u['id_real'], (string)$r['codigo_finca'], (string)$r['codigo_cuartel'])] = true;
            }

            if (!$skipRevisadoNo) {
                if (is_array($allCsvCuits) && !empty($allCsvCuits)) {
                    $associatedProducerIds = $this->fetchProducerIdsByCoop($cooperativaIdReal);
                    $allCsvUserIds = $this->fetchUserIdsByCuits($allCsvCuits);
                    $notInCsvIds = array_values(array_diff($associatedProducerIds, $allCsvUserIds));
                }
            } else {
                $notInCsvIds = [];
            }

            if (!$skipRevisadoNo) {
                if (is_array($allCsvRows) && !empty($allCsvRows)) {
                    $fullPlan = $this->buildPlan($allCsvRows, $cooperativaIdReal);
                    if (($fullPlan['summary']['rows_omitted'] ?? 0) > 0) {
                        throw new InvalidArgumentException('No se puede ejecutar sincronizacion estricta: hay filas omitidas en el CSV. Corregi la sabana y volve a intentar.');
                    }
                    $snapshot = $this->buildSnapshotMapsFromProcessable($fullPlan['processable_rows'] ?? []);
                    $snapshotProducerIdReals = $snapshot['producers'];
                    $snapshotFincaKeys = $snapshot['fincas'];
                    $snapshotCuartelKeys = $snapshot['cuarteles'];
                } elseif (($plan['summary']['rows_omitted'] ?? 0) > 0) {
                    throw new InvalidArgumentException('No se puede ejecutar sincronizacion estricta: hay filas omitidas en el CSV. Corregi la sabana y volve a intentar.');
                }
                $cleanup = $this->syncStrictSnapshot(
                    $cooperativaIdReal,
                    $snapshotProducerIdReals,
                    $snapshotFincaKeys,
                    $snapshotCuartelKeys
                );
                $applied['rel_productor_coop_deleted'] += (int)($cleanup['rel_productor_coop_deleted'] ?? 0);
                $applied['fincas_deleted'] += (int)($cleanup['fincas_deleted'] ?? 0);
                $applied['cuarteles_deleted'] += (int)($cleanup['cuarteles_deleted'] ?? 0);
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
                'meta' => [
                    'skip_revisado_no' => $skipRevisadoNo,
                    'rows_in_batch' => count($rows),
                ],
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

        $csvTargetIdRealByCuit = [];
        foreach ($normalizedRows as $row) {
            if ($row['id_real'] === null) {
                continue;
            }
            if (isset($csvTargetIdRealByCuit[$row['cuit']]) && $csvTargetIdRealByCuit[$row['cuit']] !== $row['id_real']) {
                $errors[] = 'CUIT ' . $row['cuit'] . ': id_real inconsistente en CSV (' . $csvTargetIdRealByCuit[$row['cuit']] . ' vs ' . $row['id_real'] . ').';
                continue;
            }
            $csvTargetIdRealByCuit[$row['cuit']] = $row['id_real'];
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
            $targetIdReal = $row['id_real'];

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
                    'usuario_encontrado' => 'no',
                ];

                $changeSet = $this->buildChangeSet(null, $row, $cooperativaIdReal, (string)$targetIdReal);
                $previewRows[count($previewRows) - 1] = array_merge($previewRows[count($previewRows) - 1], $changeSet);

                $processable[] = [
                    'row' => $row,
                    'user' => null,
                    'target_id_real' => $targetIdReal,
                ];
                $processableByCuit[$row['cuit']] = true;
                continue;
            }

            if ($targetIdReal === null) {
                $targetIdReal = (string)$user['id_real'];
            }

            if ($targetIdReal !== (string)$user['id_real']) {
                $sameIdReal = $this->findUserByIdReal($targetIdReal);
                if ($sameIdReal !== null && (int)$sameIdReal['id'] !== (int)$user['id']) {
                    $previewRows[] = [
                        'linea' => $row['_csv_line'],
                        'cuit' => $row['cuit'],
                        'id_real_usuario' => $targetIdReal,
                        'codigo_finca' => $row['codigo_finca'],
                        'codigo_cuartel' => $row['codigo_cuartel'],
                        'resultado' => 'omitido',
                        'detalle' => 'id_real objetivo ya pertenece a otro usuario.',
                    ];
                    continue;
                }
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

            $previewRows[] = [
                'linea' => $row['_csv_line'],
                'cuit' => $row['cuit'],
                'id_real_usuario' => $targetIdReal,
                'codigo_finca' => $row['codigo_finca'],
                'codigo_cuartel' => $row['codigo_cuartel'],
                'resultado' => 'procesar',
                'usuario_encontrado' => 'si',
            ];

            $changeSet = $this->buildChangeSet($user, $row, $cooperativaIdReal, (string)$targetIdReal);
            $previewRows[count($previewRows) - 1] = array_merge($previewRows[count($previewRows) - 1], $changeSet);

            $processable[] = [
                'row' => $row,
                'user' => $user,
                'target_id_real' => $targetIdReal,
            ];
            $inCsvUserIds[(int)$user['id']] = (int)$user['id'];
            $processableByCuit[$row['cuit']] = true;
        }

        $notInCsvIds = array_values(array_diff($associatedProducerIds, $inCsvUserIds));
        $relatedTablesSummary = $this->buildRelatedTablesSummary($cooperativaIdReal, $processable);

        $summary = [
            'cooperativa' => [
                'id_real' => $coop['id_real'],
                'razon_social' => $coop['razon_social'],
                'nombre' => $coop['nombre_cooperativa'],
            ],
            'rows_total' => count($normalizedRows),
            'rows_processable' => count($processable),
            'rows_omitted' => count($normalizedRows) - count($processable),
            'usuarios_nuevos_estimados' => $this->countEstimatedNewUsers($processable),
            'usuarios_a_revisado_si' => count($processableByCuit),
            'usuarios_a_revisado_no' => count($notInCsvIds),
            'tablas_relacionadas' => $relatedTablesSummary,
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

        $stmt = $this->pdo->prepare(
            'SELECT u.id, u.id_real, u.razon_social, u.rol, u.usuario,
                    ui.nombre AS info_nombre
             FROM usuarios u
             LEFT JOIN usuarios_info ui ON ui.usuario_id = u.id
             WHERE u.id_real = :id_real
             LIMIT 1'
        );
        $stmt->execute([':id_real' => $coopId]);
        $coop = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$coop) {
            throw new InvalidArgumentException('La cooperativa indicada no existe.');
        }

        if (($coop['rol'] ?? '') !== 'cooperativa') {
            throw new InvalidArgumentException('El id_real indicado no pertenece a una cooperativa.');
        }

        $displayName = $this->firstNonEmpty(
            $coop['razon_social'] ?? null,
            $coop['info_nombre'] ?? null,
            $coop['usuario'] ?? null
        ) ?? 'Sin nombre';
        $coop['nombre_cooperativa'] = $displayName;

        return $coop;
    }

    private function buildRelatedTablesSummary(string $cooperativaIdReal, array $processable): array
    {
        $targetProducerIds = [];
        foreach ($processable as $item) {
            $target = $this->nullableString($item['target_id_real'] ?? null);
            if ($target !== null) {
                $targetProducerIds[$target] = $target;
            }
        }
        $targetProducerIds = array_values($targetProducerIds);

        if (empty($targetProducerIds)) {
            return [];
        }

        $producerPh = implode(',', array_fill(0, count($targetProducerIds), '?'));

        return [
            [
                'tabla' => 'rel_productor_coop',
                'filas_existentes' => $this->countFromSql(
                    "SELECT COUNT(*) FROM rel_productor_coop WHERE cooperativa_id_real = ? AND productor_id_real IN ($producerPh)",
                    array_merge([$cooperativaIdReal], $targetProducerIds)
                ),
                'accion' => 'normalizar_relacion',
            ],
            [
                'tabla' => 'prod_fincas',
                'filas_existentes' => $this->countFromSql(
                    "SELECT COUNT(*) FROM prod_fincas WHERE productor_id_real IN ($producerPh)",
                    $targetProducerIds
                ),
                'accion' => 'upsert_csv',
            ],
            [
                'tabla' => 'prod_finca_direccion',
                'filas_existentes' => $this->countFromSql(
                    "SELECT COUNT(*) FROM prod_finca_direccion d
                     INNER JOIN prod_fincas f ON f.id = d.finca_id
                     WHERE f.productor_id_real IN ($producerPh)",
                    $targetProducerIds
                ),
                'accion' => 'upsert_csv',
            ],
            [
                'tabla' => 'rel_productor_finca',
                'filas_existentes' => $this->countFromSql(
                    "SELECT COUNT(*) FROM rel_productor_finca WHERE productor_id_real IN ($producerPh)",
                    $targetProducerIds
                ),
                'accion' => 'asegurar_relacion',
            ],
            [
                'tabla' => 'prod_cuartel',
                'filas_existentes' => $this->countFromSql(
                    "SELECT COUNT(*) FROM prod_cuartel
                     WHERE cooperativa_id_real = ?
                       AND id_responsable_real IN ($producerPh)",
                    array_merge([$cooperativaIdReal], $targetProducerIds)
                ),
                'accion' => 'upsert_csv',
            ],
            [
                'tabla' => 'prod_cuartel_limitantes',
                'filas_existentes' => $this->countFromSql(
                    "SELECT COUNT(*) FROM prod_cuartel_limitantes l
                     INNER JOIN prod_cuartel c ON c.id = l.cuartel_id
                     WHERE c.cooperativa_id_real = ?
                       AND c.id_responsable_real IN ($producerPh)",
                    array_merge([$cooperativaIdReal], $targetProducerIds)
                ),
                'accion' => 'reset_null_no_csv',
            ],
            [
                'tabla' => 'prod_cuartel_rendimientos',
                'filas_existentes' => $this->countFromSql(
                    "SELECT COUNT(*) FROM prod_cuartel_rendimientos r
                     INNER JOIN prod_cuartel c ON c.id = r.cuartel_id
                     WHERE c.cooperativa_id_real = ?
                       AND c.id_responsable_real IN ($producerPh)",
                    array_merge([$cooperativaIdReal], $targetProducerIds)
                ),
                'accion' => 'reset_null_no_csv',
            ],
            [
                'tabla' => 'prod_cuartel_riesgos',
                'filas_existentes' => $this->countFromSql(
                    "SELECT COUNT(*) FROM prod_cuartel_riesgos r
                     INNER JOIN prod_cuartel c ON c.id = r.cuartel_id
                     WHERE c.cooperativa_id_real = ?
                       AND c.id_responsable_real IN ($producerPh)",
                    array_merge([$cooperativaIdReal], $targetProducerIds)
                ),
                'accion' => 'reset_null_no_csv',
            ],
        ];
    }

    private function countFromSql(string $sql, array $params): int
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($params));
        return (int)$stmt->fetchColumn();
    }

    private function countEstimatedNewUsers(array $processable): int
    {
        $newByCuit = [];
        foreach ($processable as $item) {
            if (($item['user'] ?? null) !== null) {
                continue;
            }
            $row = $item['row'] ?? null;
            if (!is_array($row)) {
                continue;
            }
            $cuit = $row['cuit'] ?? null;
            if ($cuit !== null) {
                $newByCuit[(string)$cuit] = true;
            }
        }
        return count($newByCuit);
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

    private function fetchUserIdsByCuits(array $cuitValues): array
    {
        $normalized = [];
        foreach ($cuitValues as $cuit) {
            $n = $this->normalizeCuit($cuit);
            if ($n !== null) {
                $normalized[$n] = $n;
            }
        }
        $normalized = array_values($normalized);
        if (empty($normalized)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($normalized), '?'));
        $sql = "SELECT id FROM usuarios WHERE cuit IN ($placeholders)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($normalized);

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

    private function buildChangeSet(?array $user, array $row, string $cooperativaIdReal, string $targetIdReal): array
    {
        $beforeUser = $user ? [
            'cuit' => $this->normalizeCuit($user['cuit'] ?? null),
            'id_real' => (string)($user['id_real'] ?? ''),
            'rol' => $user['rol'] ?? null,
            'permiso_ingreso' => $user['permiso_ingreso'] ?? null,
            'razon_social' => $user['razon_social'] ?? null,
            'revisado' => null,
        ] : null;

        $afterUser = [
            'cuit' => $row['cuit'],
            'id_real' => $targetIdReal,
            'rol' => $this->firstNonEmpty($row['rol'], $user['rol'] ?? null, 'productor'),
            'permiso_ingreso' => $this->firstNonEmpty($row['permiso_ingreso'], $user['permiso_ingreso'] ?? null, 'Habilitado'),
            'razon_social' => $row['razon_social'],
            'revisado' => 'Esta revisado',
        ];

        $beforeInfo = null;
        if ($user && isset($user['id'])) {
            $beforeInfo = $this->fetchUserInfoByUsuarioId((int)$user['id']);
        }
        $afterInfo = [
            'nombre' => $row['nombre'],
            'direccion' => $row['direccion'],
            'telefono' => $row['telefono'],
            'correo' => $row['correo'],
            'fecha_nacimiento' => $row['fecha_nacimiento'],
            'categorizacion' => $row['categorizacion'],
            'tipo_relacion' => $row['tipo_relacion'],
            'zona_asignada' => $this->nullableString($row['zona_asignada']) ?? '',
        ];

        $beforeFinca = $this->fetchFincaByProductorAndCodigo($targetIdReal, $row['codigo_finca']);
        $afterFinca = [
            'codigo_finca' => $row['codigo_finca'],
            'productor_id_real' => $targetIdReal,
            'nombre_finca' => $row['nombre_finca'],
            'variedad' => $row['variedad'],
        ];

        $beforeDir = null;
        if ($beforeFinca && isset($beforeFinca['id'])) {
            $beforeDir = $this->fetchFincaDireccionByFincaId((int)$beforeFinca['id']);
        }
        $afterDir = [
            'departamento' => $row['departamento'],
            'localidad' => $row['localidad'],
            'calle' => $row['calle'],
            'numero' => $row['numero'],
            'latitud' => $row['latitud'],
            'longitud' => $row['longitud'],
        ];

        $beforeCuartel = $this->fetchCuartelByKeys($cooperativaIdReal, $targetIdReal, $row['codigo_finca'], $row['codigo_cuartel']);
        $afterCuartel = [
            'id_responsable_real' => $targetIdReal,
            'cooperativa_id_real' => $cooperativaIdReal,
            'codigo_finca' => $row['codigo_finca'],
            'nombre_finca' => $row['nombre_finca'],
            'codigo_cuartel' => $row['codigo_cuartel'],
            'variedad' => $row['variedad'],
            'numero_inv' => $row['numero_inv'],
            'sistema_conduccion' => $row['sistema_conduccion'],
            'superficie_ha' => $row['superficie_ha'],
            'porcentaje_cepas_produccion' => $row['porcentaje_cepas_produccion'],
            'forma_cosecha_actual' => $row['forma_cosecha_actual'],
            'porcentaje_malla_buen_estado' => $row['porcentaje_malla_buen_estado'],
            'edad_promedio_encepado_anios' => $row['edad_promedio_encepado_anios'],
            'estado_estructura_sistema' => $row['estado_estructura_sistema'],
            'labores_mecanizables' => $row['labores_mecanizables'],
        ];

        $beforeLimitantes = null;
        $beforeRendimientos = null;
        $beforeRiesgos = null;
        if ($beforeCuartel && isset($beforeCuartel['id'])) {
            $cuartelId = (int)$beforeCuartel['id'];
            $beforeLimitantes = $this->fetchCuartelLimitantesByCuartelId($cuartelId);
            $beforeRendimientos = $this->fetchCuartelRendimientosByCuartelId($cuartelId);
            $beforeRiesgos = $this->fetchCuartelRiesgosByCuartelId($cuartelId);
        }
        $afterLimitantes = array_fill_keys(self::CUARTEL_LIMITANTES_FIELDS, null);
        $afterRendimientos = array_fill_keys(self::CUARTEL_RENDIMIENTOS_FIELDS, null);
        $afterRiesgos = array_fill_keys(self::CUARTEL_RIESGOS_FIELDS, null);

        $relBefore = $this->fetchRelProductorCoop($targetIdReal);
        $relAfter = [
            'productor_id_real' => $targetIdReal,
            'cooperativa_id_real' => $cooperativaIdReal,
        ];

        $changes = [];
        $changes = array_merge($changes, $this->collectDiffRows('usuarios', ['cuit', 'id_real', 'rol', 'permiso_ingreso', 'razon_social', 'revisado'], $beforeUser, $afterUser));
        $changes = array_merge($changes, $this->collectDiffRows('usuarios_info', ['nombre', 'direccion', 'telefono', 'correo', 'fecha_nacimiento', 'categorizacion', 'tipo_relacion', 'zona_asignada'], $beforeInfo, $afterInfo));
        $changes = array_merge($changes, $this->collectDiffRows('prod_fincas', ['codigo_finca', 'productor_id_real', 'nombre_finca', 'variedad'], $beforeFinca, $afterFinca));
        $changes = array_merge($changes, $this->collectDiffRows('prod_finca_direccion', ['departamento', 'localidad', 'calle', 'numero', 'latitud', 'longitud'], $beforeDir, $afterDir));
        $changes = array_merge($changes, $this->collectDiffRows('prod_cuartel', ['id_responsable_real', 'cooperativa_id_real', 'codigo_finca', 'nombre_finca', 'codigo_cuartel', 'variedad', 'numero_inv', 'sistema_conduccion', 'superficie_ha', 'porcentaje_cepas_produccion', 'forma_cosecha_actual', 'porcentaje_malla_buen_estado', 'edad_promedio_encepado_anios', 'estado_estructura_sistema', 'labores_mecanizables'], $beforeCuartel, $afterCuartel));
        $changes = array_merge($changes, $this->collectDiffRows('prod_cuartel_limitantes', self::CUARTEL_LIMITANTES_FIELDS, $beforeLimitantes, $afterLimitantes));
        $changes = array_merge($changes, $this->collectDiffRows('prod_cuartel_rendimientos', self::CUARTEL_RENDIMIENTOS_FIELDS, $beforeRendimientos, $afterRendimientos));
        $changes = array_merge($changes, $this->collectDiffRows('prod_cuartel_riesgos', self::CUARTEL_RIESGOS_FIELDS, $beforeRiesgos, $afterRiesgos));
        $changes = array_merge($changes, $this->collectRelationDiffRows('rel_productor_coop', $relBefore, $relAfter));
        $changes = array_merge($changes, $this->buildIdRealCascadePreview($user, $targetIdReal));

        $changedCount = 0;
        foreach ($changes as $ch) {
            if (!empty($ch['cambia'])) {
                $changedCount++;
            }
        }

        return [
            'accion_usuario' => $user ? 'actualizar' : 'crear',
            'accion_id_real' => ($user && (string)$user['id_real'] === $targetIdReal) ? 'sin_cambios' : 'actualizar',
            'accion_finca' => $beforeFinca ? 'actualizar' : 'insertar',
            'accion_cuartel' => $beforeCuartel ? 'actualizar' : 'insertar',
            'accion_relacion' => $this->relationState($targetIdReal, $cooperativaIdReal),
            'changes_count' => $changedCount,
            'changes_flat' => $changes,
        ];
    }

    private function buildIdRealCascadePreview(?array $user, string $targetIdReal): array
    {
        if (!$user || !isset($user['id_real'])) {
            return [];
        }

        $oldIdReal = (string)$user['id_real'];
        if ($oldIdReal === '' || $targetIdReal === '' || $oldIdReal === $targetIdReal) {
            return [];
        }

        $tables = [
            ['table' => 'rel_productor_coop', 'column' => 'productor_id_real'],
            ['table' => 'prod_fincas', 'column' => 'productor_id_real'],
            ['table' => 'rel_productor_finca', 'column' => 'productor_id_real'],
            ['table' => 'prod_cuartel', 'column' => 'id_responsable_real'],
            ['table' => 'drones_solicitud', 'column' => 'productor_id_real'],
            ['table' => 'login_auditoria', 'column' => 'usuario_id_real'],
            ['table' => 'rel_productor_coop', 'column' => 'cooperativa_id_real'],
            ['table' => 'rel_coop_ingeniero', 'column' => 'cooperativa_id_real'],
            ['table' => 'rel_coop_ingeniero', 'column' => 'ingeniero_id_real'],
            ['table' => 'operativos_cooperativas_participacion', 'column' => 'cooperativa_id_real'],
            ['table' => 'prod_cuartel', 'column' => 'cooperativa_id_real'],
            ['table' => 'cooperativas_rangos', 'column' => 'cooperativa_id_real'],
            ['table' => 'cosechaMecanica_coop_contrato_firma', 'column' => 'cooperativa_id_real'],
            ['table' => 'cosechaMecanica_coop_correo_log', 'column' => 'cooperativa_id_real'],
            ['table' => 'log_correos', 'column' => 'cooperativa_id_real'],
        ];

        $out = [];
        foreach ($tables as $item) {
            $count = $this->countByIdReal($item['table'], $item['column'], $oldIdReal);
            $out[] = [
                'tabla' => $item['table'],
                'campo' => $item['column'],
                'actual' => $count > 0 ? ($oldIdReal . ' (filas: ' . $count . ')') : ('Sin coincidencias de ' . $oldIdReal),
                'nuevo' => $targetIdReal . ($count > 0 ? (' (actualiza ' . $count . ' filas)') : ' (sin cambios)'),
                'cambia' => $count > 0,
            ];
        }

        return $out;
    }

    private function countByIdReal(string $table, string $column, string $idReal): int
    {
        $allowed = [
            'rel_productor_coop' => ['productor_id_real', 'cooperativa_id_real'],
            'prod_fincas' => ['productor_id_real'],
            'rel_productor_finca' => ['productor_id_real'],
            'prod_cuartel' => ['id_responsable_real', 'cooperativa_id_real'],
            'drones_solicitud' => ['productor_id_real'],
            'login_auditoria' => ['usuario_id_real'],
            'rel_coop_ingeniero' => ['cooperativa_id_real', 'ingeniero_id_real'],
            'operativos_cooperativas_participacion' => ['cooperativa_id_real'],
            'cooperativas_rangos' => ['cooperativa_id_real'],
            'cosechaMecanica_coop_contrato_firma' => ['cooperativa_id_real'],
            'cosechaMecanica_coop_correo_log' => ['cooperativa_id_real'],
            'log_correos' => ['cooperativa_id_real'],
        ];

        if (!isset($allowed[$table]) || !in_array($column, $allowed[$table], true)) {
            return 0;
        }

        $sql = "SELECT COUNT(*) FROM {$table} WHERE {$column} = :id_real";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_real' => $idReal]);
        return (int)$stmt->fetchColumn();
    }

    private function collectDiffRows(string $table, array $fields, ?array $before, array $after): array
    {
        $rows = [];
        foreach ($fields as $field) {
            $from = $before[$field] ?? null;
            $to = $after[$field] ?? null;
            $rows[] = [
                'tabla' => $table,
                'campo' => $field,
                'actual' => $from,
                'nuevo' => $to,
                'cambia' => $this->normCmp($from) !== $this->normCmp($to),
            ];
        }
        return $rows;
    }

    private function collectRelationDiffRows(string $table, array $beforeRows, array $after): array
    {
        $beforePacked = empty($beforeRows) ? null : implode(', ', array_values(array_unique($beforeRows)));
        $afterPacked = (string)$after['cooperativa_id_real'];
        return [[
            'tabla' => $table,
            'campo' => 'cooperativa_id_real',
            'actual' => $beforePacked,
            'nuevo' => $afterPacked,
            'cambia' => $beforePacked === null || strpos((string)$beforePacked, $afterPacked) === false || substr_count((string)$beforePacked, ',') > 0,
        ]];
    }

    private function normCmp($value): string
    {
        if ($value === null) {
            return '';
        }
        return trim((string)$value);
    }

    private function fetchUserInfoByUsuarioId(int $usuarioId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT nombre, direccion, telefono, correo, fecha_nacimiento, categorizacion, tipo_relacion, zona_asignada
             FROM usuarios_info
             WHERE usuario_id = :usuario_id
             LIMIT 1'
        );
        $stmt->execute([':usuario_id' => $usuarioId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function fetchFincaByProductorAndCodigo(string $productorIdReal, string $codigoFinca): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, codigo_finca, productor_id_real, nombre_finca, variedad
             FROM prod_fincas
             WHERE productor_id_real = :productor_id_real
               AND codigo_finca = :codigo_finca
             LIMIT 1'
        );
        $stmt->execute([
            ':productor_id_real' => $productorIdReal,
            ':codigo_finca' => $codigoFinca,
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function fetchFincaDireccionByFincaId(int $fincaId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT departamento, localidad, calle, numero, latitud, longitud
             FROM prod_finca_direccion
             WHERE finca_id = :finca_id
             LIMIT 1'
        );
        $stmt->execute([':finca_id' => $fincaId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function fetchCuartelByKeys(string $cooperativaIdReal, string $idResponsableReal, string $codigoFinca, string $codigoCuartel): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, id_responsable_real, cooperativa_id_real, codigo_finca, nombre_finca, codigo_cuartel, variedad, numero_inv,
                    sistema_conduccion, superficie_ha, porcentaje_cepas_produccion, forma_cosecha_actual,
                    porcentaje_malla_buen_estado, edad_promedio_encepado_anios, estado_estructura_sistema, labores_mecanizables
             FROM prod_cuartel
             WHERE cooperativa_id_real = :cooperativa_id_real
               AND id_responsable_real = :id_responsable_real
               AND codigo_finca = :codigo_finca
               AND codigo_cuartel = :codigo_cuartel
             LIMIT 1'
        );
        $stmt->execute([
            ':cooperativa_id_real' => $cooperativaIdReal,
            ':id_responsable_real' => $idResponsableReal,
            ':codigo_finca' => $codigoFinca,
            ':codigo_cuartel' => $codigoCuartel,
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function fetchRelProductorCoop(string $productorIdReal): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT cooperativa_id_real
             FROM rel_productor_coop
             WHERE productor_id_real = :productor_id_real'
        );
        $stmt->execute([':productor_id_real' => $productorIdReal]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    private function fetchCuartelLimitantesByCuartelId(int $cuartelId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT limitantes_suelo, observaciones, categoria_1, limitante_1, inversion_accion1_1,
                    obs_inversion_accion1_1, ciclo_agricola1_1, inversion_accion2_1, obs_inversion_accion2_1,
                    ciclo_agricola2_1, categoria_2, limitante_2, inversion_accion1_2, obs_inversion_accion1_2,
                    ciclo_agricola1_2, inversion_accion2_2, obs_inversion_accion2_2, ciclo_agricola2_2
             FROM prod_cuartel_limitantes
             WHERE cuartel_id = :cuartel_id
             LIMIT 1'
        );
        $stmt->execute([':cuartel_id' => $cuartelId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function fetchCuartelRendimientosByCuartelId(int $cuartelId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT rend_2020_qq_ha, rend_2021_qq_ha, rend_2022_qq_ha, ing_2023_kg, rend_2023_qq_ha,
                    ing_2024_kg, rend_2024_qq_ha, ing_2025_kg, rend_2025_qq_ha, rend_promedio_5anios_qq_ha
             FROM prod_cuartel_rendimientos
             WHERE cuartel_id = :cuartel_id
             LIMIT 1'
        );
        $stmt->execute([':cuartel_id' => $cuartelId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function fetchCuartelRiesgosByCuartelId(int $cuartelId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT tiene_seguro_agricola, porcentaje_dano_granizo, heladas_dano_promedio_5anios,
                    presencia_freatica, plagas_no_convencionales
             FROM prod_cuartel_riesgos
             WHERE cuartel_id = :cuartel_id
             LIMIT 1'
        );
        $stmt->execute([':cuartel_id' => $cuartelId]);
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

    private function cuartelExists(string $cooperativaIdReal, string $idResponsableReal, string $codigoFinca, string $codigoCuartel): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM prod_cuartel
             WHERE cooperativa_id_real = :coop
               AND id_responsable_real = :id_responsable_real
               AND codigo_finca = :codigo_finca
               AND codigo_cuartel = :codigo_cuartel
             LIMIT 1'
        );
        $stmt->execute([
            ':coop' => $cooperativaIdReal,
            ':id_responsable_real' => $idResponsableReal,
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

    private function migrateUserIdReal(int $userId, string $oldIdReal, string $newIdReal): void
    {
        if ($oldIdReal === $newIdReal) {
            return;
        }

        $existing = $this->findUserByIdReal($newIdReal);
        if ($existing !== null && (int)$existing['id'] !== $userId) {
            throw new InvalidArgumentException('No se puede actualizar id_real a "' . $newIdReal . '" porque ya existe en otro usuario.');
        }

        // En esta migración hay FKs sobre usuarios.id_real. Para evitar violaciones
        // intermedias, hacemos el bloque con checks deshabilitados dentro de la sesión.
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS=0');
        try {
            $updates = [
                // Relaciones directas de productor
                'UPDATE rel_productor_coop SET productor_id_real = :new_id_real WHERE productor_id_real = :old_id_real',
                'UPDATE prod_fincas SET productor_id_real = :new_id_real WHERE productor_id_real = :old_id_real',
                'UPDATE rel_productor_finca SET productor_id_real = :new_id_real WHERE productor_id_real = :old_id_real',
                'UPDATE prod_cuartel SET id_responsable_real = :new_id_real WHERE id_responsable_real = :old_id_real',
                'UPDATE drones_solicitud SET productor_id_real = :new_id_real WHERE productor_id_real = :old_id_real',
                // Otras tablas con referencias/sombras a usuarios.id_real detectadas en estructura_bbdd.md
                'UPDATE login_auditoria SET usuario_id_real = :new_id_real WHERE usuario_id_real = :old_id_real',
                'UPDATE rel_productor_coop SET cooperativa_id_real = :new_id_real WHERE cooperativa_id_real = :old_id_real',
                'UPDATE rel_coop_ingeniero SET cooperativa_id_real = :new_id_real WHERE cooperativa_id_real = :old_id_real',
                'UPDATE rel_coop_ingeniero SET ingeniero_id_real = :new_id_real WHERE ingeniero_id_real = :old_id_real',
                'UPDATE operativos_cooperativas_participacion SET cooperativa_id_real = :new_id_real WHERE cooperativa_id_real = :old_id_real',
                'UPDATE prod_cuartel SET cooperativa_id_real = :new_id_real WHERE cooperativa_id_real = :old_id_real',
                'UPDATE cooperativas_rangos SET cooperativa_id_real = :new_id_real WHERE cooperativa_id_real = :old_id_real',
                'UPDATE cosechaMecanica_coop_contrato_firma SET cooperativa_id_real = :new_id_real WHERE cooperativa_id_real = :old_id_real',
                'UPDATE cosechaMecanica_coop_correo_log SET cooperativa_id_real = :new_id_real WHERE cooperativa_id_real = :old_id_real',
                'UPDATE log_correos SET cooperativa_id_real = :new_id_real WHERE cooperativa_id_real = :old_id_real',
            ];

            foreach ($updates as $sql) {
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    ':new_id_real' => $newIdReal,
                    ':old_id_real' => $oldIdReal,
                ]);
            }

            $qUpdateUserIdReal = $this->pdo->prepare('UPDATE usuarios SET id_real = :new_id_real WHERE id = :id');
            $qUpdateUserIdReal->execute([
                ':new_id_real' => $newIdReal,
                ':id' => $userId,
            ]);
        } finally {
            $this->pdo->exec('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    private function syncStrictSnapshot(
        string $cooperativaIdReal,
        array $snapshotProducerIdRealsMap,
        array $snapshotFincaKeysMap,
        array $snapshotCuartelKeysMap,
        bool $dryRun = false
    ): array {
        $deletedCuarteles = 0;
        $deletedFincas = 0;
        $deletedRel = 0;

        $snapshotProducerIdReals = array_values(array_keys($snapshotProducerIdRealsMap));

        $stmtCuarteles = $this->pdo->prepare(
            'SELECT id, id_responsable_real, codigo_finca, codigo_cuartel
             FROM prod_cuartel
             WHERE cooperativa_id_real = :cooperativa_id_real'
        );
        $stmtCuarteles->execute([':cooperativa_id_real' => $cooperativaIdReal]);
        $existingCuarteles = $stmtCuarteles->fetchAll(PDO::FETCH_ASSOC) ?: [];

        foreach ($existingCuarteles as $cuartel) {
            $key = $this->buildSnapshotCuartelKey(
                (string)($cuartel['id_responsable_real'] ?? ''),
                (string)($cuartel['codigo_finca'] ?? ''),
                (string)($cuartel['codigo_cuartel'] ?? '')
            );
            if (!isset($snapshotCuartelKeysMap[$key])) {
                if (!$dryRun) {
                    $this->deleteCuartelById((int)$cuartel['id']);
                }
                $deletedCuarteles++;
            }
        }

        $associatedProducerIdReals = $this->fetchProducerIdRealsByCoop($cooperativaIdReal);
        if (!empty($associatedProducerIdReals)) {
            $ph = implode(',', array_fill(0, count($associatedProducerIdReals), '?'));
            $sql = "SELECT id, productor_id_real, codigo_finca
                    FROM prod_fincas
                    WHERE productor_id_real IN ($ph)";
            $stmtFincas = $this->pdo->prepare($sql);
            $stmtFincas->execute($associatedProducerIdReals);
            $existingFincas = $stmtFincas->fetchAll(PDO::FETCH_ASSOC) ?: [];

            foreach ($existingFincas as $finca) {
                $key = $this->buildSnapshotFincaKey(
                    (string)($finca['productor_id_real'] ?? ''),
                    (string)($finca['codigo_finca'] ?? '')
                );
                if (!isset($snapshotFincaKeysMap[$key])) {
                    if (!$dryRun) {
                        $this->deleteFincaById((int)$finca['id'], (string)$finca['productor_id_real']);
                    }
                    $deletedFincas++;
                }
            }
        }

        if ($dryRun) {
            if (empty($snapshotProducerIdReals)) {
                $stmtRel = $this->pdo->prepare(
                    'SELECT COUNT(*) FROM rel_productor_coop
                     WHERE cooperativa_id_real = :cooperativa_id_real'
                );
                $stmtRel->execute([':cooperativa_id_real' => $cooperativaIdReal]);
                $deletedRel += (int)$stmtRel->fetchColumn();
            } else {
                $ph = implode(',', array_fill(0, count($snapshotProducerIdReals), '?'));
                $sql = "SELECT COUNT(*) FROM rel_productor_coop
                        WHERE cooperativa_id_real = ?
                          AND productor_id_real NOT IN ($ph)";
                $stmtRel = $this->pdo->prepare($sql);
                $stmtRel->execute(array_merge([$cooperativaIdReal], $snapshotProducerIdReals));
                $deletedRel += (int)$stmtRel->fetchColumn();
            }
        } else {
            if (empty($snapshotProducerIdReals)) {
                $stmtRel = $this->pdo->prepare(
                    'DELETE FROM rel_productor_coop
                     WHERE cooperativa_id_real = :cooperativa_id_real'
                );
                $stmtRel->execute([':cooperativa_id_real' => $cooperativaIdReal]);
                $deletedRel += $stmtRel->rowCount();
            } else {
                $ph = implode(',', array_fill(0, count($snapshotProducerIdReals), '?'));
                $sql = "DELETE FROM rel_productor_coop
                        WHERE cooperativa_id_real = ?
                          AND productor_id_real NOT IN ($ph)";
                $stmtRel = $this->pdo->prepare($sql);
                $stmtRel->execute(array_merge([$cooperativaIdReal], $snapshotProducerIdReals));
                $deletedRel += $stmtRel->rowCount();
            }
        }

        return [
            'rel_productor_coop_deleted' => $deletedRel,
            'fincas_deleted' => $deletedFincas,
            'cuarteles_deleted' => $deletedCuarteles,
        ];
    }

    private function buildSnapshotMapsFromProcessable(array $processable): array
    {
        $producers = [];
        $fincas = [];
        $cuarteles = [];

        foreach ($processable as $item) {
            $u = $item['user'] ?? null;
            $r = $item['row'] ?? null;
            $targetIdReal = $this->nullableString($item['target_id_real'] ?? null);

            if ($targetIdReal === null && is_array($u)) {
                $targetIdReal = $this->nullableString($u['id_real'] ?? null);
            }
            if ($targetIdReal === null || !is_array($r)) {
                continue;
            }

            $codigoFinca = $this->nullableString($r['codigo_finca'] ?? null);
            $codigoCuartel = $this->nullableString($r['codigo_cuartel'] ?? null);
            if ($codigoFinca === null || $codigoCuartel === null) {
                continue;
            }

            $producers[$targetIdReal] = true;
            $fincas[$this->buildSnapshotFincaKey($targetIdReal, $codigoFinca)] = true;
            $cuarteles[$this->buildSnapshotCuartelKey($targetIdReal, $codigoFinca, $codigoCuartel)] = true;
        }

        return [
            'producers' => $producers,
            'fincas' => $fincas,
            'cuarteles' => $cuarteles,
        ];
    }

    private function fetchProducerIdRealsByCoop(string $cooperativaIdReal): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT DISTINCT productor_id_real
             FROM rel_productor_coop
             WHERE cooperativa_id_real = :cooperativa_id_real'
        );
        $stmt->execute([':cooperativa_id_real' => $cooperativaIdReal]);
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
        $out = [];
        foreach ($rows as $value) {
            $v = $this->nullableString($value);
            if ($v !== null) {
                $out[$v] = $v;
            }
        }
        return array_values($out);
    }

    private function buildSnapshotFincaKey(string $productorIdReal, string $codigoFinca): string
    {
        return trim($productorIdReal) . '|' . trim($codigoFinca);
    }

    private function buildSnapshotCuartelKey(string $productorIdReal, string $codigoFinca, string $codigoCuartel): string
    {
        return trim($productorIdReal) . '|' . trim($codigoFinca) . '|' . trim($codigoCuartel);
    }

    private function deleteCuartelById(int $cuartelId): void
    {
        if ($cuartelId <= 0) {
            return;
        }

        $queries = [
            'DELETE FROM prod_cuartel_limitantes WHERE cuartel_id = :cid',
            'DELETE FROM prod_cuartel_rendimientos WHERE cuartel_id = :cid',
            'DELETE FROM prod_cuartel_riesgos WHERE cuartel_id = :cid',
            'DELETE FROM prod_cuartel WHERE id = :cid',
        ];

        foreach ($queries as $sql) {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':cid' => $cuartelId]);
        }
    }

    private function deleteFincaById(int $fincaId, string $productorIdReal): void
    {
        if ($fincaId <= 0) {
            return;
        }

        $queries = [
            'DELETE FROM prod_cuartel_limitantes WHERE cuartel_id IN (SELECT id FROM prod_cuartel WHERE finca_id = :fid)',
            'DELETE FROM prod_cuartel_rendimientos WHERE cuartel_id IN (SELECT id FROM prod_cuartel WHERE finca_id = :fid)',
            'DELETE FROM prod_cuartel_riesgos WHERE cuartel_id IN (SELECT id FROM prod_cuartel WHERE finca_id = :fid)',
            'DELETE FROM prod_cuartel WHERE finca_id = :fid',
            'DELETE FROM relevamiento_fincas WHERE finca_id = :fid',
            'DELETE FROM rel_productor_finca WHERE finca_id = :fid',
            'DELETE FROM prod_finca_direccion WHERE finca_id = :fid',
            'DELETE FROM prod_finca_superficie WHERE finca_id = :fid',
            'DELETE FROM prod_finca_cultivos WHERE finca_id = :fid',
            'DELETE FROM prod_finca_agua WHERE finca_id = :fid',
            'DELETE FROM prod_finca_maquinaria WHERE finca_id = :fid',
            'DELETE FROM prod_finca_gerencia WHERE finca_id = :fid',
            'DELETE FROM prod_fincas WHERE id = :fid AND productor_id_real = :prod',
        ];

        foreach ($queries as $sql) {
            $stmt = $this->pdo->prepare($sql);
            $params = [':fid' => $fincaId];
            if (strpos($sql, ':prod') !== false) {
                $params[':prod'] = $productorIdReal;
            }
            $stmt->execute($params);
        }
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
