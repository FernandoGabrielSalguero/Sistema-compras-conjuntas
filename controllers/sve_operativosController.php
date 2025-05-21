<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/sve_operativosModel.php';
header('Content-Type: application/json');

$model = new OperativosModel($pdo);
$method = $_SERVER['REQUEST_METHOD'];

// 🛑 PRIMERO: Simulación de DELETE via POST
if ($method === 'POST' && ($_POST['_method'] ?? '') === 'delete') {
    $id = $_POST['id'] ?? null;

    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID faltante para eliminar.']);
        exit;
    }

    try {
        $model->eliminar($id);
        echo json_encode(['success' => true, 'message' => 'Operativo eliminado correctamente.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()]);
    }
    exit;
}

// ✅ LUEGO: Crear o actualizar
if ($method === 'POST') {
    $id = $_POST['id'] ?? null;
    $nombre = $_POST['nombre'] ?? '';
    $fecha_inicio = $_POST['fecha_inicio'] ?? '';
    $fecha_cierre = $_POST['fecha_cierre'] ?? '';
    $estado = $_POST['estado'] ?? 'abierto';

    if (!$nombre || !$fecha_inicio || !$fecha_cierre || !$estado) {
        echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios.']);
        exit;
    }

    try {
        if ($id) {
            $model->actualizar($id, $nombre, $fecha_inicio, $fecha_cierre, $estado);
            echo json_encode(['success' => true, 'message' => 'Operativo actualizado correctamente.']);
        } else {
            $model->crear($nombre, $fecha_inicio, $fecha_cierre, $estado);
            echo json_encode(['success' => true, 'message' => 'Operativo creado correctamente.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al procesar: ' . $e->getMessage()]);
    }
    exit;
}

// Obtener cooperativas participantes de un operativo
if (isset($_GET['cooperativas']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $coops = $model->obtenerCooperativasPorOperativo($id);
        echo json_encode(['success' => true, 'cooperativas' => $coops]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener cooperativas: ' . $e->getMessage()]);
    }
    exit;
}


// ✅ OBTENER UNO SOLO
if (isset($_GET['id'])) {
    $data = $model->obtenerPorId($_GET['id']);
    if ($data) {
        echo json_encode(['success' => true, 'operativo' => $data]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No encontrado']);
    }
    exit;
}

// ✅ OBTENER TODOS
echo json_encode(['success' => true, 'operativos' => $model->obtenerTodos()]);
