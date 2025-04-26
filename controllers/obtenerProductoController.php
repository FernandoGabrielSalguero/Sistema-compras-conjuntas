<?php
// controllers/obtenerProductoController.php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/ProductosModel.php';

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
    exit;
}

$id = (int)$_GET['id'];

$productosModel = new ProductosModel();
$producto = $productosModel->obtenerPorId($id);

if ($producto) {
    echo json_encode(['success' => true, 'producto' => $producto]);
} else {
    echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
}
?>
