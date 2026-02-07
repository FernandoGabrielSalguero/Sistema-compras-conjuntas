<?php

declare(strict_types=1);

ini_set('display_errors', '0');
error_reporting(E_ALL);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../Mail.php';

use SVE\Mail\Mail;

function base64url_decode(string $data): string
{
    $remainder = strlen($data) % 4;
    if ($remainder) {
        $data .= str_repeat('=', 4 - $remainder);
    }
    return base64_decode(strtr($data, '-_', '+/')) ?: '';
}

function verifyToken(string $token): ?array
{
    if (!defined('COOP_ACTION_SECRET') || COOP_ACTION_SECRET === '') {
        return null;
    }
    $parts = explode('.', $token);
    if (count($parts) !== 2) {
        return null;
    }
    [$payload64, $sig64] = $parts;
    $sig = base64url_decode($sig64);
    $calc = hash_hmac('sha256', $payload64, COOP_ACTION_SECRET, true);
    if (!hash_equals($calc, $sig)) {
        return null;
    }
    $payloadJson = base64url_decode($payload64);
    $payload = json_decode($payloadJson, true);
    return is_array($payload) ? $payload : null;
}

header('Content-Type: text/html; charset=utf-8');

$token = (string)($_GET['t'] ?? '');
if ($token === '') {
    http_response_code(400);
    echo 'Token invalido.';
    exit;
}

$payload = verifyToken($token);
if ($payload === null) {
    http_response_code(400);
    echo 'Token invalido o firma incorrecta.';
    exit;
}

$sid = (int)($payload['sid'] ?? 0);
$act = (string)($payload['act'] ?? '');
$exp = (int)($payload['exp'] ?? 0);

if ($sid <= 0 || !in_array($act, ['approve', 'decline'], true)) {
    http_response_code(400);
    echo 'Token invalido.';
    exit;
}

if ($exp > 0 && time() > $exp) {
    http_response_code(410);
    echo 'Este enlace ha vencido.';
    exit;
}

// Un solo uso: no permitir si ya fue decidido.
$stmt = $pdo->prepare("SELECT estado FROM drones_solicitud WHERE id = ? LIMIT 1");
$stmt->execute([$sid]);
$estadoActual = (string)($stmt->fetchColumn() ?: '');
if ($estadoActual === '') {
    http_response_code(404);
    echo 'Solicitud no encontrada.';
    exit;
}

if (in_array($estadoActual, ['aprobada_coop', 'cancelada'], true)) {
    echo 'Esta solicitud ya fue respondida. Si necesita cambiar la decision, ingrese al sistema.';
    exit;
}

$nuevoEstado = $act === 'approve' ? 'aprobada_coop' : 'cancelada';
$upd = $pdo->prepare("UPDATE drones_solicitud SET estado = ? WHERE id = ? LIMIT 1");
$upd->execute([$nuevoEstado, $sid]);

// Notificar a involucrados
$stmt = $pdo->prepare("
    SELECT ds.productor_id_real,
           ui_prod.nombre AS prod_nombre,
           ui_prod.correo AS prod_correo
    FROM drones_solicitud ds
    LEFT JOIN usuarios u_prod ON u_prod.id_real = ds.productor_id_real
    LEFT JOIN usuarios_info ui_prod ON ui_prod.usuario_id = u_prod.id
    WHERE ds.id = ?
    LIMIT 1
");
$stmt->execute([$sid]);
$prod = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['prod_nombre' => '', 'prod_correo' => '', 'productor_id_real' => ''];

$coopIdReal = (string)($payload['coop'] ?? '');
$coop = ['coop_nombre' => '', 'coop_correo' => '', 'coop_id_real' => $coopIdReal];
if ($coopIdReal !== '') {
    $stCoop = $pdo->prepare("
        SELECT ui.nombre AS coop_nombre, ui.correo AS coop_correo
        FROM usuarios u
        LEFT JOIN usuarios_info ui ON ui.usuario_id = u.id
        WHERE u.id_real = ?
        LIMIT 1
    ");
    $stCoop->execute([$coopIdReal]);
    $rowCoop = $stCoop->fetch(PDO::FETCH_ASSOC);
    if ($rowCoop) {
        $coop['coop_nombre'] = (string)($rowCoop['coop_nombre'] ?? '');
        $coop['coop_correo'] = (string)($rowCoop['coop_correo'] ?? '');
    }
}

Mail::enviarRespuestaCoopSolicitudDron([
    'solicitud_id' => $sid,
    'estado' => $nuevoEstado,
    'productor' => [
        'nombre' => (string)($prod['prod_nombre'] ?? ''),
        'correo' => (string)($prod['prod_correo'] ?? ''),
    ],
    'cooperativa' => [
        'nombre' => (string)($coop['coop_nombre'] ?? ''),
        'correo' => (string)($coop['coop_correo'] ?? ''),
        'id_real' => $coopIdReal,
    ],
]);

echo $nuevoEstado === 'aprobada_coop'
    ? 'Solicitud autorizada correctamente.'
    : 'Solicitud denegada correctamente.';
