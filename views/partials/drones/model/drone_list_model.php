<?php
// MODEL: sólo lectura de listado con filtros básicos (sin drawer / sin full / sin update)
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

    /** Listado para tarjetas con filtros básicos. */
    public function listarSolicitudes(array $f): array
    {
        $where  = [];
        $params = [];

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
                    CONCAT(LPAD(HOUR(s.hora_visita_desde),2,'0'), ':', LPAD(MINUTE(s.hora_visita_desde),2,'0'),
                           ' - ',
                           LPAD(HOUR(s.hora_visita_hasta),2,'0'),  ':', LPAD(MINUTE(s.hora_visita_hasta),2,'0'))
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
        foreach ($params as $k => $v) $st->bindValue($k, $v);
        $st->execute();
        $rows = $st->fetchAll() ?: [];

        return ['items' => $rows, 'total' => count($rows)];
    }
}
