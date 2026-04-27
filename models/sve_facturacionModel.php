<?php

class SveFacturacionModel
{
    private PDO $pdo;
    private array $columnExistsCache = [];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function obtenerEstadoModulo()
    {
        return [
            'titulo' => 'Facturacion',
            'estado' => 'inicial'
        ];
    }

    public function obtenerFincasParticipantes(): array
    {
        $tieneCondicionPago = $this->columnExists('cosechaMecanica_facturacion', 'condicion_pago');
        $selectCondicionPago = $tieneCondicionPago
            ? "COALESCE(cf.condicion_pago, '') AS condicion_pago,"
            : "'' AS condicion_pago,";

        $sql = "SELECT
                    p.id AS participacion_id,
                    p.contrato_id,
                    c.nombre AS contrato_nombre,
                    p.nom_cooperativa,
                    p.productor,
                    COALESCE(CAST(u_prod_name.cuit AS CHAR), CAST(u_prod_ui.cuit AS CHAR), '') AS cuit,
                    p.superficie,
                    p.variedad,
                    p.prod_estimada,
                    p.fecha_estimada,
                    p.km_finca,
                    p.flete,
                    p.seguro_flete,
                    p.finca_id,
                    f.codigo_finca,
                    f.nombre_finca,
                    {$selectCondicionPago}
                    cf.fecha_servicio,
                    cf.hectareas_cosechadas,
                    cf.hectareas_anticipadas,
                    rf.id AS relevamiento_id,
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
                    rf.preparacion_obstaculos,
                    rf.observaciones,
                    rf.created_at AS fecha_evaluacion,
                    c.bon_optima,
                    c.bon_muy_buena,
                    c.bon_buena
                FROM cosechaMecanica_cooperativas_participacion p
                INNER JOIN CosechaMecanica c
                    ON c.id = p.contrato_id
                LEFT JOIN cosechaMecanica_facturacion cf
                    ON cf.participacion_id = p.id
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
                LEFT JOIN prod_fincas f
                    ON f.id = p.finca_id
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
                WHERE p.firma = 1
                  AND EXISTS (
                      SELECT 1
                      FROM cosechaMecanica_coop_contrato_firma cfirma
                      WHERE cfirma.contrato_id = p.contrato_id
                        AND cfirma.acepto = 1
                  )
                ORDER BY c.fecha_apertura DESC, p.nom_cooperativa ASC, p.productor ASC, p.id ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        foreach ($rows as &$row) {
            $calificacion = $this->calcularCalificacionFacturacion($row);
            $row['calificacion_aptitud_finca'] = $calificacion['label'];
            $row['bonificacion_aptitud_finca'] = $calificacion['bonificacion'];
            $row['tipo'] = str_starts_with((string) ($row['codigo_finca'] ?? ''), 'EXT-') ? 'Externo' : 'Interno';
            $row['finca'] = $row['nombre_finca'] ?: ($row['codigo_finca'] ?: ($row['finca_id'] ? 'Finca #' . $row['finca_id'] : 'Sin finca'));
        }
        unset($row);

        return $rows;
    }

    public function obtenerTotales(array $rows): array
    {
        $total = count($rows);
        $realizados = 0;
        foreach ($rows as $row) {
            if (!empty($row['relevamiento_id'])) {
                $realizados++;
            }
        }

        return [
            'total_registros' => $total,
            'realizados' => $realizados,
            'pendientes' => max(0, $total - $realizados),
        ];
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
            return ['label' => 'Sin calificacion', 'bonificacion' => 0.0];
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
            return ['label' => 'Optima', 'bonificacion' => (float) ($row['bon_optima'] ?? 0)];
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
        if (str_contains($raw, 'suficiente a m') && str_contains($raw, '1km')) return 3;
        if (str_contains($raw, 'insuficiente pero cercana')) return 2;
        if (str_contains($raw, 'insuficiente a m') && str_contains($raw, '1km')) return 1;
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
        if (str_contains($raw, 'mayoria') || str_contains($raw, 'mayor')) return 3;
        if (str_contains($raw, 'menores a 40cm')) return 2;
        if (str_contains($raw, 'suelo enmalezado')) return 1;
        if (str_contains($raw, 'sobre el alambre')) return 0;
        return 0;
    }

    private function columnExists(string $tableName, string $columnName): bool
    {
        $cacheKey = $tableName . '.' . $columnName;
        if (array_key_exists($cacheKey, $this->columnExistsCache)) {
            return $this->columnExistsCache[$cacheKey];
        }

        $sql = "SELECT COUNT(*)
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = :table_name
              AND COLUMN_NAME = :column_name";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':table_name' => $tableName,
            ':column_name' => $columnName,
        ]);

        $exists = ((int) $stmt->fetchColumn()) > 0;
        $this->columnExistsCache[$cacheKey] = $exists;
        return $exists;
    }
}
