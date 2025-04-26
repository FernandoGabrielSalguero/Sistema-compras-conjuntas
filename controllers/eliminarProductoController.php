<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/ProductosModel.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

parse_str(file_get_contents("php://input"), $delete_vars);

$id = $delete_vars['id'] ?? '';

if (empty($id)) {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
    exit;
}

$productosModel = new ProductosModel();

if ($productosModel->eliminarProducto($id)) {
    echo json_encode(['success' => true, 'message' => 'Producto eliminado correctamente.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar el producto.']);
}
?>
