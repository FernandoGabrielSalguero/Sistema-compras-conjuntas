<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/sve_filtracionModel.php';

header('Content-Type: application/json; charset=utf-8');

$model = new FiltracionModel($pdo);
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
    $nombre = $_POST['nombre'] ?? '';
    $precio = $_POST['precio'] ?? '';
    $moneda = $_POST['moneda'] ?? '';
    $activo = isset($_POST['activo']) ? (int)$_POST['activo'] : 1;

    if (!$nombre || $precio === '' || !$moneda) {
        echo json_encode(['success' => false, 'message' => 'Nombre, precio y moneda son obligatorios.']);
        exit;
    }

    try {
        if ($id) {
            $model->actualizar($id, $nombre, $precio, $moneda, $activo);
            echo json_encode(['success' => true, 'message' => 'Producto actualizado correctamente.']);
        } else {
            $nuevoId = $model->crear($nombre, $precio, $moneda, $activo);
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

// Obtener todos
echo json_encode(['success' => true, 'filtracion' => $model->obtenerTodos()]);
