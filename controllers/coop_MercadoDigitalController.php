<?php
// Silenciar errores en pantalla, pero seguir registrándolos en logs
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/errores.log');
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/coop_MercadoDigitalModel.php';

$model = new CoopMercadoDigitalModel($pdo);

if (isset($_GET['consultar_datos_productor']) && isset($_GET['id_real'])) {
    $idReal = $_GET['id_real'];
    $stmt = $pdo->prepare("
        SELECT 
            u.id_real, u.cuit, 
            i.telefono 
        FROM usuarios u 
        LEFT JOIN usuarios_info i ON i.usuario_id = u.id 
        WHERE u.id_real = ?
    ");
    $stmt->execute([$idReal]);
    $datos = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($datos ?: []);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    file_put_contents(__DIR__ . '/../debug_payload.log', print_r($data, true)); // ✅ ahora sí

    if (!isset($data['accion'])) {
        echo json_encode(['success' => false, 'message' => 'Acción no especificada']);
        exit;
    }

    // 👉 Acción: Guardar pedido
    if ($data['accion'] === 'guardar_pedido') {
        try {
            $resultado = $model->guardarPedidoConDetalles($data);
            echo json_encode(['success' => true, 'message' => 'Pedido guardado con éxito', 'pedido_id' => $resultado]);
        } catch (Exception $e) {
            http_response_code(500);
            error_log("🧨 Error al guardar pedido: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al guardar el pedido: ' . $e->getMessage()]);
        }
        exit;
    }

    // 👉 Acción: Actualizar datos del productor (CUIT y teléfono)
    if ($data['accion'] === 'actualizar_datos_productor') {
        $idReal = $data['id_real'];
        $telefono = $data['telefono'];
        $cuit = $data['cuit'];

        try {
            // Buscar el ID del usuario a partir de id_real
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE id_real = ?");
            $stmt->execute([$idReal]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                echo json_encode(['success' => false, 'message' => 'Productor no encontrado']);
                exit;
            }

            $usuarioId = $usuario['id'];

            // Actualizar CUIT en usuarios
            $stmt = $pdo->prepare("UPDATE usuarios SET cuit = ? WHERE id = ?");
            $stmt->execute([$cuit, $usuarioId]);

            // Actualizar teléfono en usuarios_info
            $stmt = $pdo->prepare("UPDATE usuarios_info SET telefono = ? WHERE usuario_id = ?");
            $stmt->execute([$telefono, $usuarioId]);

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al actualizar los datos: ' . $e->getMessage()]);
        }

        exit;
    }

    // 👉 Si no coincide ninguna acción
    echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
    exit;
}


if (isset($_GET['listar']) && $_GET['listar'] === 'cooperativas') {
    $data = $model->listarCooperativas();
    echo json_encode($data);
    exit;
}

if (isset($_GET['listar']) && $_GET['listar'] === 'productores' && isset($_GET['coop_id'])) {
    $data = $model->listarProductoresPorCooperativa($_GET['coop_id']);
    echo json_encode($data);
    exit;
}

if (isset($_GET['listar']) && $_GET['listar'] === 'productos_categorizados') {
    $data = $model->obtenerProductosAgrupadosPorCategoria();
    echo json_encode($data);
    exit;
}

if (isset($_GET['listar']) && $_GET['listar'] === 'operativos_abiertos' && isset($_GET['coop_id'])) {
    $data = $model->obtenerOperativosActivosPorCooperativa($_GET['coop_id']);
    echo json_encode($data);
    exit;
}

if (isset($_GET['listar']) && $_GET['listar'] === 'productos_por_operativo' && isset($_GET['operativo_id'])) {
    $operativoId = (int)$_GET['operativo_id'];
    $data = $model->obtenerProductosPorOperativo($operativoId);
    echo json_encode($data);
    exit;
}
