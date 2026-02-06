<?php
class TractorPilotDashboardModel
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
            'message' => 'Los 3 archivos están funcionando correctamente.',
            'view' => 'views/tractor_pilot/tractor_pilot_dashboard.php',
            'controller' => 'controllers/tractor_pilot_dashboardController.php',
            'model' => 'models/tractor_pilot_dashboardModel.php'
        ];
    }

    private function construirFiltros(array $filtros, ?string $excluir = null): array
    {
        $condiciones = [];
        $params = [];

        if ($excluir !== 'cooperativa' && !empty($filtros['cooperativa_id'])) {
            $condiciones[] = 'cp.nom_cooperativa = :cooperativa_id';
            $params[':cooperativa_id'] = (string) $filtros['cooperativa_id'];
        }

        if ($excluir !== 'productor' && !empty($filtros['productor_id'])) {
            $condiciones[] = 'cp.productor = :productor_id';
            $params[':productor_id'] = (string) $filtros['productor_id'];
        }

        if ($excluir !== 'finca' && !empty($filtros['finca_id'])) {
            $condiciones[] = 'cp.finca_id = :finca_id';
            $params[':finca_id'] = (int) $filtros['finca_id'];
        }

        return [$condiciones, $params];
    }

    public function obtenerFincasParticipantes(array $filtros = []): array
    {
        [$condiciones, $params] = $this->construirFiltros($filtros);

        $sql = "SELECT DISTINCT
                    cm.id AS pedido_id,
                    cp.id AS participacion_id,
                    cp.nom_cooperativa AS cooperativa_nombre,
                    cp.productor AS productor_nombre,
                    cp.finca_id,
                    cp.superficie,
                    cp.variedad,
                    f.codigo_finca,
                    f.nombre_finca,
                    rf.id AS relevamiento_id
                FROM CosechaMecanica cm
                LEFT JOIN cosechaMecanica_coop_contrato_firma ccf
                    ON ccf.contrato_id = cm.id
                INNER JOIN cosechaMecanica_cooperativas_participacion cp
                    ON cp.contrato_id = cm.id
                LEFT JOIN prod_fincas f
                    ON f.id = cp.finca_id
                LEFT JOIN cosechaMecanica_relevamiento_finca rf
                    ON rf.participacion_id = cp.id
                WHERE 1 = 1";

        if (!empty($condiciones)) {
            $sql .= " AND " . implode(' AND ', $condiciones);
        }

        $sql .= " ORDER BY cp.nom_cooperativa ASC, cp.productor ASC, f.nombre_finca ASC, f.codigo_finca ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function obtenerRelevamientoPorParticipacion(int $participacionId): ?array
    {
        $sql = "SELECT
                    id,
                    productor_id,
                    finca_id,
                    participacion_id,
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
                FROM cosechaMecanica_relevamiento_finca
                WHERE participacion_id = :participacion_id
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':participacion_id' => $participacionId
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function guardarRelevamiento(int $participacionId, ?int $fincaId, array $data): array
    {
        $sqlExiste = "SELECT id
            FROM cosechaMecanica_relevamiento_finca
            WHERE participacion_id = :participacion_id
            LIMIT 1";
        $stmtExiste = $this->pdo->prepare($sqlExiste);
        $stmtExiste->execute([
            ':participacion_id' => $participacionId,
        ]);
        $existente = $stmtExiste->fetch(PDO::FETCH_ASSOC);

        $payload = [
            ':productor_id' => null,
            ':finca_id' => $fincaId,
            ':participacion_id' => $participacionId,
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
            $sqlUpdate = "UPDATE cosechaMecanica_relevamiento_finca
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
                WHERE participacion_id = :participacion_id";
            $stmtUpdate = $this->pdo->prepare($sqlUpdate);
            $stmtUpdate->execute($payload);

            return ['id' => (int) $existente['id'], 'accion' => 'actualizado'];
        }

        $sqlInsert = "INSERT INTO cosechaMecanica_relevamiento_finca (
                productor_id,
                finca_id,
                participacion_id,
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
                :participacion_id,
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
        $baseFrom = " FROM CosechaMecanica cm
            INNER JOIN cosechaMecanica_coop_contrato_firma ccf
                ON ccf.contrato_id = cm.id
            INNER JOIN cosechaMecanica_cooperativas_participacion cp
                ON cp.contrato_id = cm.id
            LEFT JOIN prod_fincas f
                ON f.id = cp.finca_id
            WHERE 1 = 1";

        [$condCoops, $paramsCoops] = $this->construirFiltros($filtros, 'cooperativa');
        $sqlCoops = "SELECT DISTINCT cp.nom_cooperativa AS id, cp.nom_cooperativa AS nombre" . $baseFrom;
        if (!empty($condCoops)) {
            $sqlCoops .= " AND " . implode(' AND ', $condCoops);
        }
        $sqlCoops .= " ORDER BY cp.nom_cooperativa ASC";
        $stmtCoops = $this->pdo->prepare($sqlCoops);
        $stmtCoops->execute($paramsCoops);
        $cooperativas = $stmtCoops->fetchAll(PDO::FETCH_ASSOC) ?: [];

        [$condProds, $paramsProds] = $this->construirFiltros($filtros, 'productor');
        $sqlProds = "SELECT DISTINCT cp.productor AS id, cp.productor AS nombre" . $baseFrom;
        if (!empty($condProds)) {
            $sqlProds .= " AND " . implode(' AND ', $condProds);
        }
        $sqlProds .= " ORDER BY cp.productor ASC";
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
