<?php

declare(strict_types=1);

class DroneFormularioNservicioModel
{
    /** @var PDO */
    public PDO $pdo;

    /** Búsqueda de PRODUCTORES por nombre (filtrado por rol en sesión) */
    public function buscarUsuariosFiltrado(string $q, string $rol, string $idReal, ?string $coopId = null): array
    {
        $like = '%' . $q . '%';

        if ($rol === 'sve') {
            // Si SVE pasa una cooperativa por parámetro, filtramos por ella; si no, todos los habilitados
            if ($coopId) {
                $sql = "SELECT u.usuario, u.id_real
                      FROM usuarios u
                      JOIN rel_productor_coop rpc ON rpc.productor_id_real = u.id_real
                     WHERE u.rol = 'productor'
                       AND u.permiso_ingreso = 'Habilitado'
                       AND rpc.cooperativa_id_real = ?
                       AND u.usuario LIKE ?
                  ORDER BY u.usuario
                     LIMIT 10";
                $st = $this->pdo->prepare($sql);
                $st->execute([$coopId, $like]);
                return $st->fetchAll(PDO::FETCH_ASSOC);
            }

            $sql = "SELECT usuario, id_real
                  FROM usuarios
                 WHERE rol = 'productor'
                   AND permiso_ingreso = 'Habilitado'
                   AND usuario LIKE ?
              ORDER BY usuario
                 LIMIT 10";
            $st = $this->pdo->prepare($sql);
            $st->execute([$like]);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        }

        if ($rol === 'ingeniero') {
            // Productores de cooperativas vinculadas al ingeniero
            $sql = "SELECT DISTINCT u.usuario, u.id_real
                  FROM usuarios u
                  JOIN rel_productor_coop rpc ON rpc.productor_id_real = u.id_real
                  JOIN rel_coop_ingeniero rci ON rci.cooperativa_id_real = rpc.cooperativa_id_real
                 WHERE u.rol = 'productor'
                   AND u.permiso_ingreso = 'Habilitado'
                   AND rci.ingeniero_id_real = ?
                   AND u.usuario LIKE ?
              ORDER BY u.usuario
                 LIMIT 10";
            $st = $this->pdo->prepare($sql);
            $st->execute([$idReal, $like]);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        }

        if ($rol === 'cooperativa') {
            // Productores asociados a ESTA cooperativa (se usa SIEMPRE el id de sesión)
            $sql = "SELECT u.usuario, u.id_real
                  FROM usuarios u
                  JOIN rel_productor_coop rpc ON rpc.productor_id_real = u.id_real
                 WHERE u.rol = 'productor'
                   AND u.permiso_ingreso = 'Habilitado'
                   AND rpc.cooperativa_id_real = ?
                   AND u.usuario LIKE ?
              ORDER BY u.usuario
                 LIMIT 10";
            $st = $this->pdo->prepare($sql);
            $st->execute([$idReal, $like]);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        }

        if ($rol === 'productor') {
            $sql = "SELECT usuario, id_real
                  FROM usuarios
                 WHERE rol = 'productor'
                   AND permiso_ingreso = 'Habilitado'
                   AND id_real = ?
                   AND usuario LIKE ?
              ORDER BY usuario
                 LIMIT 10";
            $st = $this->pdo->prepare($sql);
            $st->execute([$idReal, $like]);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        }

        return [];
    }


    /** Rangos disponibles (orden comenzando por octubre) */
    public function rangos(): array
    {
        return [
            ['rango' => 'octubre_q1',   'label' => 'Primera quincena de Octubre'],
            ['rango' => 'octubre_q2',   'label' => 'Segunda quincena de Octubre'],
            ['rango' => 'noviembre_q1', 'label' => 'Primera quincena de Noviembre'],
            ['rango' => 'noviembre_q2', 'label' => 'Segunda quincena de Noviembre'],
            ['rango' => 'diciembre_q1', 'label' => 'Primera quincena de Diciembre'],
            ['rango' => 'diciembre_q2', 'label' => 'Segunda quincena de Diciembre'],
            ['rango' => 'enero_q1',     'label' => 'Primera quincena de Enero'],
            ['rango' => 'enero_q2',     'label' => 'Segunda quincena de Enero'],
            ['rango' => 'febrero_q1',   'label' => 'Primera quincena de Febrero'],
            ['rango' => 'febrero_q2',   'label' => 'Segunda quincena de Febrero'],
        ];
    }

    /** Cooperativas para forma de pago id=6 */
    public function cooperativas(): array
    {
        $sql = "SELECT usuario, id_real FROM usuarios WHERE rol = 'cooperativa' ORDER BY usuario";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Formas de pago activas */
    public function formasPago(): array
    {
        $sql = "SELECT id, nombre FROM dron_formas_pago WHERE activo='si' ORDER BY nombre";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Patologías activas */
    public function patologias(): array
    {
        $sql = "SELECT id, nombre FROM dron_patologias WHERE activo='si' ORDER BY nombre";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Productos por patología (con costo/ha) — SOLO activos */
    public function productosPorPatologia(int $patologiaId): array
    {
        $sql = "SELECT s.id,
                   s.nombre,
                   COALESCE(s.costo_hectarea,0) AS costo_hectarea,
                   COALESCE(s.detalle,'')       AS detalle
              FROM dron_productos_stock_patologias sp
              JOIN dron_productos_stock s ON s.id = sp.producto_id
             WHERE sp.patologia_id = ?
               AND LOWER(COALESCE(s.activo,'no')) = 'si'
          ORDER BY s.nombre";
        $st = $this->pdo->prepare($sql);
        $st->execute([$patologiaId]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }



    /** Costo base por hectárea del servicio */
    public function costoBaseHectarea(): array
    {
        $sql = "SELECT costo, COALESCE(moneda,'Pesos') AS moneda, updated_at
                  FROM dron_costo_hectarea
              ORDER BY updated_at DESC
                 LIMIT 1";
        $row = $this->pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return ['costo' => 0.00, 'moneda' => 'Pesos', 'updated_at' => null];
        }
        $row['costo'] = (float)$row['costo'];
        return $row;
    }

    /** Inserta la solicitud + secundarios en transacción (y crea costos) */
    public function crearSolicitud(array $d): array
    {
        $this->pdo->beginTransaction();
        try {
            // ===== 1) drones_solicitud
            $sql = "INSERT INTO drones_solicitud
                (productor_id_real, representante, linea_tension, zona_restringida, corriente_electrica, agua_potable,
                 libre_obstaculos, area_despegue, superficie_ha, forma_pago_id, coop_descuento_nombre,
                 dir_provincia, dir_localidad, dir_calle, dir_numero, observaciones, estado)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, 'ingresada')";
            $st = $this->pdo->prepare($sql);
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
                $d['observaciones'],
            ]);
            $solicitudId = (int)$this->pdo->lastInsertId();

            // ===== 2) motivo
            $sqlMot = "INSERT INTO drones_solicitud_motivo (solicitud_id, patologia_id, es_otros) VALUES (?,?,0)";
            $stm = $this->pdo->prepare($sqlMot);
            $stm->execute([$solicitudId, $d['patologia_id']]);

            // ===== 3) rango
            $sqlR = "INSERT INTO drones_solicitud_rango (solicitud_id, rango) VALUES (?,?)";
            $str = $this->pdo->prepare($sqlR);
            $str->execute([$solicitudId, $d['rango']]);

            // ===== 4) items
            if (!empty($d['items'])) {
                $sqlI = "INSERT INTO drones_solicitud_item
                    (solicitud_id, patologia_id, fuente, producto_id, costo_hectarea_snapshot, total_producto_snapshot, nombre_producto)
                    VALUES (?,?,?,?,?,?,?)";
                $sti = $this->pdo->prepare($sqlI);

                foreach ($d['items'] as $it) {
                    $pid    = isset($it['producto_id']) ? (int)$it['producto_id'] : 0;
                    $fuente = (string)$it['fuente'];
                    $custom = isset($it['nombre_producto_custom']) ? trim((string)$it['nombre_producto_custom']) : '';

                    if ($fuente === 'productor') {
                        $nombre        = $custom !== '' ? mb_substr($custom, 0, 150) : $this->productoNombre($pid);
                        $costoHa       = 0.00;
                        $totalSnap     = 0.00;
                        $productoIdDb  = $pid > 0 ? $pid : null; // NULL para custom
                    } else { // 'sve'
                        $nombre        = $this->productoNombre($pid);
                        $costoHa       = $this->productoCostoHa($pid);
                        $totalSnap     = (float)$d['superficie_ha'] * (float)$costoHa;
                        $productoIdDb  = $pid;
                    }

                    $sti->execute([
                        $solicitudId,
                        $d['patologia_id'],
                        $fuente,
                        $productoIdDb,
                        $costoHa,
                        $totalSnap,
                        $nombre
                    ]);
                }
            }

            // ===== 5) costos (SIEMPRE)
            // costo/ha vigente
            $stCh = $this->pdo->query("SELECT costo, COALESCE(moneda,'Pesos') AS moneda
                                         FROM dron_costo_hectarea
                                     ORDER BY updated_at DESC
                                        LIMIT 1");
            $rowCh = $stCh->fetch(PDO::FETCH_ASSOC) ?: ['costo' => 0.0, 'moneda' => 'Pesos'];
            $costoHa = (float)$rowCh['costo'];
            $moneda  = (string)$rowCh['moneda'];

            // suma de productos (solo SVE)
            $stSum = $this->pdo->prepare("SELECT COALESCE(SUM(total_producto_snapshot),0)
                                            FROM drones_solicitud_item
                                           WHERE solicitud_id = ? AND fuente = 'sve'");
            $stSum->execute([$solicitudId]);
            $productosTotal = (float)$stSum->fetchColumn();

            $baseHa     = (float)$d['superficie_ha'];
            $baseTotal  = (float)$baseHa * (float)$costoHa;
            $totalFinal = $baseTotal + $productosTotal;

            $stCost = $this->pdo->prepare("
                INSERT INTO drones_solicitud_costos
                    (solicitud_id, moneda, costo_base_por_ha, base_ha, base_total, productos_total, total, desglose_json)
                VALUES
                    (:sid, :moneda, :costo_ha, :base_ha, :base_total, :prod_total, :total, NULL)
            ");
            $stCost->execute([
                ':sid'        => $solicitudId,
                ':moneda'     => $moneda ?: 'Pesos',
                ':costo_ha'   => $costoHa,
                ':base_ha'    => $baseHa,
                ':base_total' => $baseTotal,
                ':prod_total' => $productosTotal,
                ':total'      => $totalFinal,
            ]);

            // ===== 6) evento
            $sqlE = "INSERT INTO drones_solicitud_evento (solicitud_id, tipo, detalle, actor)
                     VALUES (?, 'creada', 'Solicitud ingresada por formulario', 'sistema')";
            $ste = $this->pdo->prepare($sqlE);
            $ste->execute([$solicitudId]);

            $this->pdo->commit();
            return ['ok' => true, 'id' => $solicitudId];
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /** Recalcula y actualiza costos para una solicitud existente */
    public function recalcularCostos(int $solicitudId): void
    {
        if ($solicitudId <= 0) return;

        $stS = $this->pdo->prepare("SELECT superficie_ha FROM drones_solicitud WHERE id=?");
        $stS->execute([$solicitudId]);
        $sup = (float)($stS->fetchColumn() ?: 0);

        $stCh = $this->pdo->query("SELECT costo, COALESCE(moneda,'Pesos') AS moneda
                                     FROM dron_costo_hectarea
                                 ORDER BY updated_at DESC
                                    LIMIT 1");
        $rowCh = $stCh->fetch(PDO::FETCH_ASSOC) ?: ['costo' => 0.0, 'moneda' => 'Pesos'];
        $costoHa = (float)$rowCh['costo'];
        $moneda  = (string)$rowCh['moneda'];

        $stSum = $this->pdo->prepare("SELECT COALESCE(SUM(total_producto_snapshot),0)
                                        FROM drones_solicitud_item
                                       WHERE solicitud_id = ? AND fuente = 'sve'");
        $stSum->execute([$solicitudId]);
        $productosTotal = (float)$stSum->fetchColumn();

        $baseTotal  = $sup * $costoHa;
        $totalFinal = $baseTotal + $productosTotal;

        $stU = $this->pdo->prepare("
            UPDATE drones_solicitud_costos
               SET moneda = :moneda,
                   costo_base_por_ha = :costo_ha,
                   base_ha = :base_ha,
                   base_total = :base_total,
                   productos_total = :prod_total,
                   total = :total
             WHERE solicitud_id = :sid
        ");
        $stU->execute([
            ':moneda'     => $moneda,
            ':costo_ha'   => $costoHa,
            ':base_ha'    => $sup,
            ':base_total' => $baseTotal,
            ':prod_total' => $productosTotal,
            ':total'      => $totalFinal,
            ':sid'        => $solicitudId,
        ]);
    }



    // === Helpers ===
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
        $n = $st->fetchColumn();
        return $n ? (string)$n : '';
    }

        /** Devuelve email del usuario por id_real (tolera diferentes nombres de campo) */
    public function emailByIdReal(string $idReal): ?string
    {
        if ($idReal === '') return null;
        $sql = "SELECT COALESCE(NULLIF(TRIM(email),''), NULLIF(TRIM(correo),''), NULLIF(TRIM(mail),'')) AS email
                  FROM usuarios
                 WHERE id_real = ?
                 LIMIT 1";
        $st = $this->pdo->prepare($sql);
        $st->execute([$idReal]);
        $mail = $st->fetchColumn();
        return $mail ? (string)$mail : null;
    }

    /** Obtiene el nombre visible del usuario (para el HTML del correo) */
    public function nombreByIdReal(string $idReal): ?string
    {
        if ($idReal === '') return null;
        $sql = "SELECT usuario FROM usuarios WHERE id_real = ? LIMIT 1";
        $st  = $this->pdo->prepare($sql);
        $st->execute([$idReal]);
        $v = $st->fetchColumn();
        return $v ? (string)$v : null;
    }

        /**
     * Devuelve el correo desde usuarios_info.correo por id_real de usuarios.
     * Si no existe o está vacío, retorna null.
     */
    public function correoInfoByIdReal(string $idReal): ?string
    {
        if ($idReal === '') {
            return null;
        }

        // Obtenemos el id interno y luego el correo en usuarios_info
        $sql = "SELECT ui.correo
                  FROM usuarios u
             LEFT JOIN usuarios_info ui ON ui.usuario_id = u.id
                 WHERE u.id_real = ?
                 LIMIT 1";
        $st = $this->pdo->prepare($sql);
        $st->execute([$idReal]);
        $correo = $st->fetchColumn();

        if ($correo === false) {
            return null;
        }

        $correo = trim((string)$correo);
        return ($correo !== '') ? $correo : null;
    }

}
