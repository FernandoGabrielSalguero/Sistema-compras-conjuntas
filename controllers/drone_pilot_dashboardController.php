<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../middleware/authMiddleware.php';
require_once __DIR__ . '/../models/drone_pilot_dashboardModel.php';

header('Content-Type: application/json; charset=utf-8');
checkAccess('piloto_drone');

$usuarioId = $_SESSION['usuario_id'] ?? ($_SESSION['id'] ?? null);
if (!$usuarioId) jsonResponse(false, null, 'Sesión inválida: faltan credenciales (usuario_id).', 401);

function jsonResponse($ok, $data = null, $message = null, $code = 200)
{
    http_response_code($code);
    echo json_encode(['ok' => $ok, 'message' => $message, 'data' => $data], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$model  = new DronePilotDashboardModel($pdo);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_GET['action'] ?? $_POST['action'] ?? null;

try {

    /* ------------------- GET ------------------- */
    if ($method === 'GET') {

        if ($action === 'mis_solicitudes') {
            if (($_SESSION['rol'] ?? null) !== 'piloto_drone') {
                jsonResponse(false, null, 'Acceso denegado para este recurso.', 403);
            }
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

        if ($action === 'reporte_solicitud') {
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) jsonResponse(false, null, 'ID inválido.', 400);
            $sol = $model->getSolicitudDetalle($id, (int)$usuarioId);
            if (!$sol) jsonResponse(false, null, 'No encontrado o sin permisos.', 404);
            $rep = $model->getReporteBySolicitud($id);
            $media = $rep ? $model->getMediaByReporte((int)$rep['id']) : [];
            jsonResponse(true, ['reporte' => $rep, 'media' => $media]);
        }

        if ($action === 'receta_editable') {
            $sid = (int)($_GET['id'] ?? 0);
            if ($sid <= 0) jsonResponse(false, null, 'ID inválido', 400);
            $sol = $model->getSolicitudDetalle($sid, (int)$usuarioId);
            if (!$sol) jsonResponse(false, null, 'No encontrado o sin permisos.', 404);
            $recetas = $model->getRecetaEditableBySolicitud($sid);
            jsonResponse(true, $recetas);
        }

        if ($action === 'catalogo_productos') {
            $sql = "SELECT id, nombre, principio_activo, tiempo_carencia
                    FROM dron_productos_stock
                    WHERE activo = 'si'
                    ORDER BY nombre ASC
                    LIMIT 500";
            $st = $pdo->query($sql);
            $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
            jsonResponse(true, $rows);
        }

        jsonResponse(false, null, 'Acción no soportada.', 400);
    }

    /* ------------------- POST ------------------- */
    if ($method === 'POST') {

        if ($action === 'actualizar_receta') {
            $sid = (int)($_POST['solicitud_id'] ?? 0);
            if ($sid <= 0) jsonResponse(false, null, 'Solicitud inválida.', 400);
            $sol = $model->getSolicitudDetalle($sid, (int)$usuarioId);
            if (!$sol) jsonResponse(false, null, 'No encontrado o sin permisos.', 404);

            $rows = json_decode($_POST['recetas_json'] ?? '[]', true) ?: [];
            $model->actualizarRecetaValores($rows, $_SESSION['nombre'] ?? 'piloto');
            jsonResponse(true, null, 'Receta actualizada');
        }

        if ($action === 'agregar_producto_receta') {
            $sid = (int)($_POST['solicitud_id'] ?? 0);
            if ($sid <= 0) jsonResponse(false, null, 'Solicitud inválida.', 400);
            $sol = $model->getSolicitudDetalle($sid, (int)$usuarioId);
            if (!$sol) jsonResponse(false, null, 'No encontrado o sin permisos.', 404);

            $data = [
                'solicitud_id'      => $sid,
                'nombre_producto'   => trim($_POST['nombre_producto'] ?? ''),
                'principio_activo'  => trim($_POST['principio_activo'] ?? ''),
                'dosis'             => $_POST['dosis'] ?? null,
                'cant_prod_usado'   => $_POST['cant_prod_usado'] ?? null,
                'fecha_vencimiento' => $_POST['fecha_vencimiento'] ?? null,
                'created_by'        => $_SESSION['nombre'] ?? 'piloto',
            ];
            if ($data['nombre_producto'] === '') jsonResponse(false, null, 'Falta nombre de producto', 400);

            $model->agregarProductoAReceta($data);
            jsonResponse(true, null, 'Producto agregado a la receta');
        }

        if ($action === 'crear_reporte') {
            $sid = (int)($_POST['solicitud_id'] ?? 0);
            if ($sid <= 0) jsonResponse(false, null, 'Solicitud inválida.', 400);
            $sol = $model->getSolicitudDetalle($sid, (int)$usuarioId);
            if (!$sol) jsonResponse(false, null, 'No encontrado o sin permisos.', 404);

            $payload = [
                'solicitud_id'        => $sid,
                'nom_cliente'         => trim($_POST['nom_cliente'] ?? ''),
                'nom_piloto'          => trim($_POST['nom_piloto'] ?? ''),
                'nom_encargado'       => trim($_POST['nom_encargado'] ?? ''),
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

            $baseDir = __DIR__ . '/../uploads/ReporteDrones/' . $sid;
            if (!is_dir($baseDir)) @mkdir($baseDir, 0775, true);

            if (!empty($_FILES['fotos']['name'][0])) {
                $count = min(count($_FILES['fotos']['name']), 10);
                for ($i = 0; $i < $count; $i++) {
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

            // Firmas
            foreach (['cliente', 'piloto'] as $tipo) {
                $campo = "firma_{$tipo}_base64";
                $base64 = $_POST[$campo] ?? '';
                if ($base64 && str_starts_with($base64, 'data:image/png;base64,')) {
                    $data = base64_decode(substr($base64, strlen('data:image/png;base64,')));
                    $fname = "firma_{$tipo}_" . $reporteId . '_' . bin2hex(random_bytes(4)) . '.png';
                    $dest  = $baseDir . '/' . $fname;
                    file_put_contents($dest, $data);
                    $rutaPublica = 'uploads/ReporteDrones/' . $sid . '/' . $fname;
                    $model->guardarMedia($reporteId, "firma_{$tipo}", $rutaPublica);
                }
            }

            $model->marcarCompletada($sid);
            $pdo->commit();
            jsonResponse(true, ['reporte_id' => $reporteId], 'Reporte creado');
        }

        jsonResponse(false, null, 'Acción no soportada.', 400);
    }

    jsonResponse(false, null, 'Método HTTP no permitido.', 405);

    // === Proxy público para Registro Fitosanitario (deep JSON) ===
    // GET /controllers/drone_pilot_dashboardController.php?action=fito_json&id=123
    if (isset($_GET['action']) && $_GET['action'] === 'fito_json') {
        header('Content-Type: application/json; charset=utf-8');

        // Sanitizar ID
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            echo json_encode(['ok' => false, 'error' => 'ID inválido']);
            exit;
        }

        // Ruta ABSOLUTA al controller interno original (no dependemos del cwd/http)
        $internal = __DIR__ . '/../views/partials/drones/controller/drone_list_controller.php';
        if (!file_exists($internal)) {
            echo json_encode(['ok' => false, 'error' => 'Endpoint interno no disponible']);
            exit;
        }

        // Preparamos parámetros para el controller interno
        $backupGet = $_GET;
        $_GET['action'] = 'solicitud_json';
        $_GET['id'] = $id;

        // Aislamos la salida del include
        ob_start();
        try {
            include $internal; // debe imprimir un JSON
            $out = ob_get_clean();
        } catch (Throwable $e) {
            ob_end_clean();
            echo json_encode(['ok' => false, 'error' => 'Error interno: ' . $e->getMessage()]);
            // Restauramos GET antes de salir
            $_GET = $backupGet;
            exit;
        }

        // Restauramos GET original
        $_GET = $backupGet;

        // Validamos que el include haya devuelto JSON válido
        $decoded = json_decode($out, true);
        if (!is_array($decoded)) {
            echo json_encode(['ok' => false, 'error' => 'Respuesta inválida del endpoint interno']);
            exit;
        }

        // Passthrough (normalizamos para que siempre tenga ok/data/error)
        if (!array_key_exists('ok', $decoded)) {
            // Si el interno no devuelve la convención, lo envolvemos
            echo json_encode(['ok' => true, 'data' => $decoded]);
            exit;
        }

        echo json_encode($decoded);
        exit;
    }
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    jsonResponse(false, null, 'Error interno: ' . $e->getMessage(), 500);
}
