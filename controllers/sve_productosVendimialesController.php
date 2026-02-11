<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/sve_productosVendimialesModel.php';

header('Content-Type: application/json; charset=utf-8');

$model = new ProductosVendimialesModel($pdo);

if (isset($_GET['servicio_id'])) {
    $servicioId = (int)$_GET['servicio_id'];
    if ($servicioId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Servicio inválido.']);
        exit;
    }
    try {
        $productos = $model->obtenerPorServicio($servicioId);
        echo json_encode(['success' => true, 'productos' => $productos]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al cargar productos: ' . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Falta servicio_id.']);
