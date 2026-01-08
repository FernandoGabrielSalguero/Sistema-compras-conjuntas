<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/coop_cosechaMecanicaModel.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'cooperativa') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit;
}

$cooperativa_id = $_SESSION['id_real'] ?? null;

$model = new CoopCosechaMecanicaModel($pdo);

$action = $_GET['action'] ?? $_POST['action'] ?? null;

// Listar operativos para la cooperativa
if ($action === 'listar_operativos') {
    if (!$cooperativa_id) {
        echo json_encode([
            'success' => false,
            'message' => 'No se encontró la cooperativa en la sesión.'
        ]);
        exit;
    }
    try {
        $operativos = $model->obtenerOperativos($cooperativa_id);

        // Normalizamos contrato_firmado a entero 0/1 para que el front lo lea sin problemas
        foreach ($operativos as &$op) {
            if (isset($op['contrato_firmado'])) {
                $op['contrato_firmado'] = (int) $op['contrato_firmado'];
            } else {
                $op['contrato_firmado'] = 0;
            }
        }
        unset($op);

        echo json_encode([
            'success' => true,
            'data'    => $operativos
        ]);
    } catch (Throwable $e) {
        error_log('Error listar_operativos: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'No se pudieron obtener los operativos.'
        ]);
    }
    exit;
}

// Obtener un operativo puntual junto con la participación de la cooperativa
// Enviar correos pendientes de cierre al usuario logueado
if ($action === 'enviar_cierre_pendiente') {
    if (!$cooperativa_id) {
        echo json_encode(['success' => false, 'message' => 'No se encontro la cooperativa en la sesion.']);
        exit;
    }

    $correo = trim((string) ($_SESSION['correo'] ?? ''));
    if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Correo de sesion invalido.']);
        exit;
    }

    try {
        require_once __DIR__ . '/../mail/Mail.php';

        $nomCooperativa = $_SESSION['nombre'] ?? 'Cooperativa';
        $operativos = $model->obtenerOperativos($cooperativa_id);
        $enviados = 0;

        foreach ($operativos as $op) {
            if (($op['estado'] ?? '') !== 'cerrado') {
                continue;
            }

            $contratoId = (int) ($op['id'] ?? 0);
            if ($contratoId <= 0) {
                continue;
            }

            if ($model->correoCierreEnviado($contratoId, $cooperativa_id)) {
                continue;
            }

            $participaciones = $model->obtenerParticipacionesPorContratoYCoop($contratoId, $nomCooperativa);
            $firma = $model->obtenerFirmaContrato($contratoId, $cooperativa_id);
            $fechaFirma = $firma['fecha_firma'] ?? null;

            $mailResp = \SVE\Mail\Maill::enviarCierreCosechaMecanica([
                'cooperativa_nombre' => (string) $nomCooperativa,
                'cooperativa_correo' => $correo,
                'operativo'          => $op,
                'participaciones'    => $participaciones,
                'firma_fecha'        => $fechaFirma,
            ]);

            if (!($mailResp['ok'] ?? false)) {
                $err = $mailResp['error'] ?? 'Error desconocido';
                error_log('Error enviar_cierre_pendiente: ' . $err);
                continue;
            }

            $model->registrarCorreoCierre($contratoId, $cooperativa_id, $correo, 'manual');
            $enviados++;
        }

        echo json_encode(['success' => true, 'enviados' => $enviados]);
    } catch (Throwable $e) {
        error_log('Error enviar_cierre_pendiente: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al enviar correos pendientes.']);
    }
    exit;
}

if ($action === 'obtener_operativo') {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID inválido.']);
        exit;
    }

    if (!$cooperativa_id) {
        echo json_encode(['success' => false, 'message' => 'No se encontró la cooperativa en la sesión.']);
        exit;
    }

    try {
        $operativo = $model->obtenerOperativoPorId($id);

        if (!$operativo) {
            echo json_encode(['success' => false, 'message' => 'Operativo no encontrado.']);
        } else {
            $nomCooperativa  = $_SESSION['nombre'] ?? 'Cooperativa';
            $participaciones = $model->obtenerParticipacionesPorContratoYCoop($id, $nomCooperativa);
            $firma           = $model->obtenerFirmaContrato($id, $cooperativa_id);
            $contratoFirmado = $firma !== null && (int) $firma['acepto'] === 1;

            echo json_encode([
                'success' => true,
                'data'    => [
                    'operativo'        => $operativo,
                    'participaciones'  => $participaciones,
                    'contrato_firmado' => $contratoFirmado,
                    'firma_contrato'   => $firma,
                ]
            ]);
        }
    } catch (Throwable $e) {
        error_log('Error obtener_operativo: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'No se pudo obtener el operativo.'
        ]);
    }
    exit;
}

// Enviar correo de cierre manual al usuario logueado
if ($action === 'enviar_cierre_manual') {
    $contratoId = isset($_POST['contrato_id']) ? (int) $_POST['contrato_id'] : (int) ($_GET['id'] ?? 0);

    if ($contratoId <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de contrato invalido.']);
        exit;
    }

    if (!$cooperativa_id) {
        echo json_encode(['success' => false, 'message' => 'No se encontro la cooperativa en la sesion.']);
        exit;
    }

    $correo = trim((string) ($_SESSION['correo'] ?? ''));
    if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Correo de sesion invalido.']);
        exit;
    }

    try {
        require_once __DIR__ . '/../mail/Mail.php';

        if ($model->correoCierreEnviado($contratoId, $cooperativa_id)) {
            echo json_encode(['success' => false, 'message' => 'El correo ya fue enviado para este contrato.']);
            exit;
        }

        $operativo = $model->obtenerOperativoPorId($contratoId);
        if (!$operativo) {
            echo json_encode(['success' => false, 'message' => 'Operativo no encontrado.']);
            exit;
        }

        $nomCooperativa  = $_SESSION['nombre'] ?? 'Cooperativa';
        $participaciones = $model->obtenerParticipacionesPorContratoYCoop($contratoId, $nomCooperativa);
        $firma           = $model->obtenerFirmaContrato($contratoId, $cooperativa_id);
        $fechaFirma      = $firma['fecha_firma'] ?? null;

        $mailResp = \SVE\Mail\Maill::enviarCierreCosechaMecanica([
            'cooperativa_nombre' => (string) $nomCooperativa,
            'cooperativa_correo' => $correo,
            'operativo'          => $operativo,
            'participaciones'    => $participaciones,
            'firma_fecha'        => $fechaFirma,
        ]);

        if (!($mailResp['ok'] ?? false)) {
            $err = $mailResp['error'] ?? 'Error desconocido';
            echo json_encode(['success' => false, 'message' => 'No se pudo enviar el correo.', 'error' => $err]);
            exit;
        }

        $model->registrarCorreoCierre($contratoId, $cooperativa_id, $correo, 'manual');

        echo json_encode(['success' => true, 'message' => 'Correo enviado.']);
    } catch (Throwable $e) {
        error_log('Error enviar_cierre_manual: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al enviar el correo.']);
    }
    exit;
}

// Enviar correo de cierre (test manual) al usuario logueado
if ($action === 'enviar_cierre_test') {
    $contratoId = isset($_POST['contrato_id']) ? (int) $_POST['contrato_id'] : (int) ($_GET['id'] ?? 0);

    if ($contratoId <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de contrato invalido.']);
        exit;
    }

    if (!$cooperativa_id) {
        echo json_encode(['success' => false, 'message' => 'No se encontro la cooperativa en la sesion.']);
        exit;
    }

    $correo = trim((string) ($_SESSION['correo'] ?? ''));
    if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Correo de sesion invalido.']);
        exit;
    }

    try {
        require_once __DIR__ . '/../mail/Mail.php';

        $operativo = $model->obtenerOperativoPorId($contratoId);
        if (!$operativo) {
            echo json_encode(['success' => false, 'message' => 'Operativo no encontrado.']);
            exit;
        }

        $nomCooperativa  = $_SESSION['nombre'] ?? 'Cooperativa';
        $participaciones = $model->obtenerParticipacionesPorContratoYCoop($contratoId, $nomCooperativa);
        $firma           = $model->obtenerFirmaContrato($contratoId, $cooperativa_id);
        $fechaFirma      = $firma['fecha_firma'] ?? null;

        $mailResp = \SVE\Mail\Maill::enviarCierreCosechaMecanica([
            'cooperativa_nombre' => (string) $nomCooperativa,
            'cooperativa_correo' => $correo,
            'operativo'          => $operativo,
            'participaciones'    => $participaciones,
            'firma_fecha'        => $fechaFirma,
        ]);

        if (!($mailResp['ok'] ?? false)) {
            $err = $mailResp['error'] ?? 'Error desconocido';
            echo json_encode(['success' => false, 'message' => 'No se pudo enviar el correo.', 'error' => $err]);
            exit;
        }

        echo json_encode(['success' => true, 'message' => 'Correo enviado.']);
    } catch (Throwable $e) {
        error_log('Error enviar_cierre_test: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al enviar el correo.']);
    }
    exit;
}

// Listar productores asociados a la cooperativa
if ($action === 'listar_productores') {
    if (!$cooperativa_id) {
        echo json_encode([
            'success' => false,
            'message' => 'No se encontró la cooperativa en la sesión.'
        ]);
        exit;
    }

    try {
        $productores = $model->obtenerProductoresPorCooperativa($cooperativa_id);

        echo json_encode([
            'success' => true,
            'data'    => $productores
        ]);
    } catch (Throwable $e) {
        error_log('Error listar_productores: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'No se pudieron obtener los productores.'
        ]);
    }
    exit;
}

// Listar fincas asociadas a un productor (por id_real)
if ($action === 'listar_fincas_productor') {
    $productorIdReal = isset($_GET['productor_id_real']) ? trim($_GET['productor_id_real']) : '';

    if ($productorIdReal === '') {
        echo json_encode([
            'success' => false,
            'message' => 'ID de productor inválido.'
        ]);
        exit;
    }

    try {
        $fincas = $model->obtenerFincasPorProductor($productorIdReal);

        echo json_encode([
            'success' => true,
            'data'    => $fincas
        ]);
    } catch (Throwable $e) {
        error_log('Error listar_fincas_productor: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'No se pudieron obtener las fincas del productor.'
        ]);
    }
    exit;
}

// Firmar contrato de cosecha mecánica por parte de la cooperativa
if ($action === 'firmar_contrato') {
    $contratoId = isset($_POST['contrato_id']) ? (int) $_POST['contrato_id'] : 0;

    if ($contratoId <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de contrato inválido.'
        ]);
        exit;
    }

    if (!$cooperativa_id) {
        echo json_encode([
            'success' => false,
            'message' => 'No se encontró la cooperativa en la sesión.'
        ]);
        exit;
    }

    try {
        $model->firmarContrato($contratoId, $cooperativa_id);

        echo json_encode([
            'success' => true,
            'message' => 'Contrato firmado en conformidad.'
        ]);
    } catch (Throwable $e) {
        error_log('Error firmar_contrato: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'No se pudo registrar la firma del contrato.'
        ]);
    }
    exit;
}

// Guardar participación de productores en un operativo
if ($action === 'guardar_participacion') {
    $contratoId = isset($_POST['contrato_id']) ? (int) $_POST['contrato_id'] : 0;
    $filasJson  = $_POST['filas'] ?? '[]';
    $filas      = json_decode($filasJson, true);

    if ($contratoId <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de contrato inválido.'
        ]);
        exit;
    }

    if (!is_array($filas)) {
        echo json_encode([
            'success' => false,
            'message' => 'Formato inválido de productores.'
        ]);
        exit;
    }

    $nomCooperativa = $_SESSION['nombre'] ?? 'Cooperativa';

    try {
        $model->guardarParticipaciones($contratoId, $nomCooperativa, $filas);

        echo json_encode([
            'success' => true,
            'message' => 'Participación guardada correctamente.'
        ]);
    } catch (Throwable $e) {
        error_log('Error guardar_participacion: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'No se pudo guardar la participación.'
        ]);
    }
    exit;
}

// Acción no reconocida
echo json_encode([
    'success' => false,
    'message' => 'Acción no válida.'
]);
exit;
