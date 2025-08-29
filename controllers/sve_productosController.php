<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/sve_productosModel.php';

$productosModel = new ProductosModel();
$productos = $productosModel->obtenerTodos();




// Obtener acción del request
$accion = $_GET['accion'] ?? $_POST['accion'] ?? null;

switch ($accion) {
    case 'obtener':
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            exit;
        }
        $producto = $productosModel->obtenerPorId($_GET['id']);
        if ($producto) {
            echo json_encode(['success' => true, 'producto' => $producto]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
        }
        break;

    case 'eliminar':
        $id = $_POST['id'] ?? null;
        if (!$id || !is_numeric($id)) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            exit;
        }
        $ok = $productosModel->eliminarProducto($id);
        echo json_encode(['success' => $ok, 'message' => $ok ? 'Producto eliminado' : 'No se pudo eliminar']);
        break;

    case 'actualizar':
        $id = $_POST['id'] ?? null;
        if (!$id || !is_numeric($id)) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            exit;
        }
$ok = $productosModel->actualizarProducto(
    $id,
    $_POST['Nombre_producto'],
    $_POST['Detalle_producto'],
    $_POST['Precio_producto'],
    $_POST['Unidad_medida_venta'],
    $_POST['categoria'],
    $_POST['alicuota'],
    $_POST['moneda'] ?? 'Pesos' // <-- NUEVO
);
        echo json_encode(['success' => $ok, 'message' => $ok ? 'Producto actualizado' : 'No se pudo actualizar']);
        break;

    case 'crear':
$ok = $productosModel->crearProducto(
    $_POST['Nombre_producto'],
    $_POST['Detalle_producto'],
    $_POST['Precio_producto'],
    $_POST['Unidad_medida_venta'],
    $_POST['categoria'],
    $_POST['alicuota'],
    $_POST['moneda'] ?? 'Pesos' // <-- NUEVO
);
        echo json_encode(['success' => $ok, 'message' => $ok ? 'Producto creado' : 'No se pudo crear']);
        break;

    case 'listar':
        $productos = $productosModel->obtenerTodos();
        if (!$productos) {
            echo '<tr><td colspan="8">No hay productos cargados.</td></tr>';
            exit;
        }

        foreach ($productos as $producto) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($producto['Id']) . '</td>';
            echo '<td>' . htmlspecialchars($producto['Nombre_producto']) . '</td>';
            echo '<td>' . htmlspecialchars($producto['Detalle_producto']) . '</td>';
            echo '<td>$' . number_format(floatval($producto['Precio_producto']), 2, ',', '.') . '</td>';
            echo '<td>' . htmlspecialchars($producto['moneda']) . '</td>';
            echo '<td>' . htmlspecialchars($producto['Unidad_Medida_venta']) . '</td>';
            echo '<td>' . htmlspecialchars($producto['categoria']) . '</td>';
            echo '<td>' . htmlspecialchars($producto['alicuota']) . '%</td>';
            echo '<td style="text-align: center;">';
            echo '<button class="btn-icon" onclick="abrirModalEditar(' . intval($producto['Id']) . ')" data-tooltip="Editar Producto">';
            echo '<i class="material-icons">edit</i></button> ';
            echo '<button class="btn-icon" onclick="confirmarEliminacion(' . intval($producto['Id']) . ')" data-tooltip="Eliminar Producto">';
            echo '<i class="material-icons" style="color:red;">delete</i></button>';
            echo '</td>';
            echo '</tr>';
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
}
