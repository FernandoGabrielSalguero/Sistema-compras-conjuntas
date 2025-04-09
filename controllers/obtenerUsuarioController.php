<?php
require_once __DIR__ . '/../config.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID no recibido']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT id, nombre, correo, telefono, observaciones, permiso_ingreso 
    FROM usuarios 
    WHERE id = ?
");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo json_encode(['success' => true, 'user' => $user]);
} else {
    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
}
