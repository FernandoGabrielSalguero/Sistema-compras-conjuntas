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
        NULLIF(NULLIF(TRIM(CAST(u.cuit AS CHAR)), ''), '0') AS cuit
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
        NULLIF(NULLIF(TRIM(CAST(u.cuit AS CHAR)), ''), '0') AS cuit
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
        pc.finca_id,
        pc.variedad,
        pc.superficie_ha
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

  public function getDumpTablasProductor(string $productorIdReal, string $ingenieroIdReal): array
  {
    if ($productorIdReal === '') {
      throw new InvalidArgumentException('productor_id_real es requerido');
    }

    if (!$this->productorPerteneceAIngeniero($productorIdReal, $ingenieroIdReal)) {
      throw new RuntimeException('No autorizado para ver este productor');
    }

    $stUsuario = $this->pdo->prepare("
      SELECT *
      FROM usuarios
      WHERE id_real = :prod
        AND rol = 'productor'
      LIMIT 1
    ");
    $stUsuario->execute([':prod' => $productorIdReal]);
    $usuario = $stUsuario->fetch() ?: null;

    $usuarioId = (int)($usuario['id'] ?? 0);

    $usuariosInfo = [];
    if ($usuarioId > 0) {
      $stInfo = $this->pdo->prepare("
        SELECT *
        FROM usuarios_info
        WHERE usuario_id = :uid
        ORDER BY id ASC
      ");
      $stInfo->execute([':uid' => $usuarioId]);
      $usuariosInfo = $stInfo->fetchAll() ?: [];
    }

    $stRelCoop = $this->pdo->prepare("
      SELECT *
      FROM rel_productor_coop
      WHERE productor_id_real = :prod
      ORDER BY id ASC
    ");
    $stRelCoop->execute([':prod' => $productorIdReal]);
    $relProductorCoop = $stRelCoop->fetchAll() ?: [];

    $stFincas = $this->pdo->prepare("
      SELECT *
      FROM prod_fincas
      WHERE productor_id_real = :prod
      ORDER BY codigo_finca ASC, id ASC
    ");
    $stFincas->execute([':prod' => $productorIdReal]);
    $fincas = $stFincas->fetchAll() ?: [];

    $fincaIds = array_values(array_map('intval', array_column($fincas, 'id')));

    $prodFincaDireccion = [];
    $relProductorFinca = [];
    if (!empty($fincaIds)) {
      $phFincas = implode(',', array_fill(0, count($fincaIds), '?'));

      $stDir = $this->pdo->prepare("
        SELECT *
        FROM prod_finca_direccion
        WHERE finca_id IN ($phFincas)
        ORDER BY finca_id ASC, id ASC
      ");
      $stDir->execute($fincaIds);
      $prodFincaDireccion = $stDir->fetchAll() ?: [];

      $paramsRelFinca = $fincaIds;
      array_unshift($paramsRelFinca, $productorIdReal);
      $stRelFinca = $this->pdo->prepare("
        SELECT *
        FROM rel_productor_finca
        WHERE productor_id_real = ?
           OR finca_id IN ($phFincas)
        ORDER BY finca_id ASC, id ASC
      ");
      $stRelFinca->execute($paramsRelFinca);
      $relProductorFinca = $stRelFinca->fetchAll() ?: [];
    }

    $paramsCuarteles = [$productorIdReal];
    $sqlCuarteles = "
      SELECT DISTINCT pc.*
      FROM prod_cuartel pc
      LEFT JOIN prod_fincas pf
        ON pf.id = pc.finca_id
      WHERE pc.id_responsable_real = ?
    ";

    if (!empty($fincaIds)) {
      $phFincas = implode(',', array_fill(0, count($fincaIds), '?'));
      $sqlCuarteles .= " OR pc.finca_id IN ($phFincas) OR pf.productor_id_real = ?";
      $paramsCuarteles = array_merge($paramsCuarteles, $fincaIds, [$productorIdReal]);
    }

    $sqlCuarteles .= " ORDER BY pc.codigo_finca ASC, pc.codigo_cuartel ASC, pc.id ASC";
    $stCuarteles = $this->pdo->prepare($sqlCuarteles);
    $stCuarteles->execute($paramsCuarteles);
    $prodCuartel = $stCuarteles->fetchAll() ?: [];

    $cuartelIds = array_values(array_map('intval', array_column($prodCuartel, 'id')));

    $prodCuartelLimitantes = [];
    $prodCuartelRendimientos = [];
    $prodCuartelRiesgos = [];

    if (!empty($cuartelIds)) {
      $phCuarteles = implode(',', array_fill(0, count($cuartelIds), '?'));

      $stLimitantes = $this->pdo->prepare("
        SELECT *
        FROM prod_cuartel_limitantes
        WHERE cuartel_id IN ($phCuarteles)
        ORDER BY cuartel_id ASC, id ASC
      ");
      $stLimitantes->execute($cuartelIds);
      $prodCuartelLimitantes = $stLimitantes->fetchAll() ?: [];

      $stRend = $this->pdo->prepare("
        SELECT *
        FROM prod_cuartel_rendimientos
        WHERE cuartel_id IN ($phCuarteles)
        ORDER BY cuartel_id ASC, id ASC
      ");
      $stRend->execute($cuartelIds);
      $prodCuartelRendimientos = $stRend->fetchAll() ?: [];

      $stRiesgos = $this->pdo->prepare("
        SELECT *
        FROM prod_cuartel_riesgos
        WHERE cuartel_id IN ($phCuarteles)
        ORDER BY cuartel_id ASC, id ASC
      ");
      $stRiesgos->execute($cuartelIds);
      $prodCuartelRiesgos = $stRiesgos->fetchAll() ?: [];
    }

    return [
      'usuario' => $usuario,
      'usuarios_info' => $usuariosInfo,
      'rel_productor_coop' => $relProductorCoop,
      'prod_fincas' => $fincas,
      'prod_finca_direccion' => $prodFincaDireccion,
      'rel_productor_finca' => $relProductorFinca,
      'prod_cuartel' => $prodCuartel,
      'prod_cuartel_limitantes' => $prodCuartelLimitantes,
      'prod_cuartel_rendimientos' => $prodCuartelRendimientos,
      'prod_cuartel_riesgos' => $prodCuartelRiesgos,
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
