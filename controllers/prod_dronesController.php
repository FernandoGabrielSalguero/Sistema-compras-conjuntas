<?php

declare(strict_types=1);

ini_set('display_errors', '0');
error_reporting(E_ALL);
ob_start();

session_start();
header('Content-Type: application/json; charset=UTF-8');

// Middleware (igual que antes)
$mwPath = __DIR__ . '/../middleware/authMiddleware.php';
if (file_exists($mwPath)) {
    require_once $mwPath;
    if (function_exists('checkAccess')) {
        try {
            checkAccess('productor');
        } catch (Throwable $e) {
            http_response_code(403);
            ob_clean();
            echo json_encode(['ok' => false, 'error' => 'Acceso denegado']);
            exit;
        }
    }
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/prod_dronesModel.php';
require_once __DIR__ . '/../mail/Mail.php';

use SVE\Mail\Maill;

try {
    $model = new prodDronesModel($pdo);

    // GET catálogos (sin cambios)
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? '';

        if ($action === 'patologias') {
            $items = $model->getPatologiasActivas();
            http_response_code(200);
            ob_clean();
            echo json_encode(['ok' => true, 'data' => ['items' => $items]], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        if ($action === 'cooperativas') {
            $items = $model->getCooperativasHabilitadas();
            http_response_code(200);
            ob_clean();
            echo json_encode(['ok' => true, 'data' => ['items' => $items]], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        if ($action === 'productos') {
            $pid = isset($_GET['patologia_id']) ? (int)$_GET['patologia_id'] : 0;
            if ($pid <= 0) {
                http_response_code(400);
                ob_clean();
                echo json_encode(['ok' => false, 'error' => 'patologia_id inválido']);
                exit;
            }
            $items = $model->getProductosPorPatologia($pid);
            http_response_code(200);
            ob_clean();
            echo json_encode(['ok' => true, 'data' => ['items' => $items]], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        if ($action === 'formas_pago') {
            $items = $model->getFormasPagoActivas();
            http_response_code(200);
            ob_clean();
            echo json_encode(['ok' => true, 'data' => ['items' => $items]], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        if ($action === 'costo') {
            $row = $model->getCostoHectarea();
            http_response_code(200);
            ob_clean();
            echo json_encode(['ok' => true, 'data' => $row], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        http_response_code(400);
        ob_clean();
        echo json_encode(['ok' => false, 'error' => 'Acción GET no soportada']);
        exit;
    }

    // POST: crear solicitud (usa el modelo nuevo)
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        ob_clean();
        echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
        exit;
    }

    $raw  = file_get_contents('php://input') ?: '';
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        http_response_code(400);
        ob_clean();
        echo json_encode(['ok' => false, 'error' => 'JSON inválido']);
        exit;
    }

    $id = $model->crearSolicitud($data, $_SESSION);

    /** =======================
     *  Preparar y enviar e-mail
     *  ======================= */
    try {
        // Productor (desde sesión)
        $prodIdReal = (string)($_SESSION['id_real'] ?? '');
        $prodNombre = (string)($_SESSION['nombre'] ?? '');
        $prodCorreo = (string)($_SESSION['correo'] ?? '');

        // Cooperativa asociada (primera encontrada)
        $sqlCoop = "
        SELECT ui.nombre AS coop_nombre, ui.correo AS coop_correo
        FROM rel_productor_coop rpc
        INNER JOIN usuarios u ON u.id_real = rpc.cooperativa_id_real
        LEFT JOIN usuarios_info ui ON ui.usuario_id = u.id
        WHERE rpc.productor_id_real = ?
        ORDER BY rpc.id ASC
        LIMIT 1
    ";
        $stCoop = $pdo->prepare($sqlCoop);
        $stCoop->execute([$prodIdReal]);
        $coop = $stCoop->fetch(PDO::FETCH_ASSOC) ?: ['coop_nombre' => null, 'coop_correo' => null];

        // Forma de pago (texto)
        $formaPagoTxt = '';
        if (!empty($data['forma_pago_id'])) {
            $stFp = $pdo->prepare("SELECT nombre FROM dron_formas_pago WHERE id = ? LIMIT 1");
            $stFp->execute([(int)$data['forma_pago_id']]);
            $fp = $stFp->fetch(PDO::FETCH_ASSOC);
            $formaPagoTxt = (string)($fp['nombre'] ?? '');
        }

        // Costo base por ha (estimativo)
        $rowCosto = $model->getCostoHectarea();
        $costoBaseHa = (float)($rowCosto['costo'] ?? 0);
        $moneda      = (string)($rowCosto['moneda'] ?? 'Pesos');

        $superficie = (float)($data['superficie_ha'] ?? 0);
        $costoBaseTotal = $superficie > 0 ? $superficie * $costoBaseHa : 0.0;

        // Costo productos (estimativo del front: por patología/selección SVE)
        $costoProductos = 0.0;
        if (!empty($data['productos']) && is_array($data['productos'])) {
            foreach ($data['productos'] as $p) {
                if (($p['fuente'] ?? '') === 'sve' && !empty($p['producto_id'])) {
                    // Buscar costo_hectarea snapshot de catálogo
                    $stProd = $pdo->prepare("SELECT costo_hectarea FROM dron_productos_stock WHERE id = ? LIMIT 1");
                    $stProd->execute([(int)$p['producto_id']]);
                    $costoHa = (float)($stProd->fetchColumn() ?: 0);
                    $costoProductos += $superficie * $costoHa;
                }
            }
        }

        // Motivos legibles
        $motivosSel = (array)($data['motivo']['opciones'] ?? []);
        $motivoOtros = trim((string)($data['motivo']['otros'] ?? ''));
        if ($motivoOtros !== '') {
            $motivosSel[] = "Otros: {$motivoOtros}";
        }

        // Rango legible (simplemente pasamos códigos)
        $rangos = (array)($data['rango_fecha'] ?? []);

        // Productos legibles
        $productosLegibles = [];
        foreach ((array)($data['productos'] ?? []) as $p) {
            $productosLegibles[] = [
                'patologia' => (string)($p['patologia_nombre'] ?? ('#' . $p['patologia_id'] ?? '')),
                'fuente'    => (string)($p['fuente'] ?? ''),
                'detalle'   => (string)($p['marca'] ?? $p['producto_nombre'] ?? ''),
            ];
        }

        // Dirección & ubicación
        $direccion = (array)($data['direccion'] ?? []);
        $ubicacion = (array)($data['ubicacion'] ?? []);

        $mailPayload = [
            'solicitud_id'    => (int)$id,
            'productor'       => ['nombre' => $prodNombre, 'correo' => $prodCorreo],
            'cooperativa'     => ['nombre' => (string)($coop['coop_nombre'] ?? ''), 'correo' => (string)($coop['coop_correo'] ?? '')],
            'superficie_ha'   => $superficie,
            'forma_pago'      => $formaPagoTxt,
            'motivos'         => $motivosSel,
            'rangos'          => $rangos,
            'productos'       => $productosLegibles,
            'direccion'       => $direccion,
            'ubicacion'       => $ubicacion,
            'costos'          => [
                'moneda'   => $moneda,
                'base'     => $costoBaseTotal,
                'productos' => $costoProductos,
                'total'    => $costoBaseTotal + $costoProductos,
                'costo_ha' => $costoBaseHa
            ],
        ];

        $mailResp = Maill::enviarSolicitudDron($mailPayload);
        $mailOk = (bool)($mailResp['ok'] ?? false);
        $mailErr = $mailResp['error'] ?? null;
    } catch (Throwable $me) {
        $mailOk = false;
        $mailErr = $me->getMessage();
        error_log('✉️ Error al armar/enviar correo de solicitud dron: ' . $mailErr);
    }

    http_response_code(200);
    ob_clean();
    echo json_encode([
        'ok'      => true,
        'id'      => $id,
        'message' => 'Solicitud registrada correctamente',
        'mail_ok' => $mailOk,
        'mail_error' => $mailErr
    ], JSON_UNESCAPED_UNICODE);
    exit;
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    ob_clean();
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    exit;
} catch (RuntimeException $e) {
    http_response_code(403);
    ob_clean();
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    ob_clean();
    echo json_encode(['ok' => false, 'error' => 'Error interno.', 'detail' => $e->getMessage()]);
    exit;
}
