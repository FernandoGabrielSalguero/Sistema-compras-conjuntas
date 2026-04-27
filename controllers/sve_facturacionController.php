<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/sve_facturacionModel.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'sve') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit;
}

$model = new SveFacturacionModel($pdo);

echo json_encode([
    'success' => true,
    'modulo' => $model->obtenerEstadoModulo()
]);
