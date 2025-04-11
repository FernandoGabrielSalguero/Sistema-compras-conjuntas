// detalleOperativoController.php

require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

$tipo = $_GET['tipo'] ?? '';
$id = $_GET['id'] ?? 0;

if (!in_array($tipo, ['cooperativas', 'productores', 'productos']) || !is_numeric($id)) {
    echo json_encode(['success' => false, 'message' => 'Parámetros inválidos.']);
    exit;
}

try {
    if ($tipo === 'cooperativas') {
        $stmt = $pdo->prepare("
            SELECT u.id, u.nombre
            FROM operativos_cooperativas oc
            JOIN usuarios u ON oc.cooperativa_id = u.id
            WHERE oc.operativo_id = ?
        ");
    }

    if ($tipo === 'productores') {
        $stmt = $pdo->prepare("
            SELECT u.id, u.nombre
            FROM operativos_productores op
            JOIN usuarios u ON op.productor_id = u.id
            WHERE op.operativo_id = ?
        ");
    }

    if ($tipo === 'productos') {
        $stmt = $pdo->prepare("
            SELECT p.id, p.Nombre_producto, p.Categoria
            FROM operativos_productos op
            JOIN productos p ON op.producto_id = p.id
            WHERE op.operativo_id = ?
        ");
    }

    $stmt->execute([$id]);
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'items' => $datos]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al obtener datos: ' . $e->getMessage()]);
}
