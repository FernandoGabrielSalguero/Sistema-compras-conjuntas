<?php
class prodDronesModel
{
    private $pdo;

    // Whitelists
    private array $yesNo = ['si', 'no'];
    private array $rangos = ['enero_q1', 'enero_q2', 'febrero_q1', 'febrero_q2', 'octubre_q1', 'octubre_q2', 'noviembre_q1', 'noviembre_q2', 'diciembre_q1', 'diciembre_q2'];
    private array $fuentes = ['sve', 'yo'];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        // Por si tu config no lo setea
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

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
        $sql = "SELECT id, nombre FROM dron_formas_pago WHERE activo = 'si' ORDER BY nombre ASC";
        return $this->pdo->query($sql)->fetchAll() ?: [];
    }

    public function getCostoHectarea(): array
    {
        $sql = "SELECT costo, COALESCE(moneda, 'Pesos') AS moneda
            FROM dron_costo_hectarea
            ORDER BY updated_at DESC
            LIMIT 1";
        $row = $this->pdo->query($sql)->fetch() ?: null;
        return $row ?: ['costo' => 0.00, 'moneda' => 'Pesos'];
    }


    public function crearSolicitud(array $data, array $session): int
    {
        // Seguridad: ID real SIEMPRE desde la sesión del servidor
        $productorIdReal = $session['id_real'] ?? null;
        if (!$productorIdReal) {
            throw new RuntimeException('Sesión inválida (id_real ausente).');
        }

        // ------- Normalización de datos
        $nn = fn($v) => $v === '' ? null : $v;

        $siNo = function ($v) {
            $v = strtolower((string)$v);
            return in_array($v, $this->yesNo, true) ? $v : null;
        };

        $isoToMysql = function ($iso) {
            if (!$iso) return null;
            try {
                $dt = new DateTime($iso);
                return $dt->format('Y-m-d H:i:s');
            } catch (Throwable $e) {
                return null;
            }
        };

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

        $dir = $data['direccion'] ?? [];
        $ubic = $data['ubicacion'] ?? [];

        // Método de pago (obligatorio y activo)
        $formaPagoId = isset($data['forma_pago_id']) ? (int)$data['forma_pago_id'] : 0;
        if ($formaPagoId <= 0) {
            throw new InvalidArgumentException('Debe seleccionar un método de pago.');
        }
        $chkFp = $this->pdo->prepare("SELECT 1 FROM dron_formas_pago WHERE id = ? AND activo = 'si'");
        $chkFp->execute([$formaPagoId]);
        if (!$chkFp->fetchColumn()) {
            throw new InvalidArgumentException('Método de pago inválido o inactivo.');
        }

        $enFinca = $siNo($ubic['en_finca'] ?? null) ?? 'no';
        // Si no está en finca, dirección obligatoria completa
        if ($enFinca === 'no') {
            foreach (['provincia', 'localidad', 'calle', 'numero'] as $req) {
                if (empty($dir[$req])) {
                    throw new InvalidArgumentException("Dirección incompleta: falta {$req}.");
                }
            }
        }

        $mainRow = [
            'productor_id_real'   => $productorIdReal,
            'representante'       => $main['representante'],
            'linea_tension'       => $main['linea_tension'],
            'zona_restringida'    => $main['zona_restringida'],
            'corriente_electrica' => $main['corriente_electrica'],
            'agua_potable'        => $main['agua_potable'],
            'libre_obstaculos'    => $main['libre_obstaculos'],
            'area_despegue'       => $main['area_despegue'],
            'superficie_ha'       => $main['superficie_ha'],
            'forma_pago_id'       => $formaPagoId,
            'dir_provincia'       => $nn($dir['provincia'] ?? null),
            'dir_localidad'       => $nn($dir['localidad'] ?? null),
            'dir_calle'           => $nn($dir['calle'] ?? null),
            'dir_numero'          => $nn($dir['numero'] ?? null),
            'en_finca'            => $enFinca,
            'ubicacion_lat'       => isset($ubic['lat']) ? (float)$ubic['lat'] : null,
            'ubicacion_lng'       => isset($ubic['lng']) ? (float)$ubic['lng'] : null,
            'ubicacion_acc'       => isset($ubic['acc']) ? (float)$ubic['acc'] : null,
            'ubicacion_ts'        => $isoToMysql($ubic['timestamp'] ?? null),
            'observaciones'       => $nn($data['observaciones'] ?? null),
            // Snapshot de la sesión (desde servidor, no del JSON)
            'ses_usuario'         => $nn($session['usuario']   ?? null),
            'ses_rol'             => $nn($session['rol']       ?? null),
            'ses_nombre'          => $nn($session['nombre']    ?? null),
            'ses_correo'          => $nn($session['correo']    ?? null),
            'ses_telefono'        => $nn($session['telefono']  ?? null),
            'ses_direccion'       => $nn($session['direccion'] ?? null),
            'ses_cuit'            => $session['cuit'] ?? null,
            'ses_last_activity_ts' => isset($session['LAST_ACTIVITY']) ? date('Y-m-d H:i:s', (int)$session['LAST_ACTIVITY']) : null,
        ];


        $this->pdo->beginTransaction();
        try {
            // 1) Inserto cabecera
            $sql = "INSERT INTO dron_solicitudes
                (productor_id_real, representante, linea_tension, zona_restringida, corriente_electrica, agua_potable, libre_obstaculos, area_despegue,
                 superficie_ha, forma_pago_id, dir_provincia, dir_localidad, dir_calle, dir_numero, en_finca,
                 ubicacion_lat, ubicacion_lng, ubicacion_acc, ubicacion_ts, observaciones,
                 ses_usuario, ses_rol, ses_nombre, ses_correo, ses_telefono, ses_direccion, ses_cuit, ses_last_activity_ts)
                VALUES
                (:productor_id_real, :representante, :linea_tension, :zona_restringida, :corriente_electrica, :agua_potable, :libre_obstaculos, :area_despegue,
                 :superficie_ha, :forma_pago_id, :dir_provincia, :dir_localidad, :dir_calle, :dir_numero, :en_finca,
                 :ubicacion_lat, :ubicacion_lng, :ubicacion_acc, :ubicacion_ts, :observaciones,
                 :ses_usuario, :ses_rol, :ses_nombre, :ses_correo, :ses_telefono, :ses_direccion, :ses_cuit, :ses_last_activity_ts)";

            $st = $this->pdo->prepare($sql);
            $st->execute($mainRow);
            $solicitudId = (int)$this->pdo->lastInsertId();

            // 2) Rangos
            $rangos = (array)($data['rango_fecha'] ?? []);
            if ($rangos) {
                $stR = $this->pdo->prepare("INSERT INTO dron_solicitudes_rangos (solicitud_id, rango) VALUES (?, ?)");
                foreach ($rangos as $r) {
                    if (in_array($r, $this->rangos, true)) {
                        $stR->execute([$solicitudId, $r]);
                    }
                }
            }
            // 3) Motivos (dinámico)
            $motivo = $data['motivo'] ?? [];
            $opc    = (array)($motivo['opciones'] ?? []);
            $otrosT = $nn($motivo['otros'] ?? null);

            if ($opc) {
                $stM = $this->pdo->prepare("INSERT INTO dron_solicitudes_motivos (solicitud_id, patologia_id, motivo, otros_text) VALUES (?, ?, ?, ?)");
                foreach ($opc as $m) {
                    if ($m === 'otros') {
                        $stM->execute([$solicitudId, null, 'otros', $otrosT]);
                        continue;
                    }
                    $pid = (int)$m;
                    // validar que exista patología
                    $chk = $this->pdo->prepare("SELECT 1 FROM dron_patologias WHERE id = ?");
                    $chk->execute([$pid]);
                    if ($chk->fetchColumn()) {
                        $stM->execute([$solicitudId, $pid, null, null]);
                    }
                }
            }

            // 4) Productos (dinámico por patología)
            $prods = (array)($data['productos'] ?? []);
            if ($prods) {
                $stP = $this->pdo->prepare("INSERT INTO dron_solicitudes_productos (solicitud_id, patologia_id, producto_id, fuente, marca) VALUES (?, ?, ?, ?, ?)");
                foreach ($prods as $p) {
                    $pid    = isset($p['patologia_id']) ? (int)$p['patologia_id'] : 0;
                    $fuente = $p['fuente'] ?? null;
                    $marca  = $nn($p['marca'] ?? null);
                    $prodId = isset($p['producto_id']) ? (int)$p['producto_id'] : null;

                    if ($pid <= 0) continue;
                    if (!in_array($fuente, $this->fuentes, true)) continue;

                    // validar patología
                    $chk = $this->pdo->prepare("SELECT 1 FROM dron_patologias WHERE id = ?");
                    $chk->execute([$pid]);
                    if (!$chk->fetchColumn()) continue;

                    if ($fuente === 'sve') {
                        if (!$prodId) continue;
                        // validar que el producto esté asociado a esa patología
                        $vp = $this->pdo->prepare("SELECT 1 FROM dron_productos_stock_patologias WHERE producto_id = ? AND patologia_id = ?");
                        $vp->execute([$prodId, $pid]);
                        if (!$vp->fetchColumn()) continue;
                        $stP->execute([$solicitudId, $pid, $prodId, 'sve', null]);
                    } else { // 'yo'
                        if (!$marca) continue; // exigir marca cuando es propio
                        $stP->execute([$solicitudId, $pid, null, 'yo', $marca]);
                    }
                }
            }

 // 5) Guardar costos estimados en dron_solicitudes_costos
            $costoRow = $this->getCostoHectarea();
            $costoBaseHa = (float)($costoRow['costo'] ?? 0);
            $moneda      = $costoRow['moneda'] ?? 'Pesos';

            $sup = $mainRow['superficie_ha'];
            $baseTotal = $sup * $costoBaseHa;

            // Calcular productos_total desde payload (solo fuente sve)
            $productosTotal = 0.0;
            foreach ($prods as $p) {
                if (($p['fuente'] ?? '') === 'sve' && !empty($p['producto_id'])) {
                    // buscar costo_hectarea actual
                    $chk = $this->pdo->prepare("SELECT costo_hectarea FROM dron_productos_stock WHERE id=? AND activo='si'");
                    $chk->execute([(int)$p['producto_id']]);
                    $costoHa = (float)$chk->fetchColumn();
                    $productosTotal += $sup * $costoHa;
                }
            }
            $total = $baseTotal + $productosTotal;

            $desglose = [
                'superficie_ha' => $sup,
                'costo_base_ha' => $costoBaseHa,
                'productos'     => $prods,
            ];

            $stC = $this->pdo->prepare("INSERT INTO dron_solicitudes_costos
                (solicitud_id, moneda, costo_base_por_ha, base_ha, base_total, productos_total, total, desglose_json)
                VALUES (:sid, :moneda, :costo_base_por_ha, :base_ha, :base_total, :productos_total, :total, :desglose_json)");
            $stC->execute([
                ':sid'             => $solicitudId,
                ':moneda'          => $moneda,
                ':costo_base_por_ha' => $costoBaseHa,
                ':base_ha'         => $sup,
                ':base_total'      => $baseTotal,
                ':productos_total' => $productosTotal,
                ':total'           => $total,
                ':desglose_json'   => json_encode($desglose, JSON_UNESCAPED_UNICODE),
            ]);

            $this->pdo->commit();
            return $solicitudId;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
