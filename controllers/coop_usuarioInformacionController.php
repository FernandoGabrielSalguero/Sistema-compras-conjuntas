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

// 🔄 GET: obtener ID real disponible
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
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
    $nuevoId = $model->crearUsuarioProductor($usuario, $contrasena, $cuit, $proximoId);

    // Asociar al productor con la cooperativa
    $model->asociarProductorCooperativa($proximoId, $cooperativaIdReal);

    echo json_encode([
        'success' => true,
        'message' => 'Productor creado correctamente',
        'id_real' => $proximoId
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al crear usuario: ' . $e->getMessage()]);
}

// 🔄 GET: listar productores asociados
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'listar_productores') {
    $productores = $model->obtenerProductoresPorCooperativa($cooperativaIdReal);
    echo json_encode(['success' => true, 'productores' => $productores]);
    exit;
}

// 📝 POST: editar datos de productor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'editar_productor') {
    $usuarioId = $_POST['usuario_id'] ?? null;
    $nombre = $_POST['nombre'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $direccion = $_POST['direccion'] ?? '';

    if (!$usuarioId) {
        echo json_encode(['success' => false, 'message' => 'ID de usuario faltante']);
        exit;
    }

    $ok = $model->guardarInfoProductor($usuarioId, $nombre, $telefono, $correo, $direccion);

    if ($ok) {
        echo json_encode(['success' => true, 'message' => 'Información actualizada correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se pudo guardar la información']);
    }
    exit;
}
