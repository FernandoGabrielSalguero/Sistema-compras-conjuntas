<?php
declare(strict_types=1);

class DroneRegistroModel
{
    /** @var PDO */
    public $pdo;

    /**
     * Listado compacto para tarjetas.
     * Filtro opcional por texto (productor/localidad) y estado.
     */
    public function getSolicitudesList(?string $q = null, ?string $estado = null): array {
        $sql = "
            SELECT 
              s.id,
              s.fecha_visita,
              s.hora_visita_desde AS hora_desde,
              s.hora_visita_hasta AS hora_hasta,
              s.dir_localidad,
              s.dir_provincia,
              s.superficie_ha,
              s.agua_potable,
              s.estado,
              COALESCE(s.ses_nombre, ui.nombre) AS productor
            FROM drones_solicitud s
            LEFT JOIN usuarios u      ON u.id_real = s.productor_id_real
            LEFT JOIN usuarios_info ui ON ui.usuario_id = u.id
            WHERE 1=1
        ";
        $params = [];
        if ($q) {
            $sql .= " AND (COALESCE(s.ses_nombre, ui.nombre) LIKE :q OR s.dir_localidad LIKE :q) ";
            $params[':q'] = "%$q%";
        }
        if ($estado) {
            $sql .= " AND s.estado = :estado ";
            $params[':estado'] = $estado;
        }
        $sql .= " ORDER BY s.fecha_visita DESC, s.id DESC LIMIT 200";
        $st = $this->pdo->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Detalle completo para el Registro Fitosanitario.
     */
    public function getRegistroDetalle(int $id): array {
        // Solicitud base
        $st = $this->pdo->prepare("
            SELECT s.*,
                   COALESCE(s.ses_nombre, ui.nombre)   AS productor_nombre,
                   ui.telefono                         AS productor_telefono
            FROM drones_solicitud s
            LEFT JOIN usuarios u       ON u.id_real = s.productor_id_real
            LEFT JOIN usuarios_info ui ON ui.usuario_id = u.id
            WHERE s.id = :id
            LIMIT 1
        ");
        $st->execute([':id'=>$id]);
        $sol = $st->fetch(PDO::FETCH_ASSOC);
        if (!$sol) return [];

        // Último reporte cargado (si existe)
        $st = $this->pdo->prepare("SELECT * FROM drones_solicitud_Reporte WHERE solicitud_id = :id ORDER BY id DESC LIMIT 1");
        $st->execute([':id'=>$id]);
        $reporte = $st->fetch(PDO::FETCH_ASSOC) ?: null;

        // Parámetros
        $st = $this->pdo->prepare("SELECT * FROM drones_solicitud_parametros WHERE solicitud_id = :id ORDER BY id DESC LIMIT 1");
        $st->execute([':id'=>$id]);
        $param = $st->fetch(PDO::FETCH_ASSOC) ?: null;

        // Items + receta + info de stock
        $st = $this->pdo->prepare("
            SELECT 
              i.id AS item_id,
              i.nombre_producto,
              dps.principio_activo,
              dps.tiempo_carencia,
              r.dosis,
              r.cant_prod_usado,
              r.unidad,
              r.fecha_vencimiento,
              NULL AS fecha,
              NULL AS cuadro_cuartel
            FROM drones_solicitud_item i
            LEFT JOIN dron_productos_stock dps      ON dps.id = i.producto_id
            LEFT JOIN drones_solicitud_item_receta r ON r.solicitud_item_id = i.id
            WHERE i.solicitud_id = :id
            ORDER BY i.id ASC
        ");
        $st->execute([':id'=>$id]);
        $items = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Media (fotos y firmas) si hay reporte
        $media = [];
        if ($reporte) {
            $st = $this->pdo->prepare("
                SELECT m.tipo, m.ruta
                FROM drones_solicitud_reporte_media m
                WHERE m.reporte_id = :rid
                ORDER BY m.id ASC
            ");
            $st->execute([':rid'=>$reporte['id']]);
            $media = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }

        return [
            'solicitud'  => $sol,
            'reporte'    => $reporte,
            'parametros' => $param,
            'items'      => $items,
            'media'      => $media
        ];
    }
}
