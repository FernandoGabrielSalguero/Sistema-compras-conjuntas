<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

$id = $_POST['id'] ?? null;
$nombre = $_POST['nombre'] ?? '';
$fecha_inicio = $_POST['fecha_inicio'] ?? '';
$fecha_cierre = $_POST['fecha_cierre'] ?? '';

$cooperativas = $_POST['cooperativas'] ?? [];
$productores = $_POST['productores'] ?? [];
$productos = $_POST['productos'] ?? [];

if (!$id || !$nombre || !$fecha_inicio || !$fecha_cierre) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Actualizar operativo principal
    $stmt = $pdo->prepare("UPDATE operativos SET nombre = ?, fecha_inicio = ?, fecha_cierre = ? WHERE id = ?");
    $stmt->execute([$nombre, $fecha_inicio, $fecha_cierre, $id]);

    // 🔄 Actualizar cooperativas
    $pdo->prepare("DELETE FROM operativos_cooperativas WHERE operativo_id = ?")->execute([$id]);
    $stmt = $pdo->prepare("INSERT INTO operativos_cooperativas (operativo_id, cooperativa_id) VALUES (?, ?)");
    foreach ($cooperativas as $coopId) {
        $stmt->execute([$id, $coopId]);
    }

    // 🔄 Actualizar productores
    $pdo->prepare("DELETE FROM operativos_productores WHERE operativo_id = ?")->execute([$id]);
    $stmt = $pdo->prepare("INSERT INTO operativos_productores (operativo_id, productor_id) VALUES (?, ?)");
    foreach ($productores as $prodId) {
        $stmt->execute([$id, $prodId]);
    }

    // 🔄 Actualizar productos
    $pdo->prepare("DELETE FROM operativos_productos WHERE operativo_id = ?")->execute([$id]);
    $stmt = $pdo->prepare("INSERT INTO operativos_productos (operativo_id, producto_id) VALUES (?, ?)");
    foreach ($productos as $prodId) {
        $stmt->execute([$id, $prodId]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Operativo actualizado correctamente']);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()]);
}
