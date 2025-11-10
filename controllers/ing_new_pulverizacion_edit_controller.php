<?php

declare(strict_types=1);

ini_set('display_errors', '0');
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/ing_new_pulverizacion_edit_model.php';

$resp = function (array $data = [], bool $ok = true, ?string $err = null) {
    echo json_encode($ok ? ['ok' => true, 'data' => $data] : ['ok' => false, 'error' => $err ?? 'Error'], JSON_UNESCAPED_UNICODE);
};

try {
    session_start();
    $model  = new IngNewPulverizacionModel();
    $model->pdo = $pdo;
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    $action = $_GET['action'] ?? '';

    /* ===== GET ===== */
    if ($method === 'GET') {
        switch ($action) {
            case 'formas_pago':
                $resp($model->formasPago());
                break;
            case 'patologias':
                $resp($model->patologias());
                break;
            case 'cooperativas':
                $resp($model->cooperativas());
                break;
            case 'rangos':
                $resp($model->rangos());
                break;
            case 'costo_base_ha':
                $resp($model->costoBaseHectarea());
                break;
            case 'productos_por_patologia':
                $pid = (int)($_GET['patologia_id'] ?? 0);
                if ($pid <= 0) {
                    $resp([]);
                    break;
                }
                $resp($model->productosPorPatologia($pid));
                break;
            case 'buscar_usuarios':
                $q = trim((string)($_GET['q'] ?? ''));
                if (mb_strlen($q) < 2) {
                    $resp([]);
                    break;
                }
                $rol = strtolower((string)($_SESSION['user']['rol'] ?? $_SESSION['rol'] ?? 'ingeniero'));
                $idReal = (string)($_SESSION['user']['id_real'] ?? $_SESSION['id_real'] ?? '');
                $coopId = trim((string)($_GET['coop_id'] ?? '')) ?: null;
                $resp($model->buscarUsuariosFiltrado($q, $rol, $idReal, $coopId));
                break;
            case 'correo_por_id_real':
                $idReal = trim((string)($_GET['id_real'] ?? ''));
                $resp(['correo' => $model->correoPreferidoPorIdReal($idReal)]);
                break;
            default:
                $resp(['pong' => true]);
                break;
        }
        exit;
    }

    /* ===== POST ===== */
    if ($method === 'POST') {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            $resp([], false, 'JSON inválido');
            exit;
        }

        // Normalización (incluye patologia_id por item)
        $itemsIn = array_values(array_filter(array_map(function ($it) {
            if (!is_array($it)) return null;
            $pid    = isset($it['producto_id']) ? (int)$it['producto_id'] : 0;
            $fuente = in_array($it['fuente'] ?? '', ['sve', 'productor'], true) ? $it['fuente'] : '';
            $patId  = isset($it['patologia_id']) ? (int)$it['patologia_id'] : 0;
            $nombreCustom = isset($it['nombre_producto_custom']) ? trim((string)$it['nombre_producto_custom']) : '';
            if ($fuente === '' || $patId <= 0) return null;
            if ($fuente === 'sve') {
                if ($pid <= 0) return null;
                return ['producto_id' => $pid, 'fuente' => 'sve', 'patologia_id' => $patId];
            }
            // productor
            if ($fuente === 'productor') {
                if ($nombreCustom === '') return null;
                return [
                    'producto_id' => ($pid > 0 ? $pid : 0),
                    'fuente' => 'productor',
                    'nombre_producto_custom' => mb_substr($nombreCustom, 0, 150),
                    'patologia_id' => $patId
                ];
            }
            return null;
        }, $data['items'] ?? [])));

        $payload = [
            'productor_id_real'        => empty($data['productor_id_real']) ? null : substr((string)$data['productor_id_real'], 0, 20),
            'productor_nombre_snapshot' => isset($data['productor_nombre_snapshot']) ? mb_substr((string)$data['productor_nombre_snapshot'], 0, 150) : null,

            'representante'       => in_array($data['representante'] ?? '', ['si', 'no'], true) ? $data['representante'] : null,
            'linea_tension'       => in_array($data['linea_tension'] ?? '', ['si', 'no'], true) ? $data['linea_tension'] : null,
            'zona_restringida'    => in_array($data['zona_restringida'] ?? '', ['si', 'no'], true) ? $data['zona_restringida'] : null,
            'corriente_electrica' => in_array($data['corriente_electrica'] ?? '', ['si', 'no'], true) ? $data['corriente_electrica'] : null,
            'agua_potable'        => in_array($data['agua_potable'] ?? '', ['si', 'no'], true) ? $data['agua_potable'] : null,
            'libre_obstaculos'    => in_array($data['libre_obstaculos'] ?? '', ['si', 'no'], true) ? $data['libre_obstaculos'] : null,
            'area_despegue'       => in_array($data['area_despegue'] ?? '', ['si', 'no'], true) ? $data['area_despegue'] : null,

            'superficie_ha'       => isset($data['superficie_ha']) ? (float)$data['superficie_ha'] : null,
            'forma_pago_id'       => isset($data['forma_pago_id']) ? (int)$data['forma_pago_id'] : null,
            'coop_descuento_nombre' => !empty($data['coop_descuento_id_real']) ? substr((string)$data['coop_descuento_id_real'], 0, 100) : null,

            'patologia_ids'       => array_values(array_unique(array_filter(array_map('intval', (array)($data['patologia_ids'] ?? [])), fn($v) => $v > 0))),
            'rango'               => substr((string)($data['rango'] ?? ''), 0, 50),

            'dir_provincia'       => substr(trim((string)($data['dir_provincia'] ?? '')), 0, 100),
            'dir_localidad'       => substr(trim((string)($data['dir_localidad'] ?? '')), 0, 100),
            'dir_calle'           => substr(trim((string)($data['dir_calle'] ?? '')), 0, 150),
            'dir_numero'          => substr(trim((string)($data['dir_numero'] ?? '')), 0, 20),

            'observaciones'       => isset($data['observaciones']) ? (string)$data['observaciones'] : null,
            'items'               => $itemsIn
        ];

        // Requeridos
        $req = ['productor_id_real', 'representante', 'linea_tension', 'zona_restringida', 'corriente_electrica', 'agua_potable', 'libre_obstaculos', 'area_despegue', 'superficie_ha', 'forma_pago_id', 'patologia_ids', 'rango', 'dir_provincia', 'dir_localidad', 'dir_calle', 'dir_numero'];
        foreach ($req as $k) {
            if (empty($payload[$k]) && $payload[$k] !== 0 && $payload[$k] !== '0') {
                $resp([], false, "Campo requerido faltante: $k");
                exit;
            }
        }
        if (!($payload['superficie_ha'] > 0)) {
            $resp([], false, "superficie_ha debe ser > 0");
            exit;
        }
        if ($payload['forma_pago_id'] === 6 && empty($payload['coop_descuento_nombre'])) {
            $resp([], false, "Debe seleccionar cooperativa (forma de pago 6).");
            exit;
        }
        if (empty($payload['patologia_ids']) || !is_array($payload['patologia_ids'])) {
            $resp([], false, "Debe seleccionar al menos una patología.");
            exit;
        }

        // Validación items (si vienen)
        if (!empty($payload['items'])) {
            foreach ($payload['items'] as $it) {
                if (empty($it['fuente'])) {
                    $resp([], false, "Indicá quién aporta cada producto.");
                    exit;
                }
                if ((int)($it['patologia_id'] ?? 0) <= 0) {
                    $resp([], false, "Cada item debe indicar su patología.");
                    exit;
                }
                if ($it['fuente'] === 'sve' && (int)($it['producto_id'] ?? 0) <= 0) {
                    $resp([], false, "Producto inválido para SVE.");
                    exit;
                }
                if ($it['fuente'] === 'productor' && empty($it['nombre_producto_custom'])) {
                    $resp([], false, "Ingresá el nombre del producto del productor.");
                    exit;
                }
            }
        }

        // Crear
        $res = $model->crearSolicitud($payload);
        if (!($res['ok'] ?? false)) {
            $resp([], false, $res['error'] ?? 'No se pudo crear la solicitud');
            exit;
        }
        $id = (int)$res['id'];

        // Correos
        $prodId = (string)$payload['productor_id_real'];
        $coopId = $payload['coop_descuento_nombre'] ? (string)$payload['coop_descuento_nombre'] : '';
        $mailProd = $model->correoPreferidoPorIdReal($prodId);
        $mailCoop = $coopId !== '' ? $model->correoPreferidoPorIdReal($coopId) : null;

        // nombre efectivo (snapshot si vino, sino lookup)
        $nomProd = $payload['productor_nombre_snapshot'] ?: ($model->nombrePorIdReal($prodId) ?? 'Productor');
        $asunto  = "Solicitud de pulverización creada (ID #$id)";
        $lineas  = [
            "Hola $nomProd,",
            "Tu solicitud de pulverización con drones fue registrada.",
            "ID: #$id",
            "Hectáreas: " . $payload['superficie_ha'],
            "Patologías: " . implode(',', $payload['patologia_ids']),
            "Rango: " . $payload['rango'],
            "Dirección: " . $payload['dir_calle'] . ' ' . $payload['dir_numero'] . ', ' . $payload['dir_localidad'] . ', ' . $payload['dir_provincia'],
            "",
            "Si no fuiste vos, por favor contactá al soporte."
        ];
        $cuerpo = implode("\n", $lineas);

        @enviar_correo_simple($mailProd, $asunto, $cuerpo);
        if ($mailCoop) {
            @enviar_correo_simple($mailCoop, $asunto, "Cooperativa:\nSe creó la solicitud ID #$id para el productor $nomProd.\n" . $cuerpo);
        }

        $resp(['id' => $id]);
        exit;
    }

    $resp([], false, 'Método no permitido');
} catch (\Throwable $e) {
    echo json_encode(['ok' => false, 'error' => 'Excepción: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

/* mail() simple: reemplazar por tu lib SMTP si querés */
function enviar_correo_simple(?string $to, string $subject, string $body): bool
{
    if (!$to || !filter_var($to, FILTER_VALIDATE_EMAIL)) return false;
    $headers = [];
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-Type: text/plain; charset=UTF-8";
    $headers[] = "From: Notificaciones <no-reply@tudominio.local>";
    $headers[] = "X-Mailer: PHP/" . phpversion();
    return @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, implode("\r\n", $headers));
}
