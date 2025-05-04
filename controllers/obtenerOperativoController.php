<?php
// Mostrar errores en pantalla (útil en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    echo json_encode(['success' => false, 'message' => 'ID no válido']);
    exit;
}

// Obtener datos del operativo
$stmt = $pdo->prepare("SELECT * FROM operativos WHERE id = ?");
$stmt->execute([$id]);
$operativo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$operativo) {
    echo json_encode(['success' => false, 'message' => 'Operativo no encontrado']);
    exit;
}

// Obtener IDs de entidades asociadas
$coopsStmt = $pdo->prepare("SELECT cooperativa_id FROM operativos_cooperativas WHERE operativo_id = ?");
$coopsStmt->execute([$id]);
$cooperativas = $coopsStmt->fetchAll(PDO::FETCH_COLUMN);

$prodsStmt = $pdo->prepare("SELECT productor_id FROM operativos_productores WHERE operativo_id = ?");
$prodsStmt->execute([$id]);
$productores = $prodsStmt->fetchAll(PDO::FETCH_COLUMN);

$prodStmt = $pdo->prepare("SELECT producto_id FROM operativos_productos WHERE operativo_id = ?");
$prodStmt->execute([$id]);
$productos = $prodStmt->fetchAll(PDO::FETCH_COLUMN);

// Devolver datos al frontend
echo json_encode([
    'success' => true,
    'operativo' => $operativo,
    'cooperativas' => array_map('intval', $cooperativas),
    'productores' => array_map('intval', $productores),
    'productos' => array_map('intval', $productos)
]);

echo json_encode([
    'success' => true,
    'operativo' => [
        'id' => $operativo['id'],
        'nombre' => $operativo['nombre'],
        'fecha_inicio' => $operativo['fecha_inicio'],
        'fecha_cierre' => $operativo['fecha_cierre'],
    ],
    'cooperativas' => $cooperativas,
    'productores' => $productores,
    'productos' => $productos
]);