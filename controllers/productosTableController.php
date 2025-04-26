<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/ProductosModel.php';

$productosModel = new ProductosModel();
$productos = $productosModel->obtenerTodos();

if (!$productos) {
    echo '<tr><td colspan="7">No hay productos cargados.</td></tr>';
    exit;
}

foreach ($productos as $producto) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars($producto['Nombre_producto'] ?? '-') . '</td>';
    echo '<td>' . htmlspecialchars($producto['Detalle_producto'] ?? '-') . '</td>';
    echo '<td>$' . number_format(floatval($producto['Precio_producto'] ?? 0), 2, ',', '.') . '</td>';
    echo '<td>' . htmlspecialchars($producto['Unidad_Medida_venta'] ?? '-') . '</td>';
    echo '<td>' . htmlspecialchars($producto['categoria'] ?? '-') . '</td>';
    echo '<td>' . htmlspecialchars($producto['alicuota'] ?? '-') . '%</td>';
    

    // 👉 Columna de botones
    if (isset($producto['id'])) {
        echo '<td style="text-align: center;">';
        echo '<button class="btn-icon" onclick="abrirModalEditar(' . intval($producto['id']) . ')">';
        echo '<span class="material-icons">edit</span>';
        echo '</button>';
        echo ' ';
        echo '<button class="btn-icon" onclick="eliminarProducto(' . intval($producto['id']) . ')">';
        echo '<span class="material-icons">delete</span>';
        echo '</button>';
        echo '</td>';
    } else {
        echo '<td>-</td>';
    }

    echo '</tr>';
}
?>
