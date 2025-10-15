<?php
// core/api/log_client_error.php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config.php';
if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }

try {
    $raw = file_get_contents('php://input') ?: '{}';
    $data = json_decode($raw, true) ?: [];

    $usuarioCtx = [
        'usuario_id' => $_SESSION['usuario_id'] ?? ($_SESSION['id'] ?? null),
        'rol'        => $_SESSION['rol'] ?? null,
        'id_real'    => $_SESSION['id_real'] ?? null,
    ];

    $sql = "INSERT INTO app_js_error_log
        (ts, request_id, usuario_id, rol, id_real, url, message, stack, file, line, col, ua, referer, extra_json)
        VALUES (NOW(), :request_id, :usuario_id, :rol, :id_real, :url, :message, :stack, :file, :line, :col, :ua, :referer, :extra_json)";
    $st = $pdo->prepare($sql);
    $st->execute([
        ':request_id' => $data['requestId'] ?? bin2hex(random_bytes(8)),
        ':usuario_id' => $usuarioCtx['usuario_id'],
        ':rol'        => $usuarioCtx['rol'],
        ':id_real'    => $usuarioCtx['id_real'],
        ':url'        => $_SERVER['HTTP_X_ERROR_URL'] ?? ($_SERVER['HTTP_REFERER'] ?? ''),
        ':message'    => mb_strcut((string)($data['message'] ?? ''), 0, 2048, 'UTF-8'),
        ':stack'      => $data['stack'] ?? null,
        ':file'       => $data['file'] ?? null,
        ':line'       => isset($data['line']) ? (int)$data['line'] : null,
        ':col'        => isset($data['col']) ? (int)$data['col'] : null,
        ':ua'         => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ':referer'    => $_SERVER['HTTP_REFERER'] ?? '',
        ':extra_json' => json_encode([
            'source'  => $data['source'] ?? null,
            'payload' => $data['payload'] ?? null,
        ], JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE),
    ]);

    echo json_encode(['ok'=>true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false, 'error'=>'logger-failed']);
}
