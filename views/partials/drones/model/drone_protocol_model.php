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

        // parámetros (el más reciente para esa solicitud)
        $sqlP = "
    SELECT volumen_ha, velocidad_vuelo, alto_vuelo, ancho_pasada,
           tamano_gota, observaciones, observaciones_agua
    FROM drones_solicitud_parametros
    WHERE solicitud_id = :id
    ORDER BY id DESC
    LIMIT 1
";
        $stP = $this->pdo->prepare($sqlP);
        $stP->execute([':id' => $id]);
        $parametros = $stP->fetch(PDO::FETCH_ASSOC) ?: null;

        // items (nombre_producto preferente; si es NULL, intento nombre de stock)
        $sqlI = "
            SELECT i.id,
                   COALESCE(i.nombre_producto, ps.nombre) AS nombre_producto,
                   i.fuente
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
                $recetasPorItem[$sid][] = [
                    'id' => (int)$r['id'],
                    'principio_activo' => $r['principio_activo'],
                    'dosis' => $r['dosis'],
                    'unidad' => $r['unidad'],
                    'orden_mezcla' => $r['orden_mezcla'],
                    'notas' => $r['notas'],
                ];
            }
        }

        // adjunto recetas a items
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
                    'nombre_producto' => $x['nombre_producto'],
                    'fuente' => $x['fuente'] ?? null,
                    'receta' => $x['receta'],
                ];
            }, $items),
        ];
    }

    /**
     * Actualiza en batch recetas (dosis, orden_mezcla, notas) por id.
     * @param array<int, array{receta_id:int,dosis:?string,orden_mezcla:?int,notas:?string}> $rows
     */
    public function actualizarRecetas(array $rows): void
    {
        if (!$rows) return;
        $this->pdo->beginTransaction();
        try {
            $sql = "UPDATE drones_solicitud_item_receta
                    SET dosis = :dosis, orden_mezcla = :orden, notas = :notas, updated_at = NOW()
                    WHERE id = :id";
            $st = $this->pdo->prepare($sql);
            foreach ($rows as $r) {
                $st->execute([
                    ':dosis' => $r['dosis'],
                    ':orden' => $r['orden_mezcla'],
                    ':notas' => $r['notas'],
                    ':id'    => $r['receta_id'],
                ]);
            }
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Inserta o actualiza parámetros de vuelo para una solicitud.
     * Si existe registro, actualiza el más reciente; si no, inserta.
     * Retorna id del registro afectado.
     * @param int $solicitudId
     * @param array<string,mixed> $p
     */
    public function upsertParametros(int $solicitudId, array $p): int
    {
        // ¿Existe uno?
        $st = $this->pdo->prepare("SELECT id FROM drones_solicitud_parametros WHERE solicitud_id = :sid ORDER BY id DESC LIMIT 1");
        $st->execute([':sid' => $solicitudId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $sql = "UPDATE drones_solicitud_parametros
                    SET volumen_ha = :vol, velocidad_vuelo = :vel, alto_vuelo = :alto,
                        ancho_pasada = :ancho, tamano_gota = :gota, observaciones = :obs, updated_at = NOW(),
                        observaciones_agua = COALESCE(observaciones_agua, observaciones_agua)
                    WHERE id = :id";
            $u = $this->pdo->prepare($sql);
            $u->execute([
                ':vol'  => $p['volumen_ha'] ?? null,
                ':vel'  => $p['velocidad_vuelo'] ?? null,
                ':alto' => $p['alto_vuelo'] ?? null,
                ':ancho' => $p['ancho_pasada'] ?? null,
                ':gota' => $p['tamano_gota'] ?? null,
                ':obs'  => $p['observaciones'] ?? null,
                ':id'   => (int)$row['id'],
            ]);
            return (int)$row['id'];
        }
        $ins = $this->pdo->prepare("INSERT INTO drones_solicitud_parametros
            (solicitud_id, volumen_ha, velocidad_vuelo, alto_vuelo, ancho_pasada, tamano_gota, observaciones, created_at)
            VALUES (:sid, :vol, :vel, :alto, :ancho, :gota, :obs, NOW())");
        $ins->execute([
            ':sid'  => $solicitudId,
            ':vol'  => $p['volumen_ha'] ?? null,
            ':vel'  => $p['velocidad_vuelo'] ?? null,
            ':alto' => $p['alto_vuelo'] ?? null,
            ':ancho' => $p['ancho_pasada'] ?? null,
            ':gota' => $p['tamano_gota'] ?? null,
            ':obs'  => $p['observaciones'] ?? null,
        ]);
        /** @var int */
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Actualiza superficie (hectáreas) en drones_solicitud.
     */
    public function actualizarHectareas(int $solicitudId, $ha): void
    {
        $st = $this->pdo->prepare("UPDATE drones_solicitud SET superficie_ha = :ha, updated_at = NOW() WHERE id = :sid");
        $st->execute([':ha' => $ha, ':sid' => $solicitudId]);
    }
}
