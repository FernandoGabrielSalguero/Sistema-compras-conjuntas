<?php
declare(strict_types=1);
ini_set('display_errors','1');
ini_set('display_startup_errors','1');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../../../../middleware/authMiddleware.php';
require_once __DIR__ . '/../model/drone_formulario_model.php';

checkAccess('productor'); // restringe a productores

function jok($data){ echo json_encode(['ok'=>true,'data'=>$data], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); exit; }
function jerr($msg, $code=400){ http_response_code($code); echo json_encode(['ok'=>false,'error'=>$msg], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); exit; }

try {
    $model = new DroneFormularioModel();
    $model->pdo = $pdo;

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'costo':
                $d = $model->getCostoBase();
                jok($d);
            case 'formas_pago':
                $items = $model->getFormasPago();
                jok(['items'=>$items]);
            case 'patologias':
                $items = $model->getPatologias();
                jok(['items'=>$items]);
            case 'productos':
                $pid = (int)($_GET['patologia_id'] ?? 0);
                if ($pid <= 0) jerr('patologia_id inválido', 422);
                $items = $model->getProductosByPatologia($pid);
                jok(['items'=>$items]);
            case 'cooperativas':
                $items = $model->getCooperativasHabilitadas();
                jok(['items'=>$items]);
            default:
                // healthcheck simple
                jok(['message'=>'OK']);
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Lee JSON crudo
        $raw = file_get_contents('php://input') ?: '';
        $payload = json_decode($raw, true);
        if (!is_array($payload)) jerr('JSON inválido', 400);

        // Validaciones mínimas
        $reqSiNo = ['representante','linea_tension','zona_restringida','corriente_electrica','agua_potable','libre_obstaculos','area_despegue'];
        foreach ($reqSiNo as $k) {
            if (!isset($payload[$k]) || !in_array($payload[$k], ['si','no'], true)) jerr("Campo $k es requerido (si/no)", 422);
        }
        $sup = (float)($payload['superficie_ha'] ?? 0);
        if ($sup <= 0) jerr('superficie_ha debe ser > 0', 422);
        $fp = (int)($payload['forma_pago_id'] ?? 0);
        if ($fp <= 0) jerr('forma_pago_id inválido', 422);
        if (!isset($payload['rango_fecha']) || !is_string($payload['rango_fecha'])) jerr('rango_fecha requerido', 422);

        // Alta transaccional
        $id = $model->crearSolicitud($payload);
        echo json_encode(['ok'=>true,'id'=>$id], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        exit;
    }

    // Método no permitido
    http_response_code(405);
    jerr('Método no permitido', 405);

} catch (Throwable $e) {
    jerr('Error del servidor: '.$e->getMessage(), 500);
}
