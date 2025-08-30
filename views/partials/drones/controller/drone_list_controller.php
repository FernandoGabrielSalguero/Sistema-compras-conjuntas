<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/sve_operativosModel.php';


$model = new DroneListModel($pdo);
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {


}


