<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../middleware/authMiddleware.php';
require_once __DIR__ . '/../models/drone_pilot_dashboardModel.php';

header('Content-Type: application/json; charset=utf-8');

checkAccess('piloto_drone');

$usuarioId = $_SESSION['usuario_id'] ?? ($_SESSION['id'] ?? null);

$model  = new DronePilotDashboardModel($pdo);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_GET['action'] ?? $_POST['action'] ?? null;

function jsonResponse($ok, $data = null, $message = null, $code = 200)
{
    http_response_code($code);
    echo json_encode(['ok' => $ok, 'message' => $message, 'data' => $data], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (!$usuarioId) jsonResponse(false, null, 'Sesión inválida: faltan credenciales (usuario_id).', 401);

try {
    if ($method === 'GET') {
        if ($action === 'mis_solicitudes') {
            if (($_SESSION['rol'] ?? null) !== 'piloto_drone') jsonResponse(false, null, 'Acceso denegado para este recurso.', 403);
            $solicitudes = $model->getSolicitudesByPilotoId((int)$usuarioId);
            jsonResponse(true, $solicitudes);
        }

        if ($action === 'detalle_solicitud') {
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) jsonResponse(false, null, 'ID inválido.', 400);
            $sol = $model->getSolicitudDetalle($id, (int)$usuarioId);
            if (!$sol) jsonResponse(false, null, 'No encontrado o sin permisos.', 404);
            $rec = $model->getRecetaBySolicitud($id);
            $par = $model->getParametrosBySolicitud($id);
            jsonResponse(true, ['solicitud' => $sol, 'receta' => $rec, 'parametros' => $par]);
        }

        jsonResponse(false, null, 'Acción no soportada.', 400);
    }

    if ($method === 'POST') {
        if (($action ?? '') === 'crear_reporte') {
            // Validación básica
            $sid = (int)($_POST['solicitud_id'] ?? 0);
            if ($sid <= 0) jsonResponse(false, null, 'Solicitud inválida.', 400);

            // Asegurar que la solicitud pertenece al piloto
            $sol = $model->getSolicitudDetalle($sid, (int)$usuarioId);
            if (!$sol) jsonResponse(false, null, 'No encontrado o sin permisos.', 404);

            $payload = [
                'solicitud_id'        => $sid,
                'nom_cliente'         => trim($_POST['nom_cliente'] ?? ''),
                'nom_piloto'          => trim($_POST['nom_piloto'] ?? ''),
                'fecha_visita'        => $_POST['fecha_visita'] ?? null,
                'hora_ingreso'        => $_POST['hora_ingreso'] ?? null,
                'hora_egreso'         => $_POST['hora_egreso'] ?? null,
                'nombre_finca'        => trim($_POST['nombre_finca'] ?? ''),
                'cultivo_pulverizado' => trim($_POST['cultivo_pulverizado'] ?? ''),
                'cuadro_cuartel'      => trim($_POST['cuadro_cuartel'] ?? ''),
                'sup_pulverizada'     => $_POST['sup_pulverizada'] ?? null,
                'vol_aplicado'        => $_POST['vol_aplicado'] ?? null,
                'vel_viento'          => $_POST['vel_viento'] ?? null,
                'temperatura'         => $_POST['temperatura'] ?? null,
                'humedad_relativa'    => $_POST['humedad_relativa'] ?? null,
                'observaciones'       => trim($_POST['observaciones'] ?? ''),
            ];

            $pdo->beginTransaction();
            $reporteId = $model->crearReporte($payload);

            // Subir fotos
            $baseDir = __DIR__ . '/../uploads/ReporteDrones/' . $sid;
            if (!is_dir($baseDir)) @mkdir($baseDir, 0775, true);

            if (!empty($_FILES['fotos']['name'][0])) {
                $count = min(count($_FILES['fotos']['name']), 10);
                for ($i = 0; $i < $count; $i++) {
                    $name = $_FILES['fotos']['name'][$i];
                    $tmp  = $_FILES['fotos']['tmp_name'][$i];
                    $err  = $_FILES['fotos']['error'][$i];
                    if ($err !== UPLOAD_ERR_OK) continue;

                    $mime = mime_content_type($tmp);
                    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'])) continue;

                    $ext  = match ($mime) {
                        'image/jpeg' => 'jpg',
                        'image/png'  => 'png',
                        'image/webp' => 'webp',
                        default      => 'bin'
                    };
                    $fname = 'foto_' . $reporteId . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    $dest  = $baseDir . '/' . $fname;
                    if (move_uploaded_file($tmp, $dest)) {
                        $rutaPublica = 'uploads/ReporteDrones/' . $sid . '/' . $fname;
                        $model->guardarMedia($reporteId, 'foto', $rutaPublica);
                    }
                }
            }

            // Firma cliente base64
            $firmaCliente64 = $_POST['firma_cliente_base64'] ?? '';
            if ($firmaCliente64 && str_starts_with($firmaCliente64, 'data:image/png;base64,')) {
                $data = base64_decode(substr($firmaCliente64, strlen('data:image/png;base64,')));
                $fname = 'firma_cliente_' . $reporteId . '_' . bin2hex(random_bytes(4)) . '.png';
                $dest  = $baseDir . '/' . $fname;
                file_put_contents($dest, $data);
                $rutaPublica = 'uploads/ReporteDrones/' . $sid . '/' . $fname;
                $model->guardarMedia($reporteId, 'firma_cliente', $rutaPublica);
            }

            // Firma piloto base64
            $firmaPiloto64 = $_POST['firma_piloto_base64'] ?? '';
            if ($firmaPiloto64 && str_starts_with($firmaPiloto64, 'data:image/png;base64,')) {
                $data = base64_decode(substr($firmaPiloto64, strlen('data:image/png;base64,')));
                $fname = 'firma_piloto_' . $reporteId . '_' . bin2hex(random_bytes(4)) . '.png';
                $dest  = $baseDir . '/' . $fname;
                file_put_contents($dest, $data);
                $rutaPublica = 'uploads/ReporteDrones/' . $sid . '/' . $fname;
                $model->guardarMedia($reporteId, 'firma_piloto', $rutaPublica);
            }


            $pdo->commit();
            jsonResponse(true, ['reporte_id' => $reporteId], 'Reporte creado');
        }

        jsonResponse(false, null, 'Acción no soportada.', 400);
    }

    jsonResponse(false, null, 'Método HTTP no permitido.', 405);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    jsonResponse(false, null, 'Error interno: ' . $e->getMessage(), 500);
}
