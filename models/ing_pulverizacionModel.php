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

  /**
   * Obtiene el Registro Fitosanitario de una solicitud
   * validando que pertenezca a productores de coops del ingeniero.
   */
  public function getRegistroBySolicitud(int $solicitudId, string $ingenieroIdReal): array
  {
    // Verificación de acceso (ingeniero -> productores de sus cooperativas)
    $sqlChk = "
            SELECT 1
            FROM drones_solicitud ds
            WHERE ds.id = :sid
              AND ds.productor_id_real IN (
                SELECT rpc.productor_id_real
                FROM rel_productor_coop rpc
                JOIN rel_coop_ingeniero rci
                  ON rci.cooperativa_id_real = rpc.cooperativa_id_real
                WHERE rci.ingeniero_id_real = :ing
              )";
    $stChk = $this->pdo->prepare($sqlChk);
    $stChk->execute([':sid' => $solicitudId, ':ing' => $ingenieroIdReal]);
    if (!$stChk->fetchColumn()) {
      throw new InvalidArgumentException('No autorizado o solicitud inexistente.');
    }

    // Cabecera + nombres
    $sqlHead = "
            SELECT ds.id AS solicitud_id, ds.fecha_visita, ds.estado,
                   COALESCE(ui_prod.nombre, uprod.usuario) AS productor_nombre,
                   COALESCE(ui_pil.nombre, upil.usuario)   AS piloto_nombre
            FROM drones_solicitud ds
            JOIN usuarios uprod ON uprod.id_real = ds.productor_id_real
            LEFT JOIN usuarios_info ui_prod ON ui_prod.usuario_id = uprod.id
            LEFT JOIN usuarios upil ON upil.id = ds.piloto_id
            LEFT JOIN usuarios_info ui_pil ON ui_pil.usuario_id = upil.id
            WHERE ds.id = :sid";
    $h = $this->pdo->prepare($sqlHead);
    $h->execute([':sid' => $solicitudId]);
    $head = $h->fetch() ?: [];

    // Reporte operativo (último)
    $sqlRep = "
            SELECT nom_cliente, nom_piloto, nom_encargado,
                   fecha_visita, hora_ingreso, hora_egreso,
                   nombre_finca, cultivo_pulverizado, sup_pulverizada,
                   vol_aplicado, vel_viento, temperatura, humedad_relativa
            FROM drones_solicitud_Reporte
            WHERE solicitud_id = :sid
            ORDER BY id DESC
            LIMIT 1";
    $r = $this->pdo->prepare($sqlRep);
    $r->execute([':sid' => $solicitudId]);
    $rep = $r->fetch() ?: [];

    // Productos (nombre comercial + receta)
    $sqlProd = "
            SELECT
              COALESCE(dsi.nombre_producto, dps.nombre) AS nombre,
              dsir.principio_activo,
              dsir.dosis,
              dsir.unidad,
              dsir.cant_prod_usado,
              dsir.fecha_vencimiento
            FROM drones_solicitud_item dsi
            LEFT JOIN dron_productos_stock dps ON dps.id = dsi.producto_id
            LEFT JOIN drones_solicitud_item_receta dsir ON dsir.solicitud_item_id = dsi.id
            WHERE dsi.solicitud_id = :sid
            ORDER BY dsi.id ASC";
    $p = $this->pdo->prepare($sqlProd);
    $p->execute([':sid' => $solicitudId]);
    $prods = [];
    foreach ($p->fetchAll() ?: [] as $row) {
      $prods[] = [
        'nombre'    => $row['nombre'] ?? '',
        'principio' => $row['principio_activo'] ?? '',
        'dosis'     => $row['dosis'] ?? '',
        'unidad'    => $row['unidad'] ?? '',
        'cant_usada' => $row['cant_prod_usado'] ?? '',
        'vto'       => $row['fecha_vencimiento'] ?? ''
      ];
    }

    // Media (fotos y firmas)
    $sqlMed = "
            SELECT tipo, ruta
            FROM drones_solicitud_reporte_media
            WHERE reporte_id = (
                SELECT id FROM drones_solicitud_Reporte
                WHERE solicitud_id = :sid ORDER BY id DESC LIMIT 1
            )";
    $m = $this->pdo->prepare($sqlMed);
    $m->execute([':sid' => $solicitudId]);
    $fotos = [];
    $firmaCliente = null;
    $firmaPrestador = null;
    foreach ($m->fetchAll() ?: [] as $row) {
      if ($row['tipo'] === 'foto') $fotos[] = $row['ruta'];
      if ($row['tipo'] === 'firma_cliente') $firmaCliente = $row['ruta'];
      if ($row['tipo'] === 'firma_piloto') $firmaPrestador = $row['ruta'];
    }

    return [
      'solicitud_id'  => $head['solicitud_id'] ?? $solicitudId,
      'fecha_visita'  => $rep['fecha_visita'] ?? ($head['fecha_visita'] ?? null),
      'productor_nombre' => $head['productor_nombre'] ?? null,
      'piloto_nombre' => $head['piloto_nombre'] ?? null,
      'representante' => $rep['nom_encargado'] ?? null,
      'nombre_finca'  => $rep['nombre_finca'] ?? null,
      'cultivo'       => $rep['cultivo_pulverizado'] ?? null,
      'superficie'    => $rep['sup_pulverizada'] ?? null,
      'hora_ingreso'  => $rep['hora_ingreso'] ?? null,
      'hora_egreso'   => $rep['hora_egreso'] ?? null,
      'temperatura'   => $rep['temperatura'] ?? null,
      'humedad'       => $rep['humedad_relativa'] ?? null,
      'vel_viento'    => $rep['vel_viento'] ?? null,
      'vol_aplicado'  => $rep['vol_aplicado'] ?? null,
      'productos'     => $prods,
      'fotos'         => $fotos,
      'firma_cliente' => $firmaCliente,
      'firma_prestador' => $firmaPrestador
    ];
  }

  /**
   * Detalle completo para pantalla Ver/Editar.
   */
  public function getDetalleEditable(int $solicitudId, string $ingenieroIdReal): array
  {
    // Autorización idéntica a getRegistroBySolicitud
    $sqlChk = "
            SELECT 1
            FROM drones_solicitud ds
            WHERE ds.id = :sid
              AND ds.productor_id_real IN (
                SELECT rpc.productor_id_real
                FROM rel_productor_coop rpc
                JOIN rel_coop_ingeniero rci
                  ON rci.cooperativa_id_real = rpc.cooperativa_id_real
                WHERE rci.ingeniero_id_real = :ing
              )";
    $st = $this->pdo->prepare($sqlChk);
    $st->execute([':sid' => $solicitudId, ':ing' => $ingenieroIdReal]);
    if (!$st->fetchColumn()) {
      throw new InvalidArgumentException('No autorizado o solicitud inexistente.');
    }

    $sql = "
            SELECT
                ds.id,
                ds.productor_id_real,
                COALESCE(uip.nombre, up.usuario) AS productor_nombre,
                COALESCE(uicoop.nombre, ucoop.usuario) AS cooperativa_nombre,
                ds.fecha_visita, ds.hora_visita_desde, ds.hora_visita_hasta,
                ds.estado, ds.piloto_id, ds.forma_pago_id, ds.observaciones
            FROM drones_solicitud ds
            JOIN usuarios up ON up.id_real = ds.productor_id_real
            LEFT JOIN usuarios_info uip ON uip.usuario_id = up.id
            LEFT JOIN rel_productor_coop rpc2 ON rpc2.productor_id_real = ds.productor_id_real
            LEFT JOIN usuarios ucoop ON ucoop.id_real = rpc2.cooperativa_id_real
            LEFT JOIN usuarios_info uicoop ON uicoop.usuario_id = ucoop.id
            WHERE ds.id = :sid";
    $h = $this->pdo->prepare($sql);
    $h->execute([':sid' => $solicitudId]);
    $head = $h->fetch() ?: [];

    $sqlCosto = "SELECT COALESCE(total,0) AS total FROM drones_solicitud_costos WHERE solicitud_id = :sid";
    $c = $this->pdo->prepare($sqlCosto);
    $c->execute([':sid' => $solicitudId]);
    $head['costo_total'] = (float)($c->fetchColumn() ?: 0);

    return $head;
  }

  /**
   * Actualización básica de campos editables.
   */
  public function updateSolicitudBasic(int $solicitudId, string $ingenieroIdReal, array $d): void
  {
    // Misma verificación de acceso
    $sqlChk = "
            SELECT 1
            FROM drones_solicitud ds
            WHERE ds.id = :sid
              AND ds.productor_id_real IN (
                SELECT rpc.productor_id_real
                FROM rel_productor_coop rpc
                JOIN rel_coop_ingeniero rci
                  ON rci.cooperativa_id_real = rpc.cooperativa_id_real
                WHERE rci.ingeniero_id_real = :ing
              )";
    $stChk = $this->pdo->prepare($sqlChk);
    $stChk->execute([':sid' => $solicitudId, ':ing' => $ingenieroIdReal]);
    if (!$stChk->fetchColumn()) {
      throw new InvalidArgumentException('No autorizado o solicitud inexistente.');
    }

    $fields = [
      'fecha_visita' => $d['fecha_visita'] ?? null,
      'hora_visita_desde' => $d['hora_visita_desde'] ?? null,
      'hora_visita_hasta' => $d['hora_visita_hasta'] ?? null,
      'estado' => $d['estado'] ?? null,
      'piloto_id' => $d['piloto_id'] ?? null,
      'forma_pago_id' => $d['forma_pago_id'] ?? null,
      'observaciones' => $d['observaciones'] ?? null,
    ];

    $sets = [];
    $params = [':sid' => $solicitudId];
    foreach ($fields as $k => $v) {
      if ($v === null) continue;
      $sets[] = " $k = :$k ";
      $params[":$k"] = $v;
    }
    if (!$sets) return;

    $sql = "UPDATE drones_solicitud SET " . implode(',', $sets) . " WHERE id = :sid";
    $st = $this->pdo->prepare($sql);
    $st->execute($params);
  }

  /**
   * Cancelar solicitud (soft-delete).
   */
  public function cancelSolicitud(int $solicitudId, string $ingenieroIdReal): void
  {
    // Verificación acceso
    $sqlChk = "
            SELECT 1
            FROM drones_solicitud ds
            WHERE ds.id = :sid
              AND ds.productor_id_real IN (
                SELECT rpc.productor_id_real
                FROM rel_productor_coop rpc
                JOIN rel_coop_ingeniero rci
                  ON rci.cooperativa_id_real = rpc.cooperativa_id_real
                WHERE rci.ingeniero_id_real = :ing
              )";
    $stChk = $this->pdo->prepare($sqlChk);
    $stChk->execute([':sid' => $solicitudId, ':ing' => $ingenieroIdReal]);
    if (!$stChk->fetchColumn()) {
      throw new InvalidArgumentException('No autorizado o solicitud inexistente.');
    }

    $sql = "UPDATE drones_solicitud SET estado = 'cancelada', motivo_cancelacion = 'Eliminado por ingeniero' WHERE id = :sid";
    $st = $this->pdo->prepare($sql);
    $st->execute([':sid' => $solicitudId]);

    // (Opcional) registrar evento
    try {
      $st2 = $this->pdo->prepare("INSERT INTO drones_solicitud_evento (solicitud_id,tipo,detalle,actor) VALUES (:sid,'cancelacion','Eliminado por ingeniero',:actor)");
      $st2->execute([':sid' => $solicitudId, ':actor' => $ingenieroIdReal]);
    } catch (\Throwable $e) { /* no bloquear */
    }
  }
}
