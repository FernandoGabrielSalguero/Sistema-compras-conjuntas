<?php
// Mostrar errores en pantalla (útil en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require_once __DIR__ . '/../models/OperativosModel.php';
require_once __DIR__ . '/../config.php';

$model = new OperativosModel($pdo);

// Validar entrada
$nombre = $_POST['nombre'] ?? '';
$fecha_inicio = $_POST['fecha_inicio'] ?? '';
$fecha_cierre = $_POST['fecha_cierre'] ?? '';
$cooperativas = $_POST['cooperativas'] ?? [];
$productores = $_POST['productores'] ?? [];
$productos = $_POST['productos'] ?? [];

if (empty($nombre) || empty($fecha_inicio) || empty($fecha_cierre)) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
    exit;
}

if ($model->existeNombre($nombre)) {
    echo json_encode(['success' => false, 'message' => 'Ya existe un operativo con ese nombre.']);
    exit;
}

file_put_contents('php://stderr', "Cooperativas:\n" . print_r($_POST['cooperativas'], true));
file_put_contents('php://stderr', "Productores:\n" . print_r($_POST['productores'], true));
file_put_contents('php://stderr', "Productos:\n" . print_r($_POST['productos'], true));


try {
    $pdo->beginTransaction();

    $operativo_id = $model->crearOperativo($nombre, $fecha_inicio, $fecha_cierre);
    $model->guardarCooperativas($operativo_id, $cooperativas);
    $model->guardarProductores($operativo_id, $productores);
    $model->guardarProductos($operativo_id, $productos);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => '✅ Operativo creado correctamente']);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Error al crear el operativo: ' . $e->getMessage()
    ]);
}
