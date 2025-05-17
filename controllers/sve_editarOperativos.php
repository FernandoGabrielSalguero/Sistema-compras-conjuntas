<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

// Obtener datos del formulario
$id = $_POST['id'] ?? null;
$nombre = $_POST['nombre'] ?? '';
$fecha_inicio = $_POST['fecha_inicio'] ?? '';
$fecha_cierre = $_POST['fecha_cierre'] ?? '';

// Validar campos obligatorios
if (!$id || !$nombre || !$fecha_inicio || !$fecha_cierre) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

try {
    // Actualizar el operativo
    $stmt = $pdo->prepare("UPDATE operativos SET nombre = ?, fecha_inicio = ?, fecha_cierre = ? WHERE id = ?");
    $stmt->execute([$nombre, $fecha_inicio, $fecha_cierre, $id]);

    echo json_encode(['success' => true, 'message' => 'Operativo actualizado correctamente']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()]);
}
