<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');


require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../model/drone_variables_controller.php';


$model = new droneVariablesModel();
$model->pdo = $pdo;
