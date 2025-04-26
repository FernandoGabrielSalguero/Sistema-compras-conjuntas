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
    echo '<td>' . htmlspecialchars($producto['Unidad_medida_venta'] ?? '-') . '</td>';
    echo '<td>' . htmlspecialchars($producto['categoria'] ?? '-') . '</td>';
    echo '<td>' . htmlspecialchars($producto['alicuota'] ?? '-') . '%</td>';
    
    if (isset($producto['id'])) {
        echo '<td>
                <button class="btn-icon" onclick="abrirModalEditar(' . intval($producto['id']) . ')">
                    <span class="material-icons">edit</span>
                </button>
                <button class="btn-icon" onclick="eliminarProducto(' . intval($producto['id']) . ')">
                    <span class="material-icons">delete</span>
                </button>
              </td>';
    } else {
        echo '<td>-</td>';
    }

    echo '</tr>';
}
?>
