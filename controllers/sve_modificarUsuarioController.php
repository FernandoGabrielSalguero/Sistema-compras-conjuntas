<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID no recibido']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT u.id, u.usuario, u.rol, u.permiso_ingreso, u.cuit, u.id_real,
               i.nombre, i.direccion, i.telefono, i.correo
        FROM usuarios u
        LEFT JOIN usuarios_info i ON u.id = i.usuario_id
        WHERE u.id = ?
    ");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al consultar usuario']);
}
