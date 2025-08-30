<?php


require_once __DIR__ . '/../../../config.php';

class DroneListModel
{
    /**
     * Devuelve un texto inicial simple para la vista.
     * Más adelante podés cambiarlo por datos reales desde la BD.
     */
    public function getMensajeInicial(): string
    {
        return 'Este módulo mostrará las solicitudes. Próximamente verás el listado y los filtros acá.';
    }
}
