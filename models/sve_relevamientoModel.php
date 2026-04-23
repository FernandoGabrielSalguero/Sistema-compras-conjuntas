<?php


require_once __DIR__ . '/../config.php';

final class SveRelevamientoModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function obtenerResumenCooperativas(string $q = ''): array
    {
        $whereSql = '';
        $params = [];

        if ($q !== '') {
            $whereSql = "
                WHERE (
                    COALESCE(NULLIF(TRIM(cui.nombre), ''), NULLIF(TRIM(cu.razon_social), ''), NULLIF(TRIM(cu.usuario), ''), rpc.cooperativa_id_real) LIKE :q
                    OR CAST(COALESCE(cu.cuit, '') AS CHAR) LIKE :q
                    OR rpc.cooperativa_id_real LIKE :q
                )
            ";
            $params[':q'] = '%' . $q . '%';
        }

        $sql = "
            SELECT
                rpc.cooperativa_id_real,
                COALESCE(
                    NULLIF(TRIM(cui.nombre), ''),
                    NULLIF(TRIM(cu.razon_social), ''),
                    NULLIF(TRIM(cu.usuario), ''),
                    rpc.cooperativa_id_real
                ) AS cooperativa_nombre,
                COUNT(DISTINCT rpc.productor_id_real) AS productores_total,
                SUM(CASE WHEN COALESCE(pf_stats.fincas_count, 0) > 0 THEN 1 ELSE 0 END) AS productores_con_fincas,
                SUM(CASE WHEN COALESCE(pc_stats.cuarteles_count, 0) > 0 THEN 1 ELSE 0 END) AS productores_con_cuarteles
            FROM rel_productor_coop rpc
            LEFT JOIN usuarios cu
                ON cu.id_real = rpc.cooperativa_id_real
               AND cu.rol = 'cooperativa'
            LEFT JOIN usuarios_info cui
                ON cui.usuario_id = cu.id
            LEFT JOIN (
                SELECT
                    pf.productor_id_real,
                    COUNT(*) AS fincas_count
                FROM prod_fincas pf
                GROUP BY pf.productor_id_real
            ) pf_stats
                ON pf_stats.productor_id_real = rpc.productor_id_real
            LEFT JOIN (
                SELECT
                    z.productor_id_real,
                    COUNT(DISTINCT z.cuartel_id) AS cuarteles_count
                FROM (
                    SELECT
                        pf.productor_id_real,
                        pc.id AS cuartel_id
                    FROM prod_cuartel pc
                    INNER JOIN prod_fincas pf
                        ON pf.id = pc.finca_id

                    UNION

                    SELECT
                        pc.id_responsable_real AS productor_id_real,
                        pc.id AS cuartel_id
                    FROM prod_cuartel pc
                    WHERE pc.id_responsable_real IS NOT NULL
                      AND pc.id_responsable_real <> ''
                ) z
                GROUP BY z.productor_id_real
            ) pc_stats
                ON pc_stats.productor_id_real = rpc.productor_id_real
            {$whereSql}
            GROUP BY rpc.cooperativa_id_real, cooperativa_nombre
            ORDER BY cooperativa_nombre ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll() ?: [];
    }

    /**
     * @return array{rows: array<int, array<string, mixed>>, total: int}
     */
    public function obtenerListadoProductores(string $coopIdReal, string $q, int $page, int $perPage): array
    {
        $where = [];
        $params = [];

        if ($coopIdReal !== '') {
            $where[] = 'rpc.cooperativa_id_real = :coop_id_real';
            $params[':coop_id_real'] = $coopIdReal;
        }

        if ($q !== '') {
            $where[] = "(
                COALESCE(NULLIF(TRIM(pui.nombre), ''), NULLIF(TRIM(pu.razon_social), ''), NULLIF(TRIM(pu.usuario), ''), rpc.productor_id_real) LIKE :q
                OR CAST(COALESCE(pu.cuit, '') AS CHAR) LIKE :q
                OR rpc.productor_id_real LIKE :q
                OR COALESCE(NULLIF(TRIM(cui.nombre), ''), NULLIF(TRIM(cu.razon_social), ''), NULLIF(TRIM(cu.usuario), ''), rpc.cooperativa_id_real) LIKE :q
            )";
            $params[':q'] = '%' . $q . '%';
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
        $offset = max(0, ($page - 1) * $perPage);

        $sqlCount = "
            SELECT COUNT(*)
            FROM rel_productor_coop rpc
            LEFT JOIN usuarios pu
                ON pu.id_real = rpc.productor_id_real
               AND pu.rol = 'productor'
            LEFT JOIN usuarios_info pui
                ON pui.usuario_id = pu.id
            LEFT JOIN usuarios cu
                ON cu.id_real = rpc.cooperativa_id_real
               AND cu.rol = 'cooperativa'
            LEFT JOIN usuarios_info cui
                ON cui.usuario_id = cu.id
            {$whereSql}
        ";

        $stmtCount = $this->pdo->prepare($sqlCount);
        foreach ($params as $key => $value) {
            $stmtCount->bindValue($key, $value, PDO::PARAM_STR);
        }
        $stmtCount->execute();
        $total = (int) ($stmtCount->fetchColumn() ?: 0);

        $sql = "
            SELECT
                rpc.id AS relacion_id,
                rpc.cooperativa_id_real,
                COALESCE(
                    NULLIF(TRIM(cui.nombre), ''),
                    NULLIF(TRIM(cu.razon_social), ''),
                    NULLIF(TRIM(cu.usuario), ''),
                    rpc.cooperativa_id_real
                ) AS cooperativa_nombre,
                rpc.productor_id_real,
                COALESCE(
                    NULLIF(TRIM(pui.nombre), ''),
                    NULLIF(TRIM(pu.razon_social), ''),
                    NULLIF(TRIM(pu.usuario), ''),
                    rpc.productor_id_real
                ) AS productor_nombre,
                NULLIF(NULLIF(TRIM(CAST(pu.cuit AS CHAR)), ''), '0') AS productor_cuit,
                COALESCE(pf_stats.fincas_count, 0) AS fincas_count,
                COALESCE(pc_stats.cuarteles_count, 0) AS cuarteles_count
            FROM rel_productor_coop rpc
            LEFT JOIN usuarios pu
                ON pu.id_real = rpc.productor_id_real
               AND pu.rol = 'productor'
            LEFT JOIN usuarios_info pui
                ON pui.usuario_id = pu.id
            LEFT JOIN usuarios cu
                ON cu.id_real = rpc.cooperativa_id_real
               AND cu.rol = 'cooperativa'
            LEFT JOIN usuarios_info cui
                ON cui.usuario_id = cu.id
            LEFT JOIN (
                SELECT
                    pf.productor_id_real,
                    COUNT(*) AS fincas_count
                FROM prod_fincas pf
                GROUP BY pf.productor_id_real
            ) pf_stats
                ON pf_stats.productor_id_real = rpc.productor_id_real
            LEFT JOIN (
                SELECT
                    z.productor_id_real,
                    COUNT(DISTINCT z.cuartel_id) AS cuarteles_count
                FROM (
                    SELECT
                        pf.productor_id_real,
                        pc.id AS cuartel_id
                    FROM prod_cuartel pc
                    INNER JOIN prod_fincas pf
                        ON pf.id = pc.finca_id

                    UNION

                    SELECT
                        pc.id_responsable_real AS productor_id_real,
                        pc.id AS cuartel_id
                    FROM prod_cuartel pc
                    WHERE pc.id_responsable_real IS NOT NULL
                      AND pc.id_responsable_real <> ''
                ) z
                GROUP BY z.productor_id_real
            ) pc_stats
                ON pc_stats.productor_id_real = rpc.productor_id_real
            {$whereSql}
            ORDER BY cooperativa_nombre ASC, productor_nombre ASC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'rows' => $stmt->fetchAll() ?: [],
            'total' => $total,
        ];
    }
}
