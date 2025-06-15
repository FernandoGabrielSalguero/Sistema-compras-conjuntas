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
    $descripcion = $_POST['descripcion'] ?? 'Sin descripción';

    if (!$nombre || !$fecha_inicio || !$fecha_cierre || !$estado) {
        echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios.']);
        exit;
    }

    try {
        if ($id) {
            $model->actualizar($id, $nombre, $fecha_inicio, $fecha_cierre, $estado, $descripcion);
            echo json_encode(['success' => true, 'message' => 'Operativo actualizado correctamente.']);
        } else {
            $model->crear($nombre, $fecha_inicio, $fecha_cierre, $estado, $descripcion);
            echo json_encode(['success' => true, 'message' => 'Operativo creado correctamente.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al procesar: ' . $e->getMessage()]);
    }
    exit;
}

// Guardar productos seleccionados para operativo
if (isset($_POST['productos']) && isset($_POST['operativo_id'])) {
    $id = $_POST['operativo_id'];
    $productos = $_POST['productos'];

    try {
        $pdo->prepare("DELETE FROM operativos_productos WHERE operativo_id = ?")->execute([$id]);

        $insert = $pdo->prepare("INSERT INTO operativos_productos (operativo_id, producto_id) VALUES (?, ?)");
        foreach ($productos as $prod_id) {
            $insert->execute([$id, $prod_id]);
        }

        echo json_encode(['success' => true, 'message' => 'Productos guardados correctamente.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al guardar productos: ' . $e->getMessage()]);
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

// Obtener productos del operativo por categoría
if (isset($_GET['productos']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $seleccionados = $pdo->prepare("SELECT producto_id as id FROM operativos_productos WHERE operativo_id = ?");
        $seleccionados->execute([$id]);
        $seleccionados = $seleccionados->fetchAll(PDO::FETCH_ASSOC);

        $productos = $pdo->query("SELECT * FROM productos")->fetchAll(PDO::FETCH_ASSOC);
        $agrupados = [];

        foreach ($productos as $p) {
            $agrupados[$p['categoria']][] = $p;
        }

        $res = [];
        foreach ($agrupados as $cat => $items) {
            $res[] = ['categoria' => $cat, 'productos' => $items];
        }

        echo json_encode(['success' => true, 'categorias' => $res, 'seleccionados' => $seleccionados]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al cargar productos: ' . $e->getMessage()]);
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
