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
            $where[] = "p.nombre LIKE :piloto";
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
                p.nombre AS piloto,
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
            LEFT JOIN dron_pilotos p            ON p.id = s.piloto_id
            LEFT JOIN drones_solicitud_costos c ON c.solicitud_id = s.id
            LEFT JOIN usuarios u                ON u.id_real = s.productor_id_real
            LEFT JOIN usuarios_info ui          ON ui.usuario_id = u.id
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
            $where[]      = "(s.ses_usuario LIKE :q OR p.nombre LIKE :q OR s.productor_id_real LIKE :q)";
            $params[':q'] = '%' . $f['q'] . '%';
        }
        if (!empty($f['ses_usuario'])) {
            $where[] = "s.ses_usuario LIKE :ses_usuario";
            $params[':ses_usuario'] = '%' . $f['ses_usuario'] . '%';
        }
        if (!empty($f['piloto'])) {
            $where[] = "p.nombre LIKE :piloto";
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
                s.productor_id_real,
                s.ses_usuario,
                COALESCE(ui.nombre, u.usuario) AS productor_nombre,
                p.nombre AS piloto,
                s.piloto_id,
                s.fecha_visita,
                s.hora_visita_desde,
                s.hora_visita_hasta,
                s.observaciones,
                s.estado,
                s.motivo_cancelacion,
                s.coop_descuento_nombre,
                c.moneda,
                c.costo_base_por_ha,
                c.base_ha,
                c.base_total,
                c.productos_total,
                c.total,
                s.created_at,
                s.updated_at
            FROM drones_solicitud s
            LEFT JOIN dron_pilotos p            ON p.id = s.piloto_id
            LEFT JOIN drones_solicitud_costos c ON c.solicitud_id = s.id
            LEFT JOIN usuarios u                ON u.id_real = s.productor_id_real
            LEFT JOIN usuarios_info ui          ON ui.usuario_id = u.id
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
