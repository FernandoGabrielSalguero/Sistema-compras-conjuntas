<?php
// CONTROLLER del drawer: detalle + actualización
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../model/drone_drawerListado_model.php';
require_once __DIR__ . '/../../../../mail/Mail.php';

use SVE\Mail\Mail;

function read_json_body(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

try {
    $model  = new DroneDrawerListadoModel($pdo);
    $action = $_GET['action'] ?? 'get_detalle';

    switch ($action) {
        case 'get_detalle': {
                $id = (int)($_GET['id'] ?? 0);
                if ($id <= 0) {
                    http_response_code(400);
                    echo json_encode(['ok' => false, 'error' => 'ID inválido'], JSON_UNESCAPED_UNICODE);
                    break;
                }
                $detalle = $model->obtenerSolicitudFull($id);
                if (!$detalle) {
                    http_response_code(404);
                    echo json_encode(['ok' => false, 'error' => 'Solicitud no encontrada'], JSON_UNESCAPED_UNICODE);
                    break;
                }
                echo json_encode(['ok' => true, 'data' => $detalle], JSON_UNESCAPED_UNICODE);
                break;
            }

        case 'update_solicitud': {
                $body = read_json_body();
                try {
                    if (!isset($body['id']) || !is_numeric($body['id'])) {
                        http_response_code(400);
                        echo json_encode(['ok' => false, 'error' => 'Falta id (num)'], JSON_UNESCAPED_UNICODE);
                        break;
                    }
                    $body['id'] = (int)$body['id'];

                    // 1) Snapshot ANTES
                    $antes = $model->obtenerSolicitudFull($body['id']);

                    // 2) Actualizar
                    $updatedId = $model->actualizarSolicitud($body);

                    // 3) Snapshot DESPUÉS
                    $despues = $model->obtenerSolicitudFull($updatedId);

                    // 4) Construir resumen de cambios
                    $mk = function ($v) {
                        if ($v === null || $v === '') return '—';
                        if (is_float($v) || is_int($v)) return number_format((float)$v, 2, ',', '.');
                        return (string)$v;
                    };

                    $cambios = [];
                    $cmp = function ($campo, $label) use ($antes, $despues, $mk, &$cambios) {
                        $a = $antes['solicitud'][$campo] ?? null;
                        $d = $despues['solicitud'][$campo] ?? null;
                        if ($a !== $d) {
                            $cambios[] = ['campo' => $label, 'antes' => $mk($a), 'despues' => $mk($d)];
                        }
                    };

                    // Campos simples
                    $cmp('estado', 'Estado');
                    $cmp('fecha_visita', 'Fecha visita');
                    $cmp('hora_visita_desde', 'Hora desde');
                    $cmp('hora_visita_hasta', 'Hora hasta');
                    $cmp('piloto_id', 'Piloto');
                    $cmp('forma_pago_id', 'Forma de pago');
                    $cmp('superficie_ha', 'Superficie (ha)');
                    $cmp('observaciones', 'Observaciones');

                    // Costos (snapshot efectivo)
                    $costA = $antes['costos'] ?? null;
                    $costD = $despues['costos'] ?? null;
                    if ($costA && $costD) {
                        if ((float)$costA['base_total'] !== (float)$costD['base_total']) {
                            $cambios[] = ['campo' => 'Base total', 'antes' => $mk($costA['base_total']), 'despues' => $mk($costD['base_total'])];
                        }
                        if ((float)$costA['productos_total'] !== (float)$costD['productos_total']) {
                            $cambios[] = ['campo' => 'Productos total', 'antes' => $mk($costA['productos_total']), 'despues' => $mk($costD['productos_total'])];
                        }
                        if ((float)$costA['total'] !== (float)$costD['total']) {
                            $cambios[] = ['campo' => 'Total', 'antes' => $mk($costA['total']), 'despues' => $mk($costD['total'])];
                        }
                    }

                    // Items (por simplicidad: lista comparativa)
                    $itemsA = array_map(fn($i) => ($i['producto_nombre'] ?? $i['nombre_producto'] ?? 'Producto') . ' (id ' . ($i['producto_id'] ?? '—') . ')', $antes['items'] ?? []);
                    $itemsD = array_map(fn($i) => ($i['producto_nombre'] ?? $i['nombre_producto'] ?? 'Producto') . ' (id ' . ($i['producto_id'] ?? '—') . ')', $despues['items'] ?? []);
                    if (json_encode($itemsA, JSON_UNESCAPED_UNICODE) !== json_encode($itemsD, JSON_UNESCAPED_UNICODE)) {
                        $cambios[] = [
                            'campo' => 'Productos (lista)',
                            'antes' => $itemsA ? implode(', ', $itemsA) : '—',
                            'despues' => $itemsD ? implode(', ', $itemsD) : '—'
                        ];
                    }

                    // Motivos (patologías)
                    $motA = array_map(fn($m) => $m['patologia_nombre'] ?? ($m['otros_text'] ?? 'Otro'), $antes['motivos'] ?? []);
                    $motD = array_map(fn($m) => $m['patologia_nombre'] ?? ($m['otros_text'] ?? 'Otro'), $despues['motivos'] ?? []);
                    if (json_encode($motA, JSON_UNESCAPED_UNICODE) !== json_encode($motD, JSON_UNESCAPED_UNICODE)) {
                        $cambios[] = [
                            'campo' => 'Motivos',
                            'antes' => $motA ? implode(', ', $motA) : '—',
                            'despues' => $motD ? implode(', ', $motD) : '—'
                        ];
                    }

                    // Rangos
                    $ranA = array_map(fn($r) => $r['rango'] ?? '', $antes['rangos'] ?? []);
                    $ranD = array_map(fn($r) => $r['rango'] ?? '', $despues['rangos'] ?? []);
                    if (json_encode($ranA, JSON_UNESCAPED_UNICODE) !== json_encode($ranD, JSON_UNESCAPED_UNICODE)) {
                        $cambios[] = [
                            'campo' => 'Rangos',
                            'antes' => $ranA ? implode(', ', $ranA) : '—',
                            'despues' => $ranD ? implode(', ', $ranD) : '—'
                        ];
                    }

                    // 5) Enviar correo
                    $payloadMail = [
                        'solicitud_id'    => (int)$updatedId,
                        'estado_anterior' => $antes['solicitud']['estado'] ?? null,
                        'estado_actual'   => $despues['solicitud']['estado'] ?? null,
                        'productor'       => [
                            'nombre' => $despues['productor']['nombre'] ?? ($despues['productor']['usuario'] ?? null),
                            'correo' => $despues['productor']['correo'] ?? null
                        ],
                        'cooperativas'    => array_map(function ($c) {
                            return [
                                'usuario' => $c['cooperativa_usuario'] ?? null,
                                'correo'  => $c['cooperativa_correo'] ?? null
                            ];
                        }, $despues['productor']['cooperativas'] ?? []),
                        'cambios'         => $cambios,
                        'costos'          => [
                            'moneda' => $despues['costos']['moneda'] ?? 'Pesos',
                            'base_total' => (float)($despues['costos']['base_total'] ?? 0),
                            'productos_total' => (float)($despues['costos']['productos_total'] ?? 0),
                            'total' => (float)($despues['costos']['total'] ?? 0),
                        ]
                    ];
                    // Ignorar errores de email en la respuesta HTTP: se loguea en server (si PHPMailer lanza).
                    try {
                        $rol = (string)($_SESSION['rol'] ?? '');
                        if ($rol === 'sve') {
                            Mail::enviarSolicitudDronActualizadaSVE($payloadMail);
                        } else {
                            Mail::enviarSolicitudDronActualizadaCooperativa($payloadMail);
                        }
                    } catch (\Throwable $__) {
                    }

                    echo json_encode(['ok' => true, 'data' => ['id' => $updatedId]], JSON_UNESCAPED_UNICODE);
                } catch (InvalidArgumentException $e) {
                    http_response_code(400);
                    echo json_encode(['ok' => false, 'error' => $e->getMessage(), 'detail' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
                } catch (Throwable $e) {
                    http_response_code(500);
                    echo json_encode(['ok' => false, 'error' => 'Error al actualizar', 'detail' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
                }
                break;
            }

            // === NUEVOS: catálogos para el drawer ===
        case 'list_pilotos': {
                echo json_encode(['ok' => true, 'data' => $model->listPilotos()], JSON_UNESCAPED_UNICODE);
                break;
            }
        case 'list_formas_pago': {
                echo json_encode(['ok' => true, 'data' => $model->listFormasPago()], JSON_UNESCAPED_UNICODE);
                break;
            }
        case 'list_patologias': {
                echo json_encode(['ok' => true, 'data' => $model->listPatologias()], JSON_UNESCAPED_UNICODE);
                break;
            }
        case 'list_productos': {
                echo json_encode(['ok' => true, 'data' => $model->listProductos()], JSON_UNESCAPED_UNICODE);
                break;
            }
        case 'list_cooperativas': {
                echo json_encode(['ok' => true, 'data' => $model->listCooperativas()], JSON_UNESCAPED_UNICODE);
                break;
            }

        default: {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Acción no soportada'], JSON_UNESCAPED_UNICODE);
                break;
            }
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error del servidor', 'detail' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
