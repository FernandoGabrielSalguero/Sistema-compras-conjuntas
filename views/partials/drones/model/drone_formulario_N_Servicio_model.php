<?php

declare(strict_types=1);

class DroneFormularioNservicioModel
{
    /** @var PDO */
    public PDO $pdo;

    /** Búsqueda de usuarios por nombre (solo activos en cualquier rol) */
    public function buscarUsuarios(string $q): array
    {
        $sql = "SELECT usuario, id_real FROM usuarios WHERE usuario LIKE ? ORDER BY usuario LIMIT 10";
        $st = $this->pdo->prepare($sql);
        $st->execute(['%' . $q . '%']);
        return $st->fetchAll(PDO::FETCH_ASSOC);
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

    /** Productos por patología usando tabla puente */
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

            // drones_solicitud_item (uno por producto)
            if (!empty($d['productos'])) {
                $sqlI = "INSERT INTO drones_solicitud_item (solicitud_id, patologia_id, fuente, producto_id, nombre_producto)
                         VALUES (?,?,?,?,?)";
                $sti = $this->pdo->prepare($sqlI);
                foreach ($d['productos'] as $pid) {
                    $nombre = $this->productoNombre((int)$pid);
                    $sti->execute([$solicitudId, $d['patologia_id'], $d['productos_fuente'], $pid, $nombre]);
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

    private function productoNombre(int $id): string
    {
        $st = $this->pdo->prepare("SELECT nombre FROM dron_productos_stock WHERE id=?");
        $st->execute([$id]);
        $n = $st->fetchColumn();
        return $n ? (string)$n : '';
    }
}
