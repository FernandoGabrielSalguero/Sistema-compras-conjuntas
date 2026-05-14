<?php

declare(strict_types=1);

final class StepEditModel
{
    private PDO $pdo;

    private const PRODUCTOR_TABLES_BY_USER_ID = [
        'usuarios_info' => ['owner' => 'usuario_id', 'annual' => false, 'defaults' => ['zona_asignada' => '']],
        'productores_contactos_alternos' => ['owner' => 'productor_id', 'annual' => false, 'defaults' => []],
        'info_productor' => ['owner' => 'productor_id', 'annual' => true, 'defaults' => []],
        'prod_colaboradores' => ['owner' => 'productor_id', 'annual' => true, 'defaults' => []],
        'prod_hijos' => ['owner' => 'productor_id', 'annual' => true, 'defaults' => []],
    ];

    private const FINCA_TABLES = [
        'prod_finca_direccion' => ['annual' => false],
        'prod_finca_superficie' => ['annual' => true],
        'prod_finca_cultivos' => ['annual' => true],
        'prod_finca_agua' => ['annual' => true],
        'prod_finca_maquinaria' => ['annual' => true],
        'prod_finca_gerencia' => ['annual' => true],
    ];

    private const CUARTEL_TABLES = [
        'prod_cuartel_limitantes',
        'prod_cuartel_rendimientos',
        'prod_cuartel_riesgos',
    ];

    private const SELECT_FIELDS = [
        'acceso_internet', 'vive_en_finca', 'tiene_otra_finca', 'hijos_sobrinos_participan',
        'tiene_flexibilizacion_entrega_agua', 'perforacion_activa_1', 'perforacion_activa_2',
        'agua_analizada', 'utiliza_empresa_servicios', 'posee_deposito_fitosanitarios',
        'analisis_suelo_completo', 'tiene_seguro_agricola',
    ];

    private const DATE_FIELDS = [
        'fecha_nacimiento', 'fecha_nacimiento_1', 'fecha_nacimiento_2', 'fecha_nacimiento_3',
    ];

    private const TEXTAREA_FIELDS = [
        'labores_mecanizables', 'limitantes_suelo', 'observaciones', 'plagas_no_convencionales',
        'inversion_accion1_1', 'obs_inversion_accion1_1', 'inversion_accion2_1',
        'obs_inversion_accion2_1', 'inversion_accion1_2', 'obs_inversion_accion1_2',
        'inversion_accion2_2', 'obs_inversion_accion2_2',
        'prob_gerenciamiento_1', 'prob_personal_1', 'prob_tecnologicos_1',
        'prob_administracion_1', 'prob_medios_produccion_1', 'prob_observacion_1',
        'prob_gerenciamiento_2', 'prob_personal_2', 'prob_tecnologicos_2',
        'prob_administracion_2', 'prob_medios_produccion_2', 'prob_observacion_2',
    ];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public function listarOperativosAbiertos(): array
    {
        $stmt = $this->pdo->query("
            SELECT ro.id, ro.nombre, ro.fecha_inicio, ro.fecha_fin, ro.estado, COUNT(roc.id) AS campos_count
            FROM relevamiento_operativos ro
            LEFT JOIN relevamiento_operativo_campos roc ON roc.operativo_id = ro.id
            WHERE ro.estado = 'abierto'
            GROUP BY ro.id, ro.nombre, ro.fecha_inicio, ro.fecha_fin, ro.estado
            ORDER BY ro.fecha_inicio DESC, ro.id DESC
        ");

        return $stmt->fetchAll() ?: [];
    }

    public function listarCooperativasConAvance(int $operativoId, string $ingenieroIdReal): array
    {
        $campos = $this->getCamposOperativoAbierto($operativoId);
        $coops = $this->getCoopsByIngeniero($ingenieroIdReal);

        foreach ($coops as &$coop) {
            $productores = $this->getProductoresByCooperativa((string)$coop['id_real'], $ingenieroIdReal);
            $ids = array_values(array_map(static fn($p) => (string)$p['id_real'], $productores));
            $coop['productores_count'] = count($ids);
            $coop['avance'] = $this->calcularAvanceParaProductores($operativoId, $campos, $ids);
        }
        unset($coop);

        return $coops;
    }

    public function listarProductoresConAvance(int $operativoId, string $coopIdReal, string $ingenieroIdReal): array
    {
        $this->assertCoopPerteneceAIngeniero($coopIdReal, $ingenieroIdReal);
        $campos = $this->getCamposOperativoAbierto($operativoId);
        $productores = $this->getProductoresByCooperativa($coopIdReal, $ingenieroIdReal);

        foreach ($productores as &$productor) {
            $productor['avance'] = $this->calcularAvanceProductor($operativoId, $campos, (string)$productor['id_real']);
        }
        unset($productor);

        return $productores;
    }

    public function obtenerFormularioProductor(int $operativoId, string $productorIdReal, string $ingenieroIdReal): array
    {
        $campos = $this->getCamposOperativoAbierto($operativoId);
        $this->assertProductorPerteneceAIngeniero($productorIdReal, $ingenieroIdReal);

        $productor = $this->getProductor($productorIdReal);
        $fincas = $this->getFincasProductor($productorIdReal);
        $cuarteles = $this->getCuartelesProductor($productorIdReal);
        $values = $this->getValoresProductor($productorIdReal, $campos, $fincas, $cuarteles);

        return [
            'productor' => $productor,
            'fincas' => $fincas,
            'cuarteles' => $cuarteles,
            'campos' => $this->decorateCampos($campos),
            'values' => $values,
            'avance' => $this->calcularAvanceProductor($operativoId, $campos, $productorIdReal),
        ];
    }

    public function obtenerAvanceGeneral(int $operativoId, string $ingenieroIdReal): array
    {
        $campos = $this->getCamposOperativoAbierto($operativoId);
        $coops = $this->listarCooperativasConAvance($operativoId, $ingenieroIdReal);
        $productorIds = [];
        foreach ($coops as $coop) {
            foreach ($this->getProductoresByCooperativa((string)$coop['id_real'], $ingenieroIdReal) as $productor) {
                $productorIds[] = (string)$productor['id_real'];
            }
        }

        return [
            'general' => $this->calcularAvanceParaProductores($operativoId, $campos, array_values(array_unique($productorIds))),
            'cooperativas' => $coops,
        ];
    }

    public function guardarCampo(array $payload, string $ingenieroIdReal): array
    {
        $operativoId = (int)($payload['operativo_id'] ?? 0);
        $productorIdReal = trim((string)($payload['productor_id_real'] ?? ''));
        $tabla = trim((string)($payload['tabla'] ?? ''));
        $campo = trim((string)($payload['campo'] ?? ''));
        $alcance = trim((string)($payload['alcance'] ?? ''));
        $entityId = (int)($payload['entity_id'] ?? 0);
        $value = $this->normalizeValue($payload['value'] ?? null);

        if ($operativoId <= 0 || $productorIdReal === '' || $tabla === '' || $campo === '') {
            throw new InvalidArgumentException('Datos incompletos para guardar el campo');
        }

        $campos = $this->getCamposOperativoAbierto($operativoId);
        $campoOperativo = $this->findCampoOperativo($campos, $tabla, $campo, $alcance);
        if (!$campoOperativo) {
            throw new RuntimeException('El campo no pertenece al operativo seleccionado');
        }

        $this->assertProductorPerteneceAIngeniero($productorIdReal, $ingenieroIdReal);
        $productor = $this->getProductor($productorIdReal);

        $this->pdo->beginTransaction();
        try {
            $oldValue = $this->getCampoValor($productor, $tabla, $campo, $alcance, $entityId, $productorIdReal);
            $ids = $this->guardarValor($productor, $tabla, $campo, $alcance, $entityId, $productorIdReal, $value);
            $this->registrarCambio($operativoId, $ingenieroIdReal, $productorIdReal, $tabla, $campo, $oldValue, $value, $ids);
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }

        return [
            'campo' => $campoOperativo,
            'value' => $value,
            'avance' => $this->calcularAvanceProductor($operativoId, $campos, $productorIdReal),
        ];
    }

    private function getCamposOperativoAbierto(int $operativoId): array
    {
        if ($operativoId <= 0) {
            throw new InvalidArgumentException('Operativo invalido');
        }

        $op = $this->pdo->prepare("SELECT id FROM relevamiento_operativos WHERE id = :id AND estado = 'abierto' LIMIT 1");
        $op->execute([':id' => $operativoId]);
        if (!$op->fetchColumn()) {
            throw new RuntimeException('El operativo no esta abierto');
        }

        $stmt = $this->pdo->prepare("
            SELECT tabla, campo, etiqueta, grupo, alcance, obligatorio, orden
            FROM relevamiento_operativo_campos
            WHERE operativo_id = :id
            ORDER BY orden ASC, id ASC
        ");
        $stmt->execute([':id' => $operativoId]);
        return $stmt->fetchAll() ?: [];
    }

    private function getCoopsByIngeniero(string $ingenieroIdReal): array
    {
        $stmt = $this->pdo->prepare("
            SELECT u.id_real,
                   COALESCE(NULLIF(TRIM(ui.nombre), ''), NULLIF(TRIM(u.razon_social), ''), NULLIF(TRIM(u.usuario), ''), u.id_real) AS nombre,
                   NULLIF(NULLIF(TRIM(CAST(u.cuit AS CHAR)), ''), '0') AS cuit
            FROM rel_coop_ingeniero rci
            JOIN usuarios u ON u.id_real = rci.cooperativa_id_real AND u.rol = 'cooperativa'
            LEFT JOIN usuarios_info ui ON ui.usuario_id = u.id
            WHERE rci.ingeniero_id_real = :ing
            ORDER BY nombre ASC
        ");
        $stmt->execute([':ing' => $ingenieroIdReal]);
        return $stmt->fetchAll() ?: [];
    }

    private function getProductoresByCooperativa(string $coopIdReal, string $ingenieroIdReal): array
    {
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT rpc.productor_id_real AS id_real,
                   COALESCE(NULLIF(TRIM(ui.nombre), ''), NULLIF(TRIM(u.razon_social), ''), NULLIF(TRIM(u.usuario), ''), rpc.productor_id_real) AS nombre,
                   NULLIF(NULLIF(TRIM(CAST(u.cuit AS CHAR)), ''), '0') AS cuit
            FROM rel_productor_coop rpc
            JOIN rel_coop_ingeniero rci ON rci.cooperativa_id_real = rpc.cooperativa_id_real
            JOIN usuarios u ON u.id_real = rpc.productor_id_real AND u.rol = 'productor'
            LEFT JOIN usuarios_info ui ON ui.usuario_id = u.id
            WHERE rpc.cooperativa_id_real = :coop
              AND rci.ingeniero_id_real = :ing
              AND COALESCE(u.archivado, 0) = 0
            ORDER BY nombre ASC
        ");
        $stmt->execute([':coop' => $coopIdReal, ':ing' => $ingenieroIdReal]);
        return $stmt->fetchAll() ?: [];
    }

    private function getProductor(string $productorIdReal): array
    {
        $stmt = $this->pdo->prepare("
            SELECT u.id, u.id_real, u.cuit, u.razon_social,
                   COALESCE(NULLIF(TRIM(ui.nombre), ''), NULLIF(TRIM(u.razon_social), ''), NULLIF(TRIM(u.usuario), ''), u.id_real) AS nombre
            FROM usuarios u
            LEFT JOIN usuarios_info ui ON ui.usuario_id = u.id
            WHERE u.id_real = :id_real AND u.rol = 'productor'
            LIMIT 1
        ");
        $stmt->execute([':id_real' => $productorIdReal]);
        $row = $stmt->fetch();
        if (!$row) {
            throw new RuntimeException('Productor no encontrado');
        }
        return $row;
    }

    private function getFincasProductor(string $productorIdReal): array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, codigo_finca, nombre_finca
            FROM prod_fincas
            WHERE productor_id_real = :prod AND COALESCE(archivado, 0) = 0
            ORDER BY codigo_finca ASC, id ASC
        ");
        $stmt->execute([':prod' => $productorIdReal]);
        return $stmt->fetchAll() ?: [];
    }

    private function getCuartelesProductor(string $productorIdReal): array
    {
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT pc.id, pc.finca_id, pc.codigo_finca, pc.nombre_finca, pc.codigo_cuartel, pc.variedad
            FROM prod_cuartel pc
            LEFT JOIN prod_fincas pf ON pf.id = pc.finca_id
            WHERE (pc.id_responsable_real = :prod OR pf.productor_id_real = :prod)
              AND COALESCE(pc.archivado, 0) = 0
            ORDER BY pc.codigo_finca ASC, pc.codigo_cuartel ASC, pc.id ASC
        ");
        $stmt->execute([':prod' => $productorIdReal]);
        return $stmt->fetchAll() ?: [];
    }

    private function getValoresProductor(string $productorIdReal, array $campos, array $fincas, array $cuarteles): array
    {
        $productor = $this->getProductor($productorIdReal);
        $values = ['productor' => [], 'finca' => [], 'cuartel' => []];

        foreach ($campos as $campo) {
            $tabla = (string)$campo['tabla'];
            $field = (string)$campo['campo'];
            $alcance = (string)$campo['alcance'];
            $key = $tabla . '.' . $field;

            if ($alcance === 'productor') {
                $values['productor'][$key] = $this->getCampoValor($productor, $tabla, $field, $alcance, 0, $productorIdReal);
            } elseif ($alcance === 'finca') {
                foreach ($fincas as $finca) {
                    $fincaId = (int)$finca['id'];
                    $values['finca'][$fincaId][$key] = $this->getCampoValor($productor, $tabla, $field, $alcance, $fincaId, $productorIdReal);
                }
            } elseif ($alcance === 'cuartel') {
                foreach ($cuarteles as $cuartel) {
                    $cuartelId = (int)$cuartel['id'];
                    $values['cuartel'][$cuartelId][$key] = $this->getCampoValor($productor, $tabla, $field, $alcance, $cuartelId, $productorIdReal);
                }
            }
        }

        return $values;
    }

    private function getCampoValor(array $productor, string $tabla, string $campo, string $alcance, int $entityId, string $productorIdReal)
    {
        $this->assertSafeIdentifier($tabla);
        $this->assertSafeIdentifier($campo);

        if ($tabla === 'usuarios') {
            return $this->fetchScalar("SELECT {$this->qid($campo)} FROM usuarios WHERE id = :id LIMIT 1", [':id' => (int)$productor['id']]);
        }

        if ($alcance === 'productor' && isset(self::PRODUCTOR_TABLES_BY_USER_ID[$tabla])) {
            $meta = self::PRODUCTOR_TABLES_BY_USER_ID[$tabla];
            $order = $meta['annual'] ? ' ORDER BY anio DESC, id DESC' : '';
            return $this->fetchScalar("SELECT {$this->qid($campo)} FROM {$tabla} WHERE {$meta['owner']} = :owner{$order} LIMIT 1", [':owner' => (int)$productor['id']]);
        }

        if ($alcance === 'finca') {
            $this->assertFincaPerteneceAProductor($entityId, $productorIdReal);
            if ($tabla === 'prod_fincas') {
                return $this->fetchScalar("SELECT {$this->qid($campo)} FROM prod_fincas WHERE id = :id LIMIT 1", [':id' => $entityId]);
            }
            if (isset(self::FINCA_TABLES[$tabla])) {
                $order = self::FINCA_TABLES[$tabla]['annual'] ? ' ORDER BY anio DESC, id DESC' : '';
                return $this->fetchScalar("SELECT {$this->qid($campo)} FROM {$tabla} WHERE finca_id = :id{$order} LIMIT 1", [':id' => $entityId]);
            }
        }

        if ($alcance === 'cuartel') {
            $this->assertCuartelPerteneceAProductor($entityId, $productorIdReal);
            if ($tabla === 'prod_cuartel') {
                return $this->fetchScalar("SELECT {$this->qid($campo)} FROM prod_cuartel WHERE id = :id LIMIT 1", [':id' => $entityId]);
            }
            if (in_array($tabla, self::CUARTEL_TABLES, true)) {
                return $this->fetchScalar("SELECT {$this->qid($campo)} FROM {$tabla} WHERE cuartel_id = :id LIMIT 1", [':id' => $entityId]);
            }
        }

        return null;
    }

    private function guardarValor(array $productor, string $tabla, string $campo, string $alcance, int $entityId, string $productorIdReal, $value): array
    {
        $this->assertSafeIdentifier($tabla);
        $this->assertSafeIdentifier($campo);

        if ($tabla === 'usuarios') {
            $this->updateById('usuarios', (int)$productor['id'], $campo, $value);
            return ['productor_id_real' => $productorIdReal];
        }

        if ($alcance === 'productor' && isset(self::PRODUCTOR_TABLES_BY_USER_ID[$tabla])) {
            $this->upsertOwnedRow($tabla, self::PRODUCTOR_TABLES_BY_USER_ID[$tabla], (int)$productor['id'], $campo, $value);
            return ['productor_id_real' => $productorIdReal];
        }

        if ($alcance === 'finca') {
            $this->assertFincaPerteneceAProductor($entityId, $productorIdReal);
            if ($tabla === 'prod_fincas') {
                $this->updateById('prod_fincas', $entityId, $campo, $value);
            } elseif (isset(self::FINCA_TABLES[$tabla])) {
                $this->upsertFincaRow($tabla, self::FINCA_TABLES[$tabla]['annual'], $entityId, $campo, $value);
            } else {
                throw new RuntimeException('Tabla de finca no habilitada');
            }
            return ['productor_id_real' => $productorIdReal, 'finca_id' => $entityId];
        }

        if ($alcance === 'cuartel') {
            $this->assertCuartelPerteneceAProductor($entityId, $productorIdReal);
            if ($tabla === 'prod_cuartel') {
                $this->updateById('prod_cuartel', $entityId, $campo, $value);
            } elseif (in_array($tabla, self::CUARTEL_TABLES, true)) {
                $this->upsertCuartelRow($tabla, $entityId, $campo, $value);
            } else {
                throw new RuntimeException('Tabla de cuartel no habilitada');
            }
            return ['productor_id_real' => $productorIdReal, 'cuartel_id' => $entityId];
        }

        throw new RuntimeException('Alcance no habilitado');
    }

    private function calcularAvanceParaProductores(int $operativoId, array $campos, array $productorIdsReal): array
    {
        $total = ['esperados' => 0, 'completos' => 0, 'auditados' => 0, 'completitud_pct' => 0.0, 'actividad_pct' => 0.0];
        foreach ($productorIdsReal as $idReal) {
            $avance = $this->calcularAvanceProductor($operativoId, $campos, (string)$idReal);
            $total['esperados'] += $avance['esperados'];
            $total['completos'] += $avance['completos'];
            $total['auditados'] += $avance['auditados'];
        }
        $total['completitud_pct'] = $total['esperados'] > 0 ? round(($total['completos'] / $total['esperados']) * 100, 2) : 0.0;
        $total['actividad_pct'] = $total['esperados'] > 0 ? round(($total['auditados'] / $total['esperados']) * 100, 2) : 0.0;
        return $total;
    }

    private function calcularAvanceProductor(int $operativoId, array $campos, string $productorIdReal): array
    {
        $productor = $this->getProductor($productorIdReal);
        $fincas = $this->getFincasProductor($productorIdReal);
        $cuarteles = $this->getCuartelesProductor($productorIdReal);
        $esperados = 0;
        $completos = 0;

        foreach ($campos as $campo) {
            $alcance = (string)$campo['alcance'];
            $tabla = (string)$campo['tabla'];
            $field = (string)$campo['campo'];
            if ($alcance === 'productor') {
                $esperados++;
                if ($this->isFilled($this->getCampoValor($productor, $tabla, $field, $alcance, 0, $productorIdReal))) {
                    $completos++;
                }
            } elseif ($alcance === 'finca') {
                foreach ($fincas as $finca) {
                    $esperados++;
                    if ($this->isFilled($this->getCampoValor($productor, $tabla, $field, $alcance, (int)$finca['id'], $productorIdReal))) {
                        $completos++;
                    }
                }
            } elseif ($alcance === 'cuartel') {
                foreach ($cuarteles as $cuartel) {
                    $esperados++;
                    if ($this->isFilled($this->getCampoValor($productor, $tabla, $field, $alcance, (int)$cuartel['id'], $productorIdReal))) {
                        $completos++;
                    }
                }
            }
        }

        $auditados = $this->contarAuditadosProductor($operativoId, $campos, $productorIdReal);

        return [
            'esperados' => $esperados,
            'completos' => $completos,
            'auditados' => $auditados,
            'pendientes' => max(0, $esperados - $completos),
            'completitud_pct' => $esperados > 0 ? round(($completos / $esperados) * 100, 2) : 0.0,
            'actividad_pct' => $esperados > 0 ? round(($auditados / $esperados) * 100, 2) : 0.0,
        ];
    }

    private function contarAuditadosProductor(int $operativoId, array $campos, string $productorIdReal): int
    {
        $scope = [];
        foreach ($campos as $campo) {
            $scope[$campo['tabla'] . '.' . $campo['campo']] = (string)$campo['alcance'];
        }

        $stmt = $this->pdo->prepare("
            SELECT tabla, campo, finca_id, cuartel_id
            FROM relevamiento_cambios
            WHERE operativo_id = :op AND productor_id_real = :prod
        ");
        $stmt->execute([':op' => $operativoId, ':prod' => $productorIdReal]);
        $seen = [];
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $key = $row['tabla'] . '.' . $row['campo'];
            if (!isset($scope[$key])) {
                continue;
            }
            $entity = 'p';
            if ($scope[$key] === 'finca') {
                $entity = 'f' . (string)($row['finca_id'] ?? '');
            } elseif ($scope[$key] === 'cuartel') {
                $entity = 'c' . (string)($row['cuartel_id'] ?? '');
            }
            $seen[$key . '|' . $entity] = true;
        }
        return count($seen);
    }

    private function decorateCampos(array $campos): array
    {
        foreach ($campos as &$campo) {
            $field = (string)$campo['campo'];
            $campo['key'] = $campo['tabla'] . '.' . $field;
            $campo['input_type'] = $this->inputTypeFor($field);
            $campo['options'] = in_array($field, self::SELECT_FIELDS, true) ? [
                ['value' => '', 'label' => 'Sin completar'],
                ['value' => 'si', 'label' => 'Si'],
                ['value' => 'no', 'label' => 'No'],
                ['value' => 'nsnc', 'label' => 'NS/NC'],
            ] : [];
        }
        unset($campo);
        return $campos;
    }

    private function inputTypeFor(string $field): string
    {
        if (in_array($field, self::TEXTAREA_FIELDS, true)) {
            return 'textarea';
        }
        if (in_array($field, self::DATE_FIELDS, true)) {
            return 'date';
        }
        if (in_array($field, self::SELECT_FIELDS, true)) {
            return 'select';
        }
        if (preg_match('/(^sup_|_ha$|porcentaje|cantidad|anio|edad|rend_|ing_|conductividad|mujeres_|hombres_|trabajadores_)/', $field)) {
            return 'number';
        }
        return 'text';
    }

    private function updateById(string $table, int $id, string $field, $value): void
    {
        $stmt = $this->pdo->prepare("UPDATE {$table} SET {$this->qid($field)} = :value WHERE id = :id");
        $stmt->execute([':value' => $value, ':id' => $id]);
    }

    private function upsertOwnedRow(string $table, array $meta, int $ownerId, string $field, $value): void
    {
        $owner = $meta['owner'];
        $order = $meta['annual'] ? ' ORDER BY anio DESC, id DESC' : '';
        $stmt = $this->pdo->prepare("SELECT id FROM {$table} WHERE {$owner} = :owner{$order} LIMIT 1");
        $stmt->execute([':owner' => $ownerId]);
        $id = (int)($stmt->fetchColumn() ?: 0);
        if ($id > 0) {
            $this->updateById($table, $id, $field, $value);
            return;
        }

        $cols = [$owner => $ownerId, $field => $value];
        if ($meta['annual']) {
            $cols['anio'] = (int)date('Y');
        }
        foreach (($meta['defaults'] ?? []) as $key => $default) {
            $cols[$key] = $default;
        }
        $this->insertRow($table, $cols);
    }

    private function upsertFincaRow(string $table, bool $annual, int $fincaId, string $field, $value): void
    {
        $order = $annual ? ' ORDER BY anio DESC, id DESC' : '';
        $stmt = $this->pdo->prepare("SELECT id FROM {$table} WHERE finca_id = :id{$order} LIMIT 1");
        $stmt->execute([':id' => $fincaId]);
        $id = (int)($stmt->fetchColumn() ?: 0);
        if ($id > 0) {
            $this->updateById($table, $id, $field, $value);
            return;
        }

        $cols = ['finca_id' => $fincaId, $field => $value];
        if ($annual) {
            $cols['anio'] = (int)date('Y');
        }
        $this->insertRow($table, $cols);
    }

    private function upsertCuartelRow(string $table, int $cuartelId, string $field, $value): void
    {
        $stmt = $this->pdo->prepare("SELECT id FROM {$table} WHERE cuartel_id = :id LIMIT 1");
        $stmt->execute([':id' => $cuartelId]);
        $id = (int)($stmt->fetchColumn() ?: 0);
        if ($id > 0) {
            $this->updateById($table, $id, $field, $value);
            return;
        }

        $this->insertRow($table, ['cuartel_id' => $cuartelId, $field => $value]);
    }

    private function insertRow(string $table, array $cols): void
    {
        $names = array_keys($cols);
        foreach ($names as $name) {
            $this->assertSafeIdentifier((string)$name);
        }
        $sql = "INSERT INTO {$table} (" . implode(', ', array_map([$this, 'qid'], $names)) . ")
                VALUES (:" . implode(', :', $names) . ")";
        $stmt = $this->pdo->prepare($sql);
        foreach ($cols as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();
    }

    private function registrarCambio(int $operativoId, string $ingenieroIdReal, string $productorIdReal, string $tabla, string $campo, $oldValue, $newValue, array $ids): void
    {
        if ((string)$oldValue === (string)$newValue) {
            return;
        }
        $stmt = $this->pdo->prepare("
            INSERT INTO relevamiento_cambios
                (operativo_id, ingeniero_id_real, usuario_id_real, usuario_rol, productor_id_real, finca_id, cuartel_id, tabla, campo, valor_anterior, valor_nuevo)
            VALUES
                (:operativo_id, :ingeniero_id_real, :usuario_id_real, 'ingeniero', :productor_id_real, :finca_id, :cuartel_id, :tabla, :campo, :valor_anterior, :valor_nuevo)
        ");
        $stmt->execute([
            ':operativo_id' => $operativoId,
            ':ingeniero_id_real' => $ingenieroIdReal,
            ':usuario_id_real' => $ingenieroIdReal,
            ':productor_id_real' => $productorIdReal,
            ':finca_id' => $ids['finca_id'] ?? null,
            ':cuartel_id' => $ids['cuartel_id'] ?? null,
            ':tabla' => $tabla,
            ':campo' => $campo,
            ':valor_anterior' => $oldValue,
            ':valor_nuevo' => $newValue,
        ]);
    }

    private function assertCoopPerteneceAIngeniero(string $coopIdReal, string $ingenieroIdReal): void
    {
        $stmt = $this->pdo->prepare("SELECT 1 FROM rel_coop_ingeniero WHERE cooperativa_id_real = :coop AND ingeniero_id_real = :ing LIMIT 1");
        $stmt->execute([':coop' => $coopIdReal, ':ing' => $ingenieroIdReal]);
        if (!$stmt->fetchColumn()) {
            throw new RuntimeException('No autorizado para esta cooperativa');
        }
    }

    private function assertProductorPerteneceAIngeniero(string $productorIdReal, string $ingenieroIdReal): void
    {
        $stmt = $this->pdo->prepare("
            SELECT 1
            FROM rel_productor_coop rpc
            JOIN rel_coop_ingeniero rci ON rci.cooperativa_id_real = rpc.cooperativa_id_real
            JOIN usuarios u ON u.id_real = rpc.productor_id_real AND u.rol = 'productor'
            WHERE rpc.productor_id_real = :prod
              AND rci.ingeniero_id_real = :ing
              AND COALESCE(u.archivado, 0) = 0
            LIMIT 1
        ");
        $stmt->execute([':prod' => $productorIdReal, ':ing' => $ingenieroIdReal]);
        if (!$stmt->fetchColumn()) {
            throw new RuntimeException('No autorizado para este productor');
        }
    }

    private function assertFincaPerteneceAProductor(int $fincaId, string $productorIdReal): void
    {
        if ($fincaId <= 0) {
            throw new InvalidArgumentException('Finca invalida');
        }
        $stmt = $this->pdo->prepare("SELECT 1 FROM prod_fincas WHERE id = :id AND productor_id_real = :prod AND COALESCE(archivado, 0) = 0 LIMIT 1");
        $stmt->execute([':id' => $fincaId, ':prod' => $productorIdReal]);
        if (!$stmt->fetchColumn()) {
            throw new RuntimeException('La finca no pertenece al productor');
        }
    }

    private function assertCuartelPerteneceAProductor(int $cuartelId, string $productorIdReal): void
    {
        if ($cuartelId <= 0) {
            throw new InvalidArgumentException('Cuartel invalido');
        }
        $stmt = $this->pdo->prepare("
            SELECT 1
            FROM prod_cuartel pc
            LEFT JOIN prod_fincas pf ON pf.id = pc.finca_id
            WHERE pc.id = :id
              AND (pc.id_responsable_real = :prod OR pf.productor_id_real = :prod)
              AND COALESCE(pc.archivado, 0) = 0
            LIMIT 1
        ");
        $stmt->execute([':id' => $cuartelId, ':prod' => $productorIdReal]);
        if (!$stmt->fetchColumn()) {
            throw new RuntimeException('El cuartel no pertenece al productor');
        }
    }

    private function findCampoOperativo(array $campos, string $tabla, string $campo, string $alcance): ?array
    {
        foreach ($campos as $item) {
            if ((string)$item['tabla'] === $tabla && (string)$item['campo'] === $campo && (string)$item['alcance'] === $alcance) {
                return $item;
            }
        }
        return null;
    }

    private function fetchScalar(string $sql, array $params)
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $value = $stmt->fetchColumn();
        return $value === false ? null : $value;
    }

    private function normalizeValue($value)
    {
        if ($value === null) {
            return null;
        }
        $value = is_string($value) ? trim($value) : $value;
        return $value === '' ? null : $value;
    }

    private function isFilled($value): bool
    {
        return $value !== null && trim((string)$value) !== '';
    }

    private function assertSafeIdentifier(string $identifier): void
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $identifier)) {
            throw new InvalidArgumentException('Identificador invalido');
        }
    }

    private function qid(string $identifier): string
    {
        $this->assertSafeIdentifier($identifier);
        return '`' . $identifier . '`';
    }
}
