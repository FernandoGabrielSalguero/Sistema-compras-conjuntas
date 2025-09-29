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

    // Consolidado
        /**
     * Obtiene el consolidado completo para exportación.
     * Requiere rol SVE (validado en controller).
     * Respeta visibilidad si quisieras limitar, pero para SVE trae todo.
     */
    public function obtenerConsolidado(): array
    {
        // --- Solicitudes (con datos enriquecidos) ---
        $sqlSolicitudes = "
            SELECT
                s.*,
                p.nombre AS piloto_nombre,
                ui.nombre AS productor_nombre,
                u.usuario AS productor_usuario
            FROM drones_solicitud s
            LEFT JOIN dron_pilotos p ON p.id = s.piloto_id
            LEFT JOIN usuarios u     ON u.id_real = s.productor_id_real
            LEFT JOIN usuarios_info ui ON ui.usuario_id = u.id
            ORDER BY s.created_at DESC, s.id DESC
        ";
        $solicitudes = $this->pdo->query($sqlSolicitudes)->fetchAll() ?: [];

        // --- Costos ---
        $sqlCostos = "
            SELECT c.*
            FROM drones_solicitud_costos c
            ORDER BY c.solicitud_id ASC
        ";
        $costos = $this->pdo->query($sqlCostos)->fetchAll() ?: [];

        // --- Items (patologías / productos) ---
        $sqlItems = "
            SELECT
                i.*,
                pa.nombre AS patologia_nombre,
                pr.nombre AS producto_nombre_stock,
                pr.principio_activo AS producto_principio_activo
            FROM drones_solicitud_item i
            LEFT JOIN dron_patologias pa      ON pa.id = i.patologia_id
            LEFT JOIN dron_productos_stock pr ON pr.id = i.producto_id
            ORDER BY i.solicitud_id ASC, i.id ASC
        ";
        $items = $this->pdo->query($sqlItems)->fetchAll() ?: [];

        // --- Recetas por item ---
        $sqlRecetas = "
            SELECT r.*
            FROM drones_solicitud_item_receta r
            ORDER BY r.solicitud_item_id ASC, r.id ASC
        ";
        $recetas = $this->pdo->query($sqlRecetas)->fetchAll() ?: [];

        // --- Eventos ---
        $sqlEventos = "
            SELECT e.*
            FROM drones_solicitud_evento e
            ORDER BY e.solicitud_id ASC, e.id ASC
        ";
        $eventos = $this->pdo->query($sqlEventos)->fetchAll() ?: [];

        // --- Motivos ---
        $sqlMotivos = "
            SELECT m.*
            FROM drones_solicitud_motivo m
            ORDER BY m.solicitud_id ASC, m.id ASC
        ";
        $motivos = $this->pdo->query($sqlMotivos)->fetchAll() ?: [];

        // --- Parámetros ---
        $sqlParametros = "
            SELECT p.*
            FROM drones_solicitud_parametros p
            ORDER BY p.solicitud_id ASC, p.id ASC
        ";
        $parametros = $this->pdo->query($sqlParametros)->fetchAll() ?: [];

        // --- Rangos ---
        $sqlRangos = "
            SELECT r.*
            FROM drones_solicitud_rango r
            ORDER BY r.solicitud_id ASC, r.id ASC
        ";
        $rangos = $this->pdo->query($sqlRangos)->fetchAll() ?: [];

        return [
            'solicitudes' => $solicitudes,
            'costos'      => $costos,
            'items'       => $items,
            'recetas'     => $recetas,
            'eventos'     => $eventos,
            'motivos'     => $motivos,
            'parametros'  => $parametros,
            'rangos'      => $rangos,
        ];
    }

}
