<?php
class DronePilotDashboardModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /** Listado para la grilla */
    public function getSolicitudesByPilotoId(int $pilotoId): array
    {
        $sql = "SELECT 
    s.id,
    s.productor_id_real,
    COALESCE(ui.nombre, u.usuario, s.productor_id_real) AS productor_nombre,
    s.superficie_ha,
    s.fecha_visita,
    s.hora_visita_desde,
    s.hora_visita_hasta,
    s.dir_localidad,
    s.estado,
    s.agua_potable
                FROM drones_solicitud s
                LEFT JOIN usuarios u      ON u.id_real = s.productor_id_real
                LEFT JOIN usuarios_info ui ON ui.usuario_id = u.id
                WHERE s.piloto_id = :piloto_id
                ORDER BY s.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':piloto_id', $pilotoId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Detalle de una solicitud, validando pertenencia al piloto */
    public function getSolicitudDetalle(int $solicitudId, int $pilotoId): ?array
    {
        $sql = "SELECT 
    s.id,
    s.productor_id_real,
    s.superficie_ha,
    s.agua_potable,
    s.observaciones,
    s.fecha_visita,
    s.hora_visita_desde,
    s.hora_visita_hasta,
    s.dir_provincia,
    s.dir_localidad,
    s.dir_calle,
    s.dir_numero,
    s.ubicacion_lat,
    s.ubicacion_lng,
    s.estado,
    s.motivo_cancelacion
                FROM drones_solicitud s
                WHERE s.id = :id AND s.piloto_id = :piloto_id
                LIMIT 1";
        $st = $this->pdo->prepare($sql);
        $st->execute([':id' => $solicitudId, ':piloto_id' => $pilotoId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Recetas (JOIN para nombre de producto), orden por orden_mezcla ASC */
    public function getRecetaBySolicitud(int $solicitudId): array
    {
        $sql = "SELECT 
                    r.solicitud_item_id,
                    COALESCE(si.nombre_producto, dps.nombre) AS nombre_producto,
                    r.principio_activo,
                    r.dosis,
                    r.unidad,
                    r.orden_mezcla,
                    r.notas
                FROM drones_solicitud_item_receta r
                INNER JOIN drones_solicitud_item si ON si.id = r.solicitud_item_id
                LEFT JOIN dron_productos_stock dps ON dps.id = si.producto_id
                WHERE si.solicitud_id = :sid
                ORDER BY r.orden_mezcla ASC";
        $st = $this->pdo->prepare($sql);
        $st->execute([':sid' => $solicitudId]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Parámetros de vuelo */
    public function getParametrosBySolicitud(int $solicitudId): ?array
    {
        $sql = "SELECT volumen_ha, velocidad_vuelo, alto_vuelo, ancho_pasada, tamano_gota, observaciones, observaciones_agua
                FROM drones_solicitud_parametros
                WHERE solicitud_id = :sid
                LIMIT 1";
        $st = $this->pdo->prepare($sql);
        $st->execute([':sid' => $solicitudId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Crea/actualiza reporte (UPSERT por clave única solicitud_id+fecha_visita+hora_ingreso) */
    public function crearReporte(array $data): int
    {
        // Usamos ON DUPLICATE KEY UPDATE con LAST_INSERT_ID(id) para poder obtener el id también cuando es UPDATE.
        $sql = "INSERT INTO drones_solicitud_Reporte
        (solicitud_id, nom_cliente, nom_piloto, nom_encargado, fecha_visita, hora_ingreso, hora_egreso, nombre_finca, cultivo_pulverizado, cuadro_cuartel, sup_pulverizada, vol_aplicado, vel_viento, temperatura, humedad_relativa, lavado_dron_miner, triple_lavado_envases, observaciones, created_at)
        VALUES
        (:solicitud_id, :nom_cliente, :nom_piloto, :nom_encargado, :fecha_visita, :hora_ingreso, :hora_egreso, :nombre_finca, :cultivo_pulverizado, :cuadro_cuartel, :sup_pulverizada, :vol_aplicado, :vel_viento, :temperatura, :humedad_relativa, :lavado_dron_miner, :triple_lavado_envases, :observaciones, NOW())
        ON DUPLICATE KEY UPDATE
            nom_cliente           = VALUES(nom_cliente),
            nom_piloto            = VALUES(nom_piloto),
            nom_encargado         = VALUES(nom_encargado),
            fecha_visita          = VALUES(fecha_visita),
            hora_ingreso          = VALUES(hora_ingreso),
            hora_egreso           = VALUES(hora_egreso),
            nombre_finca          = VALUES(nombre_finca),
            cultivo_pulverizado   = VALUES(cultivo_pulverizado),
            cuadro_cuartel        = VALUES(cuadro_cuartel),
            sup_pulverizada       = VALUES(sup_pulverizada),
            vol_aplicado          = VALUES(vol_aplicado),
            vel_viento            = VALUES(vel_viento),
            temperatura           = VALUES(temperatura),
            humedad_relativa      = VALUES(humedad_relativa),
            lavado_dron_miner     = VALUES(lavado_dron_miner),
            triple_lavado_envases = VALUES(triple_lavado_envases),
            observaciones         = VALUES(observaciones),
            id = LAST_INSERT_ID(id)"; // ← truco para obtener el id también cuando es UPDATE

        $st = $this->pdo->prepare($sql);
        $st->execute([
            ':solicitud_id'          => $data['solicitud_id'],
            ':nom_cliente'           => $data['nom_cliente'],
            ':nom_piloto'            => $data['nom_piloto'],
            ':nom_encargado'         => $data['nom_encargado'],
            ':fecha_visita'          => $data['fecha_visita'],
            ':hora_ingreso'          => $data['hora_ingreso'],
            ':hora_egreso'           => $data['hora_egreso'],
            ':nombre_finca'          => $data['nombre_finca'],
            ':cultivo_pulverizado'   => $data['cultivo_pulverizado'],
            ':cuadro_cuartel'        => $data['cuadro_cuartel'],
            ':sup_pulverizada'       => $data['sup_pulverizada'],
            ':vol_aplicado'          => $data['vol_aplicado'],
            ':vel_viento'            => $data['vel_viento'],
            ':temperatura'           => $data['temperatura'],
            ':humedad_relativa'      => $data['humedad_relativa'],
            ':lavado_dron_miner'     => $data['lavado_dron_miner'],
            ':triple_lavado_envases' => $data['triple_lavado_envases'],
            ':observaciones'         => $data['observaciones'],
        ]);
        return (int)$this->pdo->lastInsertId();
    }


    /** Obtiene el último reporte de una solicitud (para prellenar modal) */
    public function getReporteBySolicitud(int $solicitudId): ?array
    {
        $sql = "SELECT 
                id,
                solicitud_id,
                nom_cliente,
                nom_piloto,
                nom_encargado,
                fecha_visita,
                hora_ingreso,
                hora_egreso,
                nombre_finca,
                cultivo_pulverizado,
                cuadro_cuartel,
                sup_pulverizada,
                vol_aplicado,
                vel_viento,
                temperatura,
                humedad_relativa,
                lavado_dron_miner,
                triple_lavado_envases,
                observaciones,
                created_at
            FROM drones_solicitud_Reporte
            WHERE solicitud_id = :sid
            ORDER BY created_at DESC, id DESC
            LIMIT 1";
        $st = $this->pdo->prepare($sql);
        $st->execute([':sid' => $solicitudId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Media asociada a un reporte (fotos y firmas) */
    public function getMediaByReporte(int $reporteId): array
    {
        $sql = "SELECT id, tipo, ruta
        FROM drones_solicitud_reporte_media
        WHERE reporte_id = :rid
        ORDER BY created_at ASC, id ASC";
        $st = $this->pdo->prepare($sql);
        $st->execute([':rid' => $reporteId]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Obtiene una media por id */
    public function getMediaById(int $mediaId): ?array
    {
        $sql = "SELECT id, reporte_id, tipo, ruta
            FROM drones_solicitud_reporte_media
            WHERE id = :mid
            LIMIT 1";
        $st = $this->pdo->prepare($sql);
        $st->execute([':mid' => $mediaId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Dado un reporte_id devuelve su solicitud_id */
    public function getSolicitudIdByReporte(int $reporteId): ?int
    {
        $sql = "SELECT solicitud_id
            FROM drones_solicitud_Reporte
            WHERE id = :rid
            LIMIT 1";
        $st = $this->pdo->prepare($sql);
        $st->execute([':rid' => $reporteId]);
        $sid = $st->fetchColumn();
        return $sid ? (int)$sid : null;
    }

    /** Elimina el registro de media por id */
    public function deleteMediaById(int $mediaId): void
    {
        $sql = "DELETE FROM drones_solicitud_reporte_media
            WHERE id = :mid";
        $st = $this->pdo->prepare($sql);
        $st->execute([':mid' => $mediaId]);
    }

    /** Guarda registros de media (foto/firma) */
    public function guardarMedia(int $reporteId, string $tipo, string $ruta): void
    {
        // $tipo: 'foto' | 'firma_cliente' | 'firma_piloto'
        $sql = "INSERT INTO drones_solicitud_reporte_media (reporte_id, tipo, ruta) VALUES (:rid, :tipo, :ruta)";
        $st = $this->pdo->prepare($sql);
        $st->execute([':rid' => $reporteId, ':tipo' => $tipo, ':ruta' => $ruta]);
    }

    /** Marca la solicitud como completada */
    public function marcarCompletada(int $solicitudId): void
    {
        $sql = "UPDATE drones_solicitud SET estado = 'completada' WHERE id = :id";
        $st  = $this->pdo->prepare($sql);
        $st->execute([':id' => $solicitudId]);
    }

    /** Receta editable unificando info de stock (tiempo_carencia) según nueva BD */
    public function getRecetaEditableBySolicitud(int $solicitudId): array
    {
        $sql = "SELECT 
                r.id,
                si.id AS solicitud_item_id,
                COALESCE(si.nombre_producto, dps.nombre) AS nombre_producto,
                COALESCE(r.principio_activo, dps.principio_activo) AS principio_activo,
                dps.tiempo_carencia,
                r.dosis,
                r.cant_prod_usado,
                r.fecha_vencimiento
            FROM drones_solicitud_item_receta r
            INNER JOIN drones_solicitud_item si ON si.id = r.solicitud_item_id
            LEFT JOIN dron_productos_stock dps ON dps.id = si.producto_id
            WHERE si.solicitud_id = :sid
            ORDER BY r.orden_mezcla ASC, r.id ASC";
        $st = $this->pdo->prepare($sql);
        $st->execute([':sid' => $solicitudId]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Actualiza cant_prod_usado y fecha_vencimiento de múltiples filas */
    public function actualizarRecetaValores(array $rows, ?string $actor = null): void
    {
        if (!$rows) return;
        $this->pdo->beginTransaction();
        $sql = "UPDATE drones_solicitud_item_receta 
            SET cant_prod_usado = :cant, fecha_vencimiento = :fec, updated_by = :actor 
            WHERE id = :id";
        $st = $this->pdo->prepare($sql);
        foreach ($rows as $r) {
            $st->execute([
                ':cant'  => $r['cant_prod_usado'] ?? null,
                ':fec'   => $r['fecha_vencimiento'] ?? null,
                ':actor' => $actor,
                ':id'    => $r['id'] ?? 0
            ]);
        }
        $this->pdo->commit();
    }

    /** Busca un producto de stock por nombre exacto (para vincular producto_id si existe) */
    private function findProductoStockIdByNombre(string $nombre): ?int
    {
        $st = $this->pdo->prepare("SELECT id FROM dron_productos_stock WHERE nombre = :n LIMIT 1");
        $st->execute([':n' => $nombre]);
        $id = $st->fetchColumn();
        return $id ? (int)$id : null;
    }

    /** Toma cualquier patología existente en la solicitud como fallback (para respetar FK) */
    private function pickPatologiaIdParaSolicitud(int $solicitudId): ?int
    {
        $st = $this->pdo->prepare("SELECT patologia_id FROM drones_solicitud_item WHERE solicitud_id = :sid LIMIT 1");
        $st->execute([':sid' => $solicitudId]);
        $pid = $st->fetchColumn();
        return $pid ? (int)$pid : null;
    }

    /** Agrega un producto (ítem + receta) a la solicitud (nuevo flujo) */
    public function agregarProductoAReceta(array $data): void
    {
        $this->pdo->beginTransaction();

        $productoId = $this->findProductoStockIdByNombre($data['nombre_producto']);
        $patologiaId = $this->pickPatologiaIdParaSolicitud((int)$data['solicitud_id']) ?? 1; // ← usa una existente o 1 (ej. "Otros")

        // Crear ítem
        $sqlItem = "INSERT INTO drones_solicitud_item
        (solicitud_id, patologia_id, fuente, producto_id, costo_hectarea_snapshot, total_producto_snapshot, nombre_producto, created_at)
        VALUES (:sid, :pat, 'productor', :prod_id, NULL, NULL, :nom, NOW())";
        $stI = $this->pdo->prepare($sqlItem);
        $stI->execute([
            ':sid'     => $data['solicitud_id'],
            ':pat'     => $patologiaId,
            ':prod_id' => $productoId,
            ':nom'     => $data['nombre_producto']
        ]);
        $solicitudItemId = (int)$this->pdo->lastInsertId();

        // Crear receta
        $sqlRec = "INSERT INTO drones_solicitud_item_receta
        (solicitud_item_id, principio_activo, dosis, cant_prod_usado, fecha_vencimiento, unidad, orden_mezcla, notas, created_by, created_at)
        VALUES (:siid, :pa, :dosis, :cant, :fec, NULL, NULL, NULL, :actor, NOW())";
        $stR = $this->pdo->prepare($sqlRec);
        $stR->execute([
            ':siid'  => $solicitudItemId,
            ':pa'    => $data['principio_activo'] ?: null,
            ':dosis' => $data['dosis'] ?? null,
            ':cant'  => $data['cant_prod_usado'] ?? null,
            ':fec'   => $data['fecha_vencimiento'] ?? null,
            ':actor' => $data['created_by'] ?? null
        ]);

        $this->pdo->commit();
    }
    /** Verifica si ya existe una media con la misma ruta para este reporte */
    public function mediaExists(int $reporteId, string $ruta): bool
    {
        $sql = "SELECT 1 FROM drones_solicitud_reporte_media WHERE reporte_id = :rid AND ruta = :ruta LIMIT 1";
        $st  = $this->pdo->prepare($sql);
        $st->execute([':rid' => $reporteId, ':ruta' => $ruta]);
        return (bool)$st->fetchColumn();
    }

    /** Elimina media por tipo para un reporte (útil para mantener una sola firma por tipo) */
    public function deleteMediaByTipo(int $reporteId, string $tipo): void
    {
        $sql = "DELETE FROM drones_solicitud_reporte_media WHERE reporte_id = :rid AND tipo = :tipo";
        $st  = $this->pdo->prepare($sql);
        $st->execute([':rid' => $reporteId, ':tipo' => $tipo]);
    }
}
