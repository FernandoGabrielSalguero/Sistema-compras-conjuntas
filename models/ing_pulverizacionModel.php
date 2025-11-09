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

    // Cabecera de la solicitud + nombres
    $sql = "
        SELECT
            ds.*,
            COALESCE(uip.nombre, up.usuario) AS productor_nombre,
            COALESCE(uicoop.nombre, ucoop.usuario) AS cooperativa_nombre
        FROM drones_solicitud ds
        JOIN usuarios up ON up.id_real = ds.productor_id_real
        LEFT JOIN usuarios_info uip ON uip.usuario_id = up.id
        LEFT JOIN rel_productor_coop rpc2 ON rpc2.productor_id_real = ds.productor_id_real
        LEFT JOIN usuarios ucoop ON ucoop.id_real = rpc2.cooperativa_id_real
        LEFT JOIN usuarios_info uicoop ON uicoop.usuario_id = ucoop.id
        WHERE ds.id = :sid";
    $h = $this->pdo->prepare($sql);
    $h->execute([':sid' => $solicitudId]);
    $sol = $h->fetch() ?: [];

    // Costos
    $sqlCosto = "SELECT * FROM drones_solicitud_costos WHERE solicitud_id = :sid";
    $c = $this->pdo->prepare($sqlCosto);
    $c->execute([':sid' => $solicitudId]);
    $costos = $c->fetch() ?: null;

    // Parámetros
    $sqlPar = "SELECT * FROM drones_solicitud_parametros WHERE solicitud_id = :sid ORDER BY id DESC LIMIT 1";
    $p = $this->pdo->prepare($sqlPar);
    $p->execute([':sid' => $solicitudId]);
    $parametros = $p->fetch() ?: null;

    // Motivos
    $sqlMot = "
        SELECT dsm.*, dp.nombre AS patologia_nombre
        FROM drones_solicitud_motivo dsm
        LEFT JOIN dron_patologias dp ON dp.id = dsm.patologia_id
        WHERE dsm.solicitud_id = :sid";
    $m = $this->pdo->prepare($sqlMot);
    $m->execute([':sid' => $solicitudId]);
    $motivos = $m->fetchAll() ?: [];

    // Items + Receta
    $sqlIt = "
        SELECT i.*, r.principio_activo, r.dosis, r.unidad, r.cant_prod_usado, r.fecha_vencimiento, r.orden_mezcla, r.notas
        FROM drones_solicitud_item i
        LEFT JOIN drones_solicitud_item_receta r ON r.solicitud_item_id = i.id
        WHERE i.solicitud_id = :sid
        ORDER BY i.id ASC";
    $it = $this->pdo->prepare($sqlIt);
    $it->execute([':sid' => $solicitudId]);
    $items = $it->fetchAll() ?: [];

    // Reporte + media
    $sqlRep = "SELECT * FROM drones_solicitud_Reporte WHERE solicitud_id = :sid ORDER BY id DESC LIMIT 1";
    $r = $this->pdo->prepare($sqlRep);
    $r->execute([':sid' => $solicitudId]);
    $reporte = $r->fetch() ?: null;

    $media = [];
    if ($reporte) {
      $sqlMed = "SELECT * FROM drones_solicitud_reporte_media WHERE reporte_id = :rid";
      $md = $this->pdo->prepare($sqlMed);
      $md->execute([':rid' => $reporte['id']]);
      $media = $md->fetchAll() ?: [];
    }

    return [
      'solicitud'  => $sol,
      'costos'     => $costos,
      'parametros' => $parametros,
      'motivos'    => $motivos,
      'items'      => $items,
      'reporte'    => $reporte,
      'media'      => $media
    ];
  }


  public function updateSolicitudFull(int $solicitudId, string $ingenieroIdReal, array $data): void
  {
    // Autorización
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

    $this->pdo->beginTransaction();
    try {
      // 1) Actualizar campos básicos de drones_solicitud
      $base = $data['base'] ?? [];
      if ($base) {
        $fields = [
          'fecha_visita',
          'hora_visita_desde',
          'hora_visita_hasta',
          'estado',
          'piloto_id',
          'forma_pago_id',
          'observaciones'
        ];
        $sets = [];
        $params = [':sid' => $solicitudId];
        foreach ($fields as $f) {
          if (array_key_exists($f, $base) && $base[$f] !== null) {
            $sets[] = "$f = :$f";
            $params[":$f"] = $base[$f];
          }
        }
        if ($sets) {
          $sql = "UPDATE drones_solicitud SET " . implode(',', $sets) . " WHERE id = :sid";
          $u = $this->pdo->prepare($sql);
          $u->execute($params);
        }
      }

      $full = $data['full'] ?? [];
      // 2) Parámetros (UPSERT simple)
      if (!empty($full['parametros'])) {
        $p = $full['parametros'];
        $exists = $this->pdo->prepare("SELECT id FROM drones_solicitud_parametros WHERE solicitud_id=:sid ORDER BY id DESC LIMIT 1");
        $exists->execute([':sid' => $solicitudId]);
        $pid = $exists->fetchColumn();
        $cols = ['volumen_ha', 'velocidad_vuelo', 'alto_vuelo', 'ancho_pasada', 'tamano_gota', 'observaciones', 'observaciones_agua'];
        if ($pid) {
          $sets = [];
          $params = [':id' => $pid];
          foreach ($cols as $c) {
            if (array_key_exists($c, $p)) {
              $sets[] = "$c=:$c";
              $params[":$c"] = $p[$c];
            }
          }
          if ($sets) {
            $sql = "UPDATE drones_solicitud_parametros SET " . implode(',', $sets) . " WHERE id=:id";
            $stp = $this->pdo->prepare($sql);
            $stp->execute($params);
          }
        } else {
          $params = [':sid' => $solicitudId];
          $fields = ['solicitud_id'];
          $holders = [':sid'];
          foreach ($cols as $c) {
            if (array_key_exists($c, $p)) {
              $fields[] = $c;
              $holders[] = ":$c";
              $params[":$c"] = $p[$c];
            }
          }
          $sql = "INSERT INTO drones_solicitud_parametros (" . implode(',', $fields) . ") VALUES (" . implode(',', $holders) . ")";
          $stp = $this->pdo->prepare($sql);
          $stp->execute($params);
        }
      }

      // 3) Motivos (reemplazo total)
      if (isset($full['motivos'])) {
        $del = $this->pdo->prepare("DELETE FROM drones_solicitud_motivo WHERE solicitud_id=:sid");
        $del->execute([':sid' => $solicitudId]);
        $ins = $this->pdo->prepare("INSERT INTO drones_solicitud_motivo (solicitud_id, patologia_id, es_otros, otros_text) VALUES (:sid,:pid,:eo,:ot)");
        foreach ($full['motivos'] as $m) {
          $ins->execute([
            ':sid' => $solicitudId,
            ':pid' => $m['patologia_id'] ?? null,
            ':eo' => (int)($m['es_otros'] ?? 0),
            ':ot' => $m['otros_text'] ?? null
          ]);
        }
      }

      // 4) Items + receta (reemplazo total)
      if (isset($full['items'])) {
        $this->pdo->prepare("DELETE r FROM drones_solicitud_item_receta r JOIN drones_solicitud_item i ON i.id=r.solicitud_item_id WHERE i.solicitud_id=:sid")->execute([':sid' => $solicitudId]);
        $this->pdo->prepare("DELETE FROM drones_solicitud_item WHERE solicitud_id=:sid")->execute([':sid' => $solicitudId]);

        $insI = $this->pdo->prepare("INSERT INTO drones_solicitud_item (solicitud_id, patologia_id, fuente, producto_id, costo_hectarea_snapshot, total_producto_snapshot, nombre_producto) VALUES (:sid,:pat,:fue,:prod,:chs,:tps,:nom)");
        $insR = $this->pdo->prepare("INSERT INTO drones_solicitud_item_receta (solicitud_item_id, principio_activo, dosis, cant_prod_usado, fecha_vencimiento, unidad, orden_mezcla, notas) VALUES (:iid,:pa,:dos,:cpu,:fv,:uni,:om,:not)");
        foreach ($full['items'] as $it) {
          $insI->execute([
            ':sid' => $solicitudId,
            ':pat' => $it['patologia_id'] ?? null,
            ':fue' => $it['fuente'] ?? 'sve',
            ':prod' => $it['producto_id'] ?? null,
            ':chs' => $it['costo_hectarea_snapshot'] ?? null,
            ':tps' => $it['total_producto_snapshot'] ?? null,
            ':nom' => $it['nombre_producto'] ?? null
          ]);
          $iid = (int)$this->pdo->lastInsertId();
          $insR->execute([
            ':iid' => $iid,
            ':pa' => $it['principio_activo'] ?? null,
            ':dos' => $it['dosis'] ?? null,
            ':cpu' => $it['cant_prod_usado'] ?? null,
            ':fv' => $it['fecha_vencimiento'] ?? null,
            ':uni' => $it['unidad'] ?? null,
            ':om' => $it['orden_mezcla'] ?? null,
            ':not' => $it['notas'] ?? null
          ]);
        }
      }

      // 5) Reporte (UPSERT simple del último)
      if (!empty($full['reporte'])) {
        $r = $full['reporte'];
        $exists = $this->pdo->prepare("SELECT id FROM drones_solicitud_Reporte WHERE solicitud_id=:sid ORDER BY id DESC LIMIT 1");
        $exists->execute([':sid' => $solicitudId]);
        $rid = $exists->fetchColumn();
        $cols = ['nom_cliente', 'nom_piloto', 'nom_encargado', 'fecha_visita', 'hora_ingreso', 'hora_egreso', 'nombre_finca', 'cultivo_pulverizado', 'cuadro_cuartel', 'sup_pulverizada', 'vol_aplicado', 'vel_viento', 'temperatura', 'humedad_relativa', 'observaciones'];
        if ($rid) {
          $sets = [];
          $params = [':id' => $rid];
          foreach ($cols as $c) {
            if (array_key_exists($c, $r)) {
              $sets[] = "$c=:$c";
              $params[":$c"] = $r[$c];
            }
          }
          if ($sets) {
            $sql = "UPDATE drones_solicitud_Reporte SET " . implode(',', $sets) . " WHERE id=:id";
            $ur = $this->pdo->prepare($sql);
            $ur->execute($params);
          }
        } else {
          $params = [':sid' => $solicitudId];
          $fields = ['solicitud_id'];
          $holders = [':sid'];
          foreach ($cols as $c) {
            if (array_key_exists($c, $r)) {
              $fields[] = $c;
              $holders[] = ":$c";
              $params[":$c"] = $r[$c];
            }
          }
          $sql = "INSERT INTO drones_solicitud_Reporte (" . implode(',', $fields) . ") VALUES (" . implode(',', $holders) . ")";
          $ir = $this->pdo->prepare($sql);
          $ir->execute($params);
          $rid = (int)$this->pdo->lastInsertId();
        }

        // Media (reemplazo si viene)
        if (isset($full['media'])) {
          $this->pdo->prepare("DELETE FROM drones_solicitud_reporte_media WHERE reporte_id=:rid")->execute([':rid' => $rid]);
          $insm = $this->pdo->prepare("INSERT INTO drones_solicitud_reporte_media (reporte_id,tipo,ruta) VALUES (:rid,:tip,:rut)");
          foreach ($full['media'] as $mm) {
            $insm->execute([':rid' => $rid, ':tip' => $mm['tipo'], ':rut' => $mm['ruta']]);
          }
        }
      }

      // 6) Costos (opcional)
      if (!empty($full['costos'])) {
        $co = $full['costos'];
        $exists = $this->pdo->prepare("SELECT id FROM drones_solicitud_costos WHERE solicitud_id=:sid");
        $exists->execute([':sid' => $solicitudId]);
        $cid = $exists->fetchColumn();
        $cols = ['moneda', 'costo_base_por_ha', 'base_ha', 'base_total', 'productos_total', 'total', 'desglose_json'];
        if ($cid) {
          $sets = [];
          $params = [':sid' => $solicitudId];
          foreach ($cols as $c) {
            if (array_key_exists($c, $co)) {
              $sets[] = "$c=:$c";
              $params[":$c"] = $co[$c];
            }
          }
          if ($sets) {
            $sql = "UPDATE drones_solicitud_costos SET " . implode(',', $sets) . " WHERE solicitud_id=:sid";
            $uc = $this->pdo->prepare($sql);
            $uc->execute($params);
          }
        } else {
          $params = [':sid' => $solicitudId];
          $fields = ['solicitud_id'];
          $holders = [':sid'];
          foreach ($cols as $c) {
            if (array_key_exists($c, $co)) {
              $fields[] = $c;
              $holders[] = ":$c";
              $params[":$c"] = $co[$c];
            }
          }
          $sql = "INSERT INTO drones_solicitud_costos (" . implode(',', $fields) . ") VALUES (" . implode(',', $holders) . ")";
          $ic = $this->pdo->prepare($sql);
          $ic->execute($params);
        }
      }

      $this->pdo->commit();
    } catch (\Throwable $e) {
      $this->pdo->rollBack();
      throw $e;
    }
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
