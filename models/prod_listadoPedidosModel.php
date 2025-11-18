<?php

declare(strict_types=1);

final class ProdListadoPedidosModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /**
     * Retorna items del productor con total y patologías agregadas.
     */
    public function listByProductor(string $productorIdReal, int $limit = 10, int $offset = 0): array
    {
        // Total
        $stCount = $this->pdo->prepare(
            "SELECT COUNT(*) FROM drones_solicitud WHERE productor_id_real = :pid"
        );
        $stCount->execute([':pid' => $productorIdReal]);
        $total = (int)$stCount->fetchColumn();

        // Listado (patologías y costo total agregados)
        $sql = "
SELECT s.id,
       s.superficie_ha,
       s.fecha_visita,
       -- Rango horario formateado HH:MM - HH:MM (o NULL si falta alguno)
       CASE
         WHEN s.hora_visita_desde IS NOT NULL AND s.hora_visita_hasta IS NOT NULL THEN
           CONCAT(LPAD(HOUR(s.hora_visita_desde),2,'0'), ':', LPAD(MINUTE(s.hora_visita_desde),2,'0'),
                  ' - ',
                  LPAD(HOUR(s.hora_visita_hasta),2,'0'),  ':', LPAD(MINUTE(s.hora_visita_hasta),2,'0'))
         ELSE NULL
       END AS hora_visita,
       s.estado,
       COALESCE(c.total, 0) AS costo_total,
       COALESCE(c.moneda, 'Pesos') AS moneda,
       TRIM(BOTH ',' FROM COALESCE(GROUP_CONCAT(DISTINCT
              CASE
                 WHEN sm.es_otros = 1 THEN sm.otros_text
                 WHEN sm.patologia_id IS NOT NULL THEN dp.nombre
                 ELSE NULL
              END
              ORDER BY dp.nombre SEPARATOR ', '
       ), '')) AS patologias
FROM drones_solicitud s
LEFT JOIN drones_solicitud_costos c
       ON c.solicitud_id = s.id
LEFT JOIN drones_solicitud_motivo sm
       ON sm.solicitud_id = s.id
LEFT JOIN dron_patologias dp
       ON dp.id = sm.patologia_id
WHERE s.productor_id_real = :pid
GROUP BY s.id
ORDER BY s.created_at DESC, s.id DESC
LIMIT :limit OFFSET :offset";


        $st = $this->pdo->prepare($sql);
        $st->bindValue(':pid', $productorIdReal, PDO::PARAM_STR);
        $st->bindValue(':limit', $limit, PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, PDO::PARAM_INT);
        $st->execute();
        $items = $st->fetchAll() ?: [];

        return ['total' => $total, 'items' => $items];
    }

    /**
     * Cancela una solicitud del productor (id_real debe coincidir).
     */
    public function cancelar(int $solicitudId, string $productorIdReal): void
    {
        // Validar pertenencia y estado actual
        $stSel = $this->pdo->prepare("
            SELECT estado
              FROM drones_solicitud
             WHERE id = :id AND productor_id_real = :pid
        ");
        $stSel->execute([':id' => $solicitudId, ':pid' => $productorIdReal]);
        $estado = $stSel->fetchColumn();
        if ($estado === false) {
            throw new RuntimeException('Solicitud no encontrada.');
        }

        // Solo se puede cancelar en: 'procesando' o 'aprobada_coop'
        if (!in_array($estado, ['ingresada', 'aprobada_coop'], true)) {
            throw new InvalidArgumentException('La solicitud no puede ser cancelada en su estado actual.');
        }

        $stUp = $this->pdo->prepare("
            UPDATE drones_solicitud
               SET estado = 'cancelada',
                   motivo_cancelacion = 'Cancelada por productor',
                   updated_at = CURRENT_TIMESTAMP
             WHERE id = :id AND productor_id_real = :pid
        ");
        $stUp->execute([':id' => $solicitudId, ':pid' => $productorIdReal]);
        if ($stUp->rowCount() === 0) {
            throw new RuntimeException('No se pudo cancelar la solicitud.');
        }
    }

        /**
     * Detalle completo del registro fitosanitario para una solicitud del productor.
     * Incluye: datos de solicitud, costos, patologías, reporte operativo, productos y media (fotos/firmas).
     */
    public function detalleById(int $solicitudId, string $productorIdReal): array
    {
        // Datos principales + costos + patologías agregadas
        $sql = "
        SELECT
            s.id,
            s.productor_id_real,
            s.superficie_ha,
            s.fecha_visita,
            CASE
                WHEN s.hora_visita_desde IS NOT NULL AND s.hora_visita_hasta IS NOT NULL THEN
                    CONCAT(LPAD(HOUR(s.hora_visita_desde),2,'0'), ':', LPAD(MINUTE(s.hora_visita_desde),2,'0'),
                           ' - ',
                           LPAD(HOUR(s.hora_visita_hasta),2,'0'),  ':', LPAD(MINUTE(s.hora_visita_hasta),2,'0'))
                ELSE NULL
            END AS hora_visita,
            s.estado,
            s.motivo_cancelacion,
            s.created_at,
            s.updated_at,
            s.dir_provincia,
            s.dir_localidad,
            s.dir_calle,
            s.dir_numero,
            COALESCE(c.total, 0) AS costo_total,
            COALESCE(c.moneda, 'Pesos') AS moneda,
            TRIM(BOTH ',' FROM COALESCE(GROUP_CONCAT(DISTINCT
                CASE
                    WHEN sm.es_otros = 1 THEN sm.otros_text
                    WHEN sm.patologia_id IS NOT NULL THEN dp.nombre
                    ELSE NULL
                END
                ORDER BY dp.nombre SEPARATOR ', '
            ), '')) AS patologias
        FROM drones_solicitud s
        LEFT JOIN drones_solicitud_costos c
               ON c.solicitud_id = s.id
        LEFT JOIN drones_solicitud_motivo sm
               ON sm.solicitud_id = s.id
        LEFT JOIN dron_patologias dp
               ON dp.id = sm.patologia_id
        WHERE s.id = :id AND s.productor_id_real = :pid
        GROUP BY s.id
        LIMIT 1";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':id', $solicitudId, PDO::PARAM_INT);
        $st->bindValue(':pid', $productorIdReal, PDO::PARAM_STR);
        $st->execute();
        $solicitud = $st->fetch();
        if (!$solicitud) {
            throw new RuntimeException('Solicitud no encontrada.');
        }

        // Reporte operativo (único por solicitud)
        $stRep = $this->pdo->prepare("
            SELECT r.id, r.nom_cliente, r.nom_piloto, r.nom_encargado,
                   r.fecha_visita, r.hora_ingreso, r.hora_egreso,
                   r.nombre_finca, r.cultivo_pulverizado, r.cuadro_cuartel,
                   r.sup_pulverizada, r.vol_aplicado, r.vel_viento,
                   r.temperatura, r.humedad_relativa, r.observaciones,
                   r.lavado_dron_miner, r.triple_lavado_envases
            FROM drones_solicitud_Reporte r
            WHERE r.solicitud_id = :id
            LIMIT 1
        ");
        $stRep->execute([':id' => $solicitudId]);
        $reporte = $stRep->fetch() ?: null;

        // Productos utilizados (de receta si existe, si no con fallback a item)
        $stProd = $this->pdo->prepare("
            SELECT
              COALESCE(si.nombre_producto, ps.nombre) AS nombre_comercial,
              sir.principio_activo,
              sir.dosis AS dosis_ml_ha,
              sir.cant_prod_usado AS cant_usada,
              sir.fecha_vencimiento
            FROM drones_solicitud_item si
            LEFT JOIN dron_productos_stock ps ON ps.id = si.producto_id
            LEFT JOIN drones_solicitud_item_receta sir ON sir.solicitud_item_id = si.id
            WHERE si.solicitud_id = :id
            ORDER BY COALESCE(sir.orden_mezcla, si.id)
        ");
        $stProd->execute([':id' => $solicitudId]);
        $productos = $stProd->fetchAll() ?: [];

        // Media (fotos y firmas) asociadas al reporte
        $media = ['foto' => [], 'firma_cliente' => [], 'firma_piloto' => []];
        if ($reporte && isset($reporte['id'])) {
            $stMed = $this->pdo->prepare("
                SELECT tipo, ruta
                FROM drones_solicitud_reporte_media
                WHERE reporte_id = :rid
                ORDER BY id ASC
            ");
            $stMed->execute([':rid' => $reporte['id']]);
            $rows = $stMed->fetchAll() ?: [];
            foreach ($rows as $m) {
                $t = $m['tipo'];
                if (!isset($media[$t])) $media[$t] = [];
                $media[$t][] = $m['ruta'];
            }
        }

        return [
            'solicitud' => $solicitud,
            'reporte'   => $reporte,
            'productos' => $productos,
            'media'     => $media
        ];
    }

}
