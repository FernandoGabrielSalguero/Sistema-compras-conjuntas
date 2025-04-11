<?php
// Mostrar errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

$tipo = $_GET['tipo'] ?? '';
$id = $_GET['id'] ?? 0;

if (!in_array($tipo, ['cooperativas', 'productores', 'productos']) || !is_numeric($id)) {
    echo json_encode(['success' => true, 'items' => $datos]); // 👈 cambio clave aquí

    exit;
}

try {
    if ($tipo === 'cooperativas') {
        $stmt = $pdo->prepare("
            SELECT c.id, c.nombre
            FROM operativos_cooperativas oc
            JOIN cooperativas c ON oc.cooperativa_id = c.id
            WHERE oc.operativo_id = ?
        ");
    }

    if ($tipo === 'productores') {
        $stmt = $pdo->prepare("
            SELECT p.id, p.nombre
            FROM operativos_productores op
            JOIN productores p ON op.productor_id = p.id
            WHERE op.operativo_id = ?
        ");
    }

    if ($tipo === 'productos') {
        $stmt = $pdo->prepare("
            SELECT pr.id, pr.Nombre_producto AS nombre
            FROM operativos_productos op
            JOIN productos pr ON op.producto_id = pr.id
            WHERE op.operativo_id = ?
        ");
    }

    $stmt->execute([$id]);
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $datos]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al obtener datos: ' . $e->getMessage()]);
}
