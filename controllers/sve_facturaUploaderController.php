<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pedidoId = $_POST['pedido_id'] ?? null;

    if (!$pedidoId || !isset($_FILES['factura'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Faltan datos']);
        exit;
    }

    $factura = $_FILES['factura'];

    // Validar extensión
    $extensiones = ['pdf', 'jpg', 'jpeg', 'png'];
    $ext = strtolower(pathinfo($factura['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $extensiones)) {
        echo json_encode(['success' => false, 'message' => 'Formato no permitido']);
        exit;
    }

    // Renombrar y mover
    $nuevoNombre = 'factura_' . $pedidoId . '_' . time() . '.' . $ext;
    $destino = __DIR__ . '/../uploads/tax_invoices/' . $nuevoNombre;

    if (!move_uploaded_file($factura['tmp_name'], $destino)) {
        echo json_encode(['success' => false, 'message' => 'Error al guardar archivo']);
        exit;
    }

    // Guardar en la base
    $stmt = $pdo->prepare("UPDATE pedidos SET factura = ? WHERE id = ?");
    $stmt->execute([$nuevoNombre, $pedidoId]);

    echo json_encode(['success' => true, 'archivo' => $nuevoNombre]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Solicitud no válida']);
