<?php
// controllers/productosTableController.php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/ProductosModel.php';

$productosModel = new ProductosModel();
$productos = $productosModel->obtenerTodos();

if (!$productos) {
    echo '<tr><td colspan="6">No hay productos cargados.</td></tr>';
    exit;
}

foreach ($productos as $producto) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars($producto['Nombre_producto']) . '</td>';
    echo '<td>' . htmlspecialchars($producto['Detalle_producto']) . '</td>';
    echo '<td>$' . number_format($producto['Precio_producto'], 2, ',', '.') . '</td>';
    echo '<td>' . htmlspecialchars($producto['Unidad_medida_venta']) . '</td>';
    echo '<td>' . htmlspecialchars($producto['categoria']) . '</td>';
    echo '<td>' . htmlspecialchars($producto['alicuota']) . '%</td>';
    echo '<td>
            <button class="btn-icon" onclick="abrirModalEditar(' . $producto['id'] . ')">
                <span class="material-icons">edit</span>
            </button>
            <button class="btn-icon" onclick="eliminarProducto(' . $producto['id'] . ')">
                <span class="material-icons">delete</span>
            </button>
          </td>';
    echo '</tr>';
}
?>
