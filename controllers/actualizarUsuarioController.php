<?php
require_once __DIR__ . '/../config.php';

$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID no recibido']);
    exit;
}

$stmt = $pdo->prepare("
    UPDATE usuarios 
    SET 
        nombre = :nombre,
        correo = :correo,
        telefono = :telefono,
        observaciones = :observaciones,
        permiso_ingreso = :permiso
    WHERE id = :id
");

$ok = $stmt->execute([
    'nombre' => $_POST['nombre'] ?? '',
    'correo' => $_POST['correo'] ?? '',
    'telefono' => $_POST['telefono'] ?? '',
    'observaciones' => $_POST['observaciones'] ?? '',
    'permiso' => $_POST['permiso'] ?? '',
    'id' => $id
]);

if ($ok) {
    echo json_encode(['success' => true, 'message' => 'Usuario actualizado correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar usuario']);
}
