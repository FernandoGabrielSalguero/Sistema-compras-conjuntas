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
            // ui.nombre = nombre del productor desde usuarios_info
            $where[]      = "(s.ses_usuario LIKE :q OR ui.nombre LIKE :q OR u.usuario LIKE :q OR s.productor_id_real LIKE :q)";
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
        /* Si por algún motivo total es NULL, calculamos un fallback */
        ROUND(COALESCE(c.total, c.base_total + c.productos_total), 2) AS costo_total
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

        // Predicado de visibilidad
        $pred = AuthzVista::sqlVisibleProductores('s.productor_id_real', $ctx, $params);
        $where[] = $pred;

        if (!empty($f['q'])) {
            // ui.nombre = nombre del productor desde usuarios_info
            $where[]      = "(s.ses_usuario LIKE :q OR ui.nombre LIKE :q OR u.usuario LIKE :q OR s.productor_id_real LIKE :q)";
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
    /* ===== drones_solicitud (prefijo s_) ===== */
    s.agua_potable           AS s_agua_potable,
    s.area_despegue          AS s_area_despegue,
    s.corriente_electrica    AS s_corriente_electrica,
    s.dir_calle              AS s_dir_calle,
    s.dir_localidad          AS s_dir_localidad,
    s.dir_numero             AS s_dir_numero,
    s.dir_provincia          AS s_dir_provincia,
    s.en_finca               AS s_en_finca,
    s.estado                 AS s_estado,
    s.fecha_visita           AS s_fecha_visita,
    s.forma_pago_id          AS s_forma_pago_id,
    s.hora_visita_desde      AS s_hora_visita_desde,
    s.hora_visita_hasta      AS s_hora_visita_hasta,
    s.id                     AS s_id,
    s.libre_obstaculos       AS s_libre_obstaculos,
    s.linea_tension          AS s_linea_tension,
    s.motivo_cancelacion     AS s_motivo_cancelacion,
    s.observaciones          AS s_observaciones,
    s.productor_id_real      AS s_productor_id_real,
    COALESCE(ui.nombre, u.usuario) AS s_productor_nombre,
    s.representante          AS s_representante,
    s.ses_correo             AS s_ses_correo,
    s.ses_nombre             AS s_ses_nombre,
    s.ses_rol                AS s_ses_rol,
    s.ses_telefono           AS s_ses_telefono,
    s.ses_usuario            AS s_ses_usuario,
    s.superficie_ha          AS s_superficie_ha,
    s.zona_restringida       AS s_zona_restringida,

    /* ===== drones_solicitud_costos (prefijo c_) ===== */
    c.base_ha                AS c_base_ha,
    c.base_total             AS c_base_total,
    c.productos_total        AS c_productos_total,
    c.solicitud_id           AS c_solicitud_id,
    c.total                  AS c_total,

    /* ===== drones_solicitud_item (prefijo si_) ===== */
    si.costo_hectarea_snapshot AS si_costo_hectarea_snapshot,
    si.fuente                  AS si_fuente,
    si.nombre_producto         AS si_nombre_producto,
    pa.nombre                  AS si_patologia_nombre,
    si.producto_id             AS si_producto_id,
    ps.nombre                  AS si_producto_nombre,
    si.solicitud_id            AS si_solicitud_id,
    si.total_producto_snapshot AS si_total_producto_snapshot,

    /* ===== drones_solicitud_motivo (prefijo sm_) ===== */
    pm.nombre                 AS sm_patologia_nombre,

    /* ===== drones_solicitud_rango (prefijo sr_) ===== */
    sr.rango                  AS sr_rango

FROM drones_solicitud s
LEFT JOIN drones_solicitud_costos       c   ON c.solicitud_id = s.id
LEFT JOIN drones_solicitud_item         si  ON si.solicitud_id = s.id
LEFT JOIN drones_solicitud_motivo       sm  ON sm.solicitud_id = s.id
LEFT JOIN drones_solicitud_rango        sr  ON sr.solicitud_id = s.id

/* Productor (para s_productor_nombre) */
LEFT JOIN usuarios       u   ON u.id_real = s.productor_id_real
LEFT JOIN usuarios_info  ui  ON ui.usuario_id = u.id

/* Legibles para item/motivo */
LEFT JOIN dron_patologias      pa ON pa.id = si.patologia_id
LEFT JOIN dron_productos_stock ps ON ps.id = si.producto_id
LEFT JOIN dron_patologias      pm ON pm.id = sm.patologia_id

{$whereSql}
ORDER BY s.created_at DESC, s.id DESC, si.id ASC
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
     * Devuelve JSON profundo de una solicitud y TODAS las tablas relacionadas.
     * Respeta visibilidad por rol usando AuthzVista igual que en delete/list.
     * @param int $id
     * @param array $ctx ['rol'=>string,'id_real'=>string]
     * @return array
     * @throws Throwable
     */
    public function obtenerSolicitudDeep(int $id, array $ctx = []): array
    {
        if ($id <= 0) {
            return [];
        }

        $params = [':id' => $id];
        $pred   = AuthzVista::sqlVisibleProductores('s.productor_id_real', $ctx, $params);

        // 1) solicitud (control de visibilidad)
        $sqlSolicitud = "SELECT *
                         FROM drones_solicitud s
                         WHERE s.id = :id AND {$pred}
                         LIMIT 1";
        $stSol = $this->pdo->prepare($sqlSolicitud);
        foreach ($params as $k => $v) {
            $stSol->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stSol->execute();
        $solicitud = $stSol->fetch();
        if (!$solicitud) {
            return []; // no visible o no existe
        }

        // 2) tablas relacionadas por solicitud_id
        $bindId = function (PDOStatement $st) use ($id) {
            $st->bindValue(':id', $id, PDO::PARAM_INT);
            $st->execute();
            return $st->fetchAll() ?: [];
        };

        $qCostos      = $this->pdo->prepare("SELECT * FROM drones_solicitud_costos WHERE solicitud_id = :id");
        $qItems       = $this->pdo->prepare("SELECT * FROM drones_solicitud_item WHERE solicitud_id = :id ORDER BY id ASC");
        $qItemReceta  = $this->pdo->prepare("
            SELECT r.* 
            FROM drones_solicitud_item_receta r
            JOIN drones_solicitud_item i ON i.id = r.solicitud_item_id
            WHERE i.solicitud_id = :id
            ORDER BY r.id ASC
        ");
        $qMotivo      = $this->pdo->prepare("SELECT * FROM drones_solicitud_motivo WHERE solicitud_id = :id");
        $qRango       = $this->pdo->prepare("SELECT * FROM drones_solicitud_rango WHERE solicitud_id = :id");
        $qParametros  = $this->pdo->prepare("SELECT * FROM drones_solicitud_parametros WHERE solicitud_id = :id");
        $qReporte     = $this->pdo->prepare("SELECT * FROM drones_solicitud_Reporte WHERE solicitud_id = :id");
        $qRepMedia    = $this->pdo->prepare("
            SELECT m.* 
            FROM drones_solicitud_reporte_media m
            JOIN drones_solicitud_Reporte r ON r.id = m.reporte_id
            WHERE r.solicitud_id = :id
            ORDER BY m.id ASC
        ");
        $qEventos     = $this->pdo->prepare("SELECT * FROM drones_solicitud_evento WHERE solicitud_id = :id ORDER BY id ASC");

        return [
            'solicitud'         => $solicitud,
            'costos'            => $bindId($qCostos),
            'items'             => $bindId($qItems),
            'items_recetas'     => $bindId($qItemReceta),
            'motivo'            => $bindId($qMotivo),
            'rango'             => $bindId($qRango),
            'parametros'        => $bindId($qParametros),
            'reporte'           => $bindId($qReporte),
            'reporte_media'     => $bindId($qRepMedia),
            'eventos'           => $bindId($qEventos),
        ];
    }
}
