<?php

declare(strict_types=1);

final class pulverizacionEditModel
{
    public PDO $pdo;

    /* ===== Catálogos / búsquedas ===== */

    public function formasPago(): array
    {
        $sql = "SELECT id,nombre FROM dron_formas_pago WHERE activo='si' ORDER BY nombre";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    public function patologias(): array
    {
        $sql = "SELECT id,nombre FROM dron_patologias WHERE activo='si' ORDER BY nombre";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    public function cooperativas(): array
    {
        $sql = "SELECT usuario,id_real FROM usuarios WHERE rol='cooperativa' ORDER BY usuario";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    public function rangos(): array
    {
        return [
            ['rango' => 'octubre_q1', 'label' => 'Primera quincena de Octubre'],
            ['rango' => 'octubre_q2', 'label' => 'Segunda quincena de Octubre'],
            ['rango' => 'noviembre_q1', 'label' => 'Primera quincena de Noviembre'],
            ['rango' => 'noviembre_q2', 'label' => 'Segunda quincena de Noviembre'],
            ['rango' => 'diciembre_q1', 'label' => 'Primera quincena de Diciembre'],
            ['rango' => 'diciembre_q2', 'label' => 'Segunda quincena de Diciembre'],
            ['rango' => 'enero_q1', 'label' => 'Primera quincena de Enero'],
            ['rango' => 'enero_q2', 'label' => 'Segunda quincena de Enero'],
            ['rango' => 'febrero_q1', 'label' => 'Primera quincena de Febrero'],
            ['rango' => 'febrero_q2', 'label' => 'Segunda quincena de Febrero'],
        ];
    }

    public function buscarUsuariosFiltrado(string $q, string $rol, string $idReal, ?string $coopId = null): array
    {
        $like = '%' . $q . '%';

        if ($rol === 'sve') {
            if ($coopId) {
                $st = $this->pdo->prepare("SELECT u.usuario,u.id_real
                  FROM usuarios u
                  JOIN rel_productor_coop rpc ON rpc.productor_id_real=u.id_real
                 WHERE u.rol='productor' AND u.permiso_ingreso='Habilitado'
                   AND rpc.cooperativa_id_real=? AND u.usuario LIKE ?
                 ORDER BY u.usuario LIMIT 10");
                $st->execute([$coopId, $like]);
                return $st->fetchAll(PDO::FETCH_ASSOC);
            }
            $st = $this->pdo->prepare("SELECT usuario,id_real FROM usuarios
              WHERE rol='productor' AND permiso_ingreso='Habilitado' AND usuario LIKE ?
              ORDER BY usuario LIMIT 10");
            $st->execute([$like]);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        }

        if ($rol === 'ingeniero') {
            $st = $this->pdo->prepare("SELECT DISTINCT u.usuario,u.id_real
              FROM usuarios u
              JOIN rel_productor_coop rpc ON rpc.productor_id_real=u.id_real
              JOIN rel_coop_ingeniero rci ON rci.cooperativa_id_real=rpc.cooperativa_id_real
             WHERE u.rol='productor' AND u.permiso_ingreso='Habilitado'
               AND rci.ingeniero_id_real=? AND u.usuario LIKE ?
             ORDER BY u.usuario LIMIT 10");
            $st->execute([$idReal, $like]);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        }

        if ($rol === 'cooperativa') {
            $st = $this->pdo->prepare("SELECT u.usuario,u.id_real
              FROM usuarios u
              JOIN rel_productor_coop rpc ON rpc.productor_id_real=u.id_real
             WHERE u.rol='productor' AND u.permiso_ingreso='Habilitado'
               AND rpc.cooperativa_id_real=? AND u.usuario LIKE ?
             ORDER BY u.usuario LIMIT 10");
            $st->execute([$idReal, $like]);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        }

        if ($rol === 'productor') {
            $st = $this->pdo->prepare("SELECT usuario,id_real FROM usuarios
              WHERE rol='productor' AND permiso_ingreso='Habilitado' AND id_real=? AND usuario LIKE ?
              ORDER BY usuario LIMIT 10");
            $st->execute([$idReal, $like]);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        }

        return [];
    }

    public function productosPorPatologia(int $patologiaId): array
    {
        $sql = "SELECT s.id, s.nombre, COALESCE(s.costo_hectarea,0) AS costo_hectarea, COALESCE(s.detalle,'') AS detalle
                  FROM dron_productos_stock_patologias sp
                  JOIN dron_productos_stock s ON s.id = sp.producto_id
                 WHERE sp.patologia_id = ?
                   AND LOWER(COALESCE(s.activo,'no')) = 'si'
              ORDER BY s.nombre";
        $st = $this->pdo->prepare($sql);
        $st->execute([$patologiaId]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function costoBaseHectarea(): array
    {
        $sql = "SELECT costo, COALESCE(moneda,'Pesos') AS moneda, updated_at
                  FROM dron_costo_hectarea
              ORDER BY updated_at DESC
                 LIMIT 1";
        $row = $this->pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return ['costo' => 0.0, 'moneda' => 'Pesos', 'updated_at' => null];
        }
        $row['costo'] = (float)$row['costo'];
        return $row;
    }

    /* ===== Correo / datos básicos ===== */

    public function correoPreferidoPorIdReal(string $idReal): ?string
    {
        if ($idReal === '') return null;

        // Única fuente válida según schema: usuarios_info.correo
        $st = $this->pdo->prepare(
            "SELECT ui.correo
           FROM usuarios u
           LEFT JOIN usuarios_info ui ON ui.usuario_id = u.id
          WHERE u.id_real = ? 
          LIMIT 1"
        );
        $st->execute([$idReal]);
        $v = $st->fetchColumn();

        $correo = $v ? trim((string)$v) : '';
        return $correo !== '' ? $correo : null;
    }


    public function nombrePorIdReal(string $idReal): ?string
    {
        $st = $this->pdo->prepare("SELECT usuario FROM usuarios WHERE id_real=? LIMIT 1");
        $st->execute([$idReal]);
        $v = $st->fetchColumn();
        return $v ? (string)$v : null;
    }

    /* ===== Helpers productos ===== */
    private function productoCostoHa(int $id): float
    {
        if ($id <= 0) return 0.0;
        $st = $this->pdo->prepare("SELECT costo_hectarea FROM dron_productos_stock WHERE id=?");
        $st->execute([$id]);
        $v = $st->fetchColumn();
        return $v !== false ? (float)$v : 0.0;
    }
    private function productoNombre(int $id): string
    {
        if ($id <= 0) return '';
        $st = $this->pdo->prepare("SELECT nombre FROM dron_productos_stock WHERE id=?");
        $st->execute([$id]);
        $v = $st->fetchColumn();
        return $v ? (string)$v : '';
    }

    /* ===== Intentar snapshot de productor_nombre si existe la columna ===== */
    private function actualizarProductorNombreSnapshot(int $solicitudId, ?string $nombre): void
    {
        if ($solicitudId <= 0 || !$nombre) return;
        try {
            $hasCol = (bool)$this->pdo->query("SHOW COLUMNS FROM drones_solicitud LIKE 'productor_nombre'")->fetch();
            if ($hasCol) {
                $st = $this->pdo->prepare("UPDATE drones_solicitud SET productor_nombre = :n WHERE id = :id");
                $st->execute([':n' => mb_substr($nombre, 0, 150), ':id' => $solicitudId]);
            }
        } catch (\Throwable $e) { /* silencioso */
        }
    }

    /* ===== Creación con items y costos ===== */
    public function crearSolicitud(array $d): array
    {
        $this->pdo->beginTransaction();
        try {
            // 1) solicitud
            $st = $this->pdo->prepare("INSERT INTO drones_solicitud
              (productor_id_real,representante,linea_tension,zona_restringida,corriente_electrica,agua_potable,
               libre_obstaculos,area_despegue,superficie_ha,forma_pago_id,coop_descuento_nombre,
               dir_provincia,dir_localidad,dir_calle,dir_numero,observaciones,estado)
               VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, 'ingresada')");
            $st->execute([
                $d['productor_id_real'],
                $d['representante'],
                $d['linea_tension'],
                $d['zona_restringida'],
                $d['corriente_electrica'],
                $d['agua_potable'],
                $d['libre_obstaculos'],
                $d['area_despegue'],
                $d['superficie_ha'],
                $d['forma_pago_id'],
                $d['coop_descuento_nombre'],
                $d['dir_provincia'],
                $d['dir_localidad'],
                $d['dir_calle'],
                $d['dir_numero'],
                $d['observaciones']
            ]);
            $sid = (int)$this->pdo->lastInsertId();

            // 1.b) snapshot de nombre (si columna existe)
            $this->actualizarProductorNombreSnapshot($sid, $d['productor_nombre_snapshot'] ?? null);

            // 2) motivos (varias patologías)
            $stm = $this->pdo->prepare("INSERT INTO drones_solicitud_motivo (solicitud_id,patologia_id,es_otros) VALUES (?,?,0)");
            foreach ((array)($d['patologia_ids'] ?? []) as $pid) {
                $stm->execute([$sid, (int)$pid]);
            }

            // 3) rango
            $str = $this->pdo->prepare("INSERT INTO drones_solicitud_rango (solicitud_id,rango) VALUES (?,?)");
            $str->execute([$sid, $d['rango']]);

            // 4) items (si vienen)
            $productosTotal = 0.0;
            if (!empty($d['items']) && is_array($d['items'])) {
                $sti = $this->pdo->prepare("INSERT INTO drones_solicitud_item
                    (solicitud_id, patologia_id, fuente, producto_id, costo_hectarea_snapshot, total_producto_snapshot, nombre_producto)
                    VALUES (?,?,?,?,?,?,?)");

                foreach ($d['items'] as $it) {
                    $pid    = isset($it['producto_id']) ? (int)$it['producto_id'] : 0;
                    $fuente = (string)($it['fuente'] ?? '');
                    $custom = isset($it['nombre_producto_custom']) ? trim((string)$it['nombre_producto_custom']) : '';
                    $patIt  = isset($it['patologia_id']) ? (int)$it['patologia_id'] : 0;

                    if ($fuente === 'productor') {
                        $nombre = $custom !== '' ? mb_substr($custom, 0, 150) : $this->productoNombre($pid);
                        $costoHa = 0.00;
                        $total = 0.00;
                        $productoIdDb = ($pid > 0) ? $pid : null;
                    } else { // SVE
                        $nombre = $this->productoNombre($pid);
                        $costoHa = $this->productoCostoHa($pid);
                        $total   = (float)$d['superficie_ha'] * (float)$costoHa;
                        $productoIdDb = $pid;
                        $productosTotal += $total;
                    }

                    $sti->execute([$sid, $patIt, $fuente, $productoIdDb, $costoHa, $total, $nombre]);
                }
            }

            // 5) costos
            $row = $this->costoBaseHectarea();
            $costoHa = (float)$row['costo'];
            $moneda = (string)$row['moneda'];
            $baseHa  = (float)$d['superficie_ha'];
            $baseTotal = $baseHa * $costoHa;
            $total   = $baseTotal + $productosTotal;

            $stc = $this->pdo->prepare("INSERT INTO drones_solicitud_costos
              (solicitud_id,moneda,costo_base_por_ha,base_ha,base_total,productos_total,total,desglose_json)
              VALUES (?,?,?,?,?,?,?,NULL)");
            $stc->execute([$sid, $moneda, $costoHa, $baseHa, $baseTotal, $productosTotal, $total]);

            // 6) evento
            $ste = $this->pdo->prepare("INSERT INTO drones_solicitud_evento (solicitud_id,tipo,detalle,actor)
              VALUES (?,'creada','Solicitud ingresada por formulario (ingeniero)','sistema')");
            $ste->execute([$sid]);

            $this->pdo->commit();
            return ['ok' => true, 'id' => $sid];
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
