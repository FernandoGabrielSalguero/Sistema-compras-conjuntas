<?php

class TractorPilotActualizacionesModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getEstado(): array
    {
        return [
            'message' => 'Estado OK.',
            'view' => 'views/tractor_pilot/tractor_pilot_actualizaciones.php',
            'controller' => 'controllers/tractor_pilot_actualizacionesController.php',
            'model' => 'models/tractor_pilot_actualizacionesModel.php'
        ];
    }

    private function construirFiltros(array $filtros, ?string $excluir = null): array
    {
        $condiciones = [];
        $params = [];

        if ($excluir !== 'cooperativa' && !empty($filtros['cooperativa_id'])) {
            $condiciones[] = 'coop.id = :cooperativa_id';
            $params[':cooperativa_id'] = (int) $filtros['cooperativa_id'];
        }

        if ($excluir !== 'productor' && !empty($filtros['productor_id'])) {
            $condiciones[] = 'prod.id = :productor_id';
            $params[':productor_id'] = (int) $filtros['productor_id'];
        }

        if ($excluir !== 'finca' && !empty($filtros['finca_id'])) {
            $condiciones[] = 'f.id = :finca_id';
            $params[':finca_id'] = (int) $filtros['finca_id'];
        }

        return [$condiciones, $params];
    }

    public function obtenerFincasParticipantes(array $filtros = []): array
    {
        [$condiciones, $params] = $this->construirFiltros($filtros);

        $sql = "SELECT
                    coop.id AS cooperativa_id,
                    coop.id_real AS cooperativa_id_real,
                    coop_info.nombre AS cooperativa_nombre,
                    prod.id AS productor_id,
                    prod.id_real AS productor_id_real,
                    prod_info.nombre AS productor_nombre,
                    f.id AS finca_id,
                    f.codigo_finca,
                    f.nombre_finca,
                    rf.id AS relevamiento_id
                FROM usuarios coop
                INNER JOIN rel_productor_coop rpc
                    ON rpc.cooperativa_id_real = coop.id_real
                INNER JOIN usuarios prod
                    ON prod.id_real = rpc.productor_id_real
                   AND prod.rol = 'productor'
                LEFT JOIN usuarios_info coop_info
                    ON coop_info.usuario_id = coop.id
                LEFT JOIN usuarios_info prod_info
                    ON prod_info.usuario_id = prod.id
                LEFT JOIN rel_productor_finca rpf
                    ON rpf.productor_id = prod.id
                LEFT JOIN prod_fincas f
                    ON f.id = rpf.finca_id
                LEFT JOIN relevamiento_fincas rf
                    ON rf.productor_id = prod.id
                   AND rf.finca_id = f.id
                WHERE coop.rol = 'cooperativa'";

        if (!empty($condiciones)) {
            $sql .= " AND " . implode(' AND ', $condiciones);
        }

        $sql .= " ORDER BY coop_info.nombre ASC, prod_info.nombre ASC, f.nombre_finca ASC, f.codigo_finca ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function crearFincaBasica(int $productorId, string $productorIdReal, string $codigoFinca, ?string $nombreFinca = null): array
    {
        $this->pdo->beginTransaction();
        try {
            $sqlFinca = "INSERT INTO prod_fincas (
                    codigo_finca,
                    productor_id_real,
                    nombre_finca
                ) VALUES (
                    :codigo_finca,
                    :productor_id_real,
                    :nombre_finca
                )";
            $stmtFinca = $this->pdo->prepare($sqlFinca);
            $stmtFinca->execute([
                ':codigo_finca' => $codigoFinca,
                ':productor_id_real' => $productorIdReal,
                ':nombre_finca' => $nombreFinca,
            ]);

            $fincaId = (int) $this->pdo->lastInsertId();

            $sqlRel = "INSERT INTO rel_productor_finca (
                    productor_id,
                    productor_id_real,
                    finca_id
                ) VALUES (
                    :productor_id,
                    :productor_id_real,
                    :finca_id
                )";
            $stmtRel = $this->pdo->prepare($sqlRel);
            $stmtRel->execute([
                ':productor_id' => $productorId,
                ':productor_id_real' => $productorIdReal,
                ':finca_id' => $fincaId,
            ]);

            $this->pdo->commit();

            return [
                'finca_id' => $fincaId,
                'accion' => 'creada',
            ];
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function obtenerRelevamientoPorProductorFinca(int $productorId, int $fincaId): ?array
    {
        $sql = "SELECT
                    id,
                    productor_id,
                    finca_id,
                    ancho_callejon_norte,
                    ancho_callejon_sur,
                    promedio_callejon,
                    interfilar,
                    cantidad_postes,
                    postes_mal_estado,
                    porcentaje_postes_mal_estado,
                    estructura_separadores,
                    agua_lavado,
                    preparacion_acequias,
                    preparacion_obstaculos,
                    observaciones
                FROM relevamiento_fincas
                WHERE productor_id = :productor_id
                  AND finca_id = :finca_id
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':productor_id' => $productorId,
            ':finca_id' => $fincaId
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function guardarRelevamiento(int $productorId, int $fincaId, array $data): array
    {
        $sqlExiste = "SELECT id
            FROM relevamiento_fincas
            WHERE productor_id = :productor_id
              AND finca_id = :finca_id
            LIMIT 1";
        $stmtExiste = $this->pdo->prepare($sqlExiste);
        $stmtExiste->execute([
            ':productor_id' => $productorId,
            ':finca_id' => $fincaId,
        ]);
        $existente = $stmtExiste->fetch(PDO::FETCH_ASSOC);

        $payload = [
            ':productor_id' => $productorId,
            ':finca_id' => $fincaId,
            ':ancho_callejon_norte' => $data['ancho_callejon_norte'],
            ':ancho_callejon_sur' => $data['ancho_callejon_sur'],
            ':promedio_callejon' => $data['promedio_callejon'],
            ':interfilar' => $data['interfilar'],
            ':cantidad_postes' => $data['cantidad_postes'],
            ':postes_mal_estado' => $data['postes_mal_estado'],
            ':porcentaje_postes_mal_estado' => $data['porcentaje_postes_mal_estado'],
            ':estructura_separadores' => $data['estructura_separadores'],
            ':agua_lavado' => $data['agua_lavado'],
            ':preparacion_acequias' => $data['preparacion_acequias'],
            ':preparacion_obstaculos' => $data['preparacion_obstaculos'],
            ':observaciones' => $data['observaciones'],
        ];

        if ($existente) {
            $sqlUpdate = "UPDATE relevamiento_fincas
                SET ancho_callejon_norte = :ancho_callejon_norte,
                    ancho_callejon_sur = :ancho_callejon_sur,
                    promedio_callejon = :promedio_callejon,
                    interfilar = :interfilar,
                    cantidad_postes = :cantidad_postes,
                    postes_mal_estado = :postes_mal_estado,
                    porcentaje_postes_mal_estado = :porcentaje_postes_mal_estado,
                    estructura_separadores = :estructura_separadores,
                    agua_lavado = :agua_lavado,
                    preparacion_acequias = :preparacion_acequias,
                    preparacion_obstaculos = :preparacion_obstaculos,
                    observaciones = :observaciones
                WHERE productor_id = :productor_id
                  AND finca_id = :finca_id";
            $stmtUpdate = $this->pdo->prepare($sqlUpdate);
            $stmtUpdate->execute($payload);

            return ['id' => (int) $existente['id'], 'accion' => 'actualizado'];
        }

        $sqlInsert = "INSERT INTO relevamiento_fincas (
                productor_id,
                finca_id,
                ancho_callejon_norte,
                ancho_callejon_sur,
                promedio_callejon,
                interfilar,
                cantidad_postes,
                postes_mal_estado,
                porcentaje_postes_mal_estado,
                estructura_separadores,
                agua_lavado,
                preparacion_acequias,
                preparacion_obstaculos,
                observaciones
            ) VALUES (
                :productor_id,
                :finca_id,
                :ancho_callejon_norte,
                :ancho_callejon_sur,
                :promedio_callejon,
                :interfilar,
                :cantidad_postes,
                :postes_mal_estado,
                :porcentaje_postes_mal_estado,
                :estructura_separadores,
                :agua_lavado,
                :preparacion_acequias,
                :preparacion_obstaculos,
                :observaciones
            )";

        $stmtInsert = $this->pdo->prepare($sqlInsert);
        $stmtInsert->execute($payload);

        return ['id' => (int) $this->pdo->lastInsertId(), 'accion' => 'creado'];
    }

    public function obtenerOpcionesFiltros(array $filtros = []): array
    {
        $baseFrom = " FROM usuarios coop
            INNER JOIN rel_productor_coop rpc
                ON rpc.cooperativa_id_real = coop.id_real
            INNER JOIN usuarios prod
                ON prod.id_real = rpc.productor_id_real
               AND prod.rol = 'productor'
            LEFT JOIN usuarios_info coop_info
                ON coop_info.usuario_id = coop.id
            LEFT JOIN usuarios_info prod_info
                ON prod_info.usuario_id = prod.id
            LEFT JOIN rel_productor_finca rpf
                ON rpf.productor_id = prod.id
            LEFT JOIN prod_fincas f
                ON f.id = rpf.finca_id
            WHERE coop.rol = 'cooperativa'";

        [$condCoops, $paramsCoops] = $this->construirFiltros($filtros, 'cooperativa');
        $sqlCoops = "SELECT DISTINCT coop.id, coop_info.nombre" . $baseFrom;
        if (!empty($condCoops)) {
            $sqlCoops .= " AND " . implode(' AND ', $condCoops);
        }
        $sqlCoops .= " ORDER BY coop_info.nombre ASC";
        $stmtCoops = $this->pdo->prepare($sqlCoops);
        $stmtCoops->execute($paramsCoops);
        $cooperativas = $stmtCoops->fetchAll(PDO::FETCH_ASSOC) ?: [];

        [$condProds, $paramsProds] = $this->construirFiltros($filtros, 'productor');
        $sqlProds = "SELECT DISTINCT prod.id, prod_info.nombre" . $baseFrom;
        if (!empty($condProds)) {
            $sqlProds .= " AND " . implode(' AND ', $condProds);
        }
        $sqlProds .= " ORDER BY prod_info.nombre ASC";
        $stmtProds = $this->pdo->prepare($sqlProds);
        $stmtProds->execute($paramsProds);
        $productores = $stmtProds->fetchAll(PDO::FETCH_ASSOC) ?: [];

        [$condFincas, $paramsFincas] = $this->construirFiltros($filtros, 'finca');
        $sqlFincas = "SELECT DISTINCT f.id, f.codigo_finca, f.nombre_finca" . $baseFrom;
        if (!empty($condFincas)) {
            $sqlFincas .= " AND " . implode(' AND ', $condFincas);
        }
        $sqlFincas .= " AND f.id IS NOT NULL
            ORDER BY f.nombre_finca ASC, f.codigo_finca ASC";
        $stmtFincas = $this->pdo->prepare($sqlFincas);
        $stmtFincas->execute($paramsFincas);
        $fincas = $stmtFincas->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return [
            'cooperativas' => $cooperativas,
            'productores' => $productores,
            'fincas' => $fincas,
        ];
    }
}
