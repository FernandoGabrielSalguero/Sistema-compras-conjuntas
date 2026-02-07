<?php

declare(strict_types=1);

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/errores.log');
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/coop_MercadoDigitalModel.php';
require_once __DIR__ . '/../mail/Mail.php';

use SVE\Mail\Mail;

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
        header('Content-Type: application/json; charset=utf-8');

        try {
            // 1) Guardar pedido y obtener ID
            $pedidoId = $model->guardarPedidoConDetalles($data);

            // 2) Preparar datos para e-mail
            //    a) Cooperativa (se recibe id_real en $data['cooperativa'])
            $coopNombre = '';
            $coopCorreo = null;

            $stmtCoop = $pdo->prepare("
            SELECT ui.nombre AS nombre, ui.correo AS correo
            FROM usuarios u
            LEFT JOIN usuarios_info ui ON ui.usuario_id = u.id
            WHERE u.id_real = ?
            LIMIT 1
        ");
            $stmtCoop->execute([$data['cooperativa']]);
            if ($row = $stmtCoop->fetch(PDO::FETCH_ASSOC)) {
                $coopNombre = (string)($row['nombre'] ?? '');
                $coopCorreo = $row['correo'] ?? null;
            }

            //    b) Operativo
            $opNombre = '';
            $stmtOp = $pdo->prepare("SELECT nombre FROM operativos WHERE id = ? LIMIT 1");
            $stmtOp->execute([$data['operativo_id']]);
            if ($row = $stmtOp->fetch(PDO::FETCH_ASSOC)) {
                $opNombre = (string)($row['nombre'] ?? '');
            }

            //    b.1) Productor
            $prodNombre = '';
            $prodCorreo = null;
            $stmtProd = $pdo->prepare("
                SELECT ui.nombre AS nombre, ui.correo AS correo
                FROM usuarios u
                LEFT JOIN usuarios_info ui ON ui.usuario_id = u.id
                WHERE u.id_real = ?
                LIMIT 1
            ");
            $stmtProd->execute([$data['productor']]);
            if ($row = $stmtProd->fetch(PDO::FETCH_ASSOC)) {
                $prodNombre = (string)($row['nombre'] ?? '');
                $prodCorreo = $row['correo'] ?? null;
            }

            //    c) Items (recalculo por seguridad)
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

            //    d) Totales
            $totales = [
                'sin_iva' => (float)($data['totales']['sin_iva'] ?? 0),
                'iva'     => (float)($data['totales']['iva'] ?? 0),
                'con_iva' => (float)($data['totales']['con_iva'] ?? 0),
            ];

            // 3) Enviar correo
            $mailOk = false;
            $mailError = null;

            try {
                $mailResp = Mail::enviarCompraRealizadaCooperativa([
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
                error_log('✉️ Error al enviar correo de pedido: ' . $mailError);
            }

            echo json_encode([
                'success'   => true,
                'message'   => 'Pedido guardado con éxito',
                'pedido_id' => $pedidoId,
                'mail_ok'   => $mailOk,
                'mail_error' => $mailError,
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            http_response_code(500);
            error_log("🧨 Error al guardar pedido: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error al guardar el pedido: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
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
