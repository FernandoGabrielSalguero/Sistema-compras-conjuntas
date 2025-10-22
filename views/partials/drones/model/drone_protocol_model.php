<?php

declare(strict_types=1);

class DroneProtocolModel
{
    /** @var PDO */
    public PDO $pdo;

    /**
     * Lista solicitudes con filtros opcionales.
     * @param string|null $nombre Nombre (productor) LIKE
     * @param string|null $estado Estado exacto
     * @return array<int, array<string, mixed>>
     */
    public function listarSolicitudes(?string $nombre, ?string $estado): array
    {
        $sql = "
            SELECT s.id,
                   COALESCE(ui.nombre, u.usuario) AS productor_nombre,
                   s.estado,
                   s.fecha_visita
            FROM drones_solicitud s
            LEFT JOIN usuarios u ON u.id_real = s.productor_id_real
            LEFT JOIN usuarios_info ui ON ui.usuario_id = u.id
            WHERE 1=1
        ";
        $params = [];

        if ($estado !== null && $estado !== '') {
            $sql .= " AND s.estado = :estado";
            $params[':estado'] = $estado;
        }
        if ($nombre !== null && $nombre !== '') {
            $sql .= " AND (ui.nombre LIKE :nom OR u.usuario LIKE :nom)";
            $params[':nom'] = '%' . $nombre . '%';
        }

        $sql .= " ORDER BY s.created_at DESC, s.id DESC LIMIT 100";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        /** @var array<int, array<string, mixed>> */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows ?: [];
    }

    /**
     * Obtiene detalle de protocolo por solicitud.
     * @param int $id
     * @return array<string, mixed>|null
     */
    public function obtenerProtocolo(int $id): ?array
    {
        // drones_solicitud
        $sqlS = "
            SELECT
              id,
              fecha_visita, hora_visita_desde, hora_visita_hasta,
              dir_provincia, dir_localidad, dir_calle, dir_numero,
              ubicacion_lat, ubicacion_lng,
              ses_usuario, estado, motivo_cancelacion,
              superficie_ha
            FROM drones_solicitud
            WHERE id = :id
            LIMIT 1
        ";
        $stS = $this->pdo->prepare($sqlS);
        $stS->execute([':id' => $id]);
        $solicitud = $stS->fetch(PDO::FETCH_ASSOC);
        if (!$solicitud) {
            return null;
        }

        // parámetros (último para esa solicitud)
        $sqlP = "
            SELECT id, volumen_ha, velocidad_vuelo, alto_vuelo, ancho_pasada,
                   tamano_gota, observaciones, observaciones_agua
            FROM drones_solicitud_parametros
            WHERE solicitud_id = :id
            ORDER BY id DESC
            LIMIT 1
        ";
        $stP = $this->pdo->prepare($sqlP);
        $stP->execute([':id' => $id]);
        $parametros = $stP->fetch(PDO::FETCH_ASSOC) ?: null;

        // items (preferir nombre_producto editable; fallback stock)
        $sqlI = "
            SELECT i.id,
                   i.nombre_producto,
                   COALESCE(i.nombre_producto, ps.nombre) AS nombre_producto_resuelto
            FROM drones_solicitud_item i
            LEFT JOIN dron_productos_stock ps ON ps.id = i.producto_id
            WHERE i.solicitud_id = :id
            ORDER BY i.id ASC
        ";
        $stI = $this->pdo->prepare($sqlI);
        $stI->execute([':id' => $id]);
        $items = $stI->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // recetas por item (una sola query)
        $ids = array_column($items, 'id');
        $recetasPorItem = [];
        if ($ids) {
            $in = implode(',', array_fill(0, count($ids), '?'));
            $sqlR = "
                SELECT id, solicitud_item_id, principio_activo, dosis, unidad, orden_mezcla, notas
                FROM drones_solicitud_item_receta
                WHERE solicitud_item_id IN ($in)
                ORDER BY solicitud_item_id ASC, COALESCE(orden_mezcla, 9999) ASC, id ASC
            ";
            $stR = $this->pdo->prepare($sqlR);
            $stR->execute($ids);
            while ($r = $stR->fetch(PDO::FETCH_ASSOC)) {
                $sid = (int)$r['solicitud_item_id'];
                $recetasPorItem[$sid][] = $r;
            }
        }

        foreach ($items as &$it) {
            $itId = (int)$it['id'];
            $it['receta'] = $recetasPorItem[$itId] ?? [];
        }
        unset($it);

        return [
            'solicitud'  => $solicitud,
            'parametros' => $parametros,
            'items'      => array_map(function (array $x) {
                return [
                    'id' => (int)$x['id'],
                    'nombre_producto' => $x['nombre_producto'], // editable (puede ser null)
                    'nombre_producto_resuelto' => $x['nombre_producto_resuelto'],
                    'receta' => $x['receta'],
                ];
            }, $items),
        ];
    }

    /**
     * Upsert de parámetros de vuelo.
     * Si $paramId > 0, actualiza ese registro; si no, inserta uno nuevo asociado a $solicitudId.
     * Devuelve el id del registro afectado.
     */
    public function upsertParametros(int $solicitudId, array $p, int $paramId = 0): int
    {
        $cols = [
            'volumen_ha'        => $p['volumen_ha']        ?? null,
            'velocidad_vuelo'   => $p['velocidad_vuelo']   ?? null,
            'alto_vuelo'        => $p['alto_vuelo']        ?? null,
            'ancho_pasada'      => $p['ancho_pasada']      ?? null,
            'tamano_gota'       => $p['tamano_gota']       ?? null,
            'observaciones'     => $p['observaciones']     ?? null,
            'observaciones_agua' => $p['observaciones_agua'] ?? null,
        ];

        if ($paramId > 0) {
            $sql = "
                UPDATE drones_solicitud_parametros
                SET volumen_ha = :volumen_ha,
                    velocidad_vuelo = :velocidad_vuelo,
                    alto_vuelo = :alto_vuelo,
                    ancho_pasada = :ancho_pasada,
                    tamano_gota = :tamano_gota,
                    observaciones = :observaciones,
                    observaciones_agua = :observaciones_agua
                WHERE id = :id AND solicitud_id = :sid
            ";
            $st = $this->pdo->prepare($sql);
            $st->execute([
                ':volumen_ha'        => $cols['volumen_ha'],
                ':velocidad_vuelo'   => $cols['velocidad_vuelo'],
                ':alto_vuelo'        => $cols['alto_vuelo'],
                ':ancho_pasada'      => $cols['ancho_pasada'],
                ':tamano_gota'       => $cols['tamano_gota'],
                ':observaciones'     => $cols['observaciones'],
                ':observaciones_agua' => $cols['observaciones_agua'],
                ':id'                => $paramId,
                ':sid'               => $solicitudId,
            ]);
            return $paramId;
        }

        $sql = "
            INSERT INTO drones_solicitud_parametros
                (solicitud_id, volumen_ha, velocidad_vuelo, alto_vuelo, ancho_pasada,
                 tamano_gota, observaciones, observaciones_agua)
            VALUES
                (:sid, :volumen_ha, :velocidad_vuelo, :alto_vuelo, :ancho_pasada,
                 :tamano_gota, :observaciones, :observaciones_agua)
        ";
        $st = $this->pdo->prepare($sql);
        $st->execute([
            ':sid'                => $solicitudId,
            ':volumen_ha'         => $cols['volumen_ha'],
            ':velocidad_vuelo'    => $cols['velocidad_vuelo'],
            ':alto_vuelo'         => $cols['alto_vuelo'],
            ':ancho_pasada'       => $cols['ancho_pasada'],
            ':tamano_gota'        => $cols['tamano_gota'],
            ':observaciones'      => $cols['observaciones'],
            ':observaciones_agua' => $cols['observaciones_agua'],
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Actualiza múltiples filas de receta (drones_solicitud_item_receta).
     * Espera elementos con: id, solicitud_item_id, principio_activo, dosis, unidad, orden_mezcla, notas
     * Devuelve cantidad de filas afectadas.
     */
    public function actualizarRecetas(array $recetas): int
    {
        if (!$recetas) return 0;

        $sql = "
            UPDATE drones_solicitud_item_receta
            SET principio_activo = :principio_activo,
                dosis = :dosis,
                unidad = :unidad,
                orden_mezcla = :orden_mezcla,
                notas = :notas
            WHERE id = :id AND solicitud_item_id = :sid
        ";
        $st = $this->pdo->prepare($sql);
        $total = 0;
        foreach ($recetas as $r) {
            if (empty($r['id']) || empty($r['solicitud_item_id'])) continue;
            $st->execute([
                ':principio_activo' => $r['principio_activo'] ?? null,
                ':dosis'            => $r['dosis'] ?? null,
                ':unidad'           => $r['unidad'] ?? null,
                ':orden_mezcla'     => ($r['orden_mezcla'] === '' ? null : $r['orden_mezcla']),
                ':notas'            => $r['notas'] ?? null,
                ':id'               => (int)$r['id'],
                ':sid'              => (int)$r['solicitud_item_id'],
            ]);
            $total += $st->rowCount();
        }
        return $total;
    }

    /**
     * Actualiza nombre_producto (editable) en drones_solicitud_item.
     * Espera elementos {id, nombre_producto}. Devuelve filas afectadas.
     */
    public function actualizarItemsNombre(array $items): int
    {
        if (!$items) return 0;

        $sql = "UPDATE drones_solicitud_item SET nombre_producto = :nombre WHERE id = :id";
        $st = $this->pdo->prepare($sql);
        $total = 0;
        foreach ($items as $it) {
            if (empty($it['id'])) continue;
            $nombre = $it['nombre_producto'] ?? null;
            $st->execute([
                ':nombre' => ($nombre === '' ? null : $nombre),
                ':id'     => (int)$it['id'],
            ]);
            $total += $st->rowCount();
        }
        return $total;
    }
}
