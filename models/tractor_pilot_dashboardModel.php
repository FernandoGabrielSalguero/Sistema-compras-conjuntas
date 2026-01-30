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
}
