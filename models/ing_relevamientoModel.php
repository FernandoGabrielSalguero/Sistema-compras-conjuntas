<?php
class ingRelevamientoModel
{
  private PDO $pdo;

  public function __construct(PDO $pdo)
  {
    $this->pdo = $pdo;
    $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
  }

  /**
   * Cooperativas asociadas a un ingeniero (por id_real del ingeniero).
   * Devuelve: id_real, nombre, cuit de la cooperativa.
   */
  public function getCoopsByIngeniero(string $ingenieroIdReal): array
  {
    $sql = "
      SELECT
        u.id_real,
        COALESCE(ui.nombre, u.usuario) AS nombre,
        u.cuit
      FROM rel_coop_ingeniero rci
      JOIN usuarios u
        ON u.id_real = rci.cooperativa_id_real
       AND u.rol = 'cooperativa'
      LEFT JOIN usuarios_info ui
        ON ui.usuario_id = u.id
      WHERE rci.ingeniero_id_real = :ing
      ORDER BY nombre ASC
    ";

    $st = $this->pdo->prepare($sql);
    $st->execute([':ing' => $ingenieroIdReal]);

    return $st->fetchAll() ?: [];
  }

  /**
   * Productores asociados a una cooperativa específica, restringidos al ingeniero.
   * Usa rel_productor_coop + rel_coop_ingeniero para garantizar que la coop pertenece al ingeniero.
   * Devuelve: id_real, nombre, cuit del productor.
   */
  public function getProductoresByCooperativa(string $coopIdReal, string $ingenieroIdReal): array
  {
    $sql = "
      SELECT DISTINCT
        u.id_real,
        COALESCE(ui.nombre, u.usuario) AS nombre,
        u.cuit
      FROM rel_productor_coop rpc
      JOIN usuarios u
        ON u.id_real = rpc.productor_id_real
       AND u.rol = 'productor'
      LEFT JOIN usuarios_info ui
        ON ui.usuario_id = u.id
      JOIN rel_coop_ingeniero rci
        ON rci.cooperativa_id_real = rpc.cooperativa_id_real
      WHERE rpc.cooperativa_id_real = :coop
        AND rci.ingeniero_id_real = :ing
      ORDER BY nombre ASC
    ";

    $st = $this->pdo->prepare($sql);
    $st->execute([
      ':coop' => $coopIdReal,
      ':ing'  => $ingenieroIdReal,
    ]);

    return $st->fetchAll() ?: [];
  }
}
