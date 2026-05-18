<?php

declare(strict_types=1);

final class StepEditModel
{
    private PDO $pdo;
    private array $rowCache = [];
    private array $ownershipCache = [];

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
        $this->getCamposOperativoAbierto($operativoId);
        return $this->getCoopsByIngenieroConAvance($operativoId, $ingenieroIdReal);
    }

    public function listarCooperativasLivianas(int $operativoId, string $ingenieroIdReal): array
    {
        $this->getCamposOperativoAbierto($operativoId);
        return $this->getCoopsByIngenieroConAvance($operativoId, $ingenieroIdReal);
    }

    public function listarProductoresConAvance(int $operativoId, string $coopIdReal, string $ingenieroIdReal): array
    {
        $this->assertCoopPerteneceAIngeniero($coopIdReal, $ingenieroIdReal);
        $this->getCamposOperativoAbierto($operativoId);
        $productores = $this->getProductoresByCooperativa($coopIdReal, $ingenieroIdReal);

        $ids = array_values(array_map(static fn($p) => (string)$p['id_real'], $productores));
        $estadoMap = $this->getEstadosProductores($operativoId, $ids);
        foreach ($productores as &$productor) {
            $estado = $estadoMap[(string)$productor['id_real']] ?? 'en_progreso';
            $productor['estado_relevamiento'] = $estado;
            $productor['estado_relevamiento_label'] = $this->estadoLabel($estado);
            $productor['avance'] = $this->avanceDesdeEstado($estado);
        }
        unset($productor);

        return $productores;
    }

    public function listarProductoresLivianos(int $operativoId, string $coopIdReal, string $ingenieroIdReal): array
    {
        $this->getCamposOperativoAbierto($operativoId);
        $this->assertCoopPerteneceAIngeniero($coopIdReal, $ingenieroIdReal);
        return $this->getProductoresByCooperativaConEstado($operativoId, $coopIdReal, $ingenieroIdReal);
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
            'estado_relevamiento' => $this->obtenerEstadoProductor($operativoId, $productorIdReal, $ingenieroIdReal),
        ];
    }

    public function obtenerAvanceCooperativa(int $operativoId, string $coopIdReal, string $ingenieroIdReal): array
    {
        $this->assertCoopPerteneceAIngeniero($coopIdReal, $ingenieroIdReal);
        $this->getCamposOperativoAbierto($operativoId);
        return $this->calcularAvanceCooperativaAgregado($operativoId, $coopIdReal, $ingenieroIdReal);
    }

    public function obtenerAvanceProductorPublico(int $operativoId, string $productorIdReal, string $ingenieroIdReal): array
    {
        $this->assertProductorPerteneceAIngeniero($productorIdReal, $ingenieroIdReal);
        $this->getCamposOperativoAbierto($operativoId);
        $estado = $this->getEstadoProductor($operativoId, $productorIdReal);
        return $this->avanceDesdeEstado($estado);
    }

    public function obtenerAvanceGeneral(int $operativoId, string $ingenieroIdReal): array
    {
        $this->getCamposOperativoAbierto($operativoId);
        $coops = $this->getCoopsByIngenieroConAvance($operativoId, $ingenieroIdReal);

        return [
            'general' => $this->calcularAvanceGeneralAgregado($operativoId, $ingenieroIdReal),
            'cooperativas' => $coops,
        ];
    }

    public function obtenerEstadoProductor(int $operativoId, string $productorIdReal, string $ingenieroIdReal): array
    {
        $this->getCamposOperativoAbierto($operativoId);
        $productorIdReal = trim($productorIdReal);
        if ($productorIdReal === '') {
            throw new InvalidArgumentException('Productor invalido');
        }
        $this->assertProductorPerteneceAIngeniero($productorIdReal, $ingenieroIdReal);
        $estado = $this->getEstadoProductor($operativoId, $productorIdReal);

        return [
            'estado' => $estado,
            'label' => $this->estadoLabel($estado),
        ];
    }

    public function guardarEstadoProductor(array $payload, string $ingenieroIdReal): array
    {
        $operativoId = (int)($payload['operativo_id'] ?? 0);
        $productorIdReal = trim((string)($payload['productor_id_real'] ?? ''));
        $estado = trim((string)($payload['estado'] ?? ''));

        $this->getCamposOperativoAbierto($operativoId);
        if ($productorIdReal === '') {
            throw new InvalidArgumentException('Productor invalido');
        }
        if (!in_array($estado, ['en_progreso', 'completado'], true)) {
            throw new InvalidArgumentException('Estado invalido');
        }

        $this->assertProductorPerteneceAIngeniero($productorIdReal, $ingenieroIdReal);

        $stmt = $this->pdo->prepare("
            INSERT INTO relevamiento_productor_estados
                (operativo_id, productor_id_real, estado, updated_by_real)
            VALUES
                (:operativo_id, :productor_id_real, :estado, :updated_by_real)
            ON DUPLICATE KEY UPDATE
                estado = VALUES(estado),
                updated_by_real = VALUES(updated_by_real),
                updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->execute([
            ':operativo_id' => $operativoId,
            ':productor_id_real' => $productorIdReal,
            ':estado' => $estado,
            ':updated_by_real' => $ingenieroIdReal,
        ]);

        return [
            'estado' => $estado,
            'label' => $this->estadoLabel($estado),
            'avance' => $this->avanceDesdeEstado($estado),
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
            'saved' => true,
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

    private function getEstadosProductores(int $operativoId, array $productorIdsReal): array
    {
        $ids = array_values(array_unique(array_filter(array_map(static fn($id) => trim((string)$id), $productorIdsReal))));
        if (!$ids) {
            return [];
        }

        $placeholders = [];
        $params = [':operativo_id' => $operativoId];
        foreach ($ids as $idx => $idReal) {
            $key = ':prod' . $idx;
            $placeholders[] = $key;
            $params[$key] = $idReal;
        }

        $stmt = $this->pdo->prepare("
            SELECT productor_id_real, estado
            FROM relevamiento_productor_estados
            WHERE operativo_id = :operativo_id
              AND productor_id_real IN (" . implode(',', $placeholders) . ")
        ");
        $stmt->execute($params);

        $map = [];
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $map[(string)$row['productor_id_real']] = (string)$row['estado'];
        }

        return $map;
    }

    private function getEstadoProductor(int $operativoId, string $productorIdReal): string
    {
        $stmt = $this->pdo->prepare("
            SELECT estado
            FROM relevamiento_productor_estados
            WHERE operativo_id = :operativo_id
              AND productor_id_real = :productor_id_real
            LIMIT 1
        ");
        $stmt->execute([
            ':operativo_id' => $operativoId,
            ':productor_id_real' => $productorIdReal,
        ]);
        $estado = (string)($stmt->fetchColumn() ?: 'en_progreso');
        return in_array($estado, ['en_progreso', 'completado'], true) ? $estado : 'en_progreso';
    }

    private function calcularAvanceEstadosProductores(int $operativoId, array $productorIdsReal): array
    {
        $ids = array_values(array_unique(array_filter(array_map(static fn($id) => trim((string)$id), $productorIdsReal))));
        $total = count($ids);
        if ($total === 0) {
            return [
                'esperados' => 0,
                'completos' => 0,
                'auditados' => 0,
                'pendientes' => 0,
                'en_progreso' => 0,
                'completitud_pct' => 0.0,
                'actividad_pct' => 0.0,
                'medicion' => 'productores',
            ];
        }

        $estados = $this->getEstadosProductores($operativoId, $ids);
        $completos = 0;
        foreach ($ids as $idReal) {
            if (($estados[$idReal] ?? 'en_progreso') === 'completado') {
                $completos++;
            }
        }
        $enProgreso = max(0, $total - $completos);

        return [
            'esperados' => $total,
            'completos' => $completos,
            'auditados' => $completos,
            'pendientes' => $enProgreso,
            'en_progreso' => $enProgreso,
            'completitud_pct' => round(($completos / $total) * 100, 2),
            'actividad_pct' => round(($completos / $total) * 100, 2),
            'medicion' => 'productores',
        ];
    }

    private function buildAvanceFromCounts(int $total, int $completos): array
    {
        $enProgreso = max(0, $total - $completos);

        return [
            'esperados' => $total,
            'completos' => $completos,
            'auditados' => $completos,
            'pendientes' => $enProgreso,
            'en_progreso' => $enProgreso,
            'completitud_pct' => $total > 0 ? round(($completos / $total) * 100, 2) : 0.0,
            'actividad_pct' => $total > 0 ? round(($completos / $total) * 100, 2) : 0.0,
            'medicion' => 'productores',
        ];
    }

    private function calcularAvanceCooperativaAgregado(int $operativoId, string $coopIdReal, string $ingenieroIdReal): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                COUNT(rp.productor_id_real) AS total_productores,
                COALESCE(SUM(CASE WHEN rpe.estado = 'completado' THEN 1 ELSE 0 END), 0) AS completados
            FROM (
                SELECT DISTINCT rpc.cooperativa_id_real, rpc.productor_id_real
                FROM rel_productor_coop rpc
                JOIN usuarios up ON up.id_real = rpc.productor_id_real
                    AND up.rol = 'productor'
                    AND up.archivado = 0
                WHERE rpc.cooperativa_id_real = :coop
            ) rp
            JOIN rel_coop_ingeniero rci ON rci.cooperativa_id_real = rp.cooperativa_id_real
                AND rci.ingeniero_id_real = :ing
            LEFT JOIN relevamiento_productor_estados rpe ON rpe.operativo_id = :op
                AND rpe.productor_id_real = rp.productor_id_real
        ");
        $stmt->execute([':coop' => $coopIdReal, ':ing' => $ingenieroIdReal, ':op' => $operativoId]);
        $row = $stmt->fetch() ?: [];

        return $this->buildAvanceFromCounts((int)($row['total_productores'] ?? 0), (int)($row['completados'] ?? 0));
    }

    private function calcularAvanceGeneralAgregado(int $operativoId, string $ingenieroIdReal): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                COUNT(rp.productor_id_real) AS total_productores,
                COALESCE(SUM(CASE WHEN rpe.estado = 'completado' THEN 1 ELSE 0 END), 0) AS completados
            FROM rel_coop_ingeniero rci
            JOIN (
                SELECT DISTINCT rpc.cooperativa_id_real, rpc.productor_id_real
                FROM rel_productor_coop rpc
                JOIN usuarios up ON up.id_real = rpc.productor_id_real
                    AND up.rol = 'productor'
                    AND up.archivado = 0
            ) rp ON rp.cooperativa_id_real = rci.cooperativa_id_real
            LEFT JOIN relevamiento_productor_estados rpe ON rpe.operativo_id = :op
                AND rpe.productor_id_real = rp.productor_id_real
            WHERE rci.ingeniero_id_real = :ing
        ");
        $stmt->execute([':op' => $operativoId, ':ing' => $ingenieroIdReal]);
        $row = $stmt->fetch() ?: [];

        return $this->buildAvanceFromCounts((int)($row['total_productores'] ?? 0), (int)($row['completados'] ?? 0));
    }

    private function avanceDesdeEstado(string $estado): array
    {
        $completo = $estado === 'completado' ? 1 : 0;
        return [
            'esperados' => 1,
            'completos' => $completo,
            'auditados' => $completo,
            'pendientes' => $completo ? 0 : 1,
            'en_progreso' => $completo ? 0 : 1,
            'completitud_pct' => $completo ? 100.0 : 0.0,
            'actividad_pct' => $completo ? 100.0 : 0.0,
            'medicion' => 'productores',
        ];
    }

    private function estadoLabel(string $estado): string
    {
        return $estado === 'completado' ? 'Completado' : 'En progreso';
    }

    private function getCoopsByIngenieroConAvance(int $operativoId, string $ingenieroIdReal): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                u.id_real,
                COALESCE(NULLIF(TRIM(ui.nombre), ''), NULLIF(TRIM(u.razon_social), ''), NULLIF(TRIM(u.usuario), ''), u.id_real) AS nombre,
                NULLIF(NULLIF(TRIM(CAST(u.cuit AS CHAR)), ''), '0') AS cuit,
                COUNT(rp.productor_id_real) AS productores_count,
                COALESCE(SUM(CASE WHEN rpe.estado = 'completado' THEN 1 ELSE 0 END), 0) AS completados
            FROM rel_coop_ingeniero rci
            JOIN usuarios u ON u.id_real = rci.cooperativa_id_real AND u.rol = 'cooperativa'
            LEFT JOIN usuarios_info ui ON ui.usuario_id = u.id
            LEFT JOIN (
                SELECT DISTINCT rpc.cooperativa_id_real, rpc.productor_id_real
                FROM rel_productor_coop rpc
                JOIN usuarios up ON up.id_real = rpc.productor_id_real
                    AND up.rol = 'productor'
                    AND up.archivado = 0
            ) rp ON rp.cooperativa_id_real = rci.cooperativa_id_real
            LEFT JOIN relevamiento_productor_estados rpe ON rpe.operativo_id = :op
                AND rpe.productor_id_real = rp.productor_id_real
            WHERE rci.ingeniero_id_real = :ing
            GROUP BY u.id_real, nombre, cuit
            ORDER BY nombre ASC
        ");
        $stmt->execute([':op' => $operativoId, ':ing' => $ingenieroIdReal]);

        $coops = $stmt->fetchAll() ?: [];
        foreach ($coops as &$coop) {
            $total = (int)($coop['productores_count'] ?? 0);
            $completados = (int)($coop['completados'] ?? 0);
            unset($coop['completados']);
            $coop['productores_count'] = $total;
            $coop['avance'] = $this->buildAvanceFromCounts($total, $completados);
        }
        unset($coop);

        return $coops;
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

    private function getProductoresByCooperativaConEstado(int $operativoId, string $coopIdReal, string $ingenieroIdReal): array
    {
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT
                   rpc.productor_id_real AS id_real,
                   COALESCE(NULLIF(TRIM(ui.nombre), ''), NULLIF(TRIM(u.razon_social), ''), NULLIF(TRIM(u.usuario), ''), rpc.productor_id_real) AS nombre,
                   NULLIF(NULLIF(TRIM(CAST(u.cuit AS CHAR)), ''), '0') AS cuit,
                   COALESCE(rpe.estado, 'en_progreso') AS estado_relevamiento
            FROM rel_productor_coop rpc
            JOIN rel_coop_ingeniero rci ON rci.cooperativa_id_real = rpc.cooperativa_id_real
            JOIN usuarios u ON u.id_real = rpc.productor_id_real AND u.rol = 'productor'
            LEFT JOIN usuarios_info ui ON ui.usuario_id = u.id
            LEFT JOIN relevamiento_productor_estados rpe ON rpe.operativo_id = :op
                AND rpe.productor_id_real = rpc.productor_id_real
            WHERE rpc.cooperativa_id_real = :coop
              AND rci.ingeniero_id_real = :ing
              AND u.archivado = 0
            ORDER BY nombre ASC
        ");
        $stmt->execute([':op' => $operativoId, ':coop' => $coopIdReal, ':ing' => $ingenieroIdReal]);

        $productores = $stmt->fetchAll() ?: [];
        foreach ($productores as &$productor) {
            $estado = (string)($productor['estado_relevamiento'] ?? 'en_progreso');
            $estado = in_array($estado, ['en_progreso', 'completado'], true) ? $estado : 'en_progreso';
            $productor['estado_relevamiento'] = $estado;
            $productor['estado_relevamiento_label'] = $this->estadoLabel($estado);
            $productor['avance'] = $this->avanceDesdeEstado($estado);
        }
        unset($productor);

        return $productores;
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
            SELECT DISTINCT pc.id, pc.finca_id, pc.codigo_finca, pc.nombre_finca, pc.codigo_cuartel, pc.variedad, pc.sistema_conduccion
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
        $this->rowCache = [];

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
            $row = $this->fetchCachedRow('usuarios:' . (int)$productor['id'], "SELECT * FROM usuarios WHERE id = :id LIMIT 1", [':id' => (int)$productor['id']]);
            return $row[$campo] ?? null;
        }

        if ($alcance === 'productor' && isset(self::PRODUCTOR_TABLES_BY_USER_ID[$tabla])) {
            $meta = self::PRODUCTOR_TABLES_BY_USER_ID[$tabla];
            $order = $meta['annual'] ? ' ORDER BY anio DESC, id DESC' : '';
            $row = $this->fetchCachedRow($tabla . ':owner:' . (int)$productor['id'], "SELECT * FROM {$tabla} WHERE {$meta['owner']} = :owner{$order} LIMIT 1", [':owner' => (int)$productor['id']]);
            return $row[$campo] ?? null;
        }

        if ($alcance === 'finca') {
            $this->assertFincaPerteneceAProductor($entityId, $productorIdReal);
            if ($tabla === 'prod_fincas') {
                $row = $this->fetchCachedRow('prod_fincas:' . $entityId, "SELECT * FROM prod_fincas WHERE id = :id LIMIT 1", [':id' => $entityId]);
                return $row[$campo] ?? null;
            }
            if (isset(self::FINCA_TABLES[$tabla])) {
                $order = self::FINCA_TABLES[$tabla]['annual'] ? ' ORDER BY anio DESC, id DESC' : '';
                $row = $this->fetchCachedRow($tabla . ':finca:' . $entityId, "SELECT * FROM {$tabla} WHERE finca_id = :id{$order} LIMIT 1", [':id' => $entityId]);
                return $row[$campo] ?? null;
            }
        }

        if ($alcance === 'cuartel') {
            $this->assertCuartelPerteneceAProductor($entityId, $productorIdReal);
            if ($tabla === 'prod_cuartel') {
                $row = $this->fetchCachedRow('prod_cuartel:' . $entityId, "SELECT * FROM prod_cuartel WHERE id = :id LIMIT 1", [':id' => $entityId]);
                return $row[$campo] ?? null;
            }
            if (in_array($tabla, self::CUARTEL_TABLES, true)) {
                $row = $this->fetchCachedRow($tabla . ':cuartel:' . $entityId, "SELECT * FROM {$tabla} WHERE cuartel_id = :id LIMIT 1", [':id' => $entityId]);
                return $row[$campo] ?? null;
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
        $cacheKey = 'finca:' . $fincaId . ':' . $productorIdReal;
        if (isset($this->ownershipCache[$cacheKey])) {
            return;
        }
        $stmt = $this->pdo->prepare("SELECT 1 FROM prod_fincas WHERE id = :id AND productor_id_real = :prod AND COALESCE(archivado, 0) = 0 LIMIT 1");
        $stmt->execute([':id' => $fincaId, ':prod' => $productorIdReal]);
        if (!$stmt->fetchColumn()) {
            throw new RuntimeException('La finca no pertenece al productor');
        }
        $this->ownershipCache[$cacheKey] = true;
    }

    private function assertCuartelPerteneceAProductor(int $cuartelId, string $productorIdReal): void
    {
        if ($cuartelId <= 0) {
            throw new InvalidArgumentException('Cuartel invalido');
        }
        $cacheKey = 'cuartel:' . $cuartelId . ':' . $productorIdReal;
        if (isset($this->ownershipCache[$cacheKey])) {
            return;
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
        $this->ownershipCache[$cacheKey] = true;
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

    private function fetchCachedRow(string $key, string $sql, array $params): array
    {
        if (array_key_exists($key, $this->rowCache)) {
            return $this->rowCache[$key];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        $this->rowCache[$key] = is_array($row) ? $row : [];
        return $this->rowCache[$key];
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
