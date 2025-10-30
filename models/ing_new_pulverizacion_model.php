<?php

declare(strict_types=1);

final class IngNewPulverizacionModel
{
    /** @var PDO */
    public PDO $pdo;

    /* ======= Catálogos / búsquedas ======= */

    public function formasPago(): array
    {
        $sql = "SELECT id,nombre FROM dron_formas_pago WHERE activo='si' ORDER BY nombre";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function patologias(): array
    {
        $sql = "SELECT id,nombre FROM dron_patologias WHERE activo='si' ORDER BY nombre";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cooperativas(): array
    {
        $sql = "SELECT usuario,id_real FROM usuarios WHERE rol='cooperativa' ORDER BY usuario";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Rangos simples */
    public function rangos(): array
    {
        return [
            ['rango' => 'octubre_q1', 'label' => 'Primera quincena de Octubre'],
            ['rango' => 'octubre_q2', 'label' => 'Segunda quincena de Octubre'],
            ['rango' => 'noviembre_q1', 'label' => 'Primera quincena de Noviembre'],
            ['rango' => 'noviembre_q2', 'label' => 'Segunda quincena de Noviembre'],
            ['rango' => 'diciembre_q1', 'label' => 'Primera quincena de Diciembre'],
            ['rango' => 'diciembre_q2', 'label' => 'Segunda quincena de Diciembre'],
            ['rango' => 'enero_q1', 'label' => 'Primera quincena de Enero'],
            ['rango' => 'enero_q2', 'label' => 'Segunda quincena de Enero'],
            ['rango' => 'febrero_q1', 'label' => 'Primera quincena de Febrero'],
            ['rango' => 'febrero_q2', 'label' => 'Segunda quincena de Febrero'],
        ];
    }

    /** Búsqueda de productores con filtro por rol (ingeniero/cooperativa/sve/productor) */
    public function buscarUsuariosFiltrado(string $q, string $rol, string $idReal, ?string $coopId = null): array
    {
        $like = '%' . $q . '%';

        if ($rol === 'sve') {
            if ($coopId) {
                $st = $this->pdo->prepare("SELECT u.usuario,u.id_real
                  FROM usuarios u
                  JOIN rel_productor_coop rpc ON rpc.productor_id_real=u.id_real
                 WHERE u.rol='productor' AND u.permiso_ingreso='Habilitado'
                   AND rpc.cooperativa_id_real=? AND u.usuario LIKE ?
                 ORDER BY u.usuario LIMIT 10");
                $st->execute([$coopId, $like]);
                return $st->fetchAll(PDO::FETCH_ASSOC);
            }
            $st = $this->pdo->prepare("SELECT usuario,id_real FROM usuarios
              WHERE rol='productor' AND permiso_ingreso='Habilitado' AND usuario LIKE ?
              ORDER BY usuario LIMIT 10");
            $st->execute([$like]);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        }

        if ($rol === 'ingeniero') {
            $st = $this->pdo->prepare("SELECT DISTINCT u.usuario,u.id_real
              FROM usuarios u
              JOIN rel_productor_coop rpc ON rpc.productor_id_real=u.id_real
              JOIN rel_coop_ingeniero rci ON rci.cooperativa_id_real=rpc.cooperativa_id_real
             WHERE u.rol='productor' AND u.permiso_ingreso='Habilitado'
               AND rci.ingeniero_id_real=? AND u.usuario LIKE ?
             ORDER BY u.usuario LIMIT 10");
            $st->execute([$idReal, $like]);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        }

        if ($rol === 'cooperativa') {
            $st = $this->pdo->prepare("SELECT u.usuario,u.id_real
              FROM usuarios u
              JOIN rel_productor_coop rpc ON rpc.productor_id_real=u.id_real
             WHERE u.rol='productor' AND u.permiso_ingreso='Habilitado'
               AND rpc.cooperativa_id_real=? AND u.usuario LIKE ?
             ORDER BY u.usuario LIMIT 10");
            $st->execute([$idReal, $like]);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        }

        if ($rol === 'productor') {
            $st = $this->pdo->prepare("SELECT usuario,id_real FROM usuarios
              WHERE rol='productor' AND permiso_ingreso='Habilitado' AND id_real=? AND usuario LIKE ?
              ORDER BY usuario LIMIT 10");
            $st->execute([$idReal, $like]);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        }

        return [];
    }

    /** Costo base/ha vigente */
    public function costoBaseHectarea(): array
    {
        $sql = "SELECT costo, COALESCE(moneda,'Pesos') AS moneda, updated_at
                  FROM dron_costo_hectarea
              ORDER BY updated_at DESC
                 LIMIT 1";
        $row = $this->pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return ['costo' => 0.0, 'moneda' => 'Pesos', 'updated_at' => null];
        }
        $row['costo'] = (float)$row['costo'];
        return $row;
    }

    /* ======= Correo / datos básicos ======= */

    /** Correo preferente por id_real (usuarios_info.correo si existe, sino email/correo/mail en usuarios) */
    public function correoPreferidoPorIdReal(string $idReal): ?string
    {
        if ($idReal === '') return null;
        // usuarios_info
        $st = $this->pdo->prepare("SELECT ui.correo
                                     FROM usuarios u
                                LEFT JOIN usuarios_info ui ON ui.usuario_id=u.id
                                    WHERE u.id_real=? LIMIT 1");
        $st->execute([$idReal]);
        $v = $st->fetchColumn();
        if ($v && trim((string)$v) !== '') return trim((string)$v);

        // fallback en usuarios
        $st = $this->pdo->prepare("SELECT COALESCE(NULLIF(TRIM(email),''),NULLIF(TRIM(correo),''),NULLIF(TRIM(mail),'')) AS email
                                     FROM usuarios WHERE id_real=? LIMIT 1");
        $st->execute([$idReal]);
        $v = $st->fetchColumn();
        return $v ? (string)$v : null;
    }

    public function nombrePorIdReal(string $idReal): ?string
    {
        $st = $this->pdo->prepare("SELECT usuario FROM usuarios WHERE id_real=? LIMIT 1");
        $st->execute([$idReal]);
        $v = $st->fetchColumn();
        return $v ? (string)$v : null;
    }

    /* ======= Creación de solicitud ======= */

    public function crearSolicitud(array $d): array
    {
        $this->pdo->beginTransaction();
        try {
            // 1) solicitud
            $st = $this->pdo->prepare("INSERT INTO drones_solicitud
              (productor_id_real,representante,linea_tension,zona_restringida,corriente_electrica,agua_potable,
               libre_obstaculos,area_despegue,superficie_ha,forma_pago_id,coop_descuento_nombre,
               dir_provincia,dir_localidad,dir_calle,dir_numero,observaciones,estado)
               VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, 'ingresada')");
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
                $d['observaciones']
            ]);
            $sid = (int)$this->pdo->lastInsertId();

            // 2) motivo
            $stm = $this->pdo->prepare("INSERT INTO drones_solicitud_motivo (solicitud_id,patologia_id,es_otros) VALUES (?,?,0)");
            $stm->execute([$sid, $d['patologia_id']]);

            // 3) rango
            $str = $this->pdo->prepare("INSERT INTO drones_solicitud_rango (solicitud_id,rango) VALUES (?,?)");
            $str->execute([$sid, $d['rango']]);

            // 4) costos (base = costoHa * ha)
            $row = $this->costoBaseHectarea();
            $costoHa = (float)$row['costo'];
            $moneda = (string)$row['moneda'];
            $baseHa = (float)$d['superficie_ha'];
            $baseTotal = $costoHa * $baseHa;
            $stc = $this->pdo->prepare("INSERT INTO drones_solicitud_costos
              (solicitud_id,moneda,costo_base_por_ha,base_ha,base_total,productos_total,total,desglose_json)
              VALUES (?,?,?,?,?,0,?,NULL)");
            $stc->execute([$sid, $moneda, $costoHa, $baseHa, $baseTotal, $baseTotal]);

            // 5) evento
            $ste = $this->pdo->prepare("INSERT INTO drones_solicitud_evento (solicitud_id,tipo,detalle,actor)
              VALUES (?,'creada','Solicitud ingresada por formulario (ingeniero)','sistema')");
            $ste->execute([$sid]);

            $this->pdo->commit();
            return ['ok' => true, 'id' => $sid];
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
