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

        if ($excluir !== 'contrato' && !empty($filtros['contrato_id'])) {
            $condiciones[] = 'p.contrato_id = :contrato_id';
            $params[':contrato_id'] = (int) $filtros['contrato_id'];
        }

        if ($excluir !== 'cooperativa' && !empty($filtros['cooperativa'])) {
            $condiciones[] = 'p.nom_cooperativa = :nom_cooperativa';
            $params[':nom_cooperativa'] = (string) $filtros['cooperativa'];
        }

        if ($excluir !== 'productor' && !empty($filtros['productor'])) {
            $condiciones[] = 'p.productor = :productor';
            $params[':productor'] = (string) $filtros['productor'];
        }

        if ($excluir !== 'finca' && !empty($filtros['finca_id'])) {
            $condiciones[] = 'p.finca_id = :finca_id';
            $params[':finca_id'] = (int) $filtros['finca_id'];
        }

        return [$condiciones, $params];
    }

    public function obtenerFincasParticipantes(array $filtros = []): array
    {
        [$condiciones, $params] = $this->construirFiltros($filtros);

        $sql = "SELECT
                    p.id,
                    p.contrato_id,
                    c.nombre AS contrato_nombre,
                    p.nom_cooperativa,
                    p.productor,
                    p.superficie,
                    p.variedad,
                    p.prod_estimada,
                    p.fecha_estimada,
                    p.km_finca,
                    p.flete,
                    p.seguro_flete,
                    p.finca_id,
                    f.codigo_finca,
                    f.nombre_finca
                FROM cosechaMecanica_cooperativas_participacion p
                INNER JOIN CosechaMecanica c
                    ON c.id = p.contrato_id
                LEFT JOIN prod_fincas f
                    ON f.id = p.finca_id
                WHERE p.firma = 1
                  AND EXISTS (
                      SELECT 1
                      FROM cosechaMecanica_coop_contrato_firma cf
                      WHERE cf.contrato_id = p.contrato_id
                        AND cf.acepto = 1
                  )";

        if (!empty($condiciones)) {
            $sql .= " AND " . implode(' AND ', $condiciones);
        }

        $sql .= " ORDER BY c.fecha_apertura DESC, p.nom_cooperativa ASC, p.productor ASC, p.id ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function obtenerOpcionesFiltros(array $filtros = []): array
    {
        $baseFrom = " FROM cosechaMecanica_cooperativas_participacion p
            INNER JOIN CosechaMecanica c
                ON c.id = p.contrato_id
            LEFT JOIN prod_fincas f
                ON f.id = p.finca_id
            WHERE p.firma = 1
              AND EXISTS (
                  SELECT 1
                  FROM cosechaMecanica_coop_contrato_firma cf
                  WHERE cf.contrato_id = p.contrato_id
                    AND cf.acepto = 1
              )";

        [$condContratos, $paramsContratos] = $this->construirFiltros($filtros, 'contrato');
        $sqlContratos = "SELECT DISTINCT c.id, c.nombre" . $baseFrom;
        if (!empty($condContratos)) {
            $sqlContratos .= " AND " . implode(' AND ', $condContratos);
        }
        $sqlContratos .= " ORDER BY c.fecha_apertura DESC, c.nombre ASC";
        $stmtContratos = $this->pdo->prepare($sqlContratos);
        $stmtContratos->execute($paramsContratos);
        $contratos = $stmtContratos->fetchAll(PDO::FETCH_ASSOC) ?: [];

        [$condCoops, $paramsCoops] = $this->construirFiltros($filtros, 'cooperativa');
        $sqlCoops = "SELECT DISTINCT p.nom_cooperativa" . $baseFrom;
        if (!empty($condCoops)) {
            $sqlCoops .= " AND " . implode(' AND ', $condCoops);
        }
        $sqlCoops .= " ORDER BY p.nom_cooperativa ASC";
        $stmtCoops = $this->pdo->prepare($sqlCoops);
        $stmtCoops->execute($paramsCoops);
        $cooperativas = array_map(
            fn($row) => $row['nom_cooperativa'],
            $stmtCoops->fetchAll(PDO::FETCH_ASSOC) ?: []
        );

        [$condProds, $paramsProds] = $this->construirFiltros($filtros, 'productor');
        $sqlProds = "SELECT DISTINCT p.productor" . $baseFrom;
        if (!empty($condProds)) {
            $sqlProds .= " AND " . implode(' AND ', $condProds);
        }
        $sqlProds .= " ORDER BY p.productor ASC";
        $stmtProds = $this->pdo->prepare($sqlProds);
        $stmtProds->execute($paramsProds);
        $productores = array_map(
            fn($row) => $row['productor'],
            $stmtProds->fetchAll(PDO::FETCH_ASSOC) ?: []
        );

        [$condFincas, $paramsFincas] = $this->construirFiltros($filtros, 'finca');
        $sqlFincas = "SELECT DISTINCT p.finca_id, f.codigo_finca, f.nombre_finca" . $baseFrom;
        if (!empty($condFincas)) {
            $sqlFincas .= " AND " . implode(' AND ', $condFincas);
        }
        $sqlFincas .= " AND p.finca_id IS NOT NULL
            ORDER BY f.nombre_finca ASC, f.codigo_finca ASC";
        $stmtFincas = $this->pdo->prepare($sqlFincas);
        $stmtFincas->execute($paramsFincas);
        $fincas = $stmtFincas->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return [
            'contratos' => $contratos,
            'cooperativas' => $cooperativas,
            'productores' => $productores,
            'fincas' => $fincas,
        ];
    }
}
