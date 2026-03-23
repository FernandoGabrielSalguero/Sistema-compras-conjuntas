<?php

class SveCosechaMecanicaFincasModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getEstado(): array
    {
        return [
            'message' => 'Estado OK.',
            'view' => 'views/sve/sve_cosechaMecanica.php',
            'controller' => 'controllers/sve_cosechaMecanicaFincasController.php',
            'model' => 'models/sve_cosechaMecanicaFincasModel.php'
        ];
    }

    private function construirFiltros(array $filtros, ?string $excluir = null): array
    {
        $condiciones = [];
        $params = [];

        if ($excluir !== 'contrato' && !empty($filtros['contrato_id'])) {
            $condiciones[] = 'p.contrato_id = :contrato_id';
            $params[':contrato_id'] = (int) $filtros['contrato_id'];
        }

        if ($excluir !== 'cooperativa' && !empty($filtros['cooperativa'])) {
            $condiciones[] = 'p.nom_cooperativa = :nom_cooperativa';
            $params[':nom_cooperativa'] = (string) $filtros['cooperativa'];
        }

        if ($excluir !== 'productor' && !empty($filtros['productor'])) {
            $condiciones[] = 'p.productor = :productor';
            $params[':productor'] = (string) $filtros['productor'];
        }

        if ($excluir !== 'tipo' && !empty($filtros['tipo'])) {
            $tipo = strtolower((string) $filtros['tipo']);
            if ($tipo === 'externo') {
                $condiciones[] = "f.codigo_finca LIKE 'EXT-%'";
            } elseif ($tipo === 'interno') {
                $condiciones[] = "(f.codigo_finca IS NULL OR f.codigo_finca NOT LIKE 'EXT-%')";
            }
        }

        if ($excluir !== 'finca' && !empty($filtros['finca_id'])) {
            $condiciones[] = 'p.finca_id = :finca_id';
            $params[':finca_id'] = (int) $filtros['finca_id'];
        }

        return [$condiciones, $params];
    }

    public function obtenerFincasParticipantes(array $filtros = []): array
    {
        [$condiciones, $params] = $this->construirFiltros($filtros);

        $sql = "SELECT
                    p.id,
                    p.contrato_id,
                    c.nombre AS contrato_nombre,
                    p.nom_cooperativa,
                    p.productor,
                    p.superficie,
                    p.variedad,
                    p.prod_estimada,
                    p.fecha_estimada,
                    p.km_finca,
                    p.flete,
                    p.seguro_flete,
                    p.finca_id,
                    rf.id AS relevamiento_id,
                    f.codigo_finca,
                    f.nombre_finca
                FROM cosechaMecanica_cooperativas_participacion p
                INNER JOIN CosechaMecanica c
                    ON c.id = p.contrato_id
                LEFT JOIN cosechaMecanica_relevamiento_finca rf
                    ON rf.participacion_id = p.id
                LEFT JOIN prod_fincas f
                    ON f.id = p.finca_id
                WHERE p.firma = 1
                  AND EXISTS (
                      SELECT 1
                      FROM cosechaMecanica_coop_contrato_firma cf
                      WHERE cf.contrato_id = p.contrato_id
                        AND cf.acepto = 1
                  )";

        if (!empty($condiciones)) {
            $sql .= " AND " . implode(' AND ', $condiciones);
        }

        $sql .= " ORDER BY c.fecha_apertura DESC, p.nom_cooperativa ASC, p.productor ASC, p.id ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function obtenerTotalesParticipaciones(array $filtros = []): array
    {
        [$condiciones, $params] = $this->construirFiltros($filtros);

        $sql = "SELECT
                    COUNT(DISTINCT p.id) AS total_registros,
                    COUNT(DISTINCT rf.participacion_id) AS realizados
                FROM cosechaMecanica_cooperativas_participacion p
                INNER JOIN CosechaMecanica c
                    ON c.id = p.contrato_id
                LEFT JOIN cosechaMecanica_relevamiento_finca rf
                    ON rf.participacion_id = p.id
                WHERE p.firma = 1
                  AND EXISTS (
                      SELECT 1
                      FROM cosechaMecanica_coop_contrato_firma cf
                      WHERE cf.contrato_id = p.contrato_id
                        AND cf.acepto = 1
                  )";

        if (!empty($condiciones)) {
            $sql .= " AND " . implode(' AND ', $condiciones);
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $total = isset($row['total_registros']) ? (int) $row['total_registros'] : 0;
        $realizados = isset($row['realizados']) ? (int) $row['realizados'] : 0;

        return [
            'total_registros' => $total,
            'realizados' => $realizados,
            'pendientes' => max(0, $total - $realizados),
        ];
    }

    public function obtenerOpcionesFiltros(array $filtros = []): array
    {
        $baseFrom = " FROM cosechaMecanica_cooperativas_participacion p
            INNER JOIN CosechaMecanica c
                ON c.id = p.contrato_id
            LEFT JOIN prod_fincas f
                ON f.id = p.finca_id
            WHERE p.firma = 1
              AND EXISTS (
                  SELECT 1
                  FROM cosechaMecanica_coop_contrato_firma cf
                  WHERE cf.contrato_id = p.contrato_id
                    AND cf.acepto = 1
              )";

        [$condContratos, $paramsContratos] = $this->construirFiltros($filtros, 'contrato');
        $sqlContratos = "SELECT DISTINCT c.id, c.nombre" . $baseFrom;
        if (!empty($condContratos)) {
            $sqlContratos .= " AND " . implode(' AND ', $condContratos);
        }
        $sqlContratos .= " ORDER BY c.fecha_apertura DESC, c.nombre ASC";
        $stmtContratos = $this->pdo->prepare($sqlContratos);
        $stmtContratos->execute($paramsContratos);
        $contratos = $stmtContratos->fetchAll(PDO::FETCH_ASSOC) ?: [];

        [$condCoops, $paramsCoops] = $this->construirFiltros($filtros, 'cooperativa');
        $sqlCoops = "SELECT DISTINCT p.nom_cooperativa" . $baseFrom;
        if (!empty($condCoops)) {
            $sqlCoops .= " AND " . implode(' AND ', $condCoops);
        }
        $sqlCoops .= " ORDER BY p.nom_cooperativa ASC";
        $stmtCoops = $this->pdo->prepare($sqlCoops);
        $stmtCoops->execute($paramsCoops);
        $cooperativas = array_map(
            fn($row) => $row['nom_cooperativa'],
            $stmtCoops->fetchAll(PDO::FETCH_ASSOC) ?: []
        );

        [$condProds, $paramsProds] = $this->construirFiltros($filtros, 'productor');
        $sqlProds = "SELECT DISTINCT p.productor" . $baseFrom;
        if (!empty($condProds)) {
            $sqlProds .= " AND " . implode(' AND ', $condProds);
        }
        $sqlProds .= " ORDER BY p.productor ASC";
        $stmtProds = $this->pdo->prepare($sqlProds);
        $stmtProds->execute($paramsProds);
        $productores = array_map(
            fn($row) => $row['productor'],
            $stmtProds->fetchAll(PDO::FETCH_ASSOC) ?: []
        );

        [$condFincas, $paramsFincas] = $this->construirFiltros($filtros, 'finca');
        $sqlFincas = "SELECT DISTINCT p.finca_id, f.codigo_finca, f.nombre_finca" . $baseFrom;
        if (!empty($condFincas)) {
            $sqlFincas .= " AND " . implode(' AND ', $condFincas);
        }
        $sqlFincas .= " AND p.finca_id IS NOT NULL
            ORDER BY f.nombre_finca ASC, f.codigo_finca ASC";
        $stmtFincas = $this->pdo->prepare($sqlFincas);
        $stmtFincas->execute($paramsFincas);
        $fincas = $stmtFincas->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return [
            'contratos' => $contratos,
            'cooperativas' => $cooperativas,
            'productores' => $productores,
            'fincas' => $fincas,
        ];
    }

    public function obtenerRelevamientoPorParticipacion(int $participacionId): ?array
    {
        $sql = "SELECT
                    id,
                    participacion_id,
                    ancho_callejon_norte,
                    ancho_callejon_sur,
                    promedio_callejon,
                    interfilar,
                    cantidad_postes,
                    postes_mal_estado,
                    porcentaje_postes_mal_estado,
                    estructura_separadores,
                    agua_lavado,
                    preparacion_acequias,
                    preparacion_obstaculos,
                    observaciones,
                    created_at,
                    updated_at
                FROM cosechaMecanica_relevamiento_finca
                WHERE participacion_id = :participacion_id
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':participacion_id' => $participacionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function guardarRelevamiento(int $participacionId, array $data): array
    {
        $sqlExiste = "SELECT
                id,
                ancho_callejon_norte,
                ancho_callejon_sur,
                promedio_callejon,
                interfilar,
                cantidad_postes,
                postes_mal_estado,
                porcentaje_postes_mal_estado,
                estructura_separadores,
                agua_lavado,
                preparacion_acequias,
                preparacion_obstaculos,
                observaciones
            FROM cosechaMecanica_relevamiento_finca
            WHERE participacion_id = :participacion_id
            LIMIT 1";
        $stmtExiste = $this->pdo->prepare($sqlExiste);
        $stmtExiste->execute([':participacion_id' => $participacionId]);
        $existente = $stmtExiste->fetch(PDO::FETCH_ASSOC);

        $fields = [
            'ancho_callejon_norte',
            'ancho_callejon_sur',
            'promedio_callejon',
            'interfilar',
            'cantidad_postes',
            'postes_mal_estado',
            'porcentaje_postes_mal_estado',
            'estructura_separadores',
            'agua_lavado',
            'preparacion_acequias',
            'preparacion_obstaculos',
            'observaciones',
        ];

        $merged = [];
        foreach ($fields as $field) {
            $incoming = $data[$field] ?? null;
            if ($incoming === '' || $incoming === null) {
                $merged[$field] = $existente[$field] ?? null;
            } else {
                $merged[$field] = $incoming;
            }
        }

        $norte = $merged['ancho_callejon_norte'];
        $sur = $merged['ancho_callejon_sur'];
        $totalPostes = $merged['cantidad_postes'];
        $postesMal = $merged['postes_mal_estado'];

        if (is_numeric($norte) && is_numeric($sur) && (float) $norte >= 0 && (float) $sur >= 0) {
            $merged['promedio_callejon'] = (string) round((((float) $norte) + ((float) $sur)) / 2, 2);
        }

        if (is_numeric($totalPostes) && is_numeric($postesMal) && (float) $totalPostes > 0 && (float) $postesMal >= 0) {
            $merged['porcentaje_postes_mal_estado'] = (string) round((((float) $postesMal) / ((float) $totalPostes)) * 100, 2);
        }

        $payload = [
            ':participacion_id' => $participacionId,
            ':ancho_callejon_norte' => $merged['ancho_callejon_norte'],
            ':ancho_callejon_sur' => $merged['ancho_callejon_sur'],
            ':promedio_callejon' => $merged['promedio_callejon'],
            ':interfilar' => $merged['interfilar'],
            ':cantidad_postes' => $merged['cantidad_postes'],
            ':postes_mal_estado' => $merged['postes_mal_estado'],
            ':porcentaje_postes_mal_estado' => $merged['porcentaje_postes_mal_estado'],
            ':estructura_separadores' => $merged['estructura_separadores'],
            ':agua_lavado' => $merged['agua_lavado'],
            ':preparacion_acequias' => $merged['preparacion_acequias'],
            ':preparacion_obstaculos' => $merged['preparacion_obstaculos'],
            ':observaciones' => $merged['observaciones'],
        ];

        if ($existente) {
            $sqlUpdate = "UPDATE cosechaMecanica_relevamiento_finca
                SET ancho_callejon_norte = :ancho_callejon_norte,
                    ancho_callejon_sur = :ancho_callejon_sur,
                    promedio_callejon = :promedio_callejon,
                    interfilar = :interfilar,
                    cantidad_postes = :cantidad_postes,
                    postes_mal_estado = :postes_mal_estado,
                    porcentaje_postes_mal_estado = :porcentaje_postes_mal_estado,
                    estructura_separadores = :estructura_separadores,
                    agua_lavado = :agua_lavado,
                    preparacion_acequias = :preparacion_acequias,
                    preparacion_obstaculos = :preparacion_obstaculos,
                    observaciones = :observaciones
                WHERE participacion_id = :participacion_id";
            $stmtUpdate = $this->pdo->prepare($sqlUpdate);
            $stmtUpdate->execute($payload);

            return ['id' => (int) $existente['id'], 'accion' => 'actualizado'];
        }

        $payload = $this->applyInsertDefaults($payload);

        $sqlInsert = "INSERT INTO cosechaMecanica_relevamiento_finca (
                participacion_id,
                ancho_callejon_norte,
                ancho_callejon_sur,
                promedio_callejon,
                interfilar,
                cantidad_postes,
                postes_mal_estado,
                porcentaje_postes_mal_estado,
                estructura_separadores,
                agua_lavado,
                preparacion_acequias,
                preparacion_obstaculos,
                observaciones
            ) VALUES (
                :participacion_id,
                :ancho_callejon_norte,
                :ancho_callejon_sur,
                :promedio_callejon,
                :interfilar,
                :cantidad_postes,
                :postes_mal_estado,
                :porcentaje_postes_mal_estado,
                :estructura_separadores,
                :agua_lavado,
                :preparacion_acequias,
                :preparacion_obstaculos,
                :observaciones
            )";

        $stmtInsert = $this->pdo->prepare($sqlInsert);
        $stmtInsert->execute($payload);

        return ['id' => (int) $this->pdo->lastInsertId(), 'accion' => 'creado'];
    }

    public function obtenerFacturacionPorParticipacion(int $participacionId): ?array
    {
        $sql = "SELECT
                    p.id AS participacion_id,
                    p.productor,
                    p.nom_cooperativa,
                    COALESCE(CAST(u_prod_name.cuit AS CHAR), CAST(u_prod_ui.cuit AS CHAR), '') AS cuit,
                    COALESCE(ip_latest.condicion_cooperativa, '') AS condicion_pago,
                    cf.fecha_servicio,
                    cf.hectareas_cosechadas,
                    cf.hectareas_anticipadas,
                    c.bon_optima,
                    c.bon_muy_buena,
                    c.bon_buena,
                    rf.ancho_callejon_norte,
                    rf.ancho_callejon_sur,
                    rf.promedio_callejon,
                    rf.interfilar,
                    rf.cantidad_postes,
                    rf.postes_mal_estado,
                    rf.porcentaje_postes_mal_estado,
                    rf.estructura_separadores,
                    rf.agua_lavado,
                    rf.preparacion_acequias,
                    rf.preparacion_obstaculos
                FROM cosechaMecanica_cooperativas_participacion p
                INNER JOIN CosechaMecanica c
                    ON c.id = p.contrato_id
                LEFT JOIN cosechaMecanica_facturacion cf
                    ON cf.participacion_id = p.id
                LEFT JOIN usuarios u_prod_name
                    ON u_prod_name.rol = 'productor'
                    AND (
                        u_prod_name.razon_social = p.productor
                        OR u_prod_name.usuario = p.productor
                        OR u_prod_name.id_real = p.productor
                    )
                LEFT JOIN (
                    SELECT ui.nombre, MIN(ui.usuario_id) AS usuario_id
                    FROM usuarios_info ui
                    GROUP BY ui.nombre
                ) ui_prod_match
                    ON ui_prod_match.nombre = p.productor
                LEFT JOIN usuarios u_prod_ui
                    ON u_prod_ui.id = ui_prod_match.usuario_id
                    AND u_prod_ui.rol = 'productor'
                LEFT JOIN (
                    SELECT ip1.*
                    FROM info_productor ip1
                    INNER JOIN (
                        SELECT productor_id, MAX(anio) AS max_anio
                        FROM info_productor
                        GROUP BY productor_id
                    ) ip2
                        ON ip2.productor_id = ip1.productor_id
                       AND ip2.max_anio = ip1.anio
                ) ip_latest
                    ON ip_latest.productor_id = COALESCE(u_prod_name.id, u_prod_ui.id)
                LEFT JOIN (
                    SELECT r1.*
                    FROM cosechaMecanica_relevamiento_finca r1
                    INNER JOIN (
                        SELECT participacion_id, MAX(id) AS max_id
                        FROM cosechaMecanica_relevamiento_finca
                        GROUP BY participacion_id
                    ) r2
                        ON r2.max_id = r1.id
                ) rf
                    ON rf.participacion_id = p.id
                WHERE p.id = :participacion_id
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':participacion_id' => $participacionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $calificacion = $this->calcularCalificacionFacturacion($row);
        $row['calificacion_aptitud_label'] = $calificacion['label'];
        $row['bonificacion_aptitud_finca'] = $calificacion['bonificacion'];

        return $row;
    }

    public function guardarFacturacion(int $participacionId, array $data): array
    {
        $sqlExiste = "SELECT id
            FROM cosechaMecanica_facturacion
            WHERE participacion_id = :participacion_id
            LIMIT 1";
        $stmtExiste = $this->pdo->prepare($sqlExiste);
        $stmtExiste->execute([':participacion_id' => $participacionId]);
        $existente = $stmtExiste->fetch(PDO::FETCH_ASSOC);

        $payload = [
            ':participacion_id' => $participacionId,
            ':fecha_servicio' => ($data['fecha_servicio'] ?? '') !== '' ? $data['fecha_servicio'] : null,
            ':hectareas_cosechadas' => ($data['hectareas_cosechadas'] ?? '') !== '' ? $data['hectareas_cosechadas'] : null,
            ':hectareas_anticipadas' => ($data['hectareas_anticipadas'] ?? '') !== '' ? $data['hectareas_anticipadas'] : null,
        ];

        if ($existente) {
            $sqlUpdate = "UPDATE cosechaMecanica_facturacion
                SET fecha_servicio = :fecha_servicio,
                    hectareas_cosechadas = :hectareas_cosechadas,
                    hectareas_anticipadas = :hectareas_anticipadas
                WHERE participacion_id = :participacion_id";
            $stmtUpdate = $this->pdo->prepare($sqlUpdate);
            $stmtUpdate->execute($payload);

            return ['id' => (int) $existente['id'], 'accion' => 'actualizado'];
        }

        $sqlInsert = "INSERT INTO cosechaMecanica_facturacion (
                participacion_id,
                fecha_servicio,
                hectareas_cosechadas,
                hectareas_anticipadas
            ) VALUES (
                :participacion_id,
                :fecha_servicio,
                :hectareas_cosechadas,
                :hectareas_anticipadas
            )";
        $stmtInsert = $this->pdo->prepare($sqlInsert);
        $stmtInsert->execute($payload);

        return ['id' => (int) $this->pdo->lastInsertId(), 'accion' => 'creado'];
    }

    private function calcularCalificacionFacturacion(array $row): array
    {
        $promedioCalc = $this->resolvePromedioCallejon($row);
        $interfilar = $this->parseInterfilar($row['interfilar'] ?? null);
        $postesPct = $this->resolvePorcentajePostes($row);

        $hayDatos = $promedioCalc !== null
            || $interfilar !== null
            || $postesPct !== null
            || !empty($row['estructura_separadores'])
            || !empty($row['agua_lavado'])
            || !empty($row['preparacion_acequias'])
            || !empty($row['preparacion_obstaculos']);

        if (!$hayDatos) {
            return ['label' => 'Sin calificación', 'bonificacion' => 0.0];
        }

        $filas = [
            ['puntos' => $this->puntajeCallejon($promedioCalc), 'impacto' => 15],
            ['puntos' => $this->puntajeInterfilar($interfilar), 'impacto' => 5],
            ['puntos' => $this->puntajePostes($postesPct), 'impacto' => 25],
            ['puntos' => $this->puntajeSeparadores($row['estructura_separadores'] ?? null), 'impacto' => 10],
            ['puntos' => $this->puntajeAgua($row['agua_lavado'] ?? null), 'impacto' => 30],
            ['puntos' => $this->puntajeAcequias($row['preparacion_acequias'] ?? null), 'impacto' => 10],
            ['puntos' => $this->puntajeMalezas($row['preparacion_obstaculos'] ?? null), 'impacto' => 5],
        ];

        $total = 0.0;
        foreach ($filas as $fila) {
            $total += $this->clamp((((float) $fila['puntos']) / 4) * ((float) $fila['impacto']), 0, (float) $fila['impacto']);
        }

        $total = $this->clamp($total, 0, 100);
        if ($total >= 91) {
            return ['label' => 'Óptima', 'bonificacion' => (float) ($row['bon_optima'] ?? 0)];
        }
        if ($total >= 81) {
            return ['label' => 'Muy Buena', 'bonificacion' => (float) ($row['bon_muy_buena'] ?? 0)];
        }
        if ($total >= 70) {
            return ['label' => 'Buena', 'bonificacion' => (float) ($row['bon_buena'] ?? 0)];
        }

        return ['label' => 'Regular', 'bonificacion' => 0.0];
    }

    private function toNumber($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        $raw = str_replace(',', '.', trim((string) $value));
        if ($raw === '') {
            return null;
        }
        return is_numeric($raw) ? (float) $raw : null;
    }

    private function clamp(float $num, float $min, float $max): float
    {
        return min(max($num, $min), $max);
    }

    private function resolvePromedioCallejon(array $data): ?float
    {
        $norte = $this->toNumber($data['ancho_callejon_norte'] ?? null);
        $sur = $this->toNumber($data['ancho_callejon_sur'] ?? null);
        if ($norte !== null && $sur !== null) {
            return ($norte + $sur) / 2;
        }
        return $this->toNumber($data['promedio_callejon'] ?? null);
    }

    private function parseInterfilar($value): ?float
    {
        if ($value === null) {
            return null;
        }
        $raw = strtolower(trim((string) $value));
        if ($raw === '') {
            return null;
        }
        preg_match_all('/(\d+[.,]?\d*)/', $raw, $matches);
        if (empty($matches[0])) {
            return null;
        }
        $last = str_replace(',', '.', end($matches[0]));
        return is_numeric($last) ? (float) $last : null;
    }

    private function resolvePorcentajePostes(array $data): ?float
    {
        $porcentaje = $this->toNumber($data['porcentaje_postes_mal_estado'] ?? null);
        if ($porcentaje !== null) {
            return $porcentaje;
        }

        $cantidadPostes = $this->toNumber($data['cantidad_postes'] ?? null);
        $postesMal = $this->toNumber($data['postes_mal_estado'] ?? null);
        if ($cantidadPostes !== null && $postesMal !== null && $cantidadPostes > 0) {
            return ($postesMal / $cantidadPostes) * 100;
        }

        return null;
    }

    private function puntajeCallejon(?float $metros): float
    {
        if ($metros === null) return 0;
        if ($metros > 6) return 4;
        if ($metros >= 5.7) return 3;
        if ($metros >= 5.3) return 2;
        if ($metros >= 5.0) return 1;
        return 0;
    }

    private function puntajeInterfilar(?float $metros): float
    {
        if ($metros === null) return 0;
        if ($metros >= 2.5) return 4;
        if ($metros >= 2.3) return 3;
        if ($metros >= 2.2) return 2;
        if ($metros >= 2.0) return 1;
        return 0;
    }

    private function puntajePostes(?float $porcentaje): float
    {
        if ($porcentaje === null) return 0;
        if ($porcentaje < 5) return 4;
        if ($porcentaje < 10) return 3;
        if ($porcentaje < 25) return 2;
        if ($porcentaje < 40) return 1;
        return 0;
    }

    private function puntajeSeparadores($valor): float
    {
        $raw = strtolower((string) ($valor ?? ''));
        if (str_contains($raw, 'todos asegurados')) return 4;
        if (str_contains($raw, 'algunos olvidados') || str_contains($raw, 'asegurados y tensados')) return 2;
        if (str_contains($raw, 'sin atar') || str_contains($raw, 'sin tensar')) return 0;
        return 0;
    }

    private function puntajeAgua($valor): float
    {
        $raw = strtolower((string) ($valor ?? ''));
        if (str_contains($raw, 'suficiente y cerc')) return 4;
        if (str_contains($raw, 'suficiente a mas de 1km') || str_contains($raw, 'suficiente a más de 1km')) return 3;
        if (str_contains($raw, 'insuficiente pero cercana')) return 2;
        if (str_contains($raw, 'insuficiente a mas de 1km') || str_contains($raw, 'insuficiente a más de 1km')) return 1;
        if (str_contains($raw, 'no tiene')) return 0;
        return 0;
    }

    private function puntajeAcequias($valor): float
    {
        $raw = strtolower((string) ($valor ?? ''));
        if (str_contains($raw, 'borradas')) return 4;
        if (str_contains($raw, 'suavizadas')) return 2.5;
        if (str_contains($raw, 'dificultades')) return 1;
        if (str_contains($raw, 'profundas') || str_contains($raw, 'sin borrar')) return 0;
        return 0;
    }

    private function puntajeMalezas($valor): float
    {
        $raw = strtolower((string) ($valor ?? ''));
        if (str_contains($raw, 'ausencia de malesas') || str_contains($raw, 'ausencia de malezas')) return 4;
        if (str_contains($raw, 'mayoria') || str_contains($raw, 'mayoría')) return 3;
        if (str_contains($raw, 'menores a 40cm')) return 2;
        if (str_contains($raw, 'suelo enmalezado')) return 1;
        if (str_contains($raw, 'sobre el alambre')) return 0;
        return 0;
    }

    private function applyInsertDefaults(array $payload): array
    {
        $numericKeys = [
            ':ancho_callejon_norte',
            ':ancho_callejon_sur',
            ':promedio_callejon',
            ':cantidad_postes',
            ':postes_mal_estado',
            ':porcentaje_postes_mal_estado',
        ];

        foreach ($numericKeys as $key) {
            if (!array_key_exists($key, $payload) || $payload[$key] === null || $payload[$key] === '') {
                $payload[$key] = '0';
            }
        }

        $textKeys = [
            ':interfilar',
            ':estructura_separadores',
            ':agua_lavado',
            ':preparacion_acequias',
            ':preparacion_obstaculos',
            ':observaciones',
        ];

        foreach ($textKeys as $key) {
            if (!array_key_exists($key, $payload) || $payload[$key] === null) {
                $payload[$key] = '';
            }
        }

        return $payload;
    }
}
