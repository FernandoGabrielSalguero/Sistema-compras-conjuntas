<?php
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

// Cooperativas
$coopsStmt = $pdo->prepare("SELECT u.id, u.nombre
    FROM operativos_cooperativas oc
    JOIN usuarios u ON oc.cooperativa_id = u.id
    WHERE oc.operativo_id = ?");
$coopsStmt->execute([$id]);
$cooperativas = $coopsStmt->fetchAll(PDO::FETCH_ASSOC);

// Productores (con nombre)
$prodsStmt = $pdo->prepare("
    SELECT u.id, u.nombre
    FROM operativos_productores op
    JOIN usuarios u ON op.productor_id = u.id
    WHERE op.operativo_id = ?
");
$prodsStmt->execute([$id]);
$productores = $prodsStmt->fetchAll(PDO::FETCH_ASSOC);

// Productos
$prodStmt = $pdo->prepare("
    SELECT p.id, p.Nombre_producto, p.Categoria
    FROM operativos_productos op
    JOIN productos p ON op.producto_id = p.id
    WHERE op.operativo_id = ?
");
$prodStmt->execute([$id]);
$productos = $prodStmt->fetchAll(PDO::FETCH_ASSOC);

// Devolver datos al frontend
echo json_encode([
    'success' => true,
    'operativo' => $operativo,
    'cooperativas' => $cooperativas,
    'productores' => $productores,
    'productos' => $productos
]);
