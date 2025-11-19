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

/**
 * Envía un correo transaccional usando la API de Brevo.
 *
 * @return array [bool $ok, ?string $error]
 */
function sve_sendBrevoEmail(string $toEmail, string $toName, string $subject, string $htmlContent, array $params = []): array
{
    if (!defined('BREVO_API_KEY') || !BREVO_API_KEY) {
        return [false, 'BREVO_API_KEY no definida o vacía'];
    }

    $senderEmail = defined('MAIL_FROM') ? MAIL_FROM : 'no-reply@sve.com.ar';
    $senderName  = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'SVE';

    $payload = [
        'sender' => [
            'email' => $senderEmail,
            'name'  => $senderName,
        ],
        'to' => [
            [
                'email' => $toEmail,
                'name'  => $toName,
            ],
        ],
        'subject'     => $subject,
        'htmlContent' => '<html><body>' . $htmlContent . '</body></html>',
    ];

    if (!empty($params)) {
        $payload['params'] = $params;
    }

    $ch = curl_init('https://api.brevo.com/v3/smtp/email');
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => [
            'accept: application/json',
            'api-key: ' . BREVO_API_KEY,
            'content-type: application/json',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        CURLOPT_TIMEOUT        => 15,
    ]);

    $response = curl_exec($ch);
    $curlErr  = curl_error($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        return [false, 'Error cURL: ' . $curlErr];
    }

    if ($status < 200 || $status >= 300) {
        return [false, 'HTTP ' . $status . ' - ' . $response];
    }

    return [true, null];
}

/**
 * Arma los cuerpos de correo para productor y cooperativa
 * y los envía usando Brevo.
 *
 * @return array [bool $okGlobal, ?string $errorMsg]
 */
function sve_enviarSolicitudDronViaBrevo(array $mailPayload): array
{
    $esc = static function (string $v): string {
        return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    };

    $okGlobal = true;
    $errors   = [];

    $solicitudId   = (int)($mailPayload['solicitud_id'] ?? 0);
    $productor     = $mailPayload['productor'] ?? [];
    $cooperativa   = $mailPayload['cooperativa'] ?? [];
    $motivos       = $mailPayload['motivos'] ?? [];
    $rangos        = $mailPayload['rangos'] ?? [];
    $productos     = $mailPayload['productos'] ?? [];
    $direccion     = $mailPayload['direccion'] ?? [];
    $ubicacion     = $mailPayload['ubicacion'] ?? [];
    $costos        = $mailPayload['costos'] ?? [];
    $pagoPorCoop   = !empty($mailPayload['pago_por_coop']);
    $ctaUrlBase    = (string)($mailPayload['cta_url'] ?? '');
    $coopTextoExtra= (string)($mailPayload['coop_texto_extra'] ?? '');

    $moneda     = (string)($costos['moneda'] ?? 'Pesos');
    $base       = (float)($costos['base'] ?? 0);
    $prodCosto  = (float)($costos['productos'] ?? 0);
    $total      = (float)($costos['total'] ?? 0);
    $costoHa    = (float)($costos['costo_ha'] ?? 0);
    $supHa      = (float)($mailPayload['superficie_ha'] ?? 0);

    $fmt = static function (float $n): string {
        return number_format($n, 2, ',', '.');
    };

    // Motivos
    $motivosHtml = '';
    foreach ($motivos as $m) {
        $motivosHtml .= '<li>' . $esc((string)$m) . '</li>';
    }

    // Rangos
    $rangosHtml = '';
    if (!empty($rangos)) {
        $rangosHtml = $esc(implode(', ', array_map('strval', $rangos)));
    }

    // Productos
    $productosHtml = '';
    foreach ($productos as $p) {
        $line = trim(
            (string)($p['patologia'] ?? '') . ' - ' .
            (string)($p['fuente'] ?? '') . ' - ' .
            (string)($p['detalle'] ?? '')
        );
        if ($line === '') {
            continue;
        }
        $productosHtml .= '<li>' . $esc($line) . '</li>';
    }

    // Dirección
    $dirParts = [];
    if (!empty($direccion['calle'])) {
        $calleComp = (string)$direccion['calle'];
        if (!empty($direccion['numero'])) {
            $calleComp .= ' ' . $direccion['numero'];
        }
        $dirParts[] = $calleComp;
    }
    if (!empty($direccion['localidad'])) {
        $dirParts[] = (string)$direccion['localidad'];
    }
    if (!empty($direccion['provincia'])) {
        $dirParts[] = (string)$direccion['provincia'];
    }
    $direccionTexto = $esc($dirParts ? implode(', ', $dirParts) : 'No informada');

    // Ubicación
    $ubicacionTexto = 'No informada';
    if (!empty($ubicacion['lat']) && !empty($ubicacion['lng'])) {
        $ubicacionTexto = 'Lat: ' . $esc((string)$ubicacion['lat']) .
            ' - Lng: ' . $esc((string)$ubicacion['lng']);
    }

    /** ========== CORREO A PRODUCTOR ========== */
    $prodEmail = trim((string)($productor['correo'] ?? ''));
    if ($prodEmail !== '') {
        $prodNombre   = (string)($productor['nombre'] ?? '');
        $subjectProd  = 'Solicitud de servicio de dron #' . $solicitudId;

        $htmlProd = ''
            . '<h2>Confirmación de solicitud de servicio de dron</h2>'
            . '<p>Hola ' . $esc($prodNombre) . ',</p>'
            . '<p>Hemos recibido tu solicitud de servicio de pulverización con dron.</p>'
            . '<p><strong>Número de solicitud:</strong> ' . $esc((string)$solicitudId) . '</p>'
            . '<h3>Resumen</h3>'
            . '<ul>'
            . '<li><strong>Superficie:</strong> ' . $esc($fmt($supHa)) . ' ha</li>'
            . '<li><strong>Forma de pago:</strong> ' . $esc((string)($mailPayload['forma_pago'] ?? '')) . '</li>'
            . '<li><strong>Motivos:</strong><ul>' . $motivosHtml . '</ul></li>'
            . '<li><strong>Momento deseado:</strong> ' . $rangosHtml . '</li>'
            . '<li><strong>Dirección finca:</strong> ' . $direccionTexto . '</li>'
            . '<li><strong>Ubicación GPS (si se capturó):</strong> ' . $esc($ubicacionTexto) . '</li>'
            . '</ul>'
            . '<h3>Productos</h3>'
            . '<ul>' . ($productosHtml ?: '<li>No informados</li>') . '</ul>'
            . '<h3>Costos estimados</h3>'
            . '<ul>'
            . '<li><strong>Moneda:</strong> ' . $esc($moneda) . '</li>'
            . '<li><strong>Costo base por ha:</strong> ' . $esc($fmt($costoHa)) . '</li>'
            . '<li><strong>Costo base total:</strong> ' . $esc($fmt($base)) . '</li>'
            . '<li><strong>Costo productos SVE:</strong> ' . $esc($fmt($prodCosto)) . '</li>'
            . '<li><strong>Total estimado:</strong> ' . $esc($fmt($total)) . '</li>'
            . '</ul>'
            . '<p>Este correo es solo informativo. Ante cualquier duda, comunicate con SVE.</p>';

        [$okProd, $errProd] = sve_sendBrevoEmail(
            $prodEmail,
            $prodNombre,
            $subjectProd,
            $htmlProd,
            [
                'solicitud_id' => $solicitudId,
                'tipo'         => 'productor',
            ]
        );

        if (!$okProd) {
            $okGlobal  = false;
            $errors[] = 'Productor: ' . $errProd;
        }
    }

    /** ========== CORREO A COOPERATIVA (SI CORRESPONDE) ========== */
    if ($pagoPorCoop) {
        $coopEmail = trim((string)($cooperativa['correo'] ?? ''));
        if ($coopEmail !== '') {
            $coopNombre  = (string)($cooperativa['nombre'] ?? '');
            $subjectCoop = 'Solicitud de servicio de dron para aprobación #' . $solicitudId;

            $approveUrl = '';
            $declineUrl = '';
            if ($ctaUrlBase !== '') {
                $sep        = (strpos($ctaUrlBase, '?') === false) ? '?' : '&';
                $approveUrl = $ctaUrlBase . $sep . 'accion=aprobar_solicitud_dron&id=' . urlencode((string)$solicitudId);
                $declineUrl = $ctaUrlBase . $sep . 'accion=declinar_solicitud_dron&id=' . urlencode((string)$solicitudId);
            }

            $htmlCoop = ''
                . '<h2>Solicitud de servicio de dron para aprobación</h2>'
                . '<p>Productor: <strong>' . $esc((string)$productor['nombre'] ?? '') . '</strong></p>'
                . '<p>Superficie solicitada: <strong>' . $esc($fmt($supHa)) . ' ha</strong></p>'
                . '<p>Forma de pago: <strong>' . $esc((string)($mailPayload['forma_pago'] ?? '')) . '</strong></p>'
                . '<h3>Motivos</h3>'
                . '<ul>' . $motivosHtml . '</ul>'
                . '<h3>Costos estimados</h3>'
                . '<ul>'
                . '<li><strong>Moneda:</strong> ' . $esc($moneda) . '</li>'
                . '<li><strong>Costo base total:</strong> ' . $esc($fmt($base)) . '</li>'
                . '<li><strong>Costo productos SVE:</strong> ' . $esc($fmt($prodCosto)) . '</li>'
                . '<li><strong>Total estimado:</strong> ' . $esc($fmt($total)) . '</li>'
                . '</ul>'
                . '<p>' . nl2br($esc($coopTextoExtra)) . '</p>';

            if ($approveUrl !== '' && $declineUrl !== '') {
                $htmlCoop .= ''
                    . '<p style="margin-top:20px;">'
                    . '<a href="' . $esc($approveUrl) . '" '
                    . 'style="display:inline-block;margin-right:10px;padding:10px 16px;background-color:#16a34a;color:#fff;text-decoration:none;border-radius:4px;">'
                    . 'Aprobar solicitud'
                    . '</a>'
                    . '<a href="' . $esc($declineUrl) . '" '
                    . 'style="display:inline-block;padding:10px 16px;background-color:#dc2626;color:#fff;text-decoration:none;border-radius:4px;">'
                    . 'Declinar solicitud'
                    . '</a>'
                    . '</p>';
            }

            [$okCoop, $errCoop] = sve_sendBrevoEmail(
                $coopEmail,
                $coopNombre,
                $subjectCoop,
                $htmlCoop,
                [
                    'solicitud_id' => $solicitudId,
                    'tipo'         => 'cooperativa',
                ]
            );

            if (!$okCoop) {
                $okGlobal  = false;
                $errors[] = 'Cooperativa: ' . $errCoop;
            }
        }
    }

    return [$okGlobal, $okGlobal ? null : implode(' | ', $errors)];
}

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

        // ¿Pago por cooperativa? (id = 6)
        $formaPagoId = (int)($data['forma_pago_id'] ?? 0);
        $esPagoCoop  = ($formaPagoId === 6);

        // Cooperativa destino:
        // - Si es pago por cooperativa (id 6), usar la seleccionada por el productor (usuarios.id_real == coop_descuento_nombre).
        // - Si no, usar la primera vinculada al productor (como antes).
        $coop = ['coop_nombre' => null, 'coop_correo' => null];

        if ($esPagoCoop && !empty($data['coop_descuento_nombre'])) {
            $sqlCoopSel = "
                SELECT ui.nombre AS coop_nombre, ui.correo AS coop_correo
                FROM usuarios u
                LEFT JOIN usuarios_info ui ON ui.usuario_id = u.id
                WHERE u.rol='cooperativa' AND u.permiso_ingreso='Habilitado' AND u.id_real = ?
                LIMIT 1
            ";
            $stCoopSel = $pdo->prepare($sqlCoopSel);
            $stCoopSel->execute([(string)$data['coop_descuento_nombre']]);
            $coop = $stCoopSel->fetch(PDO::FETCH_ASSOC) ?: ['coop_nombre' => null, 'coop_correo' => null];
        } else {
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
        }

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
            // Señal para construir versión especial para cooperativa y drones
            'pago_por_coop'   => $esPagoCoop,
            // URL de destino para los botones del correo de cooperativa
            'cta_url'         => 'https://compraconjunta.sve.com.ar/index.php',
            // Texto extra requerido por negocio (se usa sólo en el cuerpo para cooperativa/drones)
            'coop_texto_extra' => "Estimada cooperativa. Por el presente correo se les informa que un productor vinculado a su cooperativa a manifestado la intención de tomar el servicio de dron y de pagarlo a través del descuento por la cuota de vino. \nSi este productor productor posee los fondos necesarios para llevar a cabo el pago por favor apruébelo seleccionado el botón que dice Aprobar Solicitud el cual se encuentra al final de este correo. \nEn caso de que el productor no este en condiciones de pagarlo por esta vía por favor haga click en el botón que dice Declinar Solicitud el cual se encuentra al final de este correo. \nAnte cualquier duda por favor comuníquese al 2612072518.",
        ];

// ===== Envío de correo con sistema anterior (SMTP / Mail.php) =====
        // Se deja comentado temporalmente para poder probar el envío con Brevo.
        //
        // $mailResp = Maill::enviarSolicitudDron($mailPayload);
        // $mailOk = (bool)($mailResp['ok'] ?? false);
        // $mailErr = $mailResp['error'] ?? null;

        // ===== Nuevo envío de correo usando Brevo API =====
        [$mailOk, $mailErr] = sve_enviarSolicitudDronViaBrevo($mailPayload);
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
