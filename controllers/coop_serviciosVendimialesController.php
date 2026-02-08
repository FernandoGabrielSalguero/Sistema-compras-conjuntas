<?php

ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/errores.log');
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/coop_serviciosVendimialesModel.php';

header('Content-Type: application/json; charset=utf-8');

$model = new CoopServiciosVendimialesModel($pdo);
$coopNombre = $_SESSION['nombre'] ?? null;

if (!$coopNombre) {
    echo json_encode(['success' => false, 'message' => 'Sesión inválida.']);
    exit;
}

// GET actions
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';

    if ($action === 'init') {
        echo json_encode([
            'success' => true,
            'servicios' => $model->obtenerServiciosActivos(),
            'centrifugadoras' => $model->obtenerCentrifugadorasActivas(),
            'contrato' => $model->obtenerContratoVigente()
        ]);
        exit;
    }

    if ($action === 'listar_pedidos') {
        echo json_encode([
            'success' => true,
            'pedidos' => $model->listarPedidosPorCooperativa($coopNombre)
        ]);
        exit;
    }
}

// POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';

    if ($action !== 'crear_pedido') {
        echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
        exit;
    }

    $nombre = trim((string)($data['nombre'] ?? ''));
    $cargo = trim((string)($data['cargo'] ?? ''));
    $servicio = (int)($data['servicioAcontratar'] ?? 0);
    $volumen = $data['volumenAproximado'] ?? null;
    $unidad = trim((string)($data['unidad_volumen'] ?? 'litros'));
    $fechaEntrada = $data['fecha_entrada_equipo'] ?? null;
    $equipo = $data['equipo_centrifugadora'] ?? null;
    $observaciones = trim((string)($data['observaciones'] ?? ''));
    $aceptaContrato = isset($data['acepta_contrato']) ? (bool)$data['acepta_contrato'] : false;
    $contratoId = (int)($data['contrato_id'] ?? 0);
    $snapshot = (string)($data['contrato_snapshot'] ?? '');

    if (!$aceptaContrato) {
        echo json_encode(['success' => false, 'message' => 'Debés firmar el contrato antes de solicitar el servicio.']);
        exit;
    }

    if ($nombre === '' || $servicio <= 0) {
        echo json_encode(['success' => false, 'message' => 'Nombre y servicio son obligatorios.']);
        exit;
    }

    try {
        $pedidoId = $model->crearPedido([
            'cooperativa' => $coopNombre,
            'nombre' => $nombre,
            'cargo' => $cargo ?: null,
            'servicioAcontratar' => $servicio,
            'volumenAproximado' => $volumen !== '' ? $volumen : null,
            'unidad_volumen' => $unidad ?: 'litros',
            'fecha_entrada_equipo' => $fechaEntrada ?: null,
            'equipo_centrifugadora' => $equipo ?: null,
            'estado' => 'SOLICITADO',
            'observaciones' => $observaciones ?: null
        ]);

        if ($aceptaContrato && $contratoId > 0) {
            $model->registrarFirma([
                'pedido_id' => $pedidoId,
                'contrato_id' => $contratoId,
                'aceptado' => 1,
                'firmado_por' => $coopNombre,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'snapshot' => $snapshot
            ]);
        }

        echo json_encode(['success' => true, 'pedido_id' => $pedidoId]);
    } catch (Exception $e) {
        error_log('Error crear pedido servicios vendimiales: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al guardar la solicitud.']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Solicitud no válida.']);
