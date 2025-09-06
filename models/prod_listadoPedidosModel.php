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
        if (!in_array($estado, ['procesando','aprobada_coop'], true)) {
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


}
