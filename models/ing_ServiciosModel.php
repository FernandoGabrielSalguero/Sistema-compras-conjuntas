<?php
declare(strict_types=1);

class IngServiciosModel
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Devuelve cooperativas asociadas a un ingeniero (por id_real),
     * incluyendo nombre y CUIT de la cooperativa.
     */
    public function getCooperativasByIngeniero(string $ingenieroIdReal): array
    {
        $sql = "
            SELECT
                r.cooperativa_id_real,
                COALESCE(ui.nombre, u.usuario) AS nombre,
                u.cuit
            FROM rel_coop_ingeniero r
            JOIN usuarios u
                ON u.id_real = r.cooperativa_id_real AND u.rol = 'cooperativa'
            LEFT JOIN usuarios_info ui
                ON ui.usuario_id = u.id
            WHERE r.ingeniero_id_real = :ingeniero_id_real
            ORDER BY nombre ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':ingeniero_id_real' => $ingenieroIdReal]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Devuelve productores asociados a una cooperativa (por cooperativa_id_real),
     * incluyendo nombre, cuit, teléfono y zona.
     */
    public function getProductoresByCooperativa(string $coopIdReal): array
    {
        $sql = "
            SELECT
                u.id_real,
                COALESCE(ui.nombre, u.usuario) AS nombre,
                u.cuit,
                ui.telefono,
                ui.zona_asignada AS zona
            FROM rel_productor_coop rpc
            JOIN usuarios u
                ON u.id_real = rpc.productor_id_real AND u.rol = 'productor'
            LEFT JOIN usuarios_info ui
                ON ui.usuario_id = u.id
            WHERE rpc.cooperativa_id_real = :coop
            ORDER BY nombre ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':coop' => $coopIdReal]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }
}
