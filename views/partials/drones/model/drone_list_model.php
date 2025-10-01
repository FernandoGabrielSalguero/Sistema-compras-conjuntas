<?php

declare(strict_types=1);

final class DroneListModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /**
     * Elimina una solicitud y sus costos asociados (si existen),
     * validando autorización de visibilidad del actor.
     * @param int   $id
     * @param array $ctx ['rol'=>string,'id_real'=>string]
     */
    public function eliminarSolicitud(int $id, array $ctx = []): bool
    {
        if ($id <= 0) {
            return false;
        }

        // Comprobación de visibilidad: solo permite borrar si el actor "ve" esa solicitud
        $params = [':id' => $id];
        $pred   = AuthzVista::sqlVisibleProductores('s.productor_id_real', $ctx, $params);

        $sqlChk = "
            SELECT s.id
            FROM drones_solicitud s
            WHERE s.id = :id AND {$pred}
            LIMIT 1
        ";
        $stChk = $this->pdo->prepare($sqlChk);
        foreach ($params as $k => $v) {
            $stChk->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stChk->execute();
        if (!$stChk->fetch()) {
            // No visible → actúa como no encontrado por seguridad
            return false;
        }

        $this->pdo->beginTransaction();
        try {
            // Borrar costos asociados (si no hay FK ON DELETE CASCADE)
            $st1 = $this->pdo->prepare('DELETE FROM drones_solicitud_costos WHERE solicitud_id = :id');
            $st1->bindValue(':id', $id, PDO::PARAM_INT);
            $st1->execute();

            // Borrar la solicitud
            $st2 = $this->pdo->prepare('DELETE FROM drones_solicitud WHERE id = :id');
            $st2->bindValue(':id', $id, PDO::PARAM_INT);
            $st2->execute();

            $ok = ($st2->rowCount() > 0);
            $this->pdo->commit();
            return $ok;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Listado para tarjetas con filtros básicos + visibilidad por rol.
     * @param array $f   Filtros provenientes del controller
     * @param array $ctx ['rol'=>string,'id_real'=>string]
     */
    public function listarSolicitudes(array $f, array $ctx = []): array
    {
        $where  = [];
        $params = [];

        // Predicado de visibilidad
        $pred = AuthzVista::sqlVisibleProductores('s.productor_id_real', $ctx, $params);
        $where[] = $pred;

        if (!empty($f['q'])) {
            $where[]      = "(s.ses_usuario LIKE :q OR p.nombre LIKE :q OR s.productor_id_real LIKE :q)";
            $params[':q'] = '%' . $f['q'] . '%';
        }
        if (!empty($f['ses_usuario'])) {
            $where[] = "s.ses_usuario LIKE :ses_usuario";
            $params[':ses_usuario'] = '%' . $f['ses_usuario'] . '%';
        }
        if (!empty($f['piloto'])) {
            $where[] = "(COALESCE(uip.nombre, up.usuario) LIKE :piloto)";
            $params[':piloto'] = '%' . $f['piloto'] . '%';
        }
        if (!empty($f['estado'])) {
            $where[] = "s.estado = :estado";
            $params[':estado'] = strtolower(trim($f['estado']));
        }
        if (!empty($f['fecha_visita'])) {
            $where[] = "s.fecha_visita = :fecha_visita";
            $params[':fecha_visita'] = $f['fecha_visita'];
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "
    SELECT
        s.id,
        s.ses_usuario,
        s.productor_id_real,
        COALESCE(ui.nombre, u.usuario) AS productor_nombre,

        /* Piloto: usuarios/usuarios_info */
        COALESCE(uip.nombre, up.usuario) AS piloto,
        s.piloto_id,

        s.fecha_visita,
        CASE
          WHEN s.hora_visita_desde IS NOT NULL AND s.hora_visita_hasta IS NOT NULL THEN
            CONCAT(
              LPAD(HOUR(s.hora_visita_desde),2,'0'), ':', LPAD(MINUTE(s.hora_visita_desde),2,'0'),
              ' - ',
              LPAD(HOUR(s.hora_visita_hasta),2,'0'),  ':', LPAD(MINUTE(s.hora_visita_hasta),2,'0')
            )
          ELSE NULL
        END AS hora_visita,
        s.observaciones,
        s.estado,
        s.motivo_cancelacion,
        s.coop_descuento_nombre,
        c.total AS costo_total
    FROM drones_solicitud s
    LEFT JOIN drones_solicitud_costos c ON c.solicitud_id = s.id

    /* Productor */
    LEFT JOIN usuarios u   ON u.id_real = s.productor_id_real
    LEFT JOIN usuarios_info ui  ON ui.usuario_id = u.id

    /* Piloto (NUEVO origen) */
    LEFT JOIN usuarios up      ON up.id = s.piloto_id
    LEFT JOIN usuarios_info uip ON uip.usuario_id = up.id

    $whereSql
    ORDER BY s.created_at DESC, s.id DESC
";


        $st = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $st->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $st->execute();
        $rows = $st->fetchAll() ?: [];

        return ['items' => $rows, 'total' => count($rows)];
    }

    /**
     * Exporta solicitudes + costos respetando visibilidad y filtros.
     * @param array $f   Filtros
     * @param array $ctx Contexto sesión ['rol','id_real']
     */
    public function exportSolicitudes(array $f, array $ctx = []): array
    {
        $where  = [];
        $params = [];

        // Visibilidad por rol (cooperativa/ingeniero/productor/SVE)
        $pred = AuthzVista::sqlVisibleProductores('s.productor_id_real', $ctx, $params);
        $where[] = $pred;

        // Filtros
        if (!empty($f['q'])) {
            $where[]      = "(s.ses_usuario LIKE :q OR s.productor_id_real LIKE :q)";
            $params[':q'] = '%' . $f['q'] . '%';
        }
        if (!empty($f['ses_usuario'])) {
            $where[] = "s.ses_usuario LIKE :ses_usuario";
            $params[':ses_usuario'] = '%' . $f['ses_usuario'] . '%';
        }
        if (!empty($f['piloto'])) {
            // Búsqueda por nombre del piloto (usuarios_info.nombre) o usuario
            $where[] = "(COALESCE(uip.nombre, up.usuario) LIKE :piloto)";
            $params[':piloto'] = '%' . $f['piloto'] . '%';
        }
        if (!empty($f['estado'])) {
            $where[] = "s.estado = :estado";
            $params[':estado'] = strtolower(trim($f['estado']));
        }
        if (!empty($f['fecha_visita'])) {
            $where[] = "s.fecha_visita = :fecha_visita";
            $params[':fecha_visita'] = $f['fecha_visita'];
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        /**
         * Export FULL:
         * - s_*: todas columnas de drones_solicitud
         * - c_*: costos (1:1)
         * - fp_*: forma de pago (1:1)
         * - u_*, ui_*: productor (usuarios / usuarios_info)
         * - up_*, uip_*: piloto (usuarios / usuarios_info)
         * - sp_*: parámetros (1:1)
         * - rangos_json: JSON array de rangos
         * - items_json: JSON array de items + producto + patología + recetas por item
         * - motivos_json: JSON array de motivos
         * - eventos_json: JSON array de eventos
         */
        $sql = "
    SELECT
        /* ===== drones_solicitud (prefijo s_) ===== */
        s.id                          AS s_id,
        s.productor_id_real           AS s_productor_id_real,
        s.representante               AS s_representante,
        s.linea_tension               AS s_linea_tension,
        s.zona_restringida            AS s_zona_restringida,
        s.corriente_electrica         AS s_corriente_electrica,
        s.agua_potable                AS s_agua_potable,
        s.libre_obstaculos            AS s_libre_obstaculos,
        s.area_despegue               AS s_area_despegue,
        s.superficie_ha               AS s_superficie_ha,
        s.fecha_visita                AS s_fecha_visita,
        s.hora_visita_desde           AS s_hora_visita_desde,
        s.hora_visita_hasta           AS s_hora_visita_hasta,
        s.piloto_id                   AS s_piloto_id,
        s.forma_pago_id               AS s_forma_pago_id,
        s.coop_descuento_nombre       AS s_coop_descuento_nombre,
        s.dir_provincia               AS s_dir_provincia,
        s.dir_localidad               AS s_dir_localidad,
        s.dir_calle                   AS s_dir_calle,
        s.dir_numero                  AS s_dir_numero,
        s.en_finca                    AS s_en_finca,
        s.ubicacion_lat               AS s_ubicacion_lat,
        s.ubicacion_lng               AS s_ubicacion_lng,
        s.ubicacion_acc               AS s_ubicacion_acc,
        s.ubicacion_ts                AS s_ubicacion_ts,
        s.observaciones               AS s_observaciones,
        s.ses_usuario                 AS s_ses_usuario,
        s.ses_rol                     AS s_ses_rol,
        s.ses_nombre                  AS s_ses_nombre,
        s.ses_correo                  AS s_ses_correo,
        s.ses_telefono                AS s_ses_telefono,
        s.ses_direccion               AS s_ses_direccion,
        s.ses_cuit                    AS s_ses_cuit,
        s.ses_last_activity_ts        AS s_ses_last_activity_ts,
        s.estado                      AS s_estado,
        s.motivo_cancelacion          AS s_motivo_cancelacion,
        s.created_at                  AS s_created_at,
        s.updated_at                  AS s_updated_at,

        /* ===== costos (1:1) ===== */
        c.id                          AS c_id,
        c.solicitud_id                AS c_solicitud_id,
        c.moneda                      AS c_moneda,
        c.costo_base_por_ha           AS c_costo_base_por_ha,
        c.base_ha                     AS c_base_ha,
        c.base_total                  AS c_base_total,
        c.productos_total             AS c_productos_total,
        c.total                       AS c_total,
        c.desglose_json               AS c_desglose_json,
        c.created_at                  AS c_created_at,

        /* ===== forma de pago (1:1) ===== */
        fp.id                         AS fp_id,
        fp.nombre                     AS fp_nombre,
        fp.descripcion                AS fp_descripcion,
        fp.activo                     AS fp_activo,
        fp.created_at                 AS fp_created_at,
        fp.updated_at                 AS fp_updated_at,

        /* ===== productor ===== */
        u.id                          AS u_id,
        u.usuario                     AS u_usuario,
        u.rol                         AS u_rol,
        u.permiso_ingreso             AS u_permiso_ingreso,
        u.cuit                        AS u_cuit,
        u.id_real                     AS u_id_real,
        ui.id                         AS ui_id,
        ui.usuario_id                 AS ui_usuario_id,
        ui.nombre                     AS ui_nombre,
        ui.direccion                  AS ui_direccion,
        ui.telefono                   AS ui_telefono,
        ui.correo                     AS ui_correo,
        ui.zona_asignada              AS ui_zona_asignada,

        /* ===== piloto ===== */
        up.id                         AS up_id,
        up.usuario                    AS up_usuario,
        up.rol                        AS up_rol,
        up.permiso_ingreso            AS up_permiso_ingreso,
        up.cuit                       AS up_cuit,
        up.id_real                    AS up_id_real,
        uip.id                        AS uip_id,
        uip.usuario_id                AS uip_usuario_id,
        uip.nombre                    AS uip_nombre,
        uip.direccion                 AS uip_direccion,
        uip.telefono                  AS uip_telefono,
        uip.correo                    AS uip_correo,
        uip.zona_asignada             AS uip_zona_asignada,

        /* ===== parámetros (1:1) ===== */
        sp.id                         AS sp_id,
        sp.solicitud_id               AS sp_solicitud_id,
        sp.volumen_ha                 AS sp_volumen_ha,
        sp.velocidad_vuelo            AS sp_velocidad_vuelo,
        sp.alto_vuelo                 AS sp_alto_vuelo,
        sp.ancho_pasada               AS sp_ancho_pasada,
        sp.tamano_gota                AS sp_tamano_gota,
        sp.observaciones              AS sp_observaciones,
        sp.created_at                 AS sp_created_at,
        sp.updated_at                 AS sp_updated_at,

        /* ===== colecciones (JSON) ===== */
        /* Rangos */
        (
            SELECT
                IFNULL(JSON_ARRAYAGG(
                    JSON_OBJECT(
                        'id', sr.id,
                        'rango', sr.rango,
                        'created_at', sr.created_at
                    )
                ), JSON_ARRAY())
            FROM drones_solicitud_rango sr
            WHERE sr.solicitud_id = s.id
        ) AS rangos_json,

        /* Motivos */
        (
            SELECT
                IFNULL(JSON_ARRAYAGG(
                    JSON_OBJECT(
                        'id', sm.id,
                        'patologia_id', sm.patologia_id,
                        'es_otros', sm.es_otros,
                        'otros_text', sm.otros_text,
                        'created_at', sm.created_at
                    )
                ), JSON_ARRAY())
            FROM drones_solicitud_motivo sm
            WHERE sm.solicitud_id = s.id
        ) AS motivos_json,

        /* Eventos */
        (
            SELECT
                IFNULL(JSON_ARRAYAGG(
                    JSON_OBJECT(
                        'id', se.id,
                        'tipo', se.tipo,
                        'detalle', se.detalle,
                        'payload', se.payload,
                        'actor', se.actor,
                        'created_at', se.created_at
                    )
                ), JSON_ARRAY())
            FROM drones_solicitud_evento se
            WHERE se.solicitud_id = s.id
        ) AS eventos_json,

        /* Items + producto + patología + recetas por item */
        (
            SELECT
                IFNULL(JSON_ARRAYAGG(item_row), JSON_ARRAY())
            FROM (
                SELECT JSON_OBJECT(
                    'id', si.id,
                    'patologia_id', si.patologia_id,
                    'fuente', si.fuente,
                    'producto_id', si.producto_id,
                    'costo_hectarea_snapshot', si.costo_hectarea_snapshot,
                    'total_producto_snapshot', si.total_producto_snapshot,
                    'nombre_producto', si.nombre_producto,
                    'created_at', si.created_at,
                    'updated_at', si.updated_at,

                    'patologia', JSON_OBJECT(
                        'id', dp.id,
                        'nombre', dp.nombre,
                        'descripcion', dp.descripcion,
                        'activo', dp.activo,
                        'created_at', dp.created_at,
                        'updated_at', dp.updated_at
                    ),

                    'producto_stock', JSON_OBJECT(
                        'id', dps.id,
                        'nombre', dps.nombre,
                        'detalle', dps.detalle,
                        'principio_activo', dps.principio_activo,
                        'cantidad_deposito', dps.cantidad_deposito,
                        'costo_hectarea', dps.costo_hectarea,
                        'activo', dps.activo,
                        'created_at', dps.created_at,
                        'updated_at', dps.updated_at
                    ),

                    'receta', (
                        SELECT IFNULL(JSON_ARRAYAGG(
                            JSON_OBJECT(
                                'id', sir.id,
                                'principio_activo', sir.principio_activo,
                                'dosis', sir.dosis,
                                'unidad', sir.unidad,
                                'orden_mezcla', sir.orden_mezcla,
                                'notas', sir.notas,
                                'created_by', sir.created_by,
                                'updated_by', sir.updated_by,
                                'created_at', sir.created_at,
                                'updated_at', sir.updated_at
                            )
                        ), JSON_ARRAY())
                        FROM drones_solicitud_item_receta sir
                        WHERE sir.solicitud_item_id = si.id
                    )
                ) AS item_row
                FROM drones_solicitud_item si
                LEFT JOIN dron_patologias dp ON dp.id = si.patologia_id
                LEFT JOIN dron_productos_stock dps ON dps.id = si.producto_id
                WHERE si.solicitud_id = s.id
            ) t
        ) AS items_json

    FROM drones_solicitud s
    /* 1:1 */
    LEFT JOIN drones_solicitud_costos c ON c.solicitud_id = s.id
    LEFT JOIN dron_formas_pago fp       ON fp.id = s.forma_pago_id

    /* Productor */
    LEFT JOIN usuarios u    ON u.id_real = s.productor_id_real
    LEFT JOIN usuarios_info ui ON ui.usuario_id = u.id

    /* Piloto */
    LEFT JOIN usuarios up      ON up.id = s.piloto_id
    LEFT JOIN usuarios_info uip ON uip.usuario_id = up.id

    $whereSql
    ORDER BY s.created_at DESC, s.id DESC
    ";

        $st = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $st->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $st->execute();
        $rows = $st->fetchAll() ?: [];

        return ['items' => $rows, 'total' => count($rows)];
    }
}
