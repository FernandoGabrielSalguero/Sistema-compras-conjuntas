<?php
session_start();
header('Content-Type: application/json');

// Verificar login y rol
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'cooperativa') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

require_once '../../config/conexion.php';
require_once 'coop_usuarioInformacionModel.php';

$model = new UsuarioInformacionModel();

$cooperativaIdReal = $_SESSION['id_real'] ?? null;
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
