<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/ProductosModel.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$id = $_POST['id'] ?? '';
$Nombre_producto = $_POST['Nombre_producto'] ?? '';
$Detalle_producto = $_POST['Detalle_producto'] ?? '';
$Precio_producto = $_POST['Precio_producto'] ?? '';
$Unidad_medida_venta = $_POST['Unidad_medida_venta'] ?? '';
$categoria = $_POST['categoria'] ?? '';
$alicuota = $_POST['alicuota'] ?? '';

if (empty($id) || empty($Nombre_producto) || !is_numeric($Precio_producto) || $Precio_producto < 0) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
    exit;
}

$productosModel = new ProductosModel();

if ($productosModel->actualizarProducto($id, $Nombre_producto, $Detalle_producto, $Precio_producto, $Unidad_medida_venta, $categoria, $alicuota)) {
    echo json_encode(['success' => true, 'message' => 'Producto actualizado exitosamente.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el producto.']);
}
?>
