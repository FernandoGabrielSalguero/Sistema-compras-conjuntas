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
$cooperativas = isset($_POST['cooperativas']) && is_array($_POST['cooperativas']) ? $_POST['cooperativas'] : [];
$productores = isset($_POST['productores']) && is_array($_POST['productores']) ? $_POST['productores'] : [];
$productos = isset($_POST['productos']) && is_array($_POST['productos']) ? $_POST['productos'] : [];

if (!empty($cooperativas)) {
    $model->guardarCooperativas($operativo_id, $cooperativas);
}
if (!empty($productores)) {
    $model->guardarProductores($operativo_id, $productores);
}
if (!empty($productos)) {
    $model->guardarProductos($operativo_id, $productos);
}

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

    if (!empty($cooperativas)) $model->guardarCooperativas($operativo_id, $cooperativas);
    if (!empty($productores))  $model->guardarProductores($operativo_id, $productores);
    if (!empty($productos))    $model->guardarProductos($operativo_id, $productos);
    

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => '✅ Operativo creado correctamente']);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Error al crear el operativo: ' . $e->getMessage()
    ]);

    error_log($e->getMessage());
}
