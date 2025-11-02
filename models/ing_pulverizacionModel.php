<?php
class ingPulverizacionModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /**
     * Cooperativas vinculadas a un ingeniero.
     * Retorna [{id_real, nombre}]
     */
    public function getCoopsByIngeniero(string $ingenieroIdReal): array
    {
        $sql = "
            SELECT u.id_real, COALESCE(ui.nombre, u.usuario) AS nombre
            FROM rel_coop_ingeniero rci
            JOIN usuarios u
              ON u.id_real = rci.cooperativa_id_real AND u.rol = 'cooperativa'
            LEFT JOIN usuarios_info ui
              ON ui.usuario_id = u.id
            WHERE rci.ingeniero_id_real = :ing
            ORDER BY nombre ASC";
        $st = $this->pdo->prepare($sql);
        $st->execute([':ing' => $ingenieroIdReal]);
        return $st->fetchAll() ?: [];
    }

    /**
     * Listado de solicitudes visibles para un ingeniero con filtros.
     * qProd: filtro por nombre de productor (LIKE en usuarios_info.nombre)
     * coop:  id_real de cooperativa asociada (exacto). Vacío = todas.
     */
    public function listByIngeniero(string $ingenieroIdReal, string $qProd, string $coop, int $limit, int $offset): array
    {
        // Filtro base: productores que pertenecen a coops del ingeniero
        $filterCoop = "";
        $params = [':ing' => $ingenieroIdReal];

        if ($coop !== '') {
            $filterCoop = " AND rpc.cooperativa_id_real = :coop ";
            $params[':coop'] = $coop;
        }

        $filterProd = "";
        if ($qProd !== '') {
            $filterProd = " AND (COALESCE(uip.nombre,'') LIKE :qprod) ";
            $params[':qprod'] = '%' . $qProd . '%';
        }

        // Conteo
        $sqlCount = "
            SELECT COUNT(*) AS c
            FROM drones_solicitud ds
            JOIN usuarios up ON up.id_real = ds.productor_id_real
            LEFT JOIN usuarios_info uip ON uip.usuario_id = up.id
            WHERE ds.productor_id_real IN (
                SELECT rpc.productor_id_real
                FROM rel_productor_coop rpc
                JOIN rel_coop_ingeniero rci
                  ON rci.cooperativa_id_real = rpc.cooperativa_id_real
                WHERE rci.ingeniero_id_real = :ing
                {$filterCoop}
            )
            {$filterProd}";
        $stC = $this->pdo->prepare($sqlCount);
        $stC->execute($params);
        $total = (int)$stC->fetchColumn();

        // Items
        $sql = "
            SELECT
                ds.id,
                ds.productor_id_real,
                ds.fecha_visita,
                ds.estado,
                COALESCE(ds.observaciones,'') AS observaciones,
                COALESCE(c.total,0)          AS costo_total,
                COALESCE(uip.nombre, up.usuario) AS productor_nombre,
                COALESCE(uicoop.nombre, ucoop.usuario) AS cooperativa_nombre,
                ds.created_at
            FROM drones_solicitud ds
            JOIN usuarios up
              ON up.id_real = ds.productor_id_real
            LEFT JOIN usuarios_info uip
              ON uip.usuario_id = up.id
            LEFT JOIN drones_solicitud_costos c
              ON c.solicitud_id = ds.id
            -- obtener una coop del vínculo activo para mostrar nombre (para filtro ya usamos subconsulta)
            LEFT JOIN rel_productor_coop rpc2
              ON rpc2.productor_id_real = ds.productor_id_real
            LEFT JOIN usuarios ucoop
              ON ucoop.id_real = rpc2.cooperativa_id_real
            LEFT JOIN usuarios_info uicoop
              ON uicoop.usuario_id = ucoop.id
            WHERE ds.productor_id_real IN (
                SELECT rpc.productor_id_real
                FROM rel_productor_coop rpc
                JOIN rel_coop_ingeniero rci
                  ON rci.cooperativa_id_real = rpc.cooperativa_id_real
                WHERE rci.ingeniero_id_real = :ing
                {$filterCoop}
            )
            {$filterProd}
            ORDER BY ds.created_at DESC
            LIMIT :lim OFFSET :off";
        $st = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $st->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->bindValue(':off', $offset, PDO::PARAM_INT);
        $st->execute();
        $items = $st->fetchAll() ?: [];

        return ['items' => $items, 'total' => $total];
    }
}
