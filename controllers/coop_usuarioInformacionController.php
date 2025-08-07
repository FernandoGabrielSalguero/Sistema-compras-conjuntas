<?php
ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
header('Content-Type: application/json');

// Verificar login y rol
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'cooperativa') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/coop_usuarioInformacionModel.php';

$model = new UsuarioInformacionModel();
$cooperativaIdReal = $_SESSION['id_real'] ?? null;

// 🔄 GET: listar productores asociados
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'listar_productores') {
    $productores = $model->obtenerProductoresPorCooperativa($cooperativaIdReal);
    echo json_encode(['success' => true, 'productores' => $productores]);
    exit;
}

// 🔄 GET: obtener ID real disponible (solo si NO hay action)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['action'])) {
    if (!$cooperativaIdReal) {
        echo json_encode(['success' => false, 'message' => 'No hay sesión activa']);
        exit;
    }

    $rango = $model->obtenerRangoCooperativa($cooperativaIdReal);
    $proximoId = $model->obtenerProximoIdRealDisponible($rango['rango_productores_inicio'], $rango['rango_productores_fin']);

    ob_end_clean();
    echo json_encode(['success' => true, 'id_real' => $proximoId]);
    exit;
}

// 📝 POST: crear productor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    $usuario = $_POST['usuario'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';
    $cuit = $_POST['cuit'] ?? '';

    if (!$cooperativaIdReal || !$usuario || !$contrasena || !$cuit) {
        echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
        exit;
    }

    // Obtener rango permitido para esta cooperativa
    $rango = $model->obtenerRangoCooperativa($cooperativaIdReal);
    if (!$rango) {
        echo json_encode(['success' => false, 'message' => 'No se encontró el rango de esta cooperativa']);
        exit;
    }

    // Obtener el próximo id_real libre dentro del rango de productores
    $proximoId = $model->obtenerProximoIdRealDisponible($rango['rango_productores_inicio'], $rango['rango_productores_fin']);
    if (!$proximoId) {
        echo json_encode(['success' => false, 'message' => 'No hay IDs disponibles en tu rango de productores']);
        exit;
    }

    // Crear usuario productor
    try {
        // 1. Crear en tabla `usuarios`
        $nuevoId = $model->crearUsuarioProductor($usuario, $contrasena, $cuit, $proximoId);

        // 2. Guardar nombre también en `usuarios_info`
        $model->guardarInfoProductor($nuevoId, $usuario, '', '', '');

        // 3. Asociar con la cooperativa
        $model->asociarProductorCooperativa($proximoId, $cooperativaIdReal);

        echo json_encode([
            'success' => true,
            'message' => 'Productor creado correctamente',
            'id_real' => $proximoId
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al crear usuario: ' . $e->getMessage()]);
    }

    exit;
}



// 📝 POST: editar datos de productor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'editar_productor') {
    global $pdo; // Asegura disponibilidad del objeto

    $usuarioId = $_POST['usuario_id'] ?? null;
    $nombre = $_POST['nombre'] ?? '';
    $cuit = trim($_POST['cuit'] ?? '');
    $telefono = $_POST['telefono'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $direccion = $_POST['direccion'] ?? '';

    if (!$usuarioId) {
        echo json_encode(['success' => false, 'message' => 'ID de usuario faltante']);
        exit;
    }

    $ok = $model->guardarInfoProductor($usuarioId, $nombre, $telefono, $correo, $direccion);

    // Guardar CUIT en tabla usuarios
    $stmt = $pdo->prepare("UPDATE usuarios SET cuit = ? WHERE id = ?");
    $stmt->execute([$cuit, $usuarioId]);

    if ($ok) {
        echo json_encode(['success' => true, 'message' => 'Información actualizada correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se pudo guardar la información']);
    }
    exit;
}
