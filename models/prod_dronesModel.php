<?php
class prodDronesModel
{
    private PDO $pdo;

    // Whitelists
    private array $yesNo   = ['si', 'no'];
    // Mantengo los mismos códigos de quincenas que usa el front
    private array $rangos  = ['enero_q1', 'enero_q2', 'febrero_q1', 'febrero_q2', 'octubre_q1', 'octubre_q2', 'noviembre_q1', 'noviembre_q2', 'diciembre_q1', 'diciembre_q2'];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /* =======================
     * Catálogos (sin cambios)
     * ======================= */
    public function getPatologiasActivas(): array
    {
        $sql = "SELECT id, nombre FROM dron_patologias WHERE activo='si' ORDER BY nombre ASC";
        return $this->pdo->query($sql)->fetchAll() ?: [];
    }

    public function getProductosPorPatologia(int $patologiaId): array
    {
        $sql = "SELECT p.id, p.nombre, p.costo_hectarea
                  FROM dron_productos_stock p
                  JOIN dron_productos_stock_patologias sp ON sp.producto_id = p.id
                 WHERE sp.patologia_id = :pid
                   AND p.activo = 'si'
              ORDER BY p.nombre ASC";
        $st = $this->pdo->prepare($sql);
        $st->execute([':pid' => $patologiaId]);
        return $st->fetchAll() ?: [];
    }

    public function getFormasPagoActivas(): array
    {
        $sql = "SELECT id, nombre, COALESCE(descripcion,'') AS descripcion
                  FROM dron_formas_pago
                 WHERE activo='si'
              ORDER BY nombre ASC";
        return $this->pdo->query($sql)->fetchAll() ?: [];
    }

    public function getCooperativasHabilitadas(): array
    {
        $sql = "SELECT usuario, id_real
                  FROM usuarios
                 WHERE rol='cooperativa'
                   AND permiso_ingreso='Habilitado'
              ORDER BY usuario ASC";
        return $this->pdo->query($sql)->fetchAll() ?: [];
    }

    public function getCostoHectarea(): array
    {
        $sql = "SELECT costo, COALESCE(moneda,'Pesos') AS moneda
                  FROM dron_costo_hectarea
              ORDER BY updated_at DESC
                 LIMIT 1";
        $row = $this->pdo->query($sql)->fetch() ?: null;
        return $row ?: ['costo' => 0.00, 'moneda' => 'Pesos'];
    }

    /* =======================
     *   Crear Solicitud (NEW)
     * ======================= */
    public function crearSolicitud(array $data, array $session): int
    {
        // --- Validaciones base
        $productorIdReal = $session['id_real'] ?? null;
        if (!$productorIdReal) {
            throw new RuntimeException('Sesión inválida (id_real ausente).');
        }

        $nn = fn($v) => $v === '' ? null : $v;
        $siNo = fn($v) => in_array(strtolower((string)$v), $this->yesNo, true) ? strtolower($v) : null;
        $isoToMysql = function ($iso) {
            if (!$iso) return null;
            try {
                $dt = new DateTime($iso);
                return $dt->format('Y-m-d H:i:s');
            } catch (Throwable $e) {
                return null;
            }
        };

        // 1) Flags operativos + superficie
        $main = [
            'representante'       => $siNo($data['representante'] ?? null),
            'linea_tension'       => $siNo($data['linea_tension'] ?? null),
            'zona_restringida'    => $siNo($data['zona_restringida'] ?? null),
            'corriente_electrica' => $siNo($data['corriente_electrica'] ?? null),
            'agua_potable'        => $siNo($data['agua_potable'] ?? null),
            'libre_obstaculos'    => $siNo($data['libre_obstaculos'] ?? null),
            'area_despegue'       => $siNo($data['area_despegue'] ?? null),
            'superficie_ha'       => isset($data['superficie_ha']) ? (float)$data['superficie_ha'] : 0,
        ];
        if ($main['superficie_ha'] <= 0 || $main['superficie_ha'] > 20) {
            throw new InvalidArgumentException('La superficie debe ser un número mayor a 0 y menor o igual a 20.');
        }
        foreach ($main as $k => $v) {
            if ($v === null) throw new InvalidArgumentException("Campo requerido faltante: {$k}");
        }

        // 2) Dirección / ubicación
        $dir  = $data['direccion'] ?? [];
        $ubic = $data['ubicacion'] ?? [];
        $enFinca = $siNo($ubic['en_finca'] ?? null) ?? 'no';
        if ($enFinca === 'no') {
            foreach (['provincia', 'localidad', 'calle', 'numero'] as $req) {
                if (empty($dir[$req])) throw new InvalidArgumentException("Dirección incompleta: falta {$req}.");
            }
        }

        // 3) Forma de pago + regla de cooperativa (id=6)
        $formaPagoId = isset($data['forma_pago_id']) ? (int)$data['forma_pago_id'] : 0;
        if ($formaPagoId <= 0) {
            throw new InvalidArgumentException('Debe seleccionar un método de pago.');
        }
        $chkFp = $this->pdo->prepare("SELECT 1 FROM dron_formas_pago WHERE id=? AND activo='si'");
        $chkFp->execute([$formaPagoId]);
        if (!$chkFp->fetchColumn()) {
            throw new InvalidArgumentException('Método de pago inválido o inactivo.');
        }

        $coopDesc = trim((string)($data['coop_descuento_nombre'] ?? ''));
        if ($formaPagoId === 6) {
            if ($coopDesc === '') {
                throw new InvalidArgumentException('Debe seleccionar una cooperativa.');
            }
            $chkCoop = $this->pdo->prepare("SELECT 1 FROM usuarios WHERE rol='cooperativa' AND permiso_ingreso='Habilitado' AND id_real=?");
            $chkCoop->execute([$coopDesc]);
            if (!$chkCoop->fetchColumn()) {
                throw new InvalidArgumentException('Cooperativa inválida o no habilitada.');
            }
        }

        // 4) Motivos / rangos
        $rangos = (array)($data['rango_fecha'] ?? []);
        $motivo = $data['motivo'] ?? [];
        $opc    = (array)($motivo['opciones'] ?? []);
        $otrosT = $nn($motivo['otros'] ?? null);

        // 5) Productos (por patología) - acepto 'sve' o 'yo' desde el front,
        //    pero PERSISTO 'sve' | 'productor' en DB (tu cambio).
        $prods = (array)($data['productos'] ?? []);

        // 6) Observaciones (+ regla de nota si pago=6)
        $obsUser  = $nn($data['observaciones'] ?? null);
        if ($formaPagoId === 6 && $coopDesc !== '') {
            $obsUser = trim("Cooperativa (cuota de vino): {$coopDesc}" . ($obsUser ? " | {$obsUser}" : ''));
        }

        // 7) Snapshot de sesión
        $ses = [
            'ses_usuario'          => $nn($session['usuario']   ?? null),
            'ses_rol'              => $nn($session['rol']       ?? null),
            'ses_nombre'           => $nn($session['nombre']    ?? null),
            'ses_correo'           => $nn($session['correo']    ?? null),
            'ses_telefono'         => $nn($session['telefono']  ?? null),
            'ses_direccion'        => $nn($session['direccion'] ?? null),
            'ses_cuit'             => $session['cuit'] ?? null,
            'ses_last_activity_ts' => isset($session['LAST_ACTIVITY']) ? date('Y-m-d H:i:s', (int)$session['LAST_ACTIVITY']) : null,
        ];

        // ========================
        // PERSISTENCIA (TX)
        // ========================
        $this->pdo->beginTransaction();
        try {
            // A) Cabecera
            $sql = "INSERT INTO drones_solicitud
              (productor_id_real,representante,linea_tension,zona_restringida,corriente_electrica,agua_potable,libre_obstaculos,area_despegue,
               superficie_ha,forma_pago_id,coop_descuento_nombre,dir_provincia,dir_localidad,dir_calle,dir_numero,
               en_finca,ubicacion_lat,ubicacion_lng,ubicacion_acc,ubicacion_ts,observaciones,
               ses_usuario,ses_rol,ses_nombre,ses_correo,ses_telefono,ses_direccion,ses_cuit,ses_last_activity_ts)
            VALUES
              (:productor_id_real,:representante,:linea_tension,:zona_restringida,:corriente_electrica,:agua_potable,:libre_obstaculos,:area_despegue,
               :superficie_ha,:forma_pago_id,:coop_descuento_nombre,:dir_provincia,:dir_localidad,:dir_calle,:dir_numero,
               :en_finca,:ubicacion_lat,:ubicacion_lng,:ubicacion_acc,:ubicacion_ts,:observaciones,
               :ses_usuario,:ses_rol,:ses_nombre,:ses_correo,:ses_telefono,:ses_direccion,:ses_cuit,:ses_last_activity_ts)";
            $st = $this->pdo->prepare($sql);
            $st->execute([
                'productor_id_real'     => $productorIdReal,
                'representante'         => $main['representante'],
                'linea_tension'         => $main['linea_tension'],
                'zona_restringida'      => $main['zona_restringida'],
                'corriente_electrica'   => $main['corriente_electrica'],
                'agua_potable'          => $main['agua_potable'],
                'libre_obstaculos'      => $main['libre_obstaculos'],
                'area_despegue'         => $main['area_despegue'],
                'superficie_ha'         => $main['superficie_ha'],
                'forma_pago_id'         => $formaPagoId,
                'coop_descuento_nombre' => ($formaPagoId === 6 ? $coopDesc : null),
                'dir_provincia'         => $nn($dir['provincia'] ?? null),
                'dir_localidad'         => $nn($dir['localidad'] ?? null),
                'dir_calle'             => $nn($dir['calle'] ?? null),
                'dir_numero'            => $nn($dir['numero'] ?? null),
                'en_finca'              => $enFinca,
                'ubicacion_lat'         => isset($ubic['lat']) ? (float)$ubic['lat'] : null,
                'ubicacion_lng'         => isset($ubic['lng']) ? (float)$ubic['lng'] : null,
                'ubicacion_acc'         => isset($ubic['acc']) ? (float)$ubic['acc'] : null,
                'ubicacion_ts'          => $isoToMysql($ubic['timestamp'] ?? null),
                'observaciones'         => $obsUser,
                'ses_usuario'           => $ses['ses_usuario'],
                'ses_rol'               => $ses['ses_rol'],
                'ses_nombre'            => $ses['ses_nombre'],
                'ses_correo'            => $ses['ses_correo'],
                'ses_telefono'          => $ses['ses_telefono'],
                'ses_direccion'         => $ses['ses_direccion'],
                'ses_cuit'              => $ses['ses_cuit'],
                'ses_last_activity_ts'  => $ses['ses_last_activity_ts'],
            ]);
            $solicitudId = (int)$this->pdo->lastInsertId();

            // B) Rangos
            if ($rangos) {
                $stR = $this->pdo->prepare(
                    "INSERT INTO drones_solicitud_rango (solicitud_id, rango) VALUES (?, ?)"
                );
                foreach ($rangos as $r) {
                    if (in_array($r, $this->rangos, true)) $stR->execute([$solicitudId, $r]);
                }
            }

            // C) Motivos
            if ($opc) {
                $stM = $this->pdo->prepare(
                    "INSERT INTO drones_solicitud_motivo (solicitud_id, patologia_id, es_otros, otros_text)
                     VALUES (?, ?, ?, ?)"
                );
                foreach ($opc as $m) {
                    if ($m === 'otros') {
                        $stM->execute([$solicitudId, null, 1, $otrosT]);
                        continue;
                    }
                    $pid = (int)$m;
                    // validar patología
                    $chk = $this->pdo->prepare("SELECT 1 FROM dron_patologias WHERE id=?");
                    $chk->execute([$pid]);
                    if ($chk->fetchColumn()) $stM->execute([$solicitudId, $pid, 0, null]);
                }
            }

            // D) Ítems por patología (fuente + snapshots)
            $validItems = [];
            if ($prods) {
                $stItem = $this->pdo->prepare(
                    "INSERT INTO drones_solicitud_item
                     (solicitud_id, patologia_id, fuente, producto_id, costo_hectarea_snapshot, total_producto_snapshot, nombre_producto)
                     VALUES (?, ?, ?, ?, ?, ?, ?)"
                );

                foreach ($prods as $p) {
                    $pid = isset($p['patologia_id']) ? (int)$p['patologia_id'] : 0;
                    if ($pid <= 0) continue;

                    // validar patología existe
                    $chkPat = $this->pdo->prepare("SELECT 1 FROM dron_patologias WHERE id=?");
                    $chkPat->execute([$pid]);
                    if (!$chkPat->fetchColumn()) continue;

                    $fuenteFront = strtolower((string)($p['fuente'] ?? ''));
                    // Normalizo: 'yo' -> 'productor'
                    $fuente = $fuenteFront === 'yo' ? 'productor' : ($fuenteFront === 'sve' ? 'sve' : null);
                    if (!$fuente) continue;

                    if ($fuente === 'sve') {
                        $productoId = isset($p['producto_id']) ? (int)$p['producto_id'] : 0;
                        if ($productoId <= 0) {
                            // ítem inválido
                            continue;
                        }

                        // snapshot de costo actual del producto
                        $q = $this->pdo->prepare("SELECT costo_hectarea FROM dron_productos_stock WHERE id=? AND activo='si'");
                        $q->execute([$productoId]);
                        $costoHa = $q->fetchColumn();
                        if ($costoHa === false) {
                            // producto inexistente/inactivo -> descartar
                            continue;
                        }
                        $costoHa = (float)$costoHa;
                        $sup     = (float)$main['superficie_ha'];
                        $totalProd = $sup * $costoHa;

                        $stItem->execute([$solicitudId, $pid, $fuente, $productoId, $costoHa, $totalProd, null]);

                        $validItems[] = [
                            'patologia_id' => $pid,
                            'fuente'       => 'sve',
                            'producto_id'  => $productoId,
                            'costo_ha'     => $costoHa
                        ];
                    } else { // productor
                        $nombre = $nn($p['marca'] ?? $p['producto_nombre'] ?? null); // el front hoy manda "marca"
                        if (!$nombre) {
                            // si el productor no escribió un nombre, descarto
                            continue;
                        }
                        $stItem->execute([$solicitudId, $pid, 'productor', null, null, null, $nombre]);

                        $validItems[] = [
                            'patologia_id'   => $pid,
                            'fuente'         => 'productor',
                            'nombre_producto'=> $nombre
                        ];
                    }
                }
            }

            // E) Costeo consolidado (base + productos SVE)
            $costoRow     = $this->getCostoHectarea();
            $costoBaseHa  = (float)($costoRow['costo'] ?? 0);
            $moneda       = $costoRow['moneda'] ?? 'Pesos';
            $sup          = (float)$main['superficie_ha'];
            $baseTotal    = $sup * $costoBaseHa;

            $productosTotal = 0.0;
            foreach ($validItems as $it) {
                if ($it['fuente'] === 'sve' && !empty($it['producto_id'])) {
                    $productosTotal += $sup * (float)$it['costo_ha'];
                }
            }
            $total    = $baseTotal + $productosTotal;

            $desglose = [
                'superficie_ha'    => $sup,
                'costo_base_ha'    => $costoBaseHa,
                'productos'        => $validItems,
            ];

            $stC = $this->pdo->prepare(
                "INSERT INTO drones_solicitud_costos
                 (solicitud_id,moneda,costo_base_por_ha,base_ha,base_total,productos_total,total,desglose_json)
                 VALUES (:sid,:moneda,:costo_base_por_ha,:base_ha,:base_total,:productos_total,:total,:desglose_json)"
            );
            $stC->execute([
                ':sid'              => $solicitudId,
                ':moneda'           => $moneda,
                ':costo_base_por_ha'=> $costoBaseHa,
                ':base_ha'          => $sup,
                ':base_total'       => $baseTotal,
                ':productos_total'  => $productosTotal,
                ':total'            => $total,
                ':desglose_json'    => json_encode($desglose, JSON_UNESCAPED_UNICODE),
            ]);

            $this->pdo->commit();
            return $solicitudId;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
