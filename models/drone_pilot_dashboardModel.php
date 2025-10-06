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
                    s.dir_localidad
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
        $sql = "SELECT volumen_ha, velocidad_vuelo, alto_vuelo, ancho_pasada, tamano_gota, observaciones
                FROM drones_solicitud_parametros
                WHERE solicitud_id = :sid
                LIMIT 1";
        $st = $this->pdo->prepare($sql);
        $st->execute([':sid' => $solicitudId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Crea reporte */
    public function crearReporte(array $data): int
    {
        $sql = "INSERT INTO drones_solicitud_Reporte
            (solicitud_id, nom_cliente, nom_piloto, nom_encargado, fecha_visita, hora_ingreso, hora_egreso, nombre_finca, cultivo_pulverizado, cuadro_cuartel, sup_pulverizada, vol_aplicado, vel_viento, temperatura, humedad_relativa, observaciones, created_at)
            VALUES
            (:solicitud_id, :nom_cliente, :nom_piloto, :nom_encargado, :fecha_visita, :hora_ingreso, :hora_egreso, :nombre_finca, :cultivo_pulverizado, :cuadro_cuartel, :sup_pulverizada, :vol_aplicado, :vel_viento, :temperatura, :humedad_relativa, :observaciones, NOW())";
        $st = $this->pdo->prepare($sql);
        $st->execute([
            ':solicitud_id'       => $data['solicitud_id'],
            ':nom_cliente'        => $data['nom_cliente'],
            ':nom_piloto'         => $data['nom_piloto'],
            ':nom_encargado'      => $data['nom_encargado'],
            ':fecha_visita'       => $data['fecha_visita'],
            ':hora_ingreso'       => $data['hora_ingreso'],
            ':hora_egreso'        => $data['hora_egreso'],
            ':nombre_finca'       => $data['nombre_finca'],
            ':cultivo_pulverizado' => $data['cultivo_pulverizado'],
            ':cuadro_cuartel'     => $data['cuadro_cuartel'],
            ':sup_pulverizada'    => $data['sup_pulverizada'],
            ':vol_aplicado'       => $data['vol_aplicado'],
            ':vel_viento'         => $data['vel_viento'],
            ':temperatura'        => $data['temperatura'],
            ':humedad_relativa'   => $data['humedad_relativa'],
            ':observaciones'      => $data['observaciones'],
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
        $sql = "SELECT tipo, ruta
            FROM drones_solicitud_reporte_media
            WHERE reporte_id = :rid
            ORDER BY created_at ASC, id ASC";
        $st = $this->pdo->prepare($sql);
        $st->execute([':rid' => $reporteId]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }


    /** Guarda registros de media (foto/firma) */
    public function guardarMedia(int $reporteId, string $tipo, string $ruta): void
    {
        // $tipo: 'foto' | 'firma_cliente' | 'firma_piloto'
        $sql = "INSERT INTO drones_solicitud_reporte_media (reporte_id, tipo, ruta) VALUES (:rid, :tipo, :ruta)";
        $st = $this->pdo->prepare($sql);
        $st->execute([':rid' => $reporteId, ':tipo' => $tipo, ':ruta' => $ruta]);
    }
}
