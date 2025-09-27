<?php

declare(strict_types=1);

class DroneFormularioNservicioModel
{
    /** @var PDO */
    public PDO $pdo;

    /** Búsqueda de PRODUCTORES por nombre (solo habilitados) */
    public function buscarUsuarios(string $q): array
    {
        $sql = "SELECT usuario, id_real
                  FROM usuarios
                 WHERE rol = 'productor'
                   AND permiso_ingreso = 'Habilitado'
                   AND usuario LIKE ?
              ORDER BY usuario
                 LIMIT 10";
        $st = $this->pdo->prepare($sql);
        $st->execute(['%' . $q . '%']);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Rangos disponibles (orden comenzando por octubre) */
    public function rangos(): array
    {
        // Mantener sincronizado con enum de drones_solicitud_rango.rango
        // Orden requerido: octubre → noviembre → diciembre → enero → febrero
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

    /** Productos por patología usando tabla puente (incluye costo por hectárea) */
    public function productosPorPatologia(int $patologiaId): array
    {
        $sql = "SELECT s.id, s.nombre, s.costo_hectarea
                  FROM dron_productos_stock s
                  INNER JOIN dron_productos_stock_patologias sp ON sp.producto_id = s.id
                 WHERE sp.patologia_id = ? AND s.activo='si'
              ORDER BY s.nombre";
        $st = $this->pdo->prepare($sql);
        $st->execute([$patologiaId]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Costo base por hectárea del servicio de drones (último vigente) */
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

    /** Inserta la solicitud + secundarios en transacción */
    public function crearSolicitud(array $d): array
    {
        $this->pdo->beginTransaction();
        try {
            // drones_solicitud
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

            // drones_solicitud_motivo
            $sqlMot = "INSERT INTO drones_solicitud_motivo (solicitud_id, patologia_id, es_otros) VALUES (?,?,0)";
            $stm = $this->pdo->prepare($sqlMot);
            $stm->execute([$solicitudId, $d['patologia_id']]);

            // drones_solicitud_rango (guarda rango seleccionado)
            $sqlR = "INSERT INTO drones_solicitud_rango (solicitud_id, rango) VALUES (?,?)";
            $str = $this->pdo->prepare($sqlR);
            $str->execute([$solicitudId, $d['rango']]);

            // drones_solicitud_item (uno por producto con su fuente + snapshots)
            if (!empty($d['items'])) {
                $sqlI = "INSERT INTO drones_solicitud_item
             (solicitud_id, patologia_id, fuente, producto_id, costo_hectarea_snapshot, total_producto_snapshot, nombre_producto)
             VALUES (?,?,?,?,?,?,?)";
                $sti = $this->pdo->prepare($sqlI);
                foreach ($d['items'] as $it) {
                    $pid = (int)$it['producto_id'];
                    $fuente = (string)$it['fuente'];
                    $custom = isset($it['nombre_producto_custom']) ? trim((string)$it['nombre_producto_custom']) : '';
                    if ($fuente === 'productor') {
                        $nombre = $custom !== '' ? mb_substr($custom, 0, 150) : $this->productoNombre($pid);
                        $costoHa = 0.00;
                        $totalSnap = 0.00;
                    } else { // sve
                        $nombre = $this->productoNombre($pid);
                        $costoHa = $this->productoCostoHa($pid);
                        $totalSnap = (float)$d['superficie_ha'] * (float)$costoHa;
                    }
                    $sti->execute([
                        $solicitudId,
                        $d['patologia_id'],
                        $fuente,
                        $pid,
                        $costoHa,
                        $totalSnap,
                        $nombre
                    ]);
                }
            }

            // Evento
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

    private function productoCostoHa(int $id): float
    {
        $st = $this->pdo->prepare("SELECT costo_hectarea FROM dron_productos_stock WHERE id=?");
        $st->execute([$id]);
        $v = $st->fetchColumn();
        return $v !== false ? (float)$v : 0.0;
    }

    private function productoNombre(int $id): string
    {
        $st = $this->pdo->prepare("SELECT nombre FROM dron_productos_stock WHERE id=?");
        $st->execute([$id]);
        $n = $st->fetchColumn();
        return $n ? (string)$n : '';
    }
}
