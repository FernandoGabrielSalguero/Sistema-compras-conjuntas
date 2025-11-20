<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/CoopCosechaMecanicaModel.php';

$model = new CoopCosechaMecanicaModel($pdo);

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'cooperativa') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit;
}

$cooperativa_id = $_SESSION['id_real'] ?? null;



exit;
