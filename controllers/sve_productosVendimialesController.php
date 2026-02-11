<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/sve_productosVendimialesModel.php';

header('Content-Type: application/json; charset=utf-8');

$model = new ProductosVendimialesModel($pdo);
$method = $_SERVER['REQUEST_METHOD'];

// Simulación de DELETE vía POST
if ($method === 'POST' && ($_POST['_method'] ?? '') === 'delete') {
    $id = $_POST['id'] ?? null;

    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID faltante para eliminar.']);
        exit;
    }

    try {
        $model->eliminar($id);
        echo json_encode(['success' => true, 'message' => 'Producto eliminado correctamente.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()]);
    }
    exit;
}

// Crear o actualizar
if ($method === 'POST') {
    $id = $_POST['id'] ?? null;
    $servicioId = (int)($_POST['servicio_id'] ?? 0);
    $nombre = $_POST['nombre'] ?? '';
    $precio = $_POST['precio'] ?? '';
    $moneda = $_POST['moneda'] ?? '';
    $activo = isset($_POST['activo']) ? (int)$_POST['activo'] : 1;

    if ($servicioId <= 0 || !$nombre || $precio === '' || !$moneda) {
        echo json_encode(['success' => false, 'message' => 'Servicio, nombre, precio y moneda son obligatorios.']);
        exit;
    }

    try {
        if ($id) {
            $model->actualizar($id, $servicioId, $nombre, $precio, $moneda, $activo);
            echo json_encode(['success' => true, 'message' => 'Producto actualizado correctamente.']);
        } else {
            $nuevoId = $model->crear($servicioId, $nombre, $precio, $moneda, $activo);
            echo json_encode(['success' => true, 'message' => 'Producto creado correctamente.', 'id' => $nuevoId]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al procesar: ' . $e->getMessage()]);
    }
    exit;
}

// Obtener uno
if (isset($_GET['id'])) {
    $data = $model->obtenerPorId($_GET['id']);
    if ($data) {
        echo json_encode(['success' => true, 'producto' => $data]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No encontrado']);
    }
    exit;
}

// Obtener todos (con filtro opcional por servicio)
try {
    $servicioId = isset($_GET['servicio_id']) ? (int)$_GET['servicio_id'] : null;
    $productos = $model->obtenerTodos($servicioId ?: null);
    echo json_encode(['success' => true, 'productos' => $productos]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al cargar productos: ' . $e->getMessage()]);
}
