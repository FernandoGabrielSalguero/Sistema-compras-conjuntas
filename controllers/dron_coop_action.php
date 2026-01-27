<?php

declare(strict_types=1);

ini_set('display_errors', '0');
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';

header('Content-Type: text/html; charset=UTF-8');

function base64url_decode(string $data): ?string
{
    $data = strtr($data, '-_', '+/');
    $pad = strlen($data) % 4;
    if ($pad) {
        $data .= str_repeat('=', 4 - $pad);
    }
    $out = base64_decode($data, true);
    return ($out === false) ? null : $out;
}

function verifyCoopActionToken(string $token): ?array
{
    if (!defined('COOP_ACTION_SECRET') || COOP_ACTION_SECRET === '') {
        return null;
    }
    $parts = explode('.', $token);
    if (count($parts) !== 2) {
        return null;
    }
    [$payload64, $sig64] = $parts;
    $payloadJson = base64url_decode($payload64);
    $sig = base64url_decode($sig64);
    if ($payloadJson === null || $sig === null) {
        return null;
    }
    $expected = hash_hmac('sha256', $payload64, COOP_ACTION_SECRET, true);
    if (!hash_equals($expected, $sig)) {
        return null;
    }
    $payload = json_decode($payloadJson, true);
    if (!is_array($payload)) {
        return null;
    }
    return $payload;
}

function renderPage(string $title, string $message): void
{
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</title>';
    echo '<style>
        body{font-family:Arial,sans-serif;background:#f8fafc;color:#0f172a;margin:0;padding:24px;}
        .card{max-width:640px;margin:40px auto;background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:24px;box-shadow:0 8px 24px rgba(15,23,42,.08);}
        h1{font-size:20px;margin:0 0 12px;}
        p{margin:0;color:#334155;line-height:1.5;}
        .ok{color:#16a34a;font-weight:700;}
        .warn{color:#b45309;font-weight:700;}
        .err{color:#dc2626;font-weight:700;}
        .small{margin-top:12px;font-size:12px;color:#64748b;}
    </style></head><body><div class="card">';
    echo '<h1>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h1>';
    echo '<p>' . $message . '</p>';
    echo '<p class="small">Si creés que esto es un error, por favor contactá a SVE.</p>';
    echo '</div></body></html>';
}

$token = trim((string)($_GET['t'] ?? ''));
if ($token === '') {
    renderPage('Solicitud inválida', '<span class="err">No se encontró el token de confirmación.</span>');
    exit;
}

$payload = verifyCoopActionToken($token);
if (!$payload) {
    renderPage('Solicitud inválida', '<span class="err">El enlace es inválido o fue modificado.</span>');
    exit;
}

$sid = (int)($payload['sid'] ?? 0);
$coopId = (string)($payload['coop'] ?? '');
$act = (string)($payload['act'] ?? '');
$exp = (int)($payload['exp'] ?? 0);

if ($sid <= 0 || $coopId === '' || !in_array($act, ['approve', 'decline'], true)) {
    renderPage('Solicitud inválida', '<span class="err">Datos incompletos en el enlace.</span>');
    exit;
}
if ($exp > 0 && time() > $exp) {
    renderPage('Enlace vencido', '<span class="err">El enlace expiró. Solicitá un nuevo correo.</span>');
    exit;
}

try {
    $st = $pdo->prepare("SELECT id, estado, forma_pago_id, coop_descuento_nombre FROM drones_solicitud WHERE id = ? LIMIT 1");
    $st->execute([$sid]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        renderPage('Solicitud no encontrada', '<span class="err">No encontramos la solicitud indicada.</span>');
        exit;
    }

    if ((int)$row['forma_pago_id'] !== 6 || (string)($row['coop_descuento_nombre'] ?? '') !== $coopId) {
        renderPage('Acceso denegado', '<span class="err">Este enlace no corresponde a tu cooperativa.</span>');
        exit;
    }

    $estado = (string)$row['estado'];
    if ($estado === 'aprobada_coop') {
        renderPage('Solicitud ya aprobada', '<span class="warn">Esta solicitud ya fue aprobada anteriormente.</span>');
        exit;
    }
    if ($estado === 'cancelada') {
        renderPage('Solicitud ya rechazada', '<span class="warn">Esta solicitud ya fue rechazada anteriormente.</span>');
        exit;
    }
    if (in_array($estado, ['completada', 'visita_realizada'], true)) {
        renderPage('Solicitud cerrada', '<span class="warn">La solicitud ya está cerrada y no admite cambios.</span>');
        exit;
    }
    if (!in_array($estado, ['ingresada', 'procesando'], true)) {
        renderPage('Solicitud no editable', '<span class="warn">La solicitud no admite cambios en este estado.</span>');
        exit;
    }

    $pdo->beginTransaction();
    $actor = 'coop:' . $coopId;

    if ($act === 'approve') {
        $upd = $pdo->prepare("UPDATE drones_solicitud SET estado = 'aprobada_coop', motivo_cancelacion = NULL, updated_at = NOW() WHERE id = ?");
        $upd->execute([$sid]);
        $evt = $pdo->prepare("INSERT INTO drones_solicitud_evento (solicitud_id, tipo, detalle, payload, actor, created_at) VALUES (?, 'coop_aprobada', 'Aprobada por cooperativa', ?, ?, NOW())");
        $evt->execute([$sid, json_encode(['via' => 'email'], JSON_UNESCAPED_UNICODE), $actor]);
        $pdo->commit();
        renderPage('Solicitud aprobada', '<span class="ok">La solicitud fue aprobada correctamente.</span>');
        exit;
    }

    $upd = $pdo->prepare("UPDATE drones_solicitud SET estado = 'cancelada', motivo_cancelacion = 'Declinada por cooperativa', updated_at = NOW() WHERE id = ?");
    $upd->execute([$sid]);
    $evt = $pdo->prepare("INSERT INTO drones_solicitud_evento (solicitud_id, tipo, detalle, payload, actor, created_at) VALUES (?, 'coop_rechazada', 'Rechazada por cooperativa', ?, ?, NOW())");
    $evt->execute([$sid, json_encode(['via' => 'email'], JSON_UNESCAPED_UNICODE), $actor]);
    $pdo->commit();
    renderPage('Solicitud rechazada', '<span class="ok">La solicitud fue rechazada correctamente.</span>');
    exit;
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    renderPage('Error', '<span class="err">Ocurrió un error al procesar la solicitud.</span>');
    exit;
}
