<?php
class prodDronesModel
{
    private $pdo;

    // Enums/whitelists (de tu schema)
    private array $yesNo = ['si','no'];
    private array $motivos = ['mildiu','oidio','lobesia','podredumbre','fertilizacion','otros'];
    private array $rangos = ['enero_q1','enero_q2','febrero_q1','febrero_q2','octubre_q1','octubre_q2','noviembre_q1','noviembre_q2','diciembre_q1','diciembre_q2'];
    private array $prodTipos = ['lobesia','peronospora','oidio','podredumbre'];
    private array $fuentes = ['sve','yo'];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        // Por si tu config no lo setea
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
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

        $siNo = function($v) {
            $v = strtolower((string)$v);
            return in_array($v, $this->yesNo, true) ? $v : null;
        };

        $isoToMysql = function($iso) {
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
        if ($main['superficie_ha'] <= 0) {
            throw new InvalidArgumentException('La superficie debe ser mayor a 0.');
        }
        foreach ($main as $k => $v) {
            if ($v === null) throw new InvalidArgumentException("Campo requerido faltante: {$k}");
        }

        $dir = $data['direccion'] ?? [];
        $ubic = $data['ubicacion'] ?? [];

        $enFinca = $siNo($ubic['en_finca'] ?? null) ?? 'no';
        // Si no está en finca, dirección obligatoria completa
        if ($enFinca === 'no') {
            foreach (['provincia','localidad','calle','numero'] as $req) {
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
            'ses_last_activity_ts'=> isset($session['LAST_ACTIVITY']) ? date('Y-m-d H:i:s', (int)$session['LAST_ACTIVITY']) : null,
        ];

        $this->pdo->beginTransaction();
        try {
            // 1) Inserto cabecera
            $sql = "INSERT INTO dron_solicitudes
                (productor_id_real, representante, linea_tension, zona_restringida, corriente_electrica, agua_potable, libre_obstaculos, area_despegue,
                 superficie_ha, dir_provincia, dir_localidad, dir_calle, dir_numero, en_finca,
                 ubicacion_lat, ubicacion_lng, ubicacion_acc, ubicacion_ts, observaciones,
                 ses_usuario, ses_rol, ses_nombre, ses_correo, ses_telefono, ses_direccion, ses_cuit, ses_last_activity_ts)
                VALUES
                (:productor_id_real, :representante, :linea_tension, :zona_restringida, :corriente_electrica, :agua_potable, :libre_obstaculos, :area_despegue,
                 :superficie_ha, :dir_provincia, :dir_localidad, :dir_calle, :dir_numero, :en_finca,
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

            // 3) Motivos
            $motivo = $data['motivo'] ?? [];
            $opc    = (array)($motivo['opciones'] ?? []);
            $otrosT = $nn($motivo['otros'] ?? null);

            if ($opc) {
                $stM = $this->pdo->prepare("INSERT INTO dron_solicitudes_motivos (solicitud_id, motivo, otros_text) VALUES (?, ?, ?)");
                foreach ($opc as $m) {
                    if (!in_array($m, $this->motivos, true)) continue;
                    $ot = ($m === 'otros') ? $otrosT : null;
                    $stM->execute([$solicitudId, $m, $ot]);
                }
            }

            // 4) Productos
            $prods = (array)($data['productos'] ?? []);
            if ($prods) {
                $stP = $this->pdo->prepare("INSERT INTO dron_solicitudes_productos (solicitud_id, tipo, fuente, marca) VALUES (?, ?, ?, ?)");
                foreach ($prods as $p) {
                    $tipo   = $p['tipo']   ?? null;
                    $fuente = $p['fuente'] ?? null;
                    $marca  = $nn($p['marca'] ?? null);
                    if (!in_array($tipo, $this->prodTipos, true)) continue;
                    if (!in_array($fuente, $this->fuentes,  true)) continue;
                    // si fuente = 'yo' y no hay marca, igual permitimos NULL (tu schema lo permite)
                    $stP->execute([$solicitudId, $tipo, $fuente, $marca]);
                }
            }

            $this->pdo->commit();
            return $solicitudId;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
