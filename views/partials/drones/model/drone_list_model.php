<?php
// views/partials/drones/model/drone_list_model.php
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
     * Listado para tarjetas con filtros:
     * q, ses_usuario, piloto, estado, fecha_visita
     *
     * NOTA: hora_visita se devuelve formateada "HH:MM - HH:MM"
     * a partir de hora_visita_desde/hasta.
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
        $where[]                = "s.ses_usuario LIKE :ses_usuario";
        $params[':ses_usuario'] = '%' . $f['ses_usuario'] . '%';
    }
    if (!empty($f['piloto'])) {
        // ahora filtramos por NOMBRE del piloto (tabla dron_pilotos)
        $where[]           = "p.nombre LIKE :piloto";
        $params[':piloto'] = '%' . $f['piloto'] . '%';
    }
    if (!empty($f['estado'])) {
        $where[]           = "s.estado = :estado";
        $params[':estado'] = strtolower(trim($f['estado']));
    }
    if (!empty($f['fecha_visita'])) {
        $where[]                  = "s.fecha_visita = :fecha_visita";
        $params[':fecha_visita']  = $f['fecha_visita']; // YYYY-MM-DD
    }

    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $sql = "
        SELECT
            s.id,
            s.ses_usuario,
            p.nombre AS piloto,       -- nombre mostrado
            s.piloto_id,              -- id que se guarda
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
    foreach ($params as $k => $v) $st->bindValue($k, $v);
    $st->execute();
    $rows = $st->fetchAll() ?: [];

    return ['items' => $rows, 'total' => count($rows)];
}


    /**
     * Detalle completo de una solicitud con:
     * - solicitud (drones_solicitud)
     * - motivos (drones_solicitud_motivo + dron_patologias)
     * - productos/items (drones_solicitud_item + receta)
     * - rangos (drones_solicitud_rango)
     * - costos (drones_solicitud_costos)
     * Además expone costo base vigente y formas de pago activas.
     */
    public function obtenerSolicitud(int $id): array
    {
        // Solicitud principal
        $st = $this->pdo->prepare("
    SELECT s.*, p.nombre AS piloto
    FROM drones_solicitud s
    LEFT JOIN dron_pilotos p ON p.id = s.piloto_id
    WHERE s.id = :id
");
        $st->execute([':id' => $id]);
        $sol = $st->fetch();
        if (!$sol) return [];

        // Motivos (si es_otros=1 toma otros_text, si no nombre de patología)
        $st = $this->pdo->prepare("
            SELECT 
               CASE 
                 WHEN sm.es_otros = 1 THEN sm.otros_text
                 ELSE dp.nombre
               END AS motivo,
               sm.es_otros, sm.otros_text, sm.patologia_id
            FROM drones_solicitud_motivo sm
            LEFT JOIN dron_patologias dp ON dp.id = sm.patologia_id
            WHERE sm.solicitud_id = :id
        ");
        $st->execute([':id' => $id]);
        $motivos = $st->fetchAll() ?: [];

        // Productos/Items + 1 receta “plana” por fila (si existe)
        // - Si fuente = 'sve' -> producto_id referencia stock y nombre viene del stock
        // - Si fuente = 'productor' -> nombre_producto y receta asociada
        $st = $this->pdo->prepare("
            SELECT
              it.id,
              it.fuente,
              it.producto_id,
              COALESCE(ps.nombre, it.nombre_producto) AS producto,
              ps.principio_activo                             AS pa_stock,
              ps.costo_hectarea                               AS costo_hectarea,
              rc.principio_activo,
              rc.dosis,
              rc.unidad,
              rc.orden_mezcla
            FROM drones_solicitud_item it
            LEFT JOIN dron_productos_stock ps ON ps.id = it.producto_id
            LEFT JOIN (
                SELECT r1.*
                FROM drones_solicitud_item_receta r1
                INNER JOIN (
                    SELECT solicitud_item_id, MIN(id) AS min_id
                    FROM drones_solicitud_item_receta
                    GROUP BY solicitud_item_id
                ) x ON x.min_id = r1.id
            ) rc ON rc.solicitud_item_id = it.id
            WHERE it.solicitud_id = :id
            ORDER BY (rc.orden_mezcla IS NULL), rc.orden_mezcla, it.id
        ");
        $st->execute([':id' => $id]);
        $itemsRaw = $st->fetchAll() ?: [];

        // Normalizar para la UI que espera campos: marca/principio_activo/dosis/unidad/orden_mezcla
        $productos = array_map(function ($r) {
            $esSVE = ($r['fuente'] === 'sve');
            return [
                'id'              => (int)$r['id'],
                'fuente'          => $r['fuente'],
                'producto_id'     => $esSVE ? (int)($r['producto_id'] ?? 0) : null,
                'marca'           => $esSVE ? null : ($r['producto'] ?? null), // nombre del productor
                'producto'        => $r['producto'],
                'principio_activo' => $esSVE ? ($r['pa_stock'] ?? null) : ($r['principio_activo'] ?? null),
                'dosis'           => $r['dosis'],
                'unidad'          => $r['unidad'],
                'orden_mezcla'    => $r['orden_mezcla'],
                'costo_hectarea'  => $r['costo_hectarea'],
            ];
        }, $itemsRaw);

        // Rangos
        $st = $this->pdo->prepare("SELECT rango FROM drones_solicitud_rango WHERE solicitud_id = :id");
        $st->execute([':id' => $id]);
        $rangos = $st->fetchAll() ?: [];

        // Costo base vigente
        $st = $this->pdo->query("SELECT costo, COALESCE(moneda,'Pesos') AS moneda FROM dron_costo_hectarea ORDER BY updated_at DESC LIMIT 1");
        $costoBase = $st->fetch() ?: ['costo' => 0, 'moneda' => 'Pesos'];

        // Costos guardados (si existen)
        $st = $this->pdo->prepare("
            SELECT moneda, costo_base_por_ha, base_ha, base_total, productos_total, total, desglose_json
            FROM drones_solicitud_costos WHERE solicitud_id = :id
        ");
        $st->execute([':id' => $id]);
        $costos = $st->fetch() ?: null;

        // Formas de pago activas (misma tabla histórica)
        $st = $this->pdo->query("SELECT id, nombre, descripcion FROM dron_formas_pago WHERE activo='si' ORDER BY nombre ASC");
        $formasPago = $st->fetchAll() ?: [];

        // Datos auxiliares al front
        $sol['costo_base_ha'] = (float)$costoBase['costo'];
        $sol['costo_moneda']  = $costoBase['moneda'];
        $sol['formas_pago']   = $formasPago;

        return [
            'solicitud' => $sol,
            'motivos'   => $motivos,
            'productos' => $productos,
            'rangos'    => $rangos,
            'costos'    => $costos
        ];
    }

    /**
     * UPDATE de la solicitud principal en drones_solicitud.
     * Ojo: hay enums NOT NULL que no deben setearse a NULL.
     */
    public function actualizarSolicitud(int $id, array $data): bool
    {
        $allowed = [
    'piloto_id',         // <--- guardamos id
    'fecha_visita',
    // la vista usa un solo campo "hora_visita"; si llega lo ignoramos.
    'hora_visita_desde',
    'hora_visita_hasta',
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
    'ubicacion_acc',
    'forma_pago_id',
    'aprob_cooperativa'
];


        // Enums NOT NULL (no setear a NULL)
        $notNullEnums = [
            'en_finca',
            'linea_tension',
            'zona_restringida',
            'corriente_electrica',
            'agua_potable',
            'libre_obstaculos',
            'area_despegue'
        ];

        $set = [];
        $params = [':id' => $id];

        foreach ($allowed as $col) {
            if (!array_key_exists($col, $data)) continue;
            if (in_array($col, $notNullEnums, true) && ($data[$col] === null || $data[$col] === '')) {
                continue;
            }
            $set[] = "$col = :$col";
            $params[":$col"] = $data[$col];
        }

        if (!$set) return false;

        $sql = "UPDATE drones_solicitud SET " . implode(', ', $set) . " WHERE id = :id";
        $st  = $this->pdo->prepare($sql);
        return $st->execute($params);
    }

    /**
     * Stock de productos SVE (tabla histórica).
     */
    public function listarStockProductos(string $q = '', array $ids = []): array
    {
        $sql = "SELECT id, nombre, principio_activo, costo_hectarea FROM dron_productos_stock";
        $params = [];
        $w = [];
        if ($q !== '') {
            $w[] = "(nombre LIKE :q OR principio_activo LIKE :q)";
            $params[':q'] = "%$q%";
        }
        if ($ids) {
            $in = implode(',', array_fill(0, count($ids), '?'));
            $w[] = "id IN ($in)";
            $params = array_merge($params, $ids);
        }
        if ($w) $sql .= " WHERE " . implode(' AND ', $w);
        $sql .= " ORDER BY nombre ASC LIMIT 500";

        $st = $this->pdo->prepare($sql);
        $st->execute(array_values($params));
        return $st->fetchAll() ?: [];
    }

    /**
     * Upsert de un item de solicitud + su receta principal (1 fila).
     * - Para fuente 'sve' requiere producto_id.
     * - Para fuente 'productor' requiere nombre_producto y principio_activo (en receta).
     *
     * Devuelve ['id' => <id del item>]
     */
    public function upsertProductoSolicitud(int $solicitudId, array $d): array
    {
        $id     = isset($d['id']) ? (int)$d['id'] : 0;
        $fuente = ($d['fuente'] ?? '') === 'yo' ? 'productor' : 'sve';
        $productoId = isset($d['producto_id']) && $d['producto_id'] !== '' ? (int)$d['producto_id'] : null;
        $marca  = trim($d['marca'] ?? '') ?: null; // nombre del producto si la fuente es productor

        $pa     = trim($d['principio_activo'] ?? '');
        $dosis  = isset($d['dosis']) && $d['dosis'] !== '' ? (float)$d['dosis'] : null;
        $unidad = in_array(($d['unidad'] ?? ''), ['ml/ha', 'g/ha', 'L/ha', 'kg/ha'], true) ? $d['unidad'] : null;
        $orden  = isset($d['orden_mezcla']) && $d['orden_mezcla'] !== '' ? (int)$d['orden_mezcla'] : null;

        if ($fuente === 'sve') {
            if (!$productoId) throw new InvalidArgumentException('producto_id requerido para fuente SVE');
            $nombreProducto = null;
        } else {
            if (!$marca) throw new InvalidArgumentException('nombre de producto requerido para fuente del productor');
            if ($pa === '') throw new InvalidArgumentException('principio_activo requerido para fuente del productor');
            $nombreProducto = $marca;
            $productoId = null;
        }

        if ($id > 0) {
            // Update item
            $sql = "UPDATE drones_solicitud_item
                    SET fuente=:fuente, producto_id=:producto_id, nombre_producto=:nombre_producto, updated_at=NOW()
                    WHERE id=:id AND solicitud_id=:sid";
            $st = $this->pdo->prepare($sql);
            $st->execute([
                ':fuente' => $fuente,
                ':producto_id' => $productoId,
                ':nombre_producto' => $nombreProducto,
                ':id' => $id,
                ':sid' => $solicitudId
            ]);

            // Upsert receta principal (tomamos la primera receta del item si existe)
            $rid = (int)($this->pdo->query("SELECT id FROM drones_solicitud_item_receta WHERE solicitud_item_id=" . (int)$id . " ORDER BY id ASC LIMIT 1")->fetchColumn() ?: 0);
            if ($rid > 0) {
                $sql = "UPDATE drones_solicitud_item_receta
                        SET principio_activo=:pa, dosis=:dosis, unidad=:unidad, orden_mezcla=:orden, updated_at=NOW()
                        WHERE id=:rid";
                $st = $this->pdo->prepare($sql);
                $st->execute([':pa' => $pa ?: null, ':dosis' => $dosis, ':unidad' => $unidad, ':orden' => $orden, ':rid' => $rid]);
            } else {
                $sql = "INSERT INTO drones_solicitud_item_receta (solicitud_item_id, principio_activo, dosis, unidad, orden_mezcla)
                        VALUES (:sid, :pa, :dosis, :unidad, :orden)";
                $st = $this->pdo->prepare($sql);
                $st->execute([':sid' => $id, ':pa' => $pa ?: null, ':dosis' => $dosis, ':unidad' => $unidad, ':orden' => $orden]);
            }

            return ['id' => $id];
        }

        // Insert item
        $sql = "INSERT INTO drones_solicitud_item (solicitud_id, fuente, producto_id, nombre_producto)
                VALUES (:sid, :fuente, :producto_id, :nombre_producto)";
        $st = $this->pdo->prepare($sql);
        $st->execute([
            ':sid' => $solicitudId,
            ':fuente' => $fuente,
            ':producto_id' => $productoId,
            ':nombre_producto' => $nombreProducto
        ]);
        $newId = (int)$this->pdo->lastInsertId();

        // Insert receta principal
        $sql = "INSERT INTO drones_solicitud_item_receta (solicitud_item_id, principio_activo, dosis, unidad, orden_mezcla)
                VALUES (:sid, :pa, :dosis, :unidad, :orden)";
        $st = $this->pdo->prepare($sql);
        $st->execute([':sid' => $newId, ':pa' => ($fuente === 'productor' ? $pa : null), ':dosis' => $dosis, ':unidad' => $unidad, ':orden' => $orden]);

        return ['id' => $newId];
    }

    public function eliminarProductoSolicitud(int $solProdId, int $solicitudId): bool
    {
        // Primero recetas
        $st = $this->pdo->prepare("DELETE FROM drones_solicitud_item_receta WHERE solicitud_item_id=:id");
        $st->execute([':id' => $solProdId]);
        // Luego el item
        $st2 = $this->pdo->prepare("DELETE FROM drones_solicitud_item WHERE id=:id AND solicitud_id=:sid");
        return $st2->execute([':id' => $solProdId, ':sid' => $solicitudId]);
    }

    private function costoBaseVigente(): array
    {
        $st = $this->pdo->query("SELECT costo, COALESCE(moneda,'Pesos') AS moneda FROM dron_costo_hectarea ORDER BY updated_at DESC LIMIT 1");
        $row = $st->fetch() ?: ['costo' => 0, 'moneda' => 'Pesos'];
        return ['costo' => (float)$row['costo'], 'moneda' => $row['moneda']];
    }

    /**
     * Recalcula totales a partir de la solicitud e items actuales (sin persistir).
     */
    public function calcularCostosTotales(int $solicitudId): array
    {
        // superficie
        $st = $this->pdo->prepare("SELECT superficie_ha FROM drones_solicitud WHERE id=:id");
        $st->execute([':id' => $solicitudId]);
        $sup = (float)($st->fetchColumn() ?: 0);

        $base = $this->costoBaseVigente();
        $baseTotal = round($base['costo'] * $sup, 2);

        // suma productos SVE = costo_hectarea(stock) * superficie
        $st = $this->pdo->prepare("
            SELECT COALESCE(ps.costo_hectarea,0) AS ch
            FROM drones_solicitud_item it
            LEFT JOIN dron_productos_stock ps ON ps.id = it.producto_id
            WHERE it.solicitud_id = :sid AND it.fuente='sve' AND it.producto_id IS NOT NULL
        ");
        $st->execute([':sid' => $solicitudId]);
        $chs = $st->fetchAll(PDO::FETCH_COLUMN) ?: [];
        $prodTotal = 0.0;
        foreach ($chs as $ch) $prodTotal += ((float)$ch) * $sup;
        $prodTotal = round($prodTotal, 2);

        $total = round($baseTotal + $prodTotal, 2);

        return [
            'moneda'              => $base['moneda'],
            'costo_base_por_ha'   => $base['costo'],
            'base_ha'             => $sup,
            'base_total'          => $baseTotal,
            'productos_total'     => $prodTotal,
            'total'               => $total,
            'desglose_json'       => null
        ];
    }

    /**
     * Inserta/actualiza fila de costos en drones_solicitud_costos.
     */
    public function upsertCostos(int $solicitudId, array $c): void
    {
        $st = $this->pdo->prepare("SELECT id FROM drones_solicitud_costos WHERE solicitud_id=:sid");
        $st->execute([':sid' => $solicitudId]);
        $id = (int)($st->fetchColumn() ?: 0);

        if ($id > 0) {
            $sql = "UPDATE drones_solicitud_costos
                    SET moneda=:moneda, costo_base_por_ha=:cph, base_ha=:bha, base_total=:bt,
                        productos_total=:pt, total=:tot, desglose_json=:dj, updated_at=CURRENT_TIMESTAMP
                    WHERE solicitud_id=:sid";
        } else {
            $sql = "INSERT INTO drones_solicitud_costos
                    (solicitud_id, moneda, costo_base_por_ha, base_ha, base_total, productos_total, total, desglose_json)
                    VALUES (:sid, :moneda, :cph, :bha, :bt, :pt, :tot, :dj)";
        }

        $st = $this->pdo->prepare($sql);
        $st->execute([
            ':sid'   => $solicitudId,
            ':moneda' => $c['moneda'],
            ':cph'   => $c['costo_base_por_ha'],
            ':bha'   => $c['base_ha'],
            ':bt'    => $c['base_total'],
            ':pt'    => $c['productos_total'],
            ':tot'   => $c['total'],
            ':dj'    => $c['desglose_json'],
        ]);
    }

    /**
     * Guarda todo: update solicitud + upsert items + recálculo y upsert de costos.
     */
    public function guardarTodo(int $solicitudId, array $solicitudData, array $productos): array
    {
        $this->pdo->beginTransaction();
        try {
            // (1) actualizar solicitud
            $this->actualizarSolicitud($solicitudId, $solicitudData);

            // (2) upsert de productos/items
            $ids = [];
            foreach ($productos as $p) {
                $out = $this->upsertProductoSolicitud($solicitudId, $p);
                if (isset($out['id'])) $ids[] = (int)$out['id'];
            }

            // (3) recálculo y persistencia de costos
            $costos = $this->calcularCostosTotales($solicitudId);
            $this->upsertCostos($solicitudId, $costos);

            $this->pdo->commit();
            return ['solicitud_id' => $solicitudId, 'productos_ids' => $ids, 'costos' => $costos];
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Listamos pilotos activos (para asignar a la solicitud).
     */
    public function listarPilotos(): array
    {
        $st = $this->pdo->prepare("
        SELECT id, nombre, telefono, zona_asignada, correo
        FROM dron_pilotos
        WHERE activo = 'si'
        ORDER BY nombre ASC
        LIMIT 1000
    ");
        $st->execute();
        return $st->fetchAll() ?: [];
    }
}
