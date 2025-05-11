<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/ProductosModel.php';

try {
    // Cambiado para leer de $_GET
    if (!isset($_GET['id'])) {
        throw new Exception('ID no proporcionado.');
    }

    $id = intval($_GET['id']);
    if ($id <= 0) {
        throw new Exception('ID inválido.');
    }

    $productosModel = new ProductosModel();
    $resultado = $productosModel->eliminarProducto($id);

    if (!$resultado) {
        throw new Exception('Error al eliminar producto.');
    }

    echo json_encode(['success' => true, 'message' => 'Producto eliminado correctamente.']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

