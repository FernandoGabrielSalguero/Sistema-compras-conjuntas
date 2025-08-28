<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/sve_pulverizacionDroneModel.php';

$publicacionesModel = new DroneModel();

// al final del archivo:
$action = $_GET['action'] ?? $_POST['action'] ?? null;

switch ($action) {
    case 'get_categorias':
        echo json_encode($publicacionesModel->obtenerCategorias());
        break;
}
