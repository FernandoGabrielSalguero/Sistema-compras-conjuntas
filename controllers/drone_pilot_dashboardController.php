<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/drone_pilot_dashboardModel.php';
header('Content-Type: application/json');

$model = new DronePilotDashboardModel($pdo);
$method = $_SERVER['REQUEST_METHOD'];


