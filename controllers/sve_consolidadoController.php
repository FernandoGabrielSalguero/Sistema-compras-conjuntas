<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/coop_consolidadoModel.php';

$model = new sveConsolidadoModel($pdo);

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'sve') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit;
}

$cooperativa_id = $_SESSION['id_real'] ?? null;

// Si se solicita la lista de operativos
if (isset($_GET['action']) && $_GET['action'] === 'operativos') {
    try {
        $stmt = $pdo->prepare("
    SELECT o.id, o.nombre
    FROM operativos o
    INNER JOIN operativos_cooperativas_participacion ocp 
        ON o.id = ocp.operativo_id
    WHERE ocp.cooperativa_id_real = :coop_id
    AND ocp.participa = 'si'
    ORDER BY o.fecha_inicio DESC
");
        $stmt->execute(['coop_id' => $cooperativa_id]);
        $operativos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'operativos' => $operativos]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener operativos: ' . $e->getMessage()]);
    }
    exit;
}

try {
    $operativo_id = $_GET['operativo_id'] ?? null;
    $data = $model->obtenerConsolidadoPedidos($cooperativa_id, $operativo_id);
    echo json_encode(['success' => true, 'consolidado' => $data]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
exit;
