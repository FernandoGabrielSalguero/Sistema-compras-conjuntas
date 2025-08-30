<?php
// views/partials/drones/model/drone_list_model.php

declare(strict_types=1);

class DroneListModel
{
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        // Si querés forzar exceptions:
        // $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Listado con filtros para tarjetas.
     * Filtros soportados: q, ses_usuario, piloto, estado, fecha_visita
     */
    public function listarSolicitudes(array $f): array
    {
        $where  = [];
        $params = [];

        if (!empty($f['q'])) {
            $where[]        = "(s.ses_usuario LIKE :q OR s.piloto LIKE :q OR s.productor_id_real LIKE :q)";
            $params[':q']   = '%' . $f['q'] . '%';
        }
        if (!empty($f['ses_usuario'])) {
            $where[]                = "s.ses_usuario LIKE :ses_usuario";
            $params[':ses_usuario'] = '%' . $f['ses_usuario'] . '%';
        }
        if (!empty($f['piloto'])) {
            $where[]          = "s.piloto LIKE :piloto";
            $params[':piloto'] = '%' . $f['piloto'] . '%';
        }
        if (!empty($f['estado'])) {
            $where[]          = "s.estado = :estado";
            $params[':estado'] = strtolower(trim($f['estado']));
        }
        if (!empty($f['fecha_visita'])) {
            $where[]                  = "s.fecha_visita = :fecha_visita";
            $params[':fecha_visita']  = $f['fecha_visita']; // YYYY-MM-DD
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT
                    s.id,
                    s.ses_usuario,
                    s.piloto,
                    s.productor_id_real,
                    s.fecha_visita,
                    s.hora_visita,
                    s.observaciones,
                    s.estado,
                    s.motivo_cancelacion
                FROM dron_solicitudes s
                $whereSql
                ORDER BY s.created_at DESC";

        $st = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $st->bindValue($k, $v);
        }
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return [
            'items' => $rows,
            'total' => count($rows),
        ];
    }

    /**
     * Detalle completo de una solicitud (con tablas hijas).
     */
    public function obtenerSolicitud(int $id): array
    {
        $st = $this->pdo->prepare("SELECT * FROM dron_solicitudes WHERE id = :id");
        $st->execute([':id' => $id]);
        $sol = $st->fetch(PDO::FETCH_ASSOC);
        if (!$sol) {
            return [];
        }

        // Motivos
        $st = $this->pdo->prepare("
            SELECT motivo, otros_text
            FROM dron_solicitudes_motivos
            WHERE solicitud_id = :id
        ");
        $st->execute([':id' => $id]);
        $motivos = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Productos
        $st = $this->pdo->prepare("
            SELECT tipo, fuente, marca
            FROM dron_solicitudes_productos
            WHERE solicitud_id = :id
        ");
        $st->execute([':id' => $id]);
        $productos = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Rangos
        $st = $this->pdo->prepare("
            SELECT rango
            FROM dron_solicitudes_rangos
            WHERE solicitud_id = :id
        ");
        $st->execute([':id' => $id]);
        $rangos = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return [
            'solicitud' => $sol,
            'motivos'   => $motivos,
            'productos' => $productos,
            'rangos'    => $rangos,
        ];
    }

    public function actualizarSolicitud(int $id, array $data): bool
{
    // Campos permitidos a actualizar (tabla principal)
    $allowed = [
        'piloto','fecha_visita','hora_visita','observaciones','estado','motivo_cancelacion',
        'obs_piloto','responsable','volumen_ha','velocidad_vuelo','alto_vuelo','tamano_gota',
        'dir_provincia','dir_localidad','dir_calle','dir_numero','en_finca',
        'linea_tension','zona_restringida','corriente_electrica','agua_potable',
        'libre_obstaculos','area_despegue','ubicacion_lat','ubicacion_lng','ubicacion_acc'
    ];

    $set = [];
    $params = [':id' => $id];

    foreach ($allowed as $col) {
        if (array_key_exists($col, $data)) {
            $set[] = " $col = :$col ";
            $params[":$col"] = $data[$col];
        }
    }

    if (!$set) {
        return false; // nada para actualizar
    }

    $sql = "UPDATE dron_solicitudes SET ".implode(',', $set)." WHERE id = :id";
    $st  = $this->pdo->prepare($sql);
    return $st->execute($params);
}

}
