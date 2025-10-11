<?php
declare(strict_types=1);

class DroneRegistroModel
{
    /** @var PDO */
    public $pdo;

    /**
     * Listado para tarjetas: solo 'completada' y segmentado por rol.
     * $ctx = ['rol' => ..., 'id_real' => ...]
     */
    public function getSolicitudesList(?string $q, array $ctx): array {
        $base = "
            SELECT 
              s.id,
              s.fecha_visita,
              COALESCE(s.ses_nombre, ui.nombre) AS productor
            FROM drones_solicitud s
            LEFT JOIN usuarios u        ON u.id_real = s.productor_id_real
            LEFT JOIN usuarios_info ui  ON ui.usuario_id = u.id
            LEFT JOIN cooperativas_rangos cr ON cr.nombre_cooperativa = s.coop_descuento_nombre
            LEFT JOIN rel_coop_ingeniero rci ON rci.cooperativa_id_real = cr.cooperativa_id_real
            WHERE s.estado = 'completada'
        ";
        $params = [];

        // Filtro por rol
        $rol = $ctx['rol'] ?? '';
        $idReal = $ctx['id_real'] ?? '';
        if ($rol === 'productor') {
            $base .= " AND s.productor_id_real = :id_real ";
            $params[':id_real'] = $idReal;
        } elseif ($rol === 'cooperativa') {
            // La cooperativa ve lo asociado a su nombre → mapeado a id_real via cooperativas_rangos
            $base .= " AND cr.cooperativa_id_real = :id_real ";
            $params[':id_real'] = $idReal;
        } elseif ($rol === 'ingeniero') {
            // Ingeniero asociado a esa cooperativa
            $base .= " AND rci.ingeniero_id_real = :id_real ";
            $params[':id_real'] = $idReal;
        } elseif ($rol === 'sve') {
            // SVE ve todo (no agrega filtro)
        } else {
            // Cualquier otro rol no autorizado → 0 resultados
            return [];
        }

        if ($q) {
            $base .= " AND (COALESCE(s.ses_nombre, ui.nombre) LIKE :q OR s.dir_localidad LIKE :q) ";
            $params[':q'] = "%$q%";
        }

        $base .= " ORDER BY s.fecha_visita DESC, s.id DESC LIMIT 200";
        $st = $this->pdo->prepare($base);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Detalle completo con autorización por rol.
     * $ctx = ['rol'=>..., 'id_real'=>...]
     */
    public function getRegistroDetalle(int $id, array $ctx): array {
        // Traemos la solicitud y además datos para verificar permisos por rol
        $st = $this->pdo->prepare("
            SELECT s.*,
                   COALESCE(s.ses_nombre, ui.nombre) AS productor_nombre,
                   ui.telefono AS productor_telefono,
                   cr.cooperativa_id_real           AS coop_id_real,
                   rci.ingeniero_id_real            AS ing_id_real
            FROM drones_solicitud s
            LEFT JOIN usuarios u        ON u.id_real = s.productor_id_real
            LEFT JOIN usuarios_info ui  ON ui.usuario_id = u.id
            LEFT JOIN cooperativas_rangos cr ON cr.nombre_cooperativa = s.coop_descuento_nombre
            LEFT JOIN rel_coop_ingeniero rci ON rci.cooperativa_id_real = cr.cooperativa_id_real
            WHERE s.id = :id AND s.estado = 'completada'
            LIMIT 1
        ");
        $st->execute([':id'=>$id]);
        $sol = $st->fetch(PDO::FETCH_ASSOC);
        if (!$sol) return [];

        // Autorización por rol
        $rol = $ctx['rol'] ?? '';
        $idReal = $ctx['id_real'] ?? '';
        $autorizado = false;
        if ($rol === 'sve') $autorizado = true;
        if ($rol === 'productor'  && $sol['productor_id_real'] === $idReal) $autorizado = true;
        if ($rol === 'cooperativa'&& $sol['coop_id_real'] === $idReal)       $autorizado = true;
        if ($rol === 'ingeniero'  && $sol['ing_id_real'] === $idReal)        $autorizado = true;
        if (!$autorizado) return []; // no exponer datos

        // Reporte (último)
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
              r.fecha_vencimiento
            FROM drones_solicitud_item i
            LEFT JOIN dron_productos_stock dps       ON dps.id = i.producto_id
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
