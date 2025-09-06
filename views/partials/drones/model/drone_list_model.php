<?php
// MODEL LIMPIO: sólo lectura
declare(strict_types=1);

final class DroneListModel
{
    private PDO $pdo;
    public function __construct(PDO $pdo){
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /**
     * Listado para tarjetas con filtros básicos.
     * Filtra por nombre de piloto (JOIN a dron_pilotos si usás piloto_id).
     */
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
            p.nombre        AS piloto,
            s.piloto_id,
            s.productor_id_real,
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

            -- Forma de pago
            s.forma_pago_id,
            fp.nombre       AS forma_pago_nombre,
            -- Si tu schema tiene esta columna, la exponemos; si no, vendrá NULL
            /* ALTER TABLE recomendado:
               ALTER TABLE drones_solicitud ADD COLUMN aprob_cooperativa ENUM('Analizando','Aprobado','Cancelado') NULL; */
            s.aprob_cooperativa,

            -- Cooperativa de pertenencia (NO la de facturación)
            rpc.cooperativa_id_real      AS coop_pertenece_id_real,
            uci.nombre                   AS coop_pertenece_nombre
        FROM drones_solicitud s
        LEFT JOIN dron_pilotos p           ON p.id = s.piloto_id
        LEFT JOIN dron_formas_pago fp      ON fp.id = s.forma_pago_id
        LEFT JOIN rel_productor_coop rpc   ON rpc.productor_id_real = s.productor_id_real
        LEFT JOIN usuarios uc              ON uc.id_real = rpc.cooperativa_id_real
        LEFT JOIN usuarios_info uci        ON uci.usuario_id = uc.id
        $whereSql
        ORDER BY s.created_at DESC, s.id DESC
    ";

    $st = $this->pdo->prepare($sql);
    foreach ($params as $k=>$v) $st->bindValue($k,$v);
    $st->execute();
    $rows = $st->fetchAll() ?: [];

    return ['items'=>$rows, 'total'=>count($rows)];
}


    /**
     * Detalle mínimo (sólo para abrir el drawer y mostrar #id)
     */
    public function obtenerSolicitud(int $id): array
    {
        $st = $this->pdo->prepare("
            SELECT s.*, p.nombre AS piloto
            FROM drones_solicitud s
            LEFT JOIN dron_pilotos p ON p.id = s.piloto_id
            WHERE s.id = :id
        ");
        $st->execute([':id'=>$id]);
        $sol = $st->fetch();
        if (!$sol) return [];

        return [
            'solicitud' => [
                'id'         => (int)$sol['id'],
                'piloto'     => $sol['piloto'] ?? null,
                'piloto_id'  => $sol['piloto_id'] ?? null,
                'estado'     => $sol['estado'] ?? null,
                'fecha_visita' => $sol['fecha_visita'] ?? null,
            ],
            // Todo lo demás queda vacío por ahora
            'motivos'   => [],
            'productos' => [],
            'rangos'    => [],
            'costos'    => null
        ];
    }
}
