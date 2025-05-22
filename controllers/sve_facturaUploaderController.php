<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Log de debug
function debugLog($msg) {
    file_put_contents(__DIR__ . '/../logs/factura_debug.log', date('[Y-m-d H:i:s] ') . $msg . PHP_EOL, FILE_APPEND);
}

require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    debugLog('❌ Método no permitido');
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Validar existencia de archivo y ID
if (!isset($_FILES['factura']) || !isset($_POST['pedido_id'])) {
    debugLog('❌ Faltan datos: pedido_id o factura');
    echo json_encode(['success' => false, 'message' => 'Faltan datos para procesar la factura']);
    exit;
}

$pedidoId = intval($_POST['pedido_id']);
$archivo = $_FILES['factura'];

debugLog("📥 Recibido archivo para pedido ID: $pedidoId");

// Verificar error de subida
if ($archivo['error'] !== UPLOAD_ERR_OK) {
    $errores = [
        UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo permitido por el servidor.',
        UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo del formulario.',
        UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente.',
        UPLOAD_ERR_NO_FILE => 'No se envió ningún archivo.',
        UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal en el servidor.',
        UPLOAD_ERR_CANT_WRITE => 'No se pudo escribir el archivo en el disco.',
        UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la subida del archivo.'
    ];
    $mensajeError = $errores[$archivo['error']] ?? 'Error desconocido al subir el archivo.';
    debugLog("❌ Error al subir archivo: $mensajeError");
    echo json_encode(['success' => false, 'message' => $mensajeError]);
    exit;
}

// Validar extensión
$permitidos = ['pdf', 'jpg', 'jpeg', 'png'];
$ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $permitidos)) {
    debugLog("❌ Formato no permitido: .$ext");
    echo json_encode(['success' => false, 'message' => 'Formato de archivo no permitido']);
    exit;
}

// Ruta y nombre
$nombreArchivo = 'factura_' . $pedidoId . '_' . time() . '.' . $ext;
$carpetaDestino = __DIR__ . '/../uploads/tax_invoices/';
$rutaFinal = $carpetaDestino . $nombreArchivo;

// Verificar si la carpeta existe y es escribible
if (!is_dir($carpetaDestino) || !is_writable($carpetaDestino)) {
    debugLog("❌ Carpeta no escribible: $carpetaDestino");
    echo json_encode(['success' => false, 'message' => 'No se puede guardar el archivo. Carpeta inaccesible.']);
    exit;
}

// Guardar archivo
if (!move_uploaded_file($archivo['tmp_name'], $rutaFinal)) {
    debugLog('❌ No se pudo mover el archivo a la carpeta final.');
    echo json_encode(['success' => false, 'message' => 'No se pudo guardar el archivo en el servidor']);
    exit;
}

// Guardar en DB
try {
    $stmt = $pdo->prepare("UPDATE pedidos SET factura = ? WHERE id = ?");
    $stmt->execute([$nombreArchivo, $pedidoId]);
    debugLog("✅ Factura guardada como $nombreArchivo para pedido $pedidoId");

    echo json_encode(['success' => true, 'message' => 'Factura cargada con éxito']);
} catch (Exception $e) {
    debugLog('❌ Error SQL: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al guardar la factura en la base de datos']);
}