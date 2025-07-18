<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../config.php';

$carpetaDestino = __DIR__ . '/../uploads/tax_invoices/';
$extensionesPermitidas = ['pdf', 'jpg', 'jpeg', 'png'];

// 🔸 1. Subida de múltiples facturas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['facturas'])) {
    $pedidoId = intval($_POST['pedido_id'] ?? 0);
    if (!$pedidoId) {
        echo json_encode(['success' => false, 'message' => 'ID de pedido inválido']);
        exit;
    }

    try {
        // Validar cuántas hay ya subidas
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM factura_pedidos WHERE pedido_id = ?");
        $stmt->execute([$pedidoId]);
        $cantidadActual = $stmt->fetchColumn();

        $archivos = $_FILES['facturas'];
        $totalNuevos = count($archivos['name']);

        if ($cantidadActual + $totalNuevos > 30) {
            echo json_encode(['success' => false, 'message' => 'Máximo 30 facturas por pedido']);
            exit;
        }

        for ($i = 0; $i < $totalNuevos; $i++) {
            $nombreOriginal = $archivos['name'][$i];
            $tmp = $archivos['tmp_name'][$i];
            $error = $archivos['error'][$i];

            if ($error !== UPLOAD_ERR_OK) continue;

            $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
            if (!in_array($ext, $extensionesPermitidas)) continue;

            $nombreFinal = 'factura_' . $pedidoId . '_' . time() . '_' . uniqid() . '.' . $ext;
            $rutaFinal = $carpetaDestino . $nombreFinal;

            if (move_uploaded_file($tmp, $rutaFinal)) {
                $stmt = $pdo->prepare("INSERT INTO factura_pedidos (pedido_id, nombre_archivo, extension) VALUES (?, ?, ?)");
                $stmt->execute([$pedidoId, $nombreFinal, $ext]);
            }
        }

        echo json_encode(['success' => true]);
        exit;
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al guardar archivos']);
        exit;
    }
}

// 🔹 2. Listar facturas por pedido
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['listar']) && isset($_GET['id'])) {
    $pedidoId = intval($_GET['id']);
    try {
        $stmt = $pdo->prepare("SELECT id, nombre_archivo, extension FROM factura_pedidos WHERE pedido_id = ?");
        $stmt->execute([$pedidoId]);
        $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $facturas]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener facturas']);
    }
    exit;
}

// 🔻 3. Eliminar factura individual
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = json_decode(file_get_contents("php://input"), true);
    if (isset($json['accion']) && $json['accion'] === 'eliminar_factura_multiple') {
        $facturaId = intval($json['id']);
        if (!$facturaId) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            exit;
        }

        try {
            // Obtener nombre del archivo
            $stmt = $pdo->prepare("SELECT nombre_archivo FROM factura_pedidos WHERE id = ?");
            $stmt->execute([$facturaId]);
            $nombre = $stmt->fetchColumn();

            if ($nombre && file_exists($carpetaDestino . $nombre)) {
                unlink($carpetaDestino . $nombre);
            }

            $pdo->prepare("DELETE FROM factura_pedidos WHERE id = ?")->execute([$facturaId]);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar factura']);
        }
        exit;
    }
}

// ❌ Si no coincide ningún endpoint
http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Solicitud no válida']);
exit;
