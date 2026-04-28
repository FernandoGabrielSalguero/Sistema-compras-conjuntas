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

    public function obtenerSolicitudesDrones(): array
    {
        $this->pdo->exec('SET SESSION group_concat_max_len = 65535');

        $sql = "SELECT
                    s.id AS solicitud_id,
                    s.productor_id_real,
                    COALESCE(ui.nombre, u.usuario, s.ses_usuario, s.ses_nombre, '') AS productor_nombre,
                    s.ses_usuario,
                    s.ses_nombre,
                    s.ses_correo,
                    s.ses_telefono,
                    s.ses_direccion,
                    s.ses_cuit,
                    s.ses_rol,
                    s.representante,
                    s.superficie_ha,
                    s.fecha_visita,
                    s.hora_visita_desde,
                    s.hora_visita_hasta,
                    COALESCE(uip.nombre, up.usuario, '') AS piloto,
                    s.piloto_id,
                    s.forma_pago_id,
                    fp.nombre AS forma_pago,
                    s.coop_descuento_nombre,
                    s.dir_provincia,
                    s.dir_localidad,
                    s.dir_calle,
                    s.dir_numero,
                    s.en_finca,
                    s.ubicacion_lat,
                    s.ubicacion_lng,
                    s.ubicacion_acc,
                    s.ubicacion_ts,
                    s.linea_tension,
                    s.zona_restringida,
                    s.corriente_electrica,
                    s.agua_potable,
                    s.libre_obstaculos,
                    s.area_despegue,
                    s.observaciones AS observaciones_productor,
                    s.estado,
                    s.motivo_cancelacion,
                    s.created_at,
                    s.updated_at,
                    c.moneda,
                    c.costo_base_por_ha,
                    c.base_ha,
                    c.base_total,
                    c.productos_total,
                    c.total AS costo_total,
                    c.desglose_json AS costo_desglose_json,
                    rg.rangos,
                    mt.motivos,
                    it.productos,
                    it.productos_fuente,
                    it.productos_costo_ha,
                    it.productos_total AS productos_total_detalle,
                    rec.recetas,
                    prm.volumen_ha,
                    prm.velocidad_vuelo,
                    prm.alto_vuelo,
                    prm.ancho_pasada,
                    prm.tamano_gota,
                    prm.observaciones AS observaciones_parametros,
                    prm.observaciones_agua,
                    rep.nom_cliente AS reporte_nom_cliente,
                    rep.nom_piloto AS reporte_nom_piloto,
                    rep.nom_encargado AS reporte_nom_encargado,
                    rep.fecha_visita AS reporte_fecha_visita,
                    rep.hora_ingreso AS reporte_hora_ingreso,
                    rep.hora_egreso AS reporte_hora_egreso,
                    rep.nombre_finca AS reporte_nombre_finca,
                    rep.cultivo_pulverizado AS reporte_cultivo_pulverizado,
                    rep.cuadro_cuartel AS reporte_cuadro_cuartel,
                    rep.sup_pulverizada AS reporte_sup_pulverizada,
                    rep.vol_aplicado AS reporte_vol_aplicado,
                    rep.vel_viento AS reporte_vel_viento,
                    rep.temperatura AS reporte_temperatura,
                    rep.humedad_relativa AS reporte_humedad_relativa,
                    rep.lavado_dron_miner AS reporte_lavado_dron_miner,
                    rep.triple_lavado_envases AS reporte_triple_lavado_envases,
                    rep.observaciones AS reporte_observaciones,
                    ev.eventos
                FROM drones_solicitud s
                LEFT JOIN drones_solicitud_costos c
                    ON c.solicitud_id = s.id
                LEFT JOIN dron_formas_pago fp
                    ON fp.id = s.forma_pago_id
                LEFT JOIN usuarios u
                    ON u.id_real = s.productor_id_real
                LEFT JOIN usuarios_info ui
                    ON ui.usuario_id = u.id
                LEFT JOIN usuarios up
                    ON up.id = s.piloto_id
                LEFT JOIN usuarios_info uip
                    ON uip.usuario_id = up.id
                LEFT JOIN (
                    SELECT solicitud_id, GROUP_CONCAT(DISTINCT rango ORDER BY rango SEPARATOR ' | ') AS rangos
                    FROM drones_solicitud_rango
                    GROUP BY solicitud_id
                ) rg
                    ON rg.solicitud_id = s.id
                LEFT JOIN (
                    SELECT sm.solicitud_id,
                           GROUP_CONCAT(DISTINCT COALESCE(dp.nombre, sm.otros_text, 'Sin motivo') ORDER BY dp.nombre, sm.otros_text SEPARATOR ' | ') AS motivos
                    FROM drones_solicitud_motivo sm
                    LEFT JOIN dron_patologias dp
                        ON dp.id = sm.patologia_id
                    GROUP BY sm.solicitud_id
                ) mt
                    ON mt.solicitud_id = s.id
                LEFT JOIN (
                    SELECT si.solicitud_id,
                           GROUP_CONCAT(COALESCE(si.nombre_producto, ps.nombre, 'Sin producto') ORDER BY si.id SEPARATOR ' | ') AS productos,
                           GROUP_CONCAT(si.fuente ORDER BY si.id SEPARATOR ' | ') AS productos_fuente,
                           GROUP_CONCAT(COALESCE(si.costo_hectarea_snapshot, '') ORDER BY si.id SEPARATOR ' | ') AS productos_costo_ha,
                           GROUP_CONCAT(COALESCE(si.total_producto_snapshot, '') ORDER BY si.id SEPARATOR ' | ') AS productos_total
                    FROM drones_solicitud_item si
                    LEFT JOIN dron_productos_stock ps
                        ON ps.id = si.producto_id
                    GROUP BY si.solicitud_id
                ) it
                    ON it.solicitud_id = s.id
                LEFT JOIN (
                    SELECT si.solicitud_id,
                           GROUP_CONCAT(
                               CONCAT_WS(' / ',
                                   COALESCE(si.nombre_producto, ps.nombre, 'Sin producto'),
                                   COALESCE(ir.principio_activo, ''),
                                   COALESCE(ir.dosis, ''),
                                   COALESCE(ir.unidad, ''),
                                   COALESCE(ir.cant_prod_usado, ''),
                                   COALESCE(ir.fecha_vencimiento, ''),
                                   COALESCE(ir.orden_mezcla, ''),
                                   COALESCE(ir.notas, '')
                               )
                               ORDER BY si.id, ir.id
                               SEPARATOR ' | '
                           ) AS recetas
                    FROM drones_solicitud_item si
                    LEFT JOIN dron_productos_stock ps
                        ON ps.id = si.producto_id
                    LEFT JOIN drones_solicitud_item_receta ir
                        ON ir.solicitud_item_id = si.id
                    GROUP BY si.solicitud_id
                ) rec
                    ON rec.solicitud_id = s.id
                LEFT JOIN (
                    SELECT p1.*
                    FROM drones_solicitud_parametros p1
                    INNER JOIN (
                        SELECT solicitud_id, MAX(id) AS max_id
                        FROM drones_solicitud_parametros
                        GROUP BY solicitud_id
                    ) p2
                        ON p2.max_id = p1.id
                ) prm
                    ON prm.solicitud_id = s.id
                LEFT JOIN (
                    SELECT r1.*
                    FROM drones_solicitud_Reporte r1
                    INNER JOIN (
                        SELECT solicitud_id, MAX(id) AS max_id
                        FROM drones_solicitud_Reporte
                        GROUP BY solicitud_id
                    ) r2
                        ON r2.max_id = r1.id
                ) rep
                    ON rep.solicitud_id = s.id
                LEFT JOIN (
                    SELECT solicitud_id,
                           GROUP_CONCAT(CONCAT_WS(' / ', tipo, detalle, actor, created_at) ORDER BY id SEPARATOR ' | ') AS eventos
                    FROM drones_solicitud_evento
                    GROUP BY solicitud_id
                ) ev
                    ON ev.solicitud_id = s.id
                ORDER BY s.created_at DESC, s.id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function obtenerTotalesDrones(array $rows): array
    {
        $total = count($rows);
        $completadas = 0;
        $canceladas = 0;

        foreach ($rows as $row) {
            $estado = strtolower((string) ($row['estado'] ?? ''));
            if ($estado === 'completada') {
                $completadas++;
            }
            if ($estado === 'cancelada') {
                $canceladas++;
            }
        }

        return [
            'total_registros' => $total,
            'completadas' => $completadas,
            'pendientes' => max(0, $total - $completadas - $canceladas),
            'canceladas' => $canceladas,
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
