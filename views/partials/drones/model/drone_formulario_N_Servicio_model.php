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

    /** Patologías activas (se mantiene para compat pero ya no se usa en UI) */
    public function patologias(): array
    {
        $sql = "SELECT id, nombre FROM dron_patologias WHERE activo='si' ORDER BY nombre";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Listado de productos activos con costo por hectárea */
    public function productosActivos(): array
    {
        $sql = "SELECT id, nombre, costo_hectarea
                  FROM dron_productos_stock
                 WHERE activo='si'
              ORDER BY nombre";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Costo de servicio por hectárea (fila activa) */
    public function costoServicioHectarea(): array
    {
        $sql = "SELECT costo FROM dron_costo_hectarea WHERE activo='si' ORDER BY id DESC LIMIT 1";
        $r = $this->pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
        return $r ?: ['costo' => 0];
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

            // drones_solicitud_motivo (múltiple) - ahora opcional
            $patologias = is_array($d['patologia_ids'] ?? null) ? $d['patologia_ids'] : (isset($d['patologia_id']) ? [$d['patologia_id']] : []);
            $patologias = array_values(array_unique(array_map('intval', $patologias)));
            if (!empty($patologias)) {
                $sqlMot = "INSERT INTO drones_solicitud_motivo (solicitud_id, patologia_id, es_otros) VALUES (?,?,0)";
                $stm = $this->pdo->prepare($sqlMot);
                foreach ($patologias as $pidMot) {
                    $stm->execute([$solicitudId, $pidMot]);
                }
            }

            // drones_solicitud_rango (guarda rango seleccionado)
            $sqlR = "INSERT INTO drones_solicitud_rango (solicitud_id, rango) VALUES (?,?)";
            $str = $this->pdo->prepare($sqlR);
            $str->execute([$solicitudId, $d['rango']]);

            // Decisión: usar patología principal (primera) para relacionar los items (compatibilidad sin cambiar esquema).
            if (!empty($d['items'])) {
                $sqlI = "INSERT INTO drones_solicitud_item (solicitud_id, patologia_id, fuente, producto_id, nombre_producto)
                        VALUES (?,?,?,?,?)";
                $sti = $this->pdo->prepare($sqlI);
                $patologiaPrincipal = isset($patologias[0]) ? (int)$patologias[0] : null; // puede ser null
                foreach ($d['items'] as $it) {
                    $pid = (int)$it['producto_id'];
                    $fuente = (string)$it['fuente'];
                    $nombre = $this->productoNombre($pid);
                    $sti->execute([$solicitudId, $patologiaPrincipal, $fuente, $pid, $nombre]);
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

    /** Productos por patología (se mantiene para compat con endpoints antiguos) */
    public function productosPorPatologia(int $patologiaId): array
    {
        $sql = "SELECT s.id, s.nombre
                  FROM dron_productos_stock s
                  INNER JOIN dron_productos_stock_patologias sp ON sp.producto_id = s.id
                 WHERE sp.patologia_id = ? AND s.activo='si'
              ORDER BY s.nombre";
        $st = $this->pdo->prepare($sql);
        $st->execute([$patologiaId]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    private function productoNombre(int $id): string
    {
        $st = $this->pdo->prepare("SELECT nombre FROM dron_productos_stock WHERE id=?");
        $st->execute([$id]);
        $n = $st->fetchColumn();
        return $n ? (string)$n : '';
    }
}
