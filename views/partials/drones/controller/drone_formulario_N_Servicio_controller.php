<?php

declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../model/drone_formulario_N_Servicio_model.php';

$resp = fn(array $data = [], bool $ok = true, ?string $error = null) =>
print json_encode($ok ? ['ok' => true, 'data' => $data] : ['ok' => false, 'error' => $error ?? 'Error'], JSON_UNESCAPED_UNICODE);

try {
    $model = new DroneFormularioNservicioModel();
    $model->pdo = $pdo;
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    $action = $_GET['action'] ?? '';

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
            case 'buscar_usuarios':
                $q = trim((string)($_GET['q'] ?? ''));
                if (mb_strlen($q) < 2) {
                    $resp([], true);
                    break;
                }
                $resp($model->buscarUsuarios($q));
                break;
            case 'productos_por_patologia':
                $pid = (int)($_GET['patologia_id'] ?? 0);
                if ($pid <= 0) {
                    $resp([], true);
                    break;
                }
                $resp($model->productosPorPatologia($pid));
                break;
            case 'rangos':
                $resp($model->rangos());
                break;
            default:
                $resp(['message' => 'pong']);
                break;
        }
        exit;
    }

    if ($method === 'POST') {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            $resp([], false, 'JSON inválido');
            exit;
        }

        // Sanitización mínima y normalización
        $payload = [
            'productor_id_real'   => empty($data['productor_id_real']) ? null : substr((string)$data['productor_id_real'], 0, 20),
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
            'patologia_ids'       => array_values(array_filter(array_map(
                fn($v) => (int)$v,
                is_array($data['patologia_ids'] ?? null) ? $data['patologia_ids'] : []
            ))),
            'patologia_id'        => isset($data['patologia_id']) ? (int)$data['patologia_id'] : ( // compat: tomar primero si no llega patologia_id
                (!empty($data['patologia_ids']) ? (int)$data['patologia_ids'][0] : null)
            ),
            'rango'               => (string)($data['rango'] ?? ''),
            // items: [{producto_id, fuente}]
            'items'               => array_values(array_filter(array_map(function ($it) {
                if (!is_array($it)) return null;
                $pid = isset($it['producto_id']) ? (int)$it['producto_id'] : 0;
                $fuente = in_array($it['fuente'] ?? '', ['sve', 'productor'], true) ? $it['fuente'] : '';
                return $pid > 0 ? ['producto_id' => $pid, 'fuente' => $fuente] : null;
            }, $data['items'] ?? []))),

            'productos_fuente'    => in_array($data['productos_fuente'] ?? '', ['sve', 'productor'], true) ? $data['productos_fuente'] : null,
            'dir_provincia'       => substr(trim((string)($data['dir_provincia'] ?? '')), 0, 100),
            'dir_localidad'       => substr(trim((string)($data['dir_localidad'] ?? '')), 0, 100),
            'dir_calle'           => substr(trim((string)($data['dir_calle'] ?? '')), 0, 150),
            'dir_numero'          => substr(trim((string)($data['dir_numero'] ?? '')), 0, 20),
            'observaciones'       => isset($data['observaciones']) ? (string)$data['observaciones'] : null,
        ];

        // Validaciones mínimas
        $requeridos = ['representante', 'linea_tension', 'zona_restringida', 'corriente_electrica', 'agua_potable', 'libre_obstaculos', 'area_despegue', 'superficie_ha', 'forma_pago_id', 'rango', 'dir_provincia', 'dir_localidad', 'dir_calle', 'dir_numero'];
        foreach ($requeridos as $k) {
            if (empty($payload[$k]) && $payload[$k] !== 0 && $payload[$k] !== '0') {
                $resp([], false, "Campo requerido faltante: $k");
                exit;
            }
        }
        if ($payload['forma_pago_id'] === 6 && empty($payload['coop_descuento_nombre'])) {
            $resp([], false, "Debe seleccionar cooperativa (forma de pago 6).");
            exit;
        }
        // Si se incluyeron productos, cada item debe tener fuente
        if (!empty($payload['items'])) {
            foreach ($payload['items'] as $it) {
                if (empty($it['fuente'])) {
                    $resp([], false, "Indicá quién aporta cada producto seleccionado.");
                    exit;
                }
            }
        }

        // Validar patologías (múltiple)
        if (empty($payload['patologia_id']) && empty($payload['patologia_ids'])) {
            $resp([], false, "Debe seleccionar al menos una patología.");
            exit;
        }
        if (!empty($payload['patologia_ids'])) {
            $payload['patologia_ids'] = array_values(array_unique(array_filter($payload['patologia_ids'], fn($v) => $v > 0)));
            if (empty($payload['patologia_ids'])) {
                $resp([], false, "Debe seleccionar al menos una patología válida.");
                exit;
            }
            if (empty($payload['patologia_id'])) {
                $payload['patologia_id'] = $payload['patologia_ids'][0];
            }
        } else {
            $payload['patologia_ids'] = [$payload['patologia_id']];
        }


        $res = $model->crearSolicitud($payload);
        if ($res['ok'] ?? false) {
            $resp(['id' => $res['id']]);
        } else {
            $resp([], false, $res['error'] ?? 'No se pudo crear la solicitud');
        }
        exit;
    }

    $resp([], false, 'Método no permitido');
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'error' => 'Excepción: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
