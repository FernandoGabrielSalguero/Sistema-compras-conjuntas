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
        COALESCE(
          NULLIF(TRIM(ui.nombre), ''),
          NULLIF(TRIM(u.razon_social), ''),
          NULLIF(TRIM(u.usuario), ''),
          u.id_real
        ) AS nombre,
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
        rpc.productor_id_real AS id_real,
        COALESCE(
          NULLIF(TRIM(ui.nombre), ''),
          NULLIF(TRIM(u.razon_social), ''),
          NULLIF(TRIM(u.usuario), ''),
          rpc.productor_id_real
        ) AS nombre,
        u.cuit
      FROM rel_productor_coop rpc
      LEFT JOIN usuarios u
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

  private function productorPerteneceAIngeniero(string $productorIdReal, string $ingenieroIdReal): bool
  {
    $sql = "
      SELECT 1
      FROM rel_productor_coop rpc
      JOIN rel_coop_ingeniero rci
        ON rci.cooperativa_id_real = rpc.cooperativa_id_real
      WHERE rpc.productor_id_real = :prod
        AND rci.ingeniero_id_real = :ing
      LIMIT 1
    ";

    $st = $this->pdo->prepare($sql);
    $st->execute([
      ':prod' => $productorIdReal,
      ':ing'  => $ingenieroIdReal,
    ]);

    return (bool)$st->fetchColumn();
  }

  public function getResumenActivosProductor(string $productorIdReal, string $ingenieroIdReal): array
  {
    if ($productorIdReal === '') {
      throw new InvalidArgumentException('productor_id_real es requerido');
    }

    if (!$this->productorPerteneceAIngeniero($productorIdReal, $ingenieroIdReal)) {
      throw new RuntimeException('No autorizado para ver este productor');
    }

    $sqlFincas = "
      SELECT
        pf.id,
        pf.codigo_finca,
        pf.nombre_finca
      FROM prod_fincas pf
      WHERE pf.productor_id_real = :prod
      ORDER BY pf.codigo_finca ASC, pf.id ASC
    ";
    $stF = $this->pdo->prepare($sqlFincas);
    $stF->execute([':prod' => $productorIdReal]);
    $fincas = $stF->fetchAll() ?: [];

    $sqlCuarteles = "
      SELECT DISTINCT
        pc.id,
        pc.codigo_cuartel,
        pc.codigo_finca,
        pc.nombre_finca,
        pc.finca_id
      FROM prod_cuartel pc
      LEFT JOIN prod_fincas pf
        ON pf.id = pc.finca_id
      WHERE pf.productor_id_real = :prod
         OR pc.id_responsable_real = :prod
      ORDER BY pc.codigo_finca ASC, pc.codigo_cuartel ASC, pc.id ASC
    ";
    $stC = $this->pdo->prepare($sqlCuarteles);
    $stC->execute([':prod' => $productorIdReal]);
    $cuarteles = $stC->fetchAll() ?: [];

    return [
      'fincas_count' => count($fincas),
      'cuarteles_count' => count($cuarteles),
      'fincas' => $fincas,
      'cuarteles' => $cuarteles,
    ];
  }

  public function eliminarCuartelProductor(int $cuartelId, string $productorIdReal, string $ingenieroIdReal): void
  {
    if ($cuartelId <= 0) {
      throw new InvalidArgumentException('cuartel_id inválido');
    }
    if ($productorIdReal === '') {
      throw new InvalidArgumentException('productor_id_real es requerido');
    }
    if (!$this->productorPerteneceAIngeniero($productorIdReal, $ingenieroIdReal)) {
      throw new RuntimeException('No autorizado para eliminar este cuartel');
    }

    $sqlOwner = "
      SELECT pc.id
      FROM prod_cuartel pc
      LEFT JOIN prod_fincas pf
        ON pf.id = pc.finca_id
      WHERE pc.id = :cid
        AND (pf.productor_id_real = :prod OR pc.id_responsable_real = :prod)
      LIMIT 1
    ";
    $st = $this->pdo->prepare($sqlOwner);
    $st->execute([
      ':cid'  => $cuartelId,
      ':prod' => $productorIdReal,
    ]);
    if (!$st->fetch()) {
      throw new RuntimeException('El cuartel no pertenece al productor seleccionado');
    }

    $this->pdo->beginTransaction();
    try {
      $sqls = [
        "DELETE FROM prod_cuartel_limitantes WHERE cuartel_id = :cid",
        "DELETE FROM prod_cuartel_rendimientos WHERE cuartel_id = :cid",
        "DELETE FROM prod_cuartel_riesgos WHERE cuartel_id = :cid",
        "DELETE FROM prod_cuartel WHERE id = :cid",
      ];

      foreach ($sqls as $sql) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':cid' => $cuartelId]);
      }

      $this->pdo->commit();
    } catch (Throwable $e) {
      $this->pdo->rollBack();
      throw $e;
    }
  }

  public function eliminarFincaProductor(int $fincaId, string $productorIdReal, string $ingenieroIdReal): void
  {
    if ($fincaId <= 0) {
      throw new InvalidArgumentException('finca_id inválido');
    }
    if ($productorIdReal === '') {
      throw new InvalidArgumentException('productor_id_real es requerido');
    }
    if (!$this->productorPerteneceAIngeniero($productorIdReal, $ingenieroIdReal)) {
      throw new RuntimeException('No autorizado para eliminar esta finca');
    }

    $sqlOwner = "
      SELECT id
      FROM prod_fincas
      WHERE id = :fid
        AND productor_id_real = :prod
      LIMIT 1
    ";
    $st = $this->pdo->prepare($sqlOwner);
    $st->execute([
      ':fid'  => $fincaId,
      ':prod' => $productorIdReal,
    ]);
    if (!$st->fetch()) {
      throw new RuntimeException('La finca no pertenece al productor seleccionado');
    }

    $this->pdo->beginTransaction();
    try {
      $sqls = [
        "DELETE FROM prod_cuartel_limitantes WHERE cuartel_id IN (SELECT id FROM prod_cuartel WHERE finca_id = :fid)",
        "DELETE FROM prod_cuartel_rendimientos WHERE cuartel_id IN (SELECT id FROM prod_cuartel WHERE finca_id = :fid)",
        "DELETE FROM prod_cuartel_riesgos WHERE cuartel_id IN (SELECT id FROM prod_cuartel WHERE finca_id = :fid)",
        "DELETE FROM prod_cuartel WHERE finca_id = :fid",
        "DELETE FROM relevamiento_fincas WHERE finca_id = :fid",
        "DELETE FROM rel_productor_finca WHERE finca_id = :fid",
        "DELETE FROM prod_finca_direccion WHERE finca_id = :fid",
        "DELETE FROM prod_finca_superficie WHERE finca_id = :fid",
        "DELETE FROM prod_finca_cultivos WHERE finca_id = :fid",
        "DELETE FROM prod_finca_agua WHERE finca_id = :fid",
        "DELETE FROM prod_finca_maquinaria WHERE finca_id = :fid",
        "DELETE FROM prod_finca_gerencia WHERE finca_id = :fid",
        "DELETE FROM prod_fincas WHERE id = :fid AND productor_id_real = :prod",
      ];

      foreach ($sqls as $sql) {
        $stmt = $this->pdo->prepare($sql);
        $params = [':fid' => $fincaId];
        if (strpos($sql, ':prod') !== false) {
          $params[':prod'] = $productorIdReal;
        }
        $stmt->execute($params);
      }

      $this->pdo->commit();
    } catch (Throwable $e) {
      $this->pdo->rollBack();
      throw $e;
    }
  }
}
