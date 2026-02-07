<?php

declare(strict_types=1);

ini_set('display_errors', '0');
error_reporting(E_ALL);

require_once __DIR__ . '/../../config.php';

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

echo $nuevoEstado === 'aprobada_coop'
    ? 'Solicitud autorizada correctamente.'
    : 'Solicitud denegada correctamente.';
