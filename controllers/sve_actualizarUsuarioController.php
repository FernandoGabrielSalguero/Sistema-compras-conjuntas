<?php
declare(strict_types=1);
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

function responderError(string $message, ?string $detail = null, int $statusCode = 500): void
{
    http_response_code($statusCode);

    $payload = ['success' => false, 'message' => $message];
    if ($detail !== null && $detail !== '') {
        $payload['error_detail'] = $detail;
    }

    echo json_encode($payload);
    exit;
}

function logActualizarUsuarioError(Throwable $e, string $method, array $context = []): void
{
    $log = [
        'controller' => 'sve_actualizarUsuarioController',
        'method' => $method,
        'error' => $e->getMessage(),
        'code' => $e->getCode(),
        'context' => $context,
    ];

    error_log('[SVE editar usuario] ' . json_encode($log, JSON_UNESCAPED_UNICODE));
}

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
            responderError('ID inválido', null, 400);
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
            responderError('Usuario no encontrado', null, 404);
        }

        echo json_encode(['success' => true, 'user' => $user]);
        exit;
    }

    // ------ ACTUALIZACIÓN (POST) ------
    $id = $_POST['id'] ?? null;
    if (!$id) {
        responderError('ID faltante', null, 400);
    }

    // Normalizo entradas (evita notices)
    $usuario   = $_POST['usuario'] ?? null;
    $rol       = $_POST['rol'] ?? null;
    $permiso   = $_POST['permiso_ingreso'] ?? null;
    $cuitPost  = $_POST['cuit'] ?? null;
    $idReal    = isset($_POST['id_real']) ? trim((string) $_POST['id_real']) : null;

    $stmtOld = $pdo->prepare("SELECT id_real FROM usuarios WHERE id = :id LIMIT 1");
    $stmtOld->execute(['id' => $id]);
    $oldIdReal = $stmtOld->fetchColumn();
    if ($oldIdReal === false) {
        responderError('Usuario no encontrado', null, 404);
    }

    if ($idReal === null || $idReal === '') {
        $idReal = (string) $oldIdReal;
    }

    $willChangeIdReal = ((string) $oldIdReal !== (string) $idReal);

    $pdo->beginTransaction();

    if ($willChangeIdReal) {
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    }

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

    if ($willChangeIdReal) {
        $refUpdates = [
            ['table' => 'rel_coop_ingeniero', 'column' => 'cooperativa_id_real'],
            ['table' => 'rel_coop_ingeniero', 'column' => 'ingeniero_id_real'],
            ['table' => 'rel_productor_coop', 'column' => 'cooperativa_id_real'],
            ['table' => 'rel_productor_coop', 'column' => 'productor_id_real'],
            ['table' => 'operativos_cooperativas_participacion', 'column' => 'cooperativa_id_real'],
            ['table' => 'prod_cuartel', 'column' => 'cooperativa_id_real'],
            ['table' => 'prod_fincas', 'column' => 'productor_id_real'],
            ['table' => 'drones_solicitud', 'column' => 'productor_id_real'],
        ];

        foreach ($refUpdates as $ref) {
            $sql = "UPDATE {$ref['table']} SET {$ref['column']} = :new_id WHERE {$ref['column']} = :old_id";
            $stmtRef = $pdo->prepare($sql);
            $stmtRef->execute([
                'new_id' => $idReal,
                'old_id' => $oldIdReal,
            ]);
        }
    }

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

    if ($willChangeIdReal) {
        $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
    }
    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Usuario actualizado correctamente']);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    if (isset($pdo) && $pdo instanceof PDO) {
        try {
            $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
        } catch (Throwable $inner) {
            // No-op: evitamos ocultar el error principal
        }
    }

    $context = [];

    if ($method === 'GET') {
        $context['id'] = isset($_GET['id']) ? (int) $_GET['id'] : null;
    }

    if ($method === 'POST') {
        $context = [
            'id' => $_POST['id'] ?? null,
            'usuario' => $_POST['usuario'] ?? null,
            'rol' => $_POST['rol'] ?? null,
            'permiso_ingreso' => $_POST['permiso_ingreso'] ?? null,
            'cuit' => $_POST['cuit'] ?? null,
            'id_real' => $_POST['id_real'] ?? null,
            'correo' => $_POST['correo'] ?? null,
            'zona_asignada' => $_POST['zona_asignada'] ?? null,
        ];
    }

    logActualizarUsuarioError($e, $method, $context);
    responderError('Error al procesar la solicitud', $e->getMessage(), 500);
}
