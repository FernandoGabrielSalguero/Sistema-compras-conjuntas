<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/sve_serviciosVendimialesPedidosModel.php';

header('Content-Type: application/json; charset=utf-8');

$model = new ServiciosVendimialesPedidosModel($pdo);
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
        echo json_encode(['success' => true, 'message' => 'Pedido eliminado correctamente.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()]);
    }
    exit;
}

// Actualizar
if ($method === 'POST') {
    $id = $_POST['id'] ?? null;
    $cooperativa = $_POST['cooperativa'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $cargo = $_POST['cargo'] ?? null;
    $servicioAcontratar = (int)($_POST['servicioAcontratar'] ?? 0);
    $volumenAproximado = $_POST['volumenAproximado'] !== '' ? $_POST['volumenAproximado'] : null;
    $unidad_volumen = $_POST['unidad_volumen'] ?? 'litros';
    $fecha_entrada_equipo = $_POST['fecha_entrada_equipo'] ?? null;
    $equipo_centrifugadora = $_POST['equipo_centrifugadora'] !== '' ? $_POST['equipo_centrifugadora'] : null;
    $estado = $_POST['estado'] ?? 'BORRADOR';
    $observaciones = $_POST['observaciones'] ?? null;

    if (!$id || !$cooperativa || !$nombre || $servicioAcontratar <= 0) {
        echo json_encode(['success' => false, 'message' => 'Cooperativa, nombre y servicio son obligatorios.']);
        exit;
    }

    try {
        $model->actualizar($id, [
            'cooperativa' => $cooperativa,
            'nombre' => $nombre,
            'cargo' => $cargo,
            'servicioAcontratar' => $servicioAcontratar,
            'volumenAproximado' => $volumenAproximado,
            'unidad_volumen' => $unidad_volumen,
            'fecha_entrada_equipo' => $fecha_entrada_equipo,
            'equipo_centrifugadora' => $equipo_centrifugadora,
            'estado' => $estado,
            'observaciones' => $observaciones
        ]);
        echo json_encode(['success' => true, 'message' => 'Pedido actualizado correctamente.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al procesar: ' . $e->getMessage()]);
    }
    exit;
}

// Obtener uno
if (isset($_GET['id'])) {
    $data = $model->obtenerPorId($_GET['id']);
    if ($data) {
        echo json_encode(['success' => true, 'pedido' => $data]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No encontrado']);
    }
    exit;
}

// Obtener todos
try {
    echo json_encode(['success' => true, 'pedidos' => $model->obtenerTodos()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al cargar pedidos: ' . $e->getMessage()]);
}
