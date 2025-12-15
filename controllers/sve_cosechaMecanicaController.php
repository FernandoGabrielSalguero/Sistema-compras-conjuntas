<?php

declare(strict_types=1);

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Limpiar TODOS los buffers antes de enviar JSON
while (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../models/sve_cosechaMecanicaModel.php';

function jsonResponse(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $modelo = new cosechaMecanicaModel();
} catch (Throwable $e) {
    error_log('[CosechaMecanica] Error al instanciar modelo: ' . $e->getMessage());
    jsonResponse([
        'ok' => false,
        'error' => 'Error interno de configuración.'
    ], 500);
}

$inputRaw = file_get_contents('php://input') ?: '';
$input = json_decode($inputRaw, true);

if (!is_array($input)) {
    $input = $_POST; // fallback por si vienen form-data tradicionales
}

$action = isset($input['action']) ? (string)$input['action'] : '';

if ($action === '') {
    jsonResponse([
        'ok' => false,
        'error' => 'Acción no especificada.'
    ], 400);
}

try {
    switch ($action) {
        case 'listar':
            $filters = $input['filters'] ?? [];
            $nombre = isset($filters['nombre']) ? (string)$filters['nombre'] : null;
            $estado = isset($filters['estado']) ? (string)$filters['estado'] : null;

            $data = $modelo->listarContratos($nombre, $estado);

            jsonResponse([
                'ok' => true,
                'data' => $data
            ]);
            break;

        case 'crear':
            $nombre = trim((string)($input['nombre'] ?? ''));
            $fechaApertura = (string)($input['fecha_apertura'] ?? '');
            $fechaCierre = (string)($input['fecha_cierre'] ?? '');
            $descripcion = isset($input['descripcion']) ? (string)$input['descripcion'] : null;
            $estado = (string)($input['estado'] ?? 'borrador');

            $costoBase = (string)($input['costo_base'] ?? '0');
            $bonOptima = (string)($input['bon_optima'] ?? '0');
            $bonMuyBuena = (string)($input['bon_muy_buena'] ?? '0');
            $bonBuena = (string)($input['bon_buena'] ?? '0');
            $anticipo = (string)($input['anticipo'] ?? '0');

            if ($nombre === '' || $fechaApertura === '' || $fechaCierre === '') {
                jsonResponse([
                    'ok' => false,
                    'error' => 'Nombre, fecha de apertura y fecha de cierre son obligatorios.'
                ], 422);
            }

            $nuevoId = $modelo->crearContrato([
                'nombre' => $nombre,
                'fecha_apertura' => $fechaApertura,
                'fecha_cierre' => $fechaCierre,
                'descripcion' => $descripcion,
                'estado' => $estado,
                'costo_base' => $costoBase,
                'bon_optima' => $bonOptima,
                'bon_muy_buena' => $bonMuyBuena,
                'bon_buena' => $bonBuena,
                'anticipo' => $anticipo
            ]);

            jsonResponse([
                'ok' => true,
                'data' => [
                    'id' => $nuevoId
                ]
            ], 201);
            break;


        case 'obtener':
            $id = isset($input['id']) ? (int)$input['id'] : 0;
            if ($id <= 0) {
                jsonResponse([
                    'ok' => false,
                    'error' => 'ID inválido.'
                ], 422);
            }

            $contrato = $modelo->obtenerContratoPorId($id);
            if ($contrato === null) {
                jsonResponse([
                    'ok' => false,
                    'error' => 'Contrato no encontrado.'
                ], 404);
            }

            jsonResponse([
                'ok' => true,
                'data' => $contrato
            ]);
            break;

        case 'actualizar':
            $id = isset($input['id']) ? (int)$input['id'] : 0;
            if ($id <= 0) {
                jsonResponse([
                    'ok' => false,
                    'error' => 'ID inválido.'
                ], 422);
            }

            // Verificar que el contrato exista antes de actualizar
            $contratoExistente = $modelo->obtenerContratoPorId($id);
            if ($contratoExistente === null) {
                jsonResponse([
                    'ok' => false,
                    'error' => 'Contrato no encontrado.'
                ], 404);
            }

            $nombre = trim((string)($input['nombre'] ?? ''));
            $fechaApertura = (string)($input['fecha_apertura'] ?? '');
            $fechaCierre = (string)($input['fecha_cierre'] ?? '');
            $descripcion = isset($input['descripcion']) ? (string)$input['descripcion'] : null;
            $estado = (string)($input['estado'] ?? 'borrador');

            $costoBase = (string)($input['costo_base'] ?? '0');
            $bonOptima = (string)($input['bon_optima'] ?? '0');
            $bonMuyBuena = (string)($input['bon_muy_buena'] ?? '0');
            $bonBuena = (string)($input['bon_buena'] ?? '0');
            $anticipo = (string)($input['anticipo'] ?? '0');

            if ($nombre === '' || $fechaApertura === '' || $fechaCierre === '') {
                jsonResponse([
                    'ok' => false,
                    'error' => 'Nombre, fecha de apertura y fecha de cierre son obligatorios.'
                ], 422);
            }

            $modelo->actualizarContrato($id, [
                'nombre' => $nombre,
                'fecha_apertura' => $fechaApertura,
                'fecha_cierre' => $fechaCierre,
                'descripcion' => $descripcion,
                'estado' => $estado,
                'costo_base' => $costoBase,
                'bon_optima' => $bonOptima,
                'bon_muy_buena' => $bonMuyBuena,
                'bon_buena' => $bonBuena,
                'anticipo' => $anticipo
            ]);

            jsonResponse([
                'ok' => true,
                'data' => [
                    'id' => $id
                ]
            ]);
            break;


        case 'participaciones':
            $id = isset($input['id']) ? (int)$input['id'] : 0;
            if ($id <= 0) {
                jsonResponse([
                    'ok' => false,
                    'error' => 'ID de contrato inválido.'
                ], 422);
            }

            $data = $modelo->obtenerParticipacionesPorContrato($id);

            jsonResponse([
                'ok' => true,
                'data' => $data
            ]);
            break;

        case 'eliminar':
            $id = isset($input['id']) ? (int)$input['id'] : 0;
            if ($id <= 0) {
                jsonResponse([
                    'ok' => false,
                    'error' => 'ID inválido.'
                ], 422);
            }

            $contrato = $modelo->obtenerContratoPorId($id);
            if ($contrato === null) {
                jsonResponse([
                    'ok' => false,
                    'error' => 'Contrato no encontrado.'
                ], 404);
            }

            try {
                $ok = $modelo->eliminarContrato($id);
                if (!$ok) {
                    jsonResponse([
                        'ok' => false,
                        'error' => 'No se pudo eliminar el contrato.'
                    ], 500);
                }
            } catch (PDOException $pe) {
                // Caso típico MySQL: restricción FK (no se puede borrar padre)
                $msg = $pe->getMessage();
                if (strpos($msg, 'SQLSTATE[23000]') !== false || strpos($msg, 'Cannot delete or update a parent row') !== false) {
                    jsonResponse([
                        'ok' => false,
                        'error' => 'No se puede eliminar: existen registros relacionados (FK).',
                        'debug' => [
                            'type' => 'PDOException',
                            'message' => $msg
                        ]
                    ], 409);
                }

                throw $pe; // lo toma el catch global y devuelve ref + debug
            }

            jsonResponse([
                'ok' => true,
                'data' => [
                    'id' => $id
                ]
            ]);
            break;

        default:
            jsonResponse([
                'ok' => false,
                'error' => 'Acción no soportada.'
            ], 400);
    }
} catch (Throwable $e) {
    $errId = bin2hex(random_bytes(6));

    error_log('[CosechaMecanica][' . $errId . '] Excepción en controlador: ' . $e->getMessage());
    error_log('[CosechaMecanica][' . $errId . '] Trace: ' . $e->getTraceAsString());

    // Devolvemos un mensaje útil para debugging sin romper JSON
    $debug = [
        'id' => $errId,
        'type' => get_class($e),
        'code' => (string)$e->getCode(),
        'message' => $e->getMessage(),
    ];

    jsonResponse([
        'ok' => false,
        'error' => 'Error interno (ref: ' . $errId . ').',
        'debug' => $debug
    ], 500);
}
