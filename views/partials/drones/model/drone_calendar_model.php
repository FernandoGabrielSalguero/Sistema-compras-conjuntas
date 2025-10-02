<?php
declare(strict_types=1);

class DroneCalendarModel
{
    /** @var PDO */
    public PDO $pdo;

    /**
     * Pilotos: usuarios con rol piloto_drone. Devuelve id y nombre.
     * @return array<int, array{id:int,nombre:string}>
     */
    public function getPilots(): array
    {
        $sql = "
            SELECT u.id AS id, COALESCE(ui.nombre, u.usuario) AS nombre
            FROM usuarios u
            LEFT JOIN usuarios_info ui ON ui.usuario_id = u.id
            WHERE u.rol = 'piloto_drone'
            ORDER BY nombre ASC
        ";
        $rows = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
        return array_map(fn($r)=>['id'=>(int)$r['id'],'nombre'=>$r['nombre']], $rows);
    }

    /**
     * Zonas distintas desde usuarios_info de pilotos.
     * @return array<int, string>
     */
    public function getZones(): array
    {
        $sql = "
            SELECT DISTINCT TRIM(ui.zona_asignada) AS zona
            FROM usuarios u
            INNER JOIN usuarios_info ui ON ui.usuario_id = u.id
            WHERE u.rol = 'piloto_drone' AND ui.zona_asignada IS NOT NULL AND ui.zona_asignada <> ''
            ORDER BY zona ASC
        ";
        $rows = $this->pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN) ?: [];
        return array_values($rows);
    }

    /**
     * Visitas entre fechas, opcionalmente filtradas por piloto y/o zona.
     * @return array<int, array<string,mixed>>
     */
    public function getVisitsBetween(string $fromDate, string $toDate, ?int $pilotoId = null, ?string $zona = null): array
    {
        $sql = "
            SELECT
                ds.fecha_visita                              AS fecha,
                TIME_FORMAT(ds.hora_visita_desde, '%H:%i')  AS hora_desde,
                TIME_FORMAT(ds.hora_visita_hasta, '%H:%i')  AS hora_hasta,
                COALESCE(ui_prod.nombre, u_prod.usuario)    AS nombre,
                COALESCE(ui_pil.nombre, u_pil.usuario)      AS piloto,
                ui_pil.zona_asignada                        AS zona
            FROM drones_solicitud ds
            INNER JOIN usuarios u_prod
                ON u_prod.id_real = ds.productor_id_real
            LEFT JOIN usuarios_info ui_prod
                ON ui_prod.usuario_id = u_prod.id
            LEFT JOIN usuarios u_pil
                ON u_pil.id = ds.piloto_id
            LEFT JOIN usuarios_info ui_pil
                ON ui_pil.usuario_id = u_pil.id
            WHERE ds.fecha_visita BETWEEN :from AND :to
        ";

        $params = [':from'=>$fromDate, ':to'=>$toDate];

        if ($pilotoId !== null) {
            $sql .= " AND ds.piloto_id = :piloto_id";
            $params[':piloto_id'] = $pilotoId;
        }
        if ($zona !== null && $zona !== '') {
            $sql .= " AND ui_pil.zona_asignada = :zona";
            $params[':zona'] = $zona;
        }

        $sql .= " ORDER BY ds.fecha_visita ASC, ds.hora_visita_desde ASC";

        $stmt = $this->pdo->prepare($sql);
        foreach($params as $k=>$v){ $stmt->bindValue($k, $v); }
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        return $rows;
    }

    /**
     * Notas entre fechas (para el calendario).
     * @return array<int, array{id:int,fecha:string,texto:string}>
     */
    public function getNotesBetween(string $fromDate, string $toDate, ?int $pilotoId = null, ?string $zona = null): array
    {
        $sql = "
            SELECT id, fecha, texto
            FROM drones_calendario_notas
            WHERE fecha BETWEEN :from AND :to
        ";
        $params = [':from'=>$fromDate, ':to'=>$toDate];

        if ($pilotoId !== null) {
            $sql .= " AND (piloto_id = :piloto_id OR piloto_id IS NULL)";
            $params[':piloto_id'] = $pilotoId;
        }
        if ($zona !== null && $zona !== '') {
            $sql .= " AND (zona = :zona OR zona IS NULL)";
            $params[':zona'] = $zona;
        }

        $sql .= " ORDER BY fecha ASC, id ASC";
        $stmt = $this->pdo->prepare($sql);
        foreach($params as $k=>$v){ $stmt->bindValue($k, $v); }
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        return array_map(fn($r)=>['id'=>(int)$r['id'],'fecha'=>$r['fecha'],'texto'=>$r['texto']], $rows);
    }

    public function createNote(string $fecha, string $texto, ?int $pilotoId = null, ?string $zona = null, ?string $actor = null): int
    {
        $sql = "INSERT INTO drones_calendario_notas (fecha, texto, piloto_id, zona, created_by) VALUES (:f,:t,:p,:z,:c)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':f', $fecha);
        $stmt->bindValue(':t', $texto);
        $stmt->bindValue(':p', $pilotoId, $pilotoId===null?PDO::PARAM_NULL:PDO::PARAM_INT);
        $stmt->bindValue(':z', $zona, $zona===null?PDO::PARAM_NULL:PDO::PARAM_STR);
        $stmt->bindValue(':c', $actor);
        $stmt->execute();
        return (int)$this->pdo->lastInsertId();
    }

    public function updateNote(int $id, string $texto): bool
    {
        $sql = "UPDATE drones_calendario_notas SET texto = :t, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':t', $texto);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function deleteNote(int $id): bool
    {
        $sql = "DELETE FROM drones_calendario_notas WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
