<?php

ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/errores.log');
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/coop_serviciosVendimialesModel.php';
require_once __DIR__ . '/../mail/Mail.php';

use SVE\Mail\Mail;

header('Content-Type: application/json; charset=utf-8');

$model = new CoopServiciosVendimialesModel($pdo);
$coopNombre = $_SESSION['nombre'] ?? null;
$coopIdReal = $_SESSION['id_real'] ?? null;

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
            'contrato' => $model->obtenerContratoVigente()
        ]);
        exit;
    }

    if ($action === 'productos') {
        $servicioId = (int)($_GET['servicio_id'] ?? 0);
        if ($servicioId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Servicio inválido.']);
            exit;
        }
        echo json_encode([
            'success' => true,
            'productos' => $model->obtenerProductosActivosPorServicio($servicioId)
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
    $productoId = (int)($data['producto_id'] ?? 0);
    $volumen = $data['volumenAproximado'] ?? null;
    $unidad = trim((string)($data['unidad_volumen'] ?? 'litros'));
    $fechaEntrada = $data['fecha_entrada_equipo'] ?? null;
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
            'producto_id' => $productoId > 0 ? $productoId : null,
            'volumenAproximado' => $volumen !== '' ? $volumen : null,
            'unidad_volumen' => $unidad ?: 'litros',
            'fecha_entrada_equipo' => $fechaEntrada ?: null,
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

        // Enviar correo con detalles
        $coopCorreo = null;
        if ($coopIdReal) {
            $stmt = $pdo->prepare("
                SELECT ui.nombre AS nombre, ui.correo AS correo
                FROM usuarios u
                LEFT JOIN usuarios_info ui ON ui.usuario_id = u.id
                WHERE u.id_real = ?
                LIMIT 1
            ");
            $stmt->execute([$coopIdReal]);
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $coopCorreo = $row['correo'] ?? null;
            }
        }

        $servicioNombre = null;
        if ($servicio > 0) {
            $stmt = $pdo->prepare("SELECT nombre FROM serviciosVendimiales_serviciosOfrecidos WHERE id = ? LIMIT 1");
            $stmt->execute([$servicio]);
            $servicioNombre = $stmt->fetchColumn() ?: null;
        }

        $mailResp = Mail::enviarSolicitudServiciosVendimiales([
            'pedido_id' => $pedidoId,
            'cooperativa_nombre' => $coopNombre,
            'cooperativa_correo' => $coopCorreo,
            'cooperativa_id_real' => $coopIdReal,
            'solicitante_nombre' => $nombre,
            'solicitante_cargo' => $cargo,
            'servicio_nombre' => $servicioNombre,
            'volumen' => $volumen,
            'unidad' => $unidad,
            'fecha_entrada' => $fechaEntrada,
            'observaciones' => $observaciones,
            'contrato_aceptado' => $aceptaContrato ? 'Sí' : 'No'
        ]);

        echo json_encode([
            'success' => true,
            'pedido_id' => $pedidoId,
            'mail_ok' => (bool)($mailResp['ok'] ?? false)
        ]);
    } catch (Exception $e) {
        error_log('Error crear pedido servicios vendimiales: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al guardar la solicitud.']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Solicitud no válida.']);
