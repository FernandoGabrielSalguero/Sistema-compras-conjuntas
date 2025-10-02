<?php
declare(strict_types=1);
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

/**
 * Controlador unificado:
 * - GET  ?id=123  => devuelve datos del usuario + usuarios_info (incluye zona_asignada) para precargar el modal
 * - POST formData => actualiza usuarios y usuarios_info (incluye zona_asignada)
 */

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    if ($method === 'GET') {
        // ------ LECTURA PARA PRECARGAR MODAL ------
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            exit;
        }

        $sql = "
            SELECT
                u.id,
                u.usuario,
                u.rol,
                u.permiso_ingreso,
                u.cuit,
                u.id_real,
                COALESCE(ui.nombre, '')        AS nombre,
                COALESCE(ui.direccion, '')     AS direccion,
                COALESCE(ui.telefono, '')      AS telefono,
                COALESCE(ui.correo, '')        AS correo,
                COALESCE(ui.zona_asignada, '') AS zona_asignada
            FROM usuarios u
            LEFT JOIN usuarios_info ui ON ui.usuario_id = u.id
            WHERE u.id = :id
            LIMIT 1
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
            exit;
        }

        echo json_encode(['success' => true, 'user' => $user]);
        exit;
    }

    // ------ ACTUALIZACIÓN (POST) ------
    $id = $_POST['id'] ?? null;
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID faltante']);
        exit;
    }

    // Normalizo entradas (evita notices)
    $usuario   = $_POST['usuario'] ?? null;
    $rol       = $_POST['rol'] ?? null;
    $permiso   = $_POST['permiso_ingreso'] ?? null;
    $cuitPost  = $_POST['cuit'] ?? null;
    $idReal    = $_POST['id_real'] ?? null;

    // Actualizar tabla usuarios
    $stmt = $pdo->prepare("
        UPDATE usuarios 
        SET usuario = :usuario, rol = :rol, permiso_ingreso = :permiso_ingreso, cuit = :cuit, id_real = :id_real
        WHERE id = :id
    ");
    $stmt->execute([
        'usuario'         => $usuario,
        'rol'             => $rol,
        'permiso_ingreso' => $permiso,
        'cuit'            => $cuitPost,
        'id_real'         => $idReal,
        'id'              => $id
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
        'id'             => $id,
        'nombre'         => $_POST['nombre'] ?? null,
        'direccion'      => $_POST['direccion'] ?? null,
        'telefono'       => $_POST['telefono'] ?? null,
        'correo'         => $_POST['correo'] ?? null,
        'zona_asignada'  => $zonaAsignada,
    ]);

    echo json_encode(['success' => true, 'message' => 'Usuario actualizado correctamente']);
} catch (Throwable $e) {
    // Podés loguear el error real: $e->getMessage()
    echo json_encode(['success' => false, 'message' => 'Error al procesar la solicitud']);
}
