<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? null;
$nueva = $input['nueva_contrasena'] ?? null;

if (!$id || !$nueva) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos.']);
    exit;
}

try {
    $hash = password_hash($nueva, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE usuarios SET contrasena = :hash WHERE id = :id");
    $stmt->execute(['hash' => $hash, 'id' => $id]);

    echo json_encode(['success' => true, 'message' => 'Contraseña actualizada correctamente.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar.']);
}
