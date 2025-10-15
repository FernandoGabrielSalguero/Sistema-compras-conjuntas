<?php
// core/bootstrap/error_handlers.php
declare(strict_types=1);

/**
 * Se ejecuta antes de cualquier script (vía auto_prepend_file en .htaccess).
 * Registra handlers globales de errores PHP y excepciones. Loguea en archivo y en DB.
 */

require_once __DIR__ . '/../../config.php';
$__pdoRef = isset($pdo) && $pdo instanceof PDO ? $pdo : null; 

if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
}

/** ------- Utilidades ------- */
function core_request_id(): string {
    static $rid = null;
    if ($rid === null) {
        $rid = bin2hex(random_bytes(8));
        if (!headers_sent()) {
            header('X-Request-Id: ' . $rid);
        }
    }
    return $rid;
}

function core_user_ctx(): array {
    return [
        'usuario_id' => $_SESSION['usuario_id'] ?? ($_SESSION['id'] ?? null),
        'rol'        => $_SESSION['rol'] ?? null,
        'id_real'    => $_SESSION['id_real'] ?? null,
        'correo'     => $_SESSION['correo'] ?? null,
        'nombre'     => $_SESSION['nombre'] ?? null,
    ];
}

function core_req_ctx(): array {
    return [
        'url'        => $_SERVER['REQUEST_URI'] ?? '',
        'method'     => $_SERVER['REQUEST_METHOD'] ?? '',
        'ip'         => $_SERVER['REMOTE_ADDR'] ?? '',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'referer'    => $_SERVER['HTTP_REFERER'] ?? '',
        'request_id' => core_request_id(),
    ];
}

function core_json_sanitize(mixed $data): string {
    $maskKeys = ['password','pass','contrasena','token','authorization','auth','secret'];
    $filter = null;
    $filter = function($arr) use (&$filter, $maskKeys) {
        if (!is_array($arr)) return $arr;
        foreach ($arr as $k => &$v) {
            if (is_array($v)) { $v = $filter($v); continue; }
            if (in_array(strtolower((string)$k), $maskKeys, true)) { $v = '***'; }
        }
        return $arr;
    };
    try {
        if (is_array($data)) $data = $filter($data);
        return json_encode($data, JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);
    } catch (\Throwable) {
        return '{}';
    }
}


function core_log_write(?PDO $pdo, array $payload): void {
    $dir = __DIR__ . '/../../logs';
    if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
    $line = '['.date('c').'] '.($payload['level'] ?? 'ERROR').' '.$payload['message'].' | ctx='.json_encode($payload, JSON_UNESCAPED_UNICODE).PHP_EOL;
    @file_put_contents($dir.'/app.log', $line, FILE_APPEND);

    if (!$pdo) { return; } // si no hay PDO, sólo archivo

    try {
        $sql = "INSERT INTO app_error_log
            (ts, level, request_id, usuario_id, rol, id_real, url, method, ip, user_agent, referer, message, file, line, trace, context_json, get_json, post_json)
            VALUES (NOW(), :level, :request_id, :usuario_id, :rol, :id_real, :url, :method, :ip, :user_agent, :referer, :message, :file, :line, :trace, :context_json, :get_json, :post_json)";
        $st = $pdo->prepare($sql);
        $st->execute([
            ':level'       => $payload['level'] ?? 'ERROR',
            ':request_id'  => $payload['request_id'] ?? core_request_id(),
            ':usuario_id'  => $payload['usuario_id'] ?? null,
            ':rol'         => $payload['rol'] ?? null,
            ':id_real'     => $payload['id_real'] ?? null,
            ':url'         => $payload['url'] ?? '',
            ':method'      => $payload['method'] ?? '',
            ':ip'          => $payload['ip'] ?? '',
            ':user_agent'  => $payload['user_agent'] ?? '',
            ':referer'     => $payload['referer'] ?? '',
            ':message'     => mb_strcut((string)($payload['message'] ?? ''), 0, 2048, 'UTF-8'),
            ':file'        => $payload['file'] ?? null,
            ':line'        => $payload['line'] ?? null,
            ':trace'       => $payload['trace'] ?? null,
            ':context_json'=> $payload['context_json'] ?? '{}',
            ':get_json'    => $payload['get_json'] ?? '{}',
            ':post_json'   => $payload['post_json'] ?? '{}',
        ]);
    } catch (\Throwable) { /* swallow */ }
}

/** ------- Handlers ------- */
set_error_handler(function (int $severity, string $message, string $file = '', int $line = 0) use ($pdo) {
    if (!(error_reporting() & $severity)) { return false; } // respetar @
    $payload = array_merge(core_user_ctx(), core_req_ctx(), [
        'level'        => 'PHP_' . $severity,
        'message'      => $message,
        'file'         => $file,
        'line'         => $line,
        'trace'        => null,
        'context_json' => core_json_sanitize(['server'=>$_SERVER]),
        'get_json'     => core_json_sanitize($_GET ?? []),
        'post_json'    => core_json_sanitize($_POST ?? []),
    ]);
    core_log_write($pdo, $payload);
    return false; // no convertir a excepción
});

set_exception_handler(function (Throwable $e) use ($pdo) {
    $payload = array_merge(core_user_ctx(), core_req_ctx(), [
        'level'        => 'EXCEPTION',
        'message'      => $e->getMessage(),
        'file'         => $e->getFile(),
        'line'         => $e->getLine(),
        'trace'        => $e->getTraceAsString(),
        'context_json' => core_json_sanitize(['server'=>$_SERVER]),
        'get_json'     => core_json_sanitize($_GET ?? []),
        'post_json'    => core_json_sanitize($_POST ?? []),
    ]);
    core_log_write($pdo, $payload);
});

register_shutdown_function(function () use ($pdo) {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        $payload = array_merge(core_user_ctx(), core_req_ctx(), [
            'level'        => 'FATAL',
            'message'      => $err['message'],
            'file'         => $err['file'],
            'line'         => $err['line'],
            'trace'        => null,
            'context_json' => core_json_sanitize(['server'=>$_SERVER]),
            'get_json'     => core_json_sanitize($_GET ?? []),
            'post_json'    => core_json_sanitize($_POST ?? []),
        ]);
        core_log_write($pdo, $payload);
        if (!headers_sent()) { http_response_code(500); }
    }
});

// Inicializa request-id
core_request_id();
