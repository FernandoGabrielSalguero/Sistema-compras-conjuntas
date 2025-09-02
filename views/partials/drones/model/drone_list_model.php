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

        // Motivos: cuando 'motivo' es NULL, tomamos el nombre de la patología
        $st = $this->pdo->prepare("
            SELECT 
                COALESCE(sm.motivo, dp.nombre) AS motivo,
                sm.otros_text
            FROM dron_solicitudes_motivos sm
            LEFT JOIN dron_patologias dp ON dp.id = sm.patologia_id
            WHERE sm.solicitud_id = :id
        ");
        $st->execute([':id' => $id]);
        $motivos = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Productos (con nombre, principio activo y detalles)
        $st = $this->pdo->prepare("
    SELECT
        sp.id,
        sp.fuente,
        sp.marca,
        sp.producto_id,
        COALESCE(ps.nombre, sp.marca)           AS producto,
        COALESCE(ps.principio_activo, sp.principio_activo) AS principio_activo,
        sp.dosis,
        sp.unidad,
        sp.orden_mezcla
    FROM dron_solicitudes_productos sp
    LEFT JOIN dron_productos_stock ps ON ps.id = sp.producto_id
    WHERE sp.solicitud_id = :id
    ORDER BY (sp.orden_mezcla IS NULL), sp.orden_mezcla, sp.id
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
        $allowed = [
            'piloto',
            'fecha_visita',
            'hora_visita',
            'observaciones',
            'estado',
            'motivo_cancelacion',
            'obs_piloto',
            'responsable',
            'volumen_ha',
            'velocidad_vuelo',
            'alto_vuelo',
            'tamano_gota',
            'dir_provincia',
            'dir_localidad',
            'dir_calle',
            'dir_numero',
            'en_finca',
            'linea_tension',
            'zona_restringida',
            'corriente_electrica',
            'agua_potable',
            'libre_obstaculos',
            'area_despegue',
            'ubicacion_lat',
            'ubicacion_lng',
            'ubicacion_acc'
        ];

        // Enums NOT NULL en la tabla (no permitir setearlos a NULL accidentalmente)
        $notNullEnums = ['en_finca', 'linea_tension', 'zona_restringida', 'corriente_electrica', 'agua_potable', 'libre_obstaculos', 'area_despegue'];

        $set = [];
        $params = [':id' => $id];

        foreach ($allowed as $col) {
            if (!array_key_exists($col, $data)) continue;

            // si es enum NOT NULL y viene null/'' => ignoramos ese campo
            if (in_array($col, $notNullEnums, true) && ($data[$col] === null || $data[$col] === '')) {
                continue;
            }

            $set[] = " $col = :$col ";
            $params[":$col"] = $data[$col];
        }

        if (!$set) return false;

        $sql = "UPDATE dron_solicitudes SET " . implode(',', $set) . " WHERE id = :id";
        $st  = $this->pdo->prepare($sql);
        return $st->execute($params);
    }



    public function listarStockProductos(string $q = ''): array
    {
        $sql = "SELECT id, nombre, principio_activo FROM dron_productos_stock";
        $params = [];
        if ($q !== '') {
            $sql .= " WHERE nombre LIKE :q OR principio_activo LIKE :q";
            $params[':q'] = "%$q%";
        }
        $sql .= " ORDER BY nombre ASC LIMIT 200";
        $st = $this->pdo->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function upsertProductoSolicitud(int $solicitudId, array $d): array
    {
        // normalizar
        $id     = isset($d['id']) ? (int)$d['id'] : 0;
        $fuente = ($d['fuente'] ?? '') === 'yo' ? 'yo' : 'sve';
        $prodId = isset($d['producto_id']) ? (int)$d['producto_id'] : null;
        $marca  = trim($d['marca'] ?? '') ?: null;
        $pa     = trim($d['principio_activo'] ?? '') ?: null;        // obligatorio si fuente=yo
        $dosis  = isset($d['dosis']) ? (float)$d['dosis'] : null;
        $unidad = in_array(($d['unidad'] ?? ''), ['ml/ha', 'g/ha', 'L/ha', 'kg/ha'], true) ? $d['unidad'] : null;
        $orden  = isset($d['orden_mezcla']) && $d['orden_mezcla'] !== '' ? (int)$d['orden_mezcla'] : null;

        if ($fuente === 'sve') {
            if (!$prodId) throw new InvalidArgumentException('producto_id requerido para fuente SVE');
            $marca = null; // no aplica
            $pa    = null; // se toma del stock al leer
        } else {
            if (!$marca) throw new InvalidArgumentException('marca requerida para fuente del productor');
            if (!$pa)    throw new InvalidArgumentException('principio_activo requerido para fuente del productor');
            $prodId = null;
        }

        if ($id > 0) {
            $sql = "UPDATE dron_solicitudes_productos
                SET fuente=:fuente, producto_id=:producto_id, marca=:marca,
                    principio_activo=:principio_activo, dosis=:dosis, unidad=:unidad, orden_mezcla=:orden
                WHERE id=:id AND solicitud_id=:sid";
            $st = $this->pdo->prepare($sql);
            $st->execute([
                ':fuente' => $fuente,
                ':producto_id' => $prodId,
                ':marca' => $marca,
                ':principio_activo' => $pa,
                ':dosis' => $dosis,
                ':unidad' => $unidad,
                ':orden' => $orden,
                ':id' => $id,
                ':sid' => $solicitudId
            ]);
            return ['id' => $id];
        } else {
            $sql = "INSERT INTO dron_solicitudes_productos
                (solicitud_id, fuente, producto_id, marca, principio_activo, dosis, unidad, orden_mezcla)
                VALUES (:sid, :fuente, :producto_id, :marca, :principio_activo, :dosis, :unidad, :orden)";
            $st = $this->pdo->prepare($sql);
            $st->execute([
                ':sid' => $solicitudId,
                ':fuente' => $fuente,
                ':producto_id' => $prodId,
                ':marca' => $marca,
                ':principio_activo' => $pa,
                ':dosis' => $dosis,
                ':unidad' => $unidad,
                ':orden' => $orden
            ]);
            return ['id' => (int)$this->pdo->lastInsertId()];
        }
    }

    public function eliminarProductoSolicitud(int $solProdId, int $solicitudId): bool
    {
        $st = $this->pdo->prepare("DELETE FROM dron_solicitudes_productos WHERE id=:id AND solicitud_id=:sid");
        return $st->execute([':id' => $solProdId, ':sid' => $solicitudId]);
    }

    public function guardarTodo(int $solicitudId, array $solicitudData, array $productos): array
    {
        $this->pdo->beginTransaction();
        try {
            // actualizar datos principales (si no hay cambios, devuelve false y seguimos)
            $this->actualizarSolicitud($solicitudId, $solicitudData);

            $ids = [];
            foreach ($productos as $p) {
                $out = $this->upsertProductoSolicitud($solicitudId, $p);
                if (isset($out['id'])) $ids[] = (int)$out['id'];
            }

            $this->pdo->commit();
            return ['solicitud_id' => $solicitudId, 'productos_ids' => $ids];
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
