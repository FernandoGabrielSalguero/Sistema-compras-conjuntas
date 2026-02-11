<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/sve_serviciosVendimialesPedidosModel.php';

header('Content-Type: application/json; charset=utf-8');

$model = new ServiciosVendimialesPedidosModel($pdo);
$method = $_SERVER['REQUEST_METHOD'];

// Listado de cooperativas
if ($method === 'GET' && ($_GET['action'] ?? '') === 'cooperativas') {
    try {
        $rows = $model->obtenerCooperativas();
        $cooperativas = array_map(function ($row) {
            $nombre = $row['razon_social'] ?: ($row['usuario'] ?: $row['id_real']);
            $texto = $row['cuit'] ? ($nombre . ' - ' . $row['cuit']) : $nombre;
            return [
                'valor' => $nombre,
                'texto' => $texto
            ];
        }, $rows);
        echo json_encode(['success' => true, 'cooperativas' => $cooperativas]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al cargar cooperativas: ' . $e->getMessage()]);
    }
    exit;
}

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
    $producto_id = $_POST['producto_id'] !== '' ? (int)$_POST['producto_id'] : null;
    $volumenAproximado = $_POST['volumenAproximado'] !== '' ? $_POST['volumenAproximado'] : null;
    $unidad_volumen = $_POST['unidad_volumen'] ?? 'litros';
    $fecha_entrada_equipo = $_POST['fecha_entrada_equipo'] ?? null;
    $estado = $_POST['estado'] ?? 'BORRADOR';
    $observaciones = $_POST['observaciones'] ?? null;

    if (!$cooperativa || !$nombre || $servicioAcontratar <= 0) {
        echo json_encode(['success' => false, 'message' => 'Cooperativa, nombre y servicio son obligatorios.']);
        exit;
    }

    try {
        $payload = [
            'cooperativa' => $cooperativa,
            'nombre' => $nombre,
            'cargo' => $cargo,
            'servicioAcontratar' => $servicioAcontratar,
            'producto_id' => $producto_id,
            'volumenAproximado' => $volumenAproximado,
            'unidad_volumen' => $unidad_volumen,
            'fecha_entrada_equipo' => $fecha_entrada_equipo,
            'estado' => $estado,
            'observaciones' => $observaciones
        ];

        if ($id) {
            $model->actualizar($id, $payload);
            echo json_encode(['success' => true, 'message' => 'Pedido actualizado correctamente.']);
        } else {
            $nuevoId = $model->crear($payload);
            echo json_encode(['success' => true, 'message' => 'Pedido creado correctamente.', 'id' => $nuevoId]);
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
