<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/sve_serviciosVendimialesPedidosModel.php';

header('Content-Type: application/json; charset=utf-8');

$model = new ServiciosVendimialesPedidosModel($pdo);

try {
    echo json_encode(['success' => true, 'pedidos' => $model->obtenerTodos()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al cargar pedidos: ' . $e->getMessage()]);
}
