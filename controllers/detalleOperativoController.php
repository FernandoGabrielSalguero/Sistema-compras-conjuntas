<?php
// Mostrar errores en desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

// Validar parámetros
$tipo = $_GET['tipo'] ?? '';
$id = intval($_GET['id'] ?? 0);

if (!$id || !in_array($tipo, ['cooperativas', 'productores', 'productos'])) {
    echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
    exit;
}

try {
    switch ($tipo) {
        case 'cooperativas':
            $stmt = $pdo->prepare("
                SELECT c.id, c.nombre
                FROM operativos_cooperativas oc
                JOIN cooperativas c ON c.id = oc.cooperativa_id
                WHERE oc.operativo_id = ?
            ");
            break;

        case 'productores':
            $stmt = $pdo->prepare("
                SELECT p.id, p.nombre
                FROM operativos_productores op
                JOIN productores p ON p.id = op.productor_id
                WHERE op.operativo_id = ?
            ");
            break;

        case 'productos':
            $stmt = $pdo->prepare("
                SELECT pr.id, pr.Nombre_producto, pr.Categoria
                FROM operativos_productos op
                JOIN productos pr ON pr.id = op.producto_id
                WHERE op.operativo_id = ?
            ");
            break;
    }

    $stmt->execute([$id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'items' => $items]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener datos: ' . $e->getMessage()
    ]);
}
