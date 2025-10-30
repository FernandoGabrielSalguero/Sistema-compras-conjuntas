<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/ing_ServiciosModel.php';

$model = new IngServiciosModel($pdo);

// Autenticación básica por rol
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ingeniero') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit;
}




