<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Validar existencia de archivo y ID de pedido
if (!isset($_FILES['factura']) || !isset($_POST['pedido_id'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos para procesar la factura']);
    exit;
}

$pedidoId = intval($_POST['pedido_id']);
$archivo = $_FILES['factura'];

if ($archivo['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Error al subir el archivo']);
    exit;
}

// Validar tipo y extensión (opcionalmente MIME también)
$permitidos = ['pdf', 'jpg', 'jpeg', 'png'];
$ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $permitidos)) {
    echo json_encode(['success' => false, 'message' => 'Formato no permitido']);
    exit;
}

// Crear nombre único
$nombreArchivo = 'factura_' . $pedidoId . '_' . time() . '.' . $ext;
$rutaDestino = __DIR__ . '/../uploads/tax_invoices/' . $nombreArchivo;

if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
    echo json_encode(['success' => false, 'message' => 'No se pudo guardar el archivo']);
    exit;
}

// Guardar en la base de datos
try {
    $stmt = $pdo->prepare("UPDATE pedidos SET factura = ? WHERE id = ?");
    $stmt->execute([$nombreArchivo, $pedidoId]);

    echo json_encode(['success' => true, 'message' => 'Factura cargada con éxito']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos']);
}
