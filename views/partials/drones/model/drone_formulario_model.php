<?php
declare(strict_types=1);

final class DroneFormularioModel
{
    /** @var PDO */
    public PDO $pdo;

    /** Obtiene costo base por ha. */
    public function getCostoBase(): array
    {
        $sql = "SELECT costo, COALESCE(moneda,'Pesos') AS moneda FROM dron_costo_hectarea WHERE id = 1 LIMIT 1";
        $st = $this->pdo->query($sql);
        $row = $st ? $st->fetch(PDO::FETCH_ASSOC) : null;
        return $row ?: ['costo'=>0.00, 'moneda'=>'Pesos'];
    }

    /** Formas de pago activas */
    public function getFormasPago(): array
    {
        $st = $this->pdo->prepare("SELECT id, nombre, COALESCE(descripcion,'') AS descripcion FROM dron_formas_pago WHERE activo='si' ORDER BY nombre");
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Patologías activas */
    public function getPatologias(): array
    {
        $st = $this->pdo->prepare("SELECT id, nombre FROM dron_patologias WHERE activo='si' ORDER BY nombre");
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Productos por patología (join tabla pivote) */
    public function getProductosByPatologia(int $patologiaId): array
    {
        $sql = "SELECT p.id, p.nombre, p.costo_hectarea
                FROM dron_productos_stock p
                INNER JOIN dron_productos_stock_patologias sp ON sp.producto_id = p.id
                WHERE sp.patologia_id = :pid AND p.activo='si'
                ORDER BY p.nombre";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':pid', $patologiaId, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Cooperativas habilitadas (usuarios rol=cooperativa, permiso_ingreso=Habilitado) */
    public function getCooperativasHabilitadas(): array
    {
        $sql = "SELECT u.id_real, u.usuario
                FROM usuarios u
                WHERE u.rol='cooperativa' AND u.permiso_ingreso='Habilitado'
                ORDER BY u.usuario";
        $st = $this->pdo->query($sql);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Crea una solicitud con sus relaciones. Transaccional.
     * Espera estructura compatible con el front replicado.
     * Devuelve ID insertado.
     */
    public function crearSolicitud(array $payload): int
    {
        $this->pdo->beginTransaction();
        try {
            // drones_solicitud
            $sql = "INSERT INTO drones_solicitud
                    (productor_id_real, representante, linea_tension, zona_restringida, corriente_electrica, agua_potable,
                     libre_obstaculos, area_despegue, superficie_ha, forma_pago_id, coop_descuento_nombre,
                     dir_provincia, dir_localidad, dir_calle, dir_numero,
                     en_finca, ubicacion_lat, ubicacion_lng, ubicacion_acc, ubicacion_ts,
                     observaciones,
                     ses_usuario, ses_rol, ses_nombre, ses_correo, ses_telefono, ses_direccion, ses_cuit, ses_last_activity_ts,
                     estado)
                    VALUES
                    (:prod_id, :representante, :linea_tension, :zona_restringida, :corriente_electrica, :agua_potable,
                     :libre_obstaculos, :area_despegue, :superficie_ha, :forma_pago_id, :coop_descuento_nombre,
                     :dir_provincia, :dir_localidad, :dir_calle, :dir_numero,
                     :en_finca, :ubicacion_lat, :ubicacion_lng, :ubicacion_acc, :ubicacion_ts,
                     :observaciones,
                     :ses_usuario, :ses_rol, :ses_nombre, :ses_correo, :ses_telefono, :ses_direccion, :ses_cuit, NOW(),
                     'ingresada')";
            $st = $this->pdo->prepare($sql);

            $ses = $payload['sesion'] ?? [];

            $st->execute([
                ':prod_id'             => (string)($ses['id_real'] ?? ''),
                ':representante'       => $this->siNo($payload['representante'] ?? 'no'),
                ':linea_tension'       => $this->siNo($payload['linea_tension'] ?? 'no'),
                ':zona_restringida'    => $this->siNo($payload['zona_restringida'] ?? 'no'),
                ':corriente_electrica' => $this->siNo($payload['corriente_electrica'] ?? 'no'),
                ':agua_potable'        => $this->siNo($payload['agua_potable'] ?? 'no'),
                ':libre_obstaculos'    => $this->siNo($payload['libre_obstaculos'] ?? 'no'),
                ':area_despegue'       => $this->siNo($payload['area_despegue'] ?? 'no'),
                ':superficie_ha'       => $this->toDecimal($payload['superficie_ha'] ?? '0'),
                ':forma_pago_id'       => (int)($payload['forma_pago_id'] ?? 0),
                ':coop_descuento_nombre'=> $payload['coop_descuento_nombre'] ?? null,
                ':dir_provincia'       => $payload['direccion']['provincia'] ?? null,
                ':dir_localidad'       => $payload['direccion']['localidad'] ?? null,
                ':dir_calle'           => $payload['direccion']['calle'] ?? null,
                ':dir_numero'          => $payload['direccion']['numero'] ?? null,
                ':en_finca'            => $this->siNo($payload['ubicacion']['en_finca'] ?? 'no'),
                ':ubicacion_lat'       => $this->toNullableDecimal($payload['ubicacion']['lat'] ?? null),
                ':ubicacion_lng'       => $this->toNullableDecimal($payload['ubicacion']['lng'] ?? null),
                ':ubicacion_acc'       => $this->toNullableDecimal($payload['ubicacion']['acc'] ?? null),
                ':ubicacion_ts'        => $this->toNullableDateTime($payload['ubicacion']['timestamp'] ?? null),
                ':observaciones'       => $payload['observaciones'] ?? null,
                ':ses_usuario'         => $ses['usuario'] ?? null,
                ':ses_rol'             => $ses['rol'] ?? null,
                ':ses_nombre'          => $ses['nombre'] ?? null,
                ':ses_correo'          => $ses['correo'] ?? null,
                ':ses_telefono'        => $ses['telefono'] ?? null,
                ':ses_direccion'       => $ses['direccion'] ?? null,
                ':ses_cuit'            => $ses['cuit'] ?? null,
            ]);

            $solicitudId = (int)$this->pdo->lastInsertId();

            // rango
            if (!empty($payload['rango_fecha'])) {
                $stR = $this->pdo->prepare("INSERT INTO drones_solicitud_rango (solicitud_id, rango) VALUES (:sid, :rango)");
                $stR->execute([':sid'=>$solicitudId, ':rango'=>$payload['rango_fecha']]);
            }

            // motivos
            $mot = $payload['motivo'] ?? [];
            $opcs = is_array($mot['opciones'] ?? null) ? $mot['opciones'] : [];
            foreach ($opcs as $val) {
                if ($val === 'otros') continue;
                $stM = $this->pdo->prepare("INSERT INTO drones_solicitud_motivo (solicitud_id, patologia_id, es_otros) VALUES (:sid, :pid, 0)");
                $stM->execute([':sid'=>$solicitudId, ':pid'=>(int)$val]);
            }
            if (!empty($mot['otros'])) {
                $stM2 = $this->pdo->prepare("INSERT INTO drones_solicitud_motivo (solicitud_id, patologia_id, es_otros, otros_text) VALUES (:sid, NULL, 1, :txt)");
                $stM2->execute([':sid'=>$solicitudId, ':txt'=>trim((string)$mot['otros'])]);
            }

            // items productos
            $productos = is_array($payload['productos'] ?? null) ? $payload['productos'] : [];
            $sup = $this->toDecimal($payload['superficie_ha'] ?? '0');
            foreach ($productos as $p) {
                $fuente = ($p['fuente'] ?? '') === 'yo' ? 'productor' : 'sve';
                $productoId = isset($p['producto_id']) ? (int)$p['producto_id'] : null;
                $nombreProducto = $fuente === 'productor' ? ($p['marca'] ?? null) : ($p['producto_nombre'] ?? null);

                $costoHa = null;
                $totalProd = null;
                if ($fuente === 'sve' && $productoId) {
                    $stC = $this->pdo->prepare("SELECT costo_hectarea FROM dron_productos_stock WHERE id=:id AND activo='si'");
                    $stC->execute([':id'=>$productoId]);
                    $costoHa = (float)($stC->fetchColumn() ?: 0);
                    $totalProd = $costoHa * (float)$sup;
                }

                $stI = $this->pdo->prepare("INSERT INTO drones_solicitud_item
                    (solicitud_id, patologia_id, fuente, producto_id, costo_hectarea_snapshot, total_producto_snapshot, nombre_producto)
                    VALUES (:sid, :pat, :fue, :pid, :cst, :tot, :nom)");
                $stI->execute([
                    ':sid' => $solicitudId,
                    ':pat' => (int)($p['patologia_id'] ?? 0),
                    ':fue' => $fuente,
                    ':pid' => $productoId,
                    ':cst' => $costoHa,
                    ':tot' => $totalProd,
                    ':nom' => $nombreProducto
                ]);
            }

            // costos agregados
            $costo = $this->getCostoBase();
            $baseTotal = (float)$costo['costo'] * (float)$sup;

            $stProdSum = $this->pdo->prepare("SELECT COALESCE(SUM(total_producto_snapshot),0) FROM drones_solicitud_item WHERE solicitud_id=:sid AND fuente='sve'");
            $stProdSum->execute([':sid'=>$solicitudId]);
            $prodTotal = (float)$stProdSum->fetchColumn();

            $stCst = $this->pdo->prepare("INSERT INTO drones_solicitud_costos
                (solicitud_id, moneda, costo_base_por_ha, base_ha, base_total, productos_total, total, desglose_json)
                VALUES (:sid, :mon, :cpha, :bha, :btotal, :ptotal, :ttotal, :desg)");
            $total = $baseTotal + $prodTotal;
            $stCst->execute([
                ':sid'=>$solicitudId,
                ':mon'=>$costo['moneda'] ?? 'Pesos',
                ':cpha'=>$costo['costo'] ?? 0,
                ':bha'=>$sup,
                ':btotal'=>$baseTotal,
                ':ptotal'=>$prodTotal,
                ':ttotal'=>$total,
                ':desg'=> json_encode(['sup'=>$sup,'base_ha'=>$costo['costo'] ?? 0], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)
            ]);

            $this->pdo->commit();
            return $solicitudId;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function siNo(string $val): string
    {
        $v = strtolower(trim($val));
        return $v === 'si' ? 'si' : 'no';
    }
    private function toDecimal($v): float
    {
        return (float)str_replace(',', '.', (string)$v);
    }
    private function toNullableDecimal($v): ?float
    {
        if ($v === null || $v === '') return null;
        return (float)str_replace(',', '.', (string)$v);
    }
    private function toNullableDateTime($v): ?string
    {
        if (!$v) return null;
        $t = strtotime($v);
        return $t ? date('Y-m-d H:i:s', $t) : null;
    }
}
