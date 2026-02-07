<?php
// Silenciar errores en pantalla, pero seguir registrándolos en logs
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/errores.log');
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/sve_MercadoDigitalModel.php';
require_once __DIR__ . '/../mail/Mail.php';

use SVE\Mail\Mail;

$model = new SveMercadoDigitalModel($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    file_put_contents(__DIR__ . '/../debug_payload.log', print_r($data, true));

    if (!isset($data['accion']) || $data['accion'] !== 'guardar_pedido') {
        echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
        exit;
    }

    try {
        $pedidoId = $model->guardarPedidoConDetalles($data);

        // Datos cooperativa
        $coopNombre = '';
        $coopCorreo = null;
        $stmtCoop = $pdo->prepare("
            SELECT ui.nombre AS nombre, ui.correo AS correo
            FROM usuarios u
            LEFT JOIN usuarios_info ui ON ui.usuario_id = u.id
            WHERE u.id_real = ?
            LIMIT 1
        ");
        $stmtCoop->execute([$data['cooperativa'] ?? '']);
        if ($row = $stmtCoop->fetch(PDO::FETCH_ASSOC)) {
            $coopNombre = (string)($row['nombre'] ?? '');
            $coopCorreo = $row['correo'] ?? null;
        }

        // Datos productor
        $prodNombre = '';
        $prodCorreo = null;
        $stmtProd = $pdo->prepare("
            SELECT ui.nombre AS nombre, ui.correo AS correo
            FROM usuarios u
            LEFT JOIN usuarios_info ui ON ui.usuario_id = u.id
            WHERE u.id_real = ?
            LIMIT 1
        ");
        $stmtProd->execute([$data['productor'] ?? '']);
        if ($row = $stmtProd->fetch(PDO::FETCH_ASSOC)) {
            $prodNombre = (string)($row['nombre'] ?? '');
            $prodCorreo = $row['correo'] ?? null;
        }

        // Operativo
        $opNombre = '';
        if (!empty($data['operativo_id'])) {
            $stmtOp = $pdo->prepare("SELECT nombre FROM operativos WHERE id = ? LIMIT 1");
            $stmtOp->execute([$data['operativo_id']]);
            if ($row = $stmtOp->fetch(PDO::FETCH_ASSOC)) {
                $opNombre = (string)($row['nombre'] ?? '');
            }
        }

        // Items (recalculo por seguridad)
        $items = [];
        if (!empty($data['productos']) && is_array($data['productos'])) {
            foreach ($data['productos'] as $p) {
                $nombre   = (string)($p['nombre'] ?? 'Producto');
                $cantidad = (float)($p['cantidad'] ?? 0);
                $unidad   = (string)($p['unidad'] ?? '');
                $precio   = (float)($p['precio'] ?? 0);
                $alicuota = (float)($p['alicuota'] ?? 0);

                if ($cantidad <= 0 || $precio < 0) {
                    continue;
                }

                $subtotal = $precio * $cantidad;
                $iva      = $subtotal * ($alicuota / 100.0);
                $total    = $subtotal + $iva;

                $items[] = [
                    'nombre'   => $nombre,
                    'cantidad' => $cantidad,
                    'unidad'   => $unidad,
                    'precio'   => $precio,
                    'alicuota' => $alicuota,
                    'subtotal' => $subtotal,
                    'iva'      => $iva,
                    'total'    => $total,
                ];
            }
        }

        // Totales
        $totales = [
            'sin_iva' => (float)($data['totales']['sin_iva'] ?? 0),
            'iva'     => (float)($data['totales']['iva'] ?? 0),
            'con_iva' => (float)($data['totales']['con_iva'] ?? 0),
        ];

        // Enviar correo
        $mailOk = false;
        $mailError = null;
        try {
            $mailResp = Mail::enviarCompraRealizadaSVE([
                'cooperativa_nombre' => $coopNombre ?: 'Cooperativa',
                'cooperativa_correo' => $coopCorreo,
                'productor_nombre'   => $prodNombre ?: 'Productor',
                'productor_correo'   => $prodCorreo,
                'operativo_nombre'   => $opNombre ?: '',
                'items'              => $items,
                'totales'            => $totales,
            ]);
            $mailOk    = (bool)($mailResp['ok'] ?? false);
            $mailError = $mailResp['error'] ?? null;
        } catch (\Throwable $e) {
            $mailOk = false;
            $mailError = $e->getMessage();
            error_log('✉️ Error al enviar correo de pedido (SVE): ' . $mailError);
        }

        echo json_encode([
            'success'   => true,
            'message'   => 'Pedido guardado con éxito',
            'pedido_id' => $pedidoId,
            'mail_ok'   => $mailOk,
            'mail_error' => $mailError,
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        error_log("🧨 Error al guardar pedido: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error al guardar el pedido: ' . $e->getMessage()
        ]);
    }
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

// ✅ Lista todos los operativos activos
if (isset($_GET['listar']) && $_GET['listar'] === 'operativos') {
    $stmt = $pdo->query("SELECT id, nombre, fecha_inicio, fecha_cierre FROM operativos WHERE estado = 'abierto' ORDER BY fecha_inicio DESC");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data);
    exit;
}

// ✅ Lista productos de un operativo específico
if (isset($_GET['listar']) && $_GET['listar'] === 'productos_operativo' && isset($_GET['id'])) {
    $operativoId = (int) $_GET['id'];
    $stmt = $pdo->prepare("
        SELECT 
            p.Id as producto_id,
            p.Nombre_producto,
            p.Unidad_Medida_venta,
            p.Precio_producto,
            p.moneda,
            p.alicuota,
            p.categoria,
            p.Detalle_producto
        FROM productos p
        JOIN operativos_productos op ON op.producto_id = p.Id
        WHERE op.operativo_id = ?
        ORDER BY p.categoria, p.Nombre_producto
    ");
    $stmt->execute([$operativoId]);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $agrupados = [];
    foreach ($productos as $p) {
        $categoria = $p['categoria'];
        if (!isset($agrupados[$categoria])) {
            $agrupados[$categoria] = [];
        }
        $agrupados[$categoria][] = $p;
    }

    echo json_encode($agrupados);
    exit;
}
