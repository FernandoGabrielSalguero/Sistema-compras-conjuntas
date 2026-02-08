<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/sve_contratosVendimialesModel.php';

header('Content-Type: application/json; charset=utf-8');

$model = new ContratosVendimialesModel($pdo);
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
        echo json_encode(['success' => true, 'message' => 'Contrato eliminado correctamente.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()]);
    }
    exit;
}

// Crear o actualizar
if ($method === 'POST') {
    $id = $_POST['id'] ?? null;
    $nombre = $_POST['nombre'] ?? '';
    $contenido = $_POST['contenido'] ?? '';
    $version = $_POST['version'] ?? 1;
    $vigente = isset($_POST['vigente']) ? (int)$_POST['vigente'] : 0;
    $descripcion = $_POST['descripcion'] ?? null;

    if (!$nombre || !$contenido) {
        echo json_encode(['success' => false, 'message' => 'Nombre y contenido son obligatorios.']);
        exit;
    }

    try {
        if ($id) {
            $model->actualizar($id, $nombre, $descripcion, $contenido, $version, $vigente);
            echo json_encode(['success' => true, 'message' => 'Contrato actualizado correctamente.']);
        } else {
            $nuevoId = $model->crear($nombre, $descripcion, $contenido, $version, $vigente);
            echo json_encode(['success' => true, 'message' => 'Contrato creado correctamente.', 'id' => $nuevoId]);
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
        echo json_encode(['success' => true, 'contrato' => $data]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No encontrado']);
    }
    exit;
}

// Obtener todos
echo json_encode(['success' => true, 'contratos' => $model->obtenerTodos()]);
