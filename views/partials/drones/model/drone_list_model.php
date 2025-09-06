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
                p.nombre AS piloto,      -- nombre visible
                s.piloto_id,             -- id almacenado
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
                s.motivo_cancelacion
            FROM drones_solicitud s
            LEFT JOIN dron_pilotos p ON p.id = s.piloto_id
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
    // 1) Solicitud base + piloto + forma de pago
    $st = $this->pdo->prepare("
        SELECT
            s.*,
            p.nombre        AS piloto_nombre,
            p.telefono      AS piloto_telefono,
            p.zona_asignada AS piloto_zona_asignada,
            p.correo        AS piloto_correo,
            fp.nombre       AS forma_pago_nombre,
            fp.descripcion  AS forma_pago_descripcion
        FROM drones_solicitud s
        LEFT JOIN dron_pilotos p     ON p.id  = s.piloto_id
        LEFT JOIN dron_formas_pago fp ON fp.id = s.forma_pago_id
        WHERE s.id = :id
        LIMIT 1
    ");
    $st->execute([':id'=>$id]);
    $sol = $st->fetch();
    if (!$sol) return [];

    // 2) Costos (1:1)
    $st = $this->pdo->prepare("
        SELECT *
        FROM drones_solicitud_costos
        WHERE solicitud_id = :id
        LIMIT 1
    ");
    $st->execute([':id'=>$id]);
    $costos = $st->fetch() ?: null;

    // 3) Items (N) + joins a patologías y productos
    $st = $this->pdo->prepare("
        SELECT
            i.*,
            pa.nombre  AS patologia_nombre,
            ps.nombre  AS producto_nombre,
            ps.principio_activo,
            ps.costo_hectarea AS producto_costo_hectarea
        FROM drones_solicitud_item i
        LEFT JOIN dron_patologias pa     ON pa.id = i.patologia_id
        LEFT JOIN dron_productos_stock ps ON ps.id = i.producto_id
        WHERE i.solicitud_id = :id
        ORDER BY i.id ASC
    ");
    $st->execute([':id'=>$id]);
    $items = $st->fetchAll() ?: [];

    // 3.1) Recetas por item (N por cada item)
    $stRec = $this->pdo->prepare("
        SELECT *
        FROM drones_solicitud_item_receta
        WHERE solicitud_item_id = :item_id
        ORDER BY
            COALESCE(orden_mezcla, 32767) ASC,
            id ASC
    ");
    foreach ($items as &$it) {
        $stRec->execute([':item_id' => $it['id']]);
        $it['recetas'] = $stRec->fetchAll() ?: [];
    }
    unset($it);

    // 4) Motivos (N)
    $st = $this->pdo->prepare("
        SELECT
            m.*,
            pa.nombre AS patologia_nombre
        FROM drones_solicitud_motivo m
        LEFT JOIN dron_patologias pa ON pa.id = m.patologia_id
        WHERE m.solicitud_id = :id
        ORDER BY m.id ASC
    ");
    $st->execute([':id'=>$id]);
    $motivos = $st->fetchAll() ?: [];

    // 5) Rangos (N)
    $st = $this->pdo->prepare("
        SELECT *
        FROM drones_solicitud_rango
        WHERE solicitud_id = :id
        ORDER BY id ASC
    ");
    $st->execute([':id'=>$id]);
    $rangos = $st->fetchAll() ?: [];

    // 6) Eventos (N)
    $st = $this->pdo->prepare("
        SELECT *
        FROM drones_solicitud_evento
        WHERE solicitud_id = :id
        ORDER BY id DESC
    ");
    $st->execute([':id'=>$id]);
    $eventos = $st->fetchAll() ?: [];

    // 7) Productor (usuarios + usuarios_info) + cooperativas vinculadas
    $productor = null;
    if (!empty($sol['productor_id_real'])) {
        // 7.1) Datos del productor
        $st = $this->pdo->prepare("
            SELECT
                u.id,
                u.usuario,
                u.rol,
                u.permiso_ingreso,
                u.cuit,
                u.id_real,
                ui.nombre,
                ui.direccion,
                ui.telefono,
                ui.correo
            FROM usuarios u
            LEFT JOIN usuarios_info ui ON ui.usuario_id = u.id
            WHERE u.id_real = :id_real
            LIMIT 1
        ");
        $st->execute([':id_real' => $sol['productor_id_real']]);
        $productor = $st->fetch() ?: null;

        // 7.2) Cooperativas asociadas al productor
        $st = $this->pdo->prepare("
            SELECT
                rpc.*,
                coop.usuario AS cooperativa_usuario,
                coop.id_real AS cooperativa_id_real
            FROM rel_productor_coop rpc
            LEFT JOIN usuarios coop ON coop.id_real = rpc.cooperativa_id_real
            WHERE rpc.productor_id_real = :id_real
            ORDER BY rpc.id ASC
        ");
        $st->execute([':id_real' => $sol['productor_id_real']]);
        $productor_coops = $st->fetchAll() ?: [];
        if ($productor !== null) {
            $productor['cooperativas'] = $productor_coops;
        }
    }

    // Ensamblado final (con algunas conversiones)
    $solicitud = $sol; // devolvemos todas las columnas de s.*, además de alias
    $solicitud['id'] = (int)$solicitud['id'];

    $piloto = null;
    if (!empty($sol['piloto_id']) || !empty($sol['piloto_nombre'])) {
        $piloto = [
            'id'              => isset($sol['piloto_id']) ? (int)$sol['piloto_id'] : null,
            'nombre'          => $sol['piloto_nombre'] ?? null,
            'telefono'        => $sol['piloto_telefono'] ?? null,
            'zona_asignada'   => $sol['piloto_zona_asignada'] ?? null,
            'correo'          => $sol['piloto_correo'] ?? null,
        ];
    }

    $formaPago = null;
    if (!empty($sol['forma_pago_id']) || !empty($sol['forma_pago_nombre'])) {
        $formaPago = [
            'id'          => isset($sol['forma_pago_id']) ? (int)$sol['forma_pago_id'] : null,
            'nombre'      => $sol['forma_pago_nombre'] ?? null,
            'descripcion' => $sol['forma_pago_descripcion'] ?? null,
        ];
    }

    return [
        'solicitud'  => $solicitud,
        'piloto'     => $piloto,
        'forma_pago' => $formaPago,
        'productor'  => $productor,
        'costos'     => $costos,
        'items'      => $items,
        'motivos'    => $motivos,
        'rangos'     => $rangos,
        'eventos'    => $eventos,
    ];
}

}
