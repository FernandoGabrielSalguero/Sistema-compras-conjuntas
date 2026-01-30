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

    public function obtenerFincasParticipantes(): array
    {
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
                  )
                ORDER BY c.fecha_apertura DESC, p.nom_cooperativa ASC, p.productor ASC, p.id ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
