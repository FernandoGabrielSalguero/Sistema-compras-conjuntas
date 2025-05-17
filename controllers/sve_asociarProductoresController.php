<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$id_productor = $data['id_productor'] ?? null;
$id_cooperativa = $data['id_cooperativa'] ?? null;

if (!$id_productor || !$id_cooperativa) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
    exit;
}

try {
    // Eliminar asociación previa (si existe)
    $pdo->prepare("DELETE FROM usuario_asociaciones WHERE id_productor = ?")->execute([$id_productor]);

    // Insertar nueva
    $stmt = $pdo->prepare("INSERT INTO usuario_asociaciones (id_productor, id_cooperativa) VALUES (?, ?)");
    $stmt->execute([$id_productor, $id_cooperativa]);

    echo json_encode(['success' => true, 'message' => 'Asociación guardada correctamente.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al guardar asociación.']);
}