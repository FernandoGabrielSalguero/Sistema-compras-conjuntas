<?php
// views/partials/drones/controller/drone_list_controller.php

// Cargar el modelo del módulo
require_once __DIR__ . '/../model/drone_list_model.php';

// Instanciar el modelo
$model = new DroneListModel();

// Preparar datos mínimos para la vista (por ahora, sólo texto)
$data = [
    'titulo'  => 'Solicitudes de vuelo con drones',
    'mensaje' => $model->getMensajeInicial(), // texto simple desde el modelo
];

// Hacer disponibles las claves de $data como variables en la vista
extract($data, EXTR_SKIP);

// Incluir la vista: si no existe, mostramos un aviso
$viewPath = __DIR__ . '/../view/drone_list_view.php';
if (is_file($viewPath)) {
    require $viewPath;
} else {
    echo '<div class="card"><p>No se encontró la vista <code>drone_list_view.php</code>.</p></div>';
}
