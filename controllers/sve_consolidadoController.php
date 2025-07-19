<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/sve_consolidadoModel.php';

$model = new sveConsolidadoModel($pdo);

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'sve') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'operativos') {
    try {
        $stmt = $pdo->query("
            SELECT DISTINCT o.id, o.nombre
            FROM pedidos ped
            INNER JOIN operativos o ON ped.operativo_id = o.id
            ORDER BY o.fecha_inicio DESC
        ");
        $operativos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'operativos' => $operativos]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener operativos: ' . $e->getMessage()]);
    }
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'cooperativas') {
    try {
        $stmt = $pdo->query("
            SELECT DISTINCT
                ped.cooperativa AS id,
                ui.nombre AS nombre
            FROM pedidos ped
            LEFT JOIN usuarios u ON ped.cooperativa = u.id_real
            LEFT JOIN usuarios_info ui ON u.id = ui.usuario_id
            WHERE ped.cooperativa IS NOT NULL
            ORDER BY ui.nombre ASC
        ");
        $cooperativas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'cooperativas' => $cooperativas]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener cooperativas: ' . $e->getMessage()]);
    }
    exit;
}

// Descargar tabla extendida
if (isset($_GET['action']) && $_GET['action'] === 'descargar_extendido') {
    try {
        $operativo_id = $_GET['operativo_id'] ?? null;
        $cooperativa_id = $_GET['cooperativa_id'] ?? null;
        $data = $model->obtenerPedidosExtendidos($operativo_id, $cooperativa_id);
        echo json_encode(['success' => true, 'pedidos' => $data]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error extendido: ' . $e->getMessage()]);
    }
    exit;
}

// Obtener consolidado (solo si no hay action explícito)
if (!isset($_GET['action'])) {
    try {
        $operativo_id = $_GET['operativo_id'] ?? null;
        $cooperativa_id = $_GET['cooperativa_id'] ?? null;

        $data = $model->obtenerConsolidadoPedidos($operativo_id, $cooperativa_id);
        echo json_encode(['success' => true, 'consolidado' => $data]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error extendido: ' . $e->getMessage()]);
    }
    exit;
}
