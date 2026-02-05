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
            'message' => 'Los 3 archivos están funcionando correctamente.',
            'view' => 'views/tractor_pilot/tractor_pilot_actualizaciones.php',
            'controller' => 'controllers/tractor_pilot_actualizacionesController.php',
            'model' => 'models/tractor_pilot_actualizacionesModel.php'
        ];
    }

    public function obtenerActualizaciones(): array
    {
        return [];
    }
}

