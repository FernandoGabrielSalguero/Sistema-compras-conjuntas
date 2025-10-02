<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID faltante']);
    exit;
}

try {
    // Actualizar tabla usuarios
    $stmt = $pdo->prepare("
        UPDATE usuarios 
        SET usuario = :usuario, rol = :rol, permiso_ingreso = :permiso_ingreso, cuit = :cuit, id_real = :id_real
        WHERE id = :id
    ");
    $stmt->execute([
        'usuario' => $_POST['usuario'],
        'rol' => $_POST['rol'],
        'permiso_ingreso' => $_POST['permiso_ingreso'],
        'cuit' => $_POST['cuit'],
        'id_real' => $_POST['id_real'],
        'id' => $id
    ]);

    // Actualizar usuarios_info (crear si no existe)
    $check = $pdo->prepare("SELECT 1 FROM usuarios_info WHERE usuario_id = ?");
    $check->execute([$id]);

    $zonaAsignada = isset($_POST['zona_asignada']) ? trim((string)$_POST['zona_asignada']) : '';
    if ($check->fetch()) {
        $stmtInfo = $pdo->prepare("
        UPDATE usuarios_info 
        SET nombre = :nombre, direccion = :direccion, telefono = :telefono, correo = :correo, zona_asignada = :zona_asignada
        WHERE usuario_id = :id
    ");
    } else {
        $stmtInfo = $pdo->prepare("
        INSERT INTO usuarios_info (usuario_id, nombre, direccion, telefono, correo, zona_asignada)
        VALUES (:id, :nombre, :direccion, :telefono, :correo, :zona_asignada)
    ");
    }

    $stmtInfo->execute([
        'id' => $id,
        'nombre' => $_POST['nombre'] ?? null,
        'direccion' => $_POST['direccion'] ?? null,
        'telefono' => $_POST['telefono'] ?? null,
        'correo' => $_POST['correo'] ?? null,
        'zona_asignada' => $zonaAsignada,
    ]);

    echo json_encode(['success' => true, 'message' => 'Usuario actualizado correctamente']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al guardar los cambios']);
}
