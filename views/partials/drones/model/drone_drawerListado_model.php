<?php
// MODEL del drawer: FULL detalle + catálogos + actualización
declare(strict_types=1);

final class DroneDrawerListadoModel
{
    private PDO $pdo;
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /** FULL detalle para un ID (sólo lectura) */
    public function obtenerSolicitudFull(int $id): array
    {
        // base
        $st = $this->pdo->prepare("
            SELECT s.*, 
                   p.nombre   AS piloto_nombre, p.telefono AS piloto_telefono, p.zona_asignada AS piloto_zona_asignada, p.correo AS piloto_correo,
                   fp.nombre  AS forma_pago_nombre, fp.descripcion AS forma_pago_descripcion
            FROM drones_solicitud s
            LEFT JOIN dron_pilotos p      ON p.id  = s.piloto_id
            LEFT JOIN dron_formas_pago fp ON fp.id = s.forma_pago_id
            WHERE s.id = :id
        ");
        $st->execute([':id' => $id]);
        $sol = $st->fetch();
        if (!$sol) return [];

        // costos
        $st = $this->pdo->prepare("SELECT * FROM drones_solicitud_costos WHERE solicitud_id = :id");
        $st->execute([':id' => $id]);
        $costos = $st->fetch() ?: null;

        // items
        $st = $this->pdo->prepare("
            SELECT i.*,
                   dp.nombre AS patologia_nombre,
                   ps.nombre AS producto_nombre,
                   ps.principio_activo,
                   ps.costo_hectarea AS producto_costo_hectarea
            FROM drones_solicitud_item i
            LEFT JOIN dron_patologias      dp ON dp.id  = i.patologia_id
            LEFT JOIN dron_productos_stock ps ON ps.id  = i.producto_id
            WHERE i.solicitud_id = :id
            ORDER BY i.id ASC
        ");
        $st->execute([':id' => $id]);
        $items = $st->fetchAll();

        // recetas por item
        $stRec = $this->pdo->prepare("SELECT * FROM drones_solicitud_item_receta WHERE solicitud_item_id = :sid ORDER BY id ASC");
        foreach ($items as &$it) {
            $stRec->execute([':sid' => $it['id']]);
            $it['recetas'] = $stRec->fetchAll() ?: [];
        }
        unset($it);

        // motivos
        $st = $this->pdo->prepare("
            SELECT m.*, dp.nombre AS patologia_nombre
            FROM drones_solicitud_motivo m
            LEFT JOIN dron_patologias dp ON dp.id = m.patologia_id
            WHERE m.solicitud_id = :id
            ORDER BY m.id ASC
        ");
        $st->execute([':id' => $id]);
        $motivos = $st->fetchAll();

        // rangos
        $st = $this->pdo->prepare("SELECT * FROM drones_solicitud_rango WHERE solicitud_id = :id ORDER BY id ASC");
        $st->execute([':id' => $id]);
        $rangos = $st->fetchAll();

        // parámetros de vuelo
        $st = $this->pdo->prepare("SELECT * FROM drones_solicitud_parametros WHERE solicitud_id = :id LIMIT 1");
        $st->execute([':id' => $id]);
        $parametros = $st->fetch() ?: null;

        // productor (usuario)
        $prod = null;
        if (!empty($sol['productor_id_real'])) {
            $st = $this->pdo->prepare("
                SELECT u.id, u.usuario, u.rol, u.permiso_ingreso, u.cuit, u.id_real,
                       ui.nombre, ui.direccion, ui.telefono, ui.correo
                FROM usuarios u
                LEFT JOIN usuarios_info ui ON ui.usuario_id = u.id
                WHERE u.id_real = :idr
                LIMIT 1
            ");
            $st->execute([':idr' => $sol['productor_id_real']]);
            $prod = $st->fetch() ?: null;

            if ($prod) {
                $st2 = $this->pdo->prepare("
                    SELECT rpc.*, u.usuario AS cooperativa_usuario 
                    FROM rel_productor_coop rpc
                    LEFT JOIN usuarios u ON u.id_real = rpc.cooperativa_id_real
                    WHERE rpc.productor_id_real = :idr
                ");
                $st2->execute([':idr' => $sol['productor_id_real']]);
                $prod['cooperativas'] = $st2->fetchAll() ?: [];
            }
        }

        return [
            'solicitud'   => $sol,
            'costos'      => $costos,
            'items'       => $items,
            'motivos'     => $motivos,
            'rangos'      => $rangos,
            'parametros'  => $parametros,
            'productor'   => $prod,
            'piloto'      => [
                'nombre' => $sol['piloto_nombre'] ?? null,
                'telefono' => $sol['piloto_telefono'] ?? null,
                'zona_asignada' => $sol['piloto_zona_asignada'] ?? null,
                'correo' => $sol['piloto_correo'] ?? null
            ],
            'forma_pago'  => [
                'nombre' => $sol['forma_pago_nombre'] ?? null,
                'descripcion' => $sol['forma_pago_descripcion'] ?? null
            ],
            'eventos'     => [] // opcional
        ];
    }

    /** Catálogos */
    public function listPilotos(): array
    {
        $st = $this->pdo->query("SELECT id, nombre FROM dron_pilotos WHERE activo='si' ORDER BY nombre");
        return $st->fetchAll() ?: [];
    }
    public function listFormasPago(): array
    {
        $st = $this->pdo->query("SELECT id, nombre FROM dron_formas_pago WHERE activo='si' ORDER BY nombre");
        return $st->fetchAll() ?: [];
    }
    public function listPatologias(): array
    {
        $st = $this->pdo->query("SELECT id, nombre FROM dron_patologias WHERE activo='si' ORDER BY nombre");
        return $st->fetchAll() ?: [];
    }
    public function listProductos(): array
    {
        $st = $this->pdo->query("SELECT id, nombre, costo_hectarea FROM dron_productos_stock WHERE activo='si' ORDER BY nombre");
        return $st->fetchAll() ?: [];
    }
    public function listCooperativas(): array
    {
        $sql = "SELECT id_real, usuario FROM usuarios 
                WHERE rol = 'cooperativa' AND permiso_ingreso = 'Habilitado'
                ORDER BY usuario";
        $st = $this->pdo->query($sql);
        return $st->fetchAll() ?: [];
    }

    /** Actualiza solicitud + tablas relacionadas (transaccional) */
    public function actualizarSolicitud(array $p): int
    {
        if (empty($p['id']) || !is_int($p['id'])) {
            throw new InvalidArgumentException('ID inválido');
        }
        $id = $p['id'];
        $s  = $p['solicitud'] ?? [];
        $c          = $p['costos']      ?? null;
        $motivos    = $p['motivos']     ?? [];
        $items      = $p['items']       ?? [];
        $rangos     = $p['rangos']      ?? [];
        $parametros = $p['parametros']  ?? null;

        $this->pdo->beginTransaction();
        try {
            $estado = isset($s['estado']) ? strtolower((string)$s['estado']) : null;
            $validEstados = ['ingresada', 'procesando', 'aprobada_coop', 'cancelada', 'completada'];
            if ($estado !== null && !in_array($estado, $validEstados, true)) {
                throw new InvalidArgumentException('Estado no válido');
            }

            // UPDATE base
            $update = $this->pdo->prepare("
                UPDATE drones_solicitud SET
                    productor_id_real = :productor_id_real,
                    representante     = :representante,
                    linea_tension     = :linea_tension,
                    zona_restringida  = :zona_restringida,
                    corriente_electrica = :corriente_electrica,
                    agua_potable      = :agua_potable,
                    libre_obstaculos  = :libre_obstaculos,
                    area_despegue     = :area_despegue,
                    superficie_ha     = :superficie_ha,
                    fecha_visita      = :fecha_visita,
                    hora_visita_desde = :hora_visita_desde,
                    hora_visita_hasta = :hora_visita_hasta,
                    piloto_id         = :piloto_id,
                    forma_pago_id     = :forma_pago_id,
                    coop_descuento_nombre = :coop_descuento_nombre,
                    dir_provincia     = :dir_provincia,
                    dir_localidad     = :dir_localidad,
                    dir_calle         = :dir_calle,
                    dir_numero        = :dir_numero,
                    en_finca          = :en_finca,
                    ubicacion_lat     = :ubicacion_lat,
                    ubicacion_lng     = :ubicacion_lng,
                    ubicacion_acc     = :ubicacion_acc,
                    ubicacion_ts      = :ubicacion_ts,
                    observaciones     = :observaciones,
                    ses_usuario       = :ses_usuario,
                    estado            = COALESCE(:estado, estado),
                    motivo_cancelacion= :motivo_cancelacion,
                    updated_at        = NOW()
                WHERE id = :id
            ");
            $update->execute([
                ':productor_id_real' => self::n($s['productor_id_real'] ?? null),
                ':representante'     => self::yn($s['representante'] ?? null),
                ':linea_tension'     => self::yn($s['linea_tension'] ?? null),
                ':zona_restringida'  => self::yn($s['zona_restringida'] ?? null),
                ':corriente_electrica' => self::yn($s['corriente_electrica'] ?? null),
                ':agua_potable'      => self::yn($s['agua_potable'] ?? null),
                ':libre_obstaculos'  => self::yn($s['libre_obstaculos'] ?? null),
                ':area_despegue'     => self::yn($s['area_despegue'] ?? null),
                ':superficie_ha'     => self::dec($s['superficie_ha'] ?? null),
                ':fecha_visita'      => self::d($s['fecha_visita'] ?? null),
                ':hora_visita_desde' => self::t($s['hora_visita_desde'] ?? null),
                ':hora_visita_hasta' => self::t($s['hora_visita_hasta'] ?? null),
                ':piloto_id'         => self::intOrNull($s['piloto_id'] ?? null),
                ':forma_pago_id'     => self::intOrNull($s['forma_pago_id'] ?? null),
                ':coop_descuento_nombre' => self::n($s['coop_descuento_nombre'] ?? null),
                ':dir_provincia'     => self::n($s['dir_provincia'] ?? null),
                ':dir_localidad'     => self::n($s['dir_localidad'] ?? null),
                ':dir_calle'         => self::n($s['dir_calle'] ?? null),
                ':dir_numero'        => self::n($s['dir_numero'] ?? null),
                ':en_finca'          => self::yn($s['en_finca'] ?? null),
                ':ubicacion_lat'     => self::dec($s['ubicacion_lat'] ?? null),
                ':ubicacion_lng'     => self::dec($s['ubicacion_lng'] ?? null),
                ':ubicacion_acc'     => self::dec($s['ubicacion_acc'] ?? null),
                ':ubicacion_ts'      => self::dt($s['ubicacion_ts'] ?? null),
                ':observaciones'     => self::n($s['observaciones'] ?? null),
                ':ses_usuario'       => self::n($s['ses_usuario'] ?? null),
                ':estado'            => $estado,
                ':motivo_cancelacion' => self::n($s['motivo_cancelacion'] ?? null),
                ':id'                => $id
            ]);

            // Costos (upsert simple)
            $this->pdo->prepare("DELETE FROM drones_solicitud_costos WHERE solicitud_id=:id")->execute([':id' => $id]);
            if ($c) {
                $this->pdo->prepare("
                    INSERT INTO drones_solicitud_costos
                    (solicitud_id, moneda, costo_base_por_ha, base_ha, base_total, productos_total, total, desglose_json, created_at)
                    VALUES (:sid, :moneda, :costo_base_por_ha, :base_ha, :base_total, :productos_total, :total, :desglose_json, NOW())
                ")->execute([
                    ':sid' => $id,
                    ':moneda' => self::n($c['moneda'] ?? 'Pesos'),
                    ':costo_base_por_ha' => self::dec($c['costo_base_por_ha'] ?? null),
                    ':base_ha' => self::dec($c['base_ha'] ?? null),
                    ':base_total' => self::dec($c['base_total'] ?? null),
                    ':productos_total' => self::dec($c['productos_total'] ?? null),
                    ':total' => self::dec($c['total'] ?? null),
                    ':desglose_json' => self::n($c['desglose_json'] ?? null),
                ]);
            }

            // Motivos
            $this->pdo->prepare("DELETE FROM drones_solicitud_motivo WHERE solicitud_id=:id")->execute([':id' => $id]);
            if ($motivos) {
                $insM = $this->pdo->prepare("
                    INSERT INTO drones_solicitud_motivo (solicitud_id, patologia_id, es_otros, otros_text, created_at)
                    VALUES (:sid, :pid, :es, :txt, NOW())
                ");
                foreach ($motivos as $m) {
                    $insM->execute([
                        ':sid' => $id,
                        ':pid' => self::intOrNull($m['patologia_id'] ?? null),
                        ':es'  => !empty($m['es_otros']) ? 1 : 0,
                        ':txt' => self::n($m['otros_text'] ?? null)
                    ]);
                }
            }

            // Items + recetas
            $this->pdo->prepare("DELETE r FROM drones_solicitud_item_receta r INNER JOIN drones_solicitud_item i ON i.id=r.solicitud_item_id WHERE i.solicitud_id=:id")->execute([':id' => $id]);
            $this->pdo->prepare("DELETE FROM drones_solicitud_item WHERE solicitud_id=:id")->execute([':id' => $id]);
            if ($items) {
                $insI = $this->pdo->prepare("
                    INSERT INTO drones_solicitud_item
                    (solicitud_id, patologia_id, fuente, producto_id, costo_hectarea_snapshot, total_producto_snapshot, nombre_producto, created_at)
                    VALUES (:sid, :pid, :fuente, :prod, :chs, :tps, :np, NOW())
                ");
                $insR = $this->pdo->prepare("
                    INSERT INTO drones_solicitud_item_receta
                    (solicitud_item_id, principio_activo, dosis, unidad, orden_mezcla, notas, created_by, created_at)
                    VALUES (:iid, :pa, :dosis, :unidad, :orden, :notas, :cb, NOW())
                ");
                foreach ($items as $it) {
                    $insI->execute([
                        ':sid' => $id,
                        ':pid' => self::intOrNull($it['patologia_id'] ?? null),
                        ':fuente' => in_array(($it['fuente'] ?? ''), ['sve', 'productor'], true) ? $it['fuente'] : 'sve',
                        ':prod' => self::intOrNull($it['producto_id'] ?? null),
                        ':chs'  => self::dec($it['costo_hectarea_snapshot'] ?? null),
                        ':tps'  => self::dec($it['total_producto_snapshot'] ?? null),
                        ':np'   => self::n($it['nombre_producto'] ?? null)
                    ]);
                    $iid = (int)$this->pdo->lastInsertId();
                    foreach ($it['recetas'] ?? [] as $r) {
                        $insR->execute([
                            ':iid' => $iid,
                            ':pa'  => self::n($r['principio_activo'] ?? null),
                            ':dosis' => self::dec($r['dosis'] ?? null),
                            ':unidad' => self::n($r['unidad'] ?? null),
                            ':orden' => self::intOrNull($r['orden_mezcla'] ?? null),
                            ':notas' => self::n($r['notas'] ?? null),
                            ':cb'   => self::n($s['ses_usuario'] ?? 'sistema')
                        ]);
                    }
                }
            }

            // Rangos
            $this->pdo->prepare("DELETE FROM drones_solicitud_rango WHERE solicitud_id=:id")->execute([':id' => $id]);
            if ($rangos) {
                $insRango = $this->pdo->prepare("INSERT INTO drones_solicitud_rango (solicitud_id, rango, created_at) VALUES (:sid,:rango,NOW())");
                foreach ($rangos as $r) {
                    $insRango->execute([':sid' => $id, ':rango' => self::n($r['rango'] ?? null)]);
                }
            }

            // Parámetros de vuelo
            $this->pdo->prepare("DELETE FROM drones_solicitud_parametros WHERE solicitud_id=:id")->execute([':id' => $id]);
            if ($parametros) {
                $this->pdo->prepare("
                    INSERT INTO drones_solicitud_parametros
                    (solicitud_id, volumen_ha, velocidad_vuelo, alto_vuelo, ancho_pasada, tamano_gota, observaciones, created_at)
                    VALUES (:sid,:vol,:vel,:alto,:ancho,:gota,:obs,NOW())
                ")->execute([
                    ':sid' => $id,
                    ':vol' => self::dec($parametros['volumen_ha'] ?? null),
                    ':vel' => self::dec($parametros['velocidad_vuelo'] ?? null),
                    ':alto' => self::dec($parametros['alto_vuelo'] ?? null),
                    ':ancho' => self::dec($parametros['ancho_pasada'] ?? null),
                    ':gota' => self::n($parametros['tamano_gota'] ?? null),
                    ':obs' => self::n($parametros['observaciones'] ?? null),
                ]);
            }

            // Evento audit
            $this->pdo->prepare("
                INSERT INTO drones_solicitud_evento (solicitud_id, tipo, detalle, payload, actor, created_at)
                VALUES (:sid,'actualizacion','Actualización vía drawer', :payload, :actor, NOW())
            ")->execute([
                ':sid' => $id,
                ':payload' => json_encode($p, JSON_UNESCAPED_UNICODE),
                ':actor' => self::n($s['ses_usuario'] ?? 'sistema')
            ]);

            $this->pdo->commit();
            return $id;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // utils
    private static function n($v)
    {
        return $v === '' ? null : $v;
    }
    private static function intOrNull($v)
    {
        return ($v === '' || $v === null) ? null : (int)$v;
    }
    private static function dec($v)
    {
        return ($v === '' || $v === null) ? null : (float)$v;
    }
    private static function d($v)
    {
        return ($v === '' || $v === null) ? null : $v;
    }
    private static function t($v)
    {
        return ($v === '' || $v === null) ? null : $v;
    }
    private static function dt($v)
    {
        return ($v === '' || $v === null) ? null : str_replace('T', ' ', $v);
    }
    private static function yn($v)
    {
        $v = strtolower((string)($v ?? 'no'));
        return $v === 'si' ? 'si' : 'no';
    }
}
