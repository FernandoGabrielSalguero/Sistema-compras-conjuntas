<?php

declare(strict_types=1);

final class DroneDrawerListadoModel
{
    private PDO $pdo;
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /** FULL detalle para un ID (sólo lectura) */
    public function obtenerSolicitudFull(int $id): array
    {
        // base
        $st = $this->pdo->prepare("
            SELECT s.*, 
                   p.nombre   AS piloto_nombre, p.telefono AS piloto_telefono, p.zona_asignada AS piloto_zona_asignada, p.correo AS piloto_correo,
                   fp.nombre  AS forma_pago_nombre, fp.descripcion AS forma_pago_descripcion
            FROM drones_solicitud s
            LEFT JOIN dron_pilotos p      ON p.id  = s.piloto_id
            LEFT JOIN dron_formas_pago fp ON fp.id = s.forma_pago_id
            WHERE s.id = :id
        ");
        $st->execute([':id' => $id]);
        $sol = $st->fetch();
        if (!$sol) return [];

        // costos
        $st = $this->pdo->prepare("SELECT * FROM drones_solicitud_costos WHERE solicitud_id = :id");
        $st->execute([':id' => $id]);
        $costos = $st->fetch() ?: null;

        // items
        $st = $this->pdo->prepare("
            SELECT i.*,
                   dp.nombre AS patologia_nombre,
                   ps.nombre AS producto_nombre,
                   ps.principio_activo,
                   ps.costo_hectarea AS producto_costo_hectarea
            FROM drones_solicitud_item i
            LEFT JOIN dron_patologias     dp ON dp.id = i.patologia_id
            LEFT JOIN dron_productos_stock ps ON ps.id = i.producto_id
            WHERE i.solicitud_id = :id
            ORDER BY i.id ASC
        ");
        $st->execute([':id' => $id]);
        $items = $st->fetchAll();

        // recetas por item
        $stRec = $this->pdo->prepare("SELECT * FROM drones_solicitud_item_receta WHERE solicitud_item_id = :sid ORDER BY id ASC");
        foreach ($items as &$it) {
            $stRec->execute([':sid' => $it['id']]);
            $it['recetas'] = $stRec->fetchAll() ?: [];
        }
        unset($it);

        // motivos
        $st = $this->pdo->prepare("
            SELECT m.*, dp.nombre AS patologia_nombre
            FROM drones_solicitud_motivo m
            LEFT JOIN dron_patologias dp ON dp.id = m.patologia_id
            WHERE m.solicitud_id = :id
            ORDER BY m.id ASC
        ");
        $st->execute([':id' => $id]);
        $motivos = $st->fetchAll();

        // rangos
        $st = $this->pdo->prepare("SELECT * FROM drones_solicitud_rango WHERE solicitud_id = :id ORDER BY id ASC");
        $st->execute([':id' => $id]);
        $rangos = $st->fetchAll();

        // parámetros de vuelo
        $st = $this->pdo->prepare("SELECT * FROM drones_solicitud_parametros WHERE solicitud_id = :id LIMIT 1");
        $st->execute([':id' => $id]);
        $parametros = $st->fetch() ?: null;

        // productor (usuario)
        $prod = null;
        if (!empty($sol['productor_id_real'])) {
            $st = $this->pdo->prepare("
                SELECT u.id, u.usuario, u.rol, u.permiso_ingreso, u.cuit, u.id_real,
                       ui.nombre, ui.direccion, ui.telefono, ui.correo
                FROM usuarios u
                LEFT JOIN usuarios_info ui ON ui.usuario_id = u.id
                WHERE u.id_real = :idr
                LIMIT 1
            ");
            $st->execute([':idr' => $sol['productor_id_real']]);
            $prod = $st->fetch() ?: null;

            if ($prod) {
                $st2 = $this->pdo->prepare("
                    SELECT rpc.*, u.usuario AS cooperativa_usuario 
                    FROM rel_productor_coop rpc
                    LEFT JOIN usuarios u ON u.id_real = rpc.cooperativa_id_real
                    WHERE rpc.productor_id_real = :idr
                ");
                $st2->execute([':idr' => $sol['productor_id_real']]);
                $prod['cooperativas'] = $st2->fetchAll() ?: [];
            }
        }

        return [
            'solicitud'   => $sol,
            'costos'      => $costos,
            'items'       => $items,
            'motivos'     => $motivos,
            'rangos'      => $rangos,
            'parametros'  => $parametros,
            'productor'   => $prod,
            'piloto'      => [
                'nombre' => $sol['piloto_nombre'] ?? null,
                'telefono' => $sol['piloto_telefono'] ?? null,
                'zona_asignada' => $sol['piloto_zona_asignada'] ?? null,
                'correo' => $sol['piloto_correo'] ?? null
            ],
            'forma_pago'  => [
                'nombre' => $sol['forma_pago_nombre'] ?? null,
                'descripcion' => $sol['forma_pago_descripcion'] ?? null
            ],
            'eventos'     => [] // opcional
        ];
    }
}
