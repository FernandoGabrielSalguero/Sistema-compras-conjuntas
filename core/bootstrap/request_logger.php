<?php
// core/bootstrap/request_logger.php
declare(strict_types=1);

/**
 * Bootstrap de auditoría global.
 * - Registra toda petición HTTP (inicio y fin).
 * - Registra errores, excepciones y fatal shutdown.
 * - Escribe en MySQL usando PDO del proyecto (config.php).
 * Requisitos: MySQL 5.7+ (JSON soportado), PHP 8+.
 */

$__REQUEST_LOGGER_STARTED = microtime(true);

// Cargar config y PDO existente
$__proj_root = dirname(__DIR__, 2);
require_once $__proj_root . '/config.php';
$__pdo = isset($pdo) && $pdo instanceof PDO ? $pdo : null;

// Iniciar sesión si no está activa (necesario para obtener usuario)
if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
}

// ------------ Utilidades ------------
function audit_request_id(): string {
    static $rid = null;
    if ($rid === null) {
        $rid = bin2hex(random_bytes(8));
        if (!headers_sent()) {
            header('X-Request-Id: ' . $rid);
        }
    }
    return $rid;
}

function audit_user_ctx(): array {
    return [
        'usuario_id' => $_SESSION['usuario_id'] ?? ($_SESSION['id'] ?? null),
        'rol'        => $_SESSION['rol'] ?? null,
        'email'      => $_SESSION['correo'] ?? null,
        'nombre'     => $_SESSION['nombre'] ?? null,
    ];
}

function audit_ip(): string {
    foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','REMOTE_ADDR'] as $k) {
        if (!empty($_SERVER[$k])) {
            $val = $_SERVER[$k];
            if ($k === 'HTTP_X_FORWARDED_FOR') {
                $parts = explode(',', $val);
                return trim($parts[0]);
            }
            return $val;
        }
    }
    return '0.0.0.0';
}

function audit_sanitize_array(array $src): array {
    // Evitar almacenar credenciales o binarios
    $deny = ['password','pass','pwd','contrasena','token','csrf','file','archivo','image','foto','signature','firma'];
    $out = [];
    foreach ($src as $k => $v) {
        $lk = strtolower((string)$k);
        if (in_array($lk, $deny, true)) {
            $out[$k] = '[REDACTED]';
        } else {
            if (is_array($v))      $out[$k] = audit_sanitize_array($v);
            elseif (is_object($v)) $out[$k] = '[OBJECT]';
            else {
                $str = (string)$v;
                $out[$k] = (mb_strlen($str) > 2000) ? mb_substr($str, 0, 2000) . '…' : $str;
            }
        }
    }
    return $out;
}

function audit_db_write(PDO $pdo, array $row): void {
    static $stmt = null;
    if ($stmt === null) {
        $sql = "INSERT INTO system_audit_log
            (ts, request_id, usuario_id, rol, ip, ua, method, uri, action_type, action, status_code, duration_ms, meta)
            VALUES (NOW(), :request_id, :usuario_id, :rol, :ip, :ua, :method, :uri, :action_type, :action, :status_code, :duration_ms, :meta)";
        $stmt = $pdo->prepare($sql);
    }
    $stmt->execute([
        ':request_id' => $row['request_id'] ?? null,
        ':usuario_id' => $row['usuario_id'] ?? null,
        ':rol'        => $row['rol'] ?? null,
        ':ip'         => $row['ip'] ?? null,
        ':ua'         => $row['ua'] ?? null,
        ':method'     => $row['method'] ?? null,
        ':uri'        => $row['uri'] ?? null,
        ':action_type'=> $row['action_type'] ?? null,
        ':action'     => $row['action'] ?? null,
        ':status_code'=> $row['status_code'] ?? null,
        ':duration_ms'=> $row['duration_ms'] ?? null,
        ':meta'       => json_encode($row['meta'] ?? new stdClass(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
    ]);
}

function audit_log(string $type, string $action, array $meta = [], ?int $status = null): void {
    global $__pdo, $__REQUEST_LOGGER_STARTED;
    if (!$__pdo instanceof PDO) return; // si no hay PDO, salir silenciosamente

    $u = audit_user_ctx();
    $row = [
        'request_id'  => audit_request_id(),
        'usuario_id'  => $u['usuario_id'],
        'rol'         => $u['rol'],
        'ip'          => audit_ip(),
        'ua'          => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'method'      => $_SERVER['REQUEST_METHOD'] ?? '',
        'uri'         => ($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? ''),
        'action_type' => $type, // request|error|exception|shutdown|ui
        'action'      => $action,
        'status_code' => $status ?? (http_response_code() ?: null),
        'duration_ms' => isset($__REQUEST_LOGGER_STARTED) ? (int)round((microtime(true) - $__REQUEST_LOGGER_STARTED) * 1000) : null,
        'meta'        => $meta,
    ];
    try { audit_db_write($__pdo, $row); } catch (Throwable $e) { /* no romper flujo */ }
}

// ------------ Log de inicio de request ------------
audit_log('request', 'begin', [
    'get'     => audit_sanitize_array($_GET ?? []),
    'post'    => audit_sanitize_array($_POST ?? []),
    'headers' => [
        'referer' => $_SERVER['HTTP_REFERER'] ?? null,
        'x-requested-with' => $_SERVER['HTTP_X_REQUESTED_WITH'] ?? null,
    ],
]);

// ------------ Handlers de error/exception/shutdown ------------
set_error_handler(function (int $severity, string $message, string $file = '', int $line = 0): bool {
    audit_log('error', $message, [
        'severity' => $severity,
        'file' => $file,
        'line' => $line,
        'last_error' => error_get_last(),
    ], 500);
    // convertir a ErrorException para canalizar por flujo estándar si se desea
    return false; // permitir que PHP continúe con su manejo normal
});

set_exception_handler(function (Throwable $ex): void {
    audit_log('exception', $ex->getMessage(), [
        'type'  => get_class($ex),
        'file'  => $ex->getFile(),
        'line'  => $ex->getLine(),
        'trace' => explode("\n", $ex->getTraceAsString()),
    ], 500);
    // no hacer echo aquí; dejar que el controlador/plantilla maneje la respuesta
});

register_shutdown_function(function (): void {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        audit_log('shutdown', $err['message'], [
            'type' => $err['type'],
            'file' => $err['file'],
            'line' => $err['line'],
        ], 500);
    } else {
        audit_log('request', 'end', [], http_response_code());
    }
});

// ------------ Endpoint opcional para UI (usado por assets/js/activity_logger.js) ------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_SERVER['REQUEST_URI'] ?? '') === '/logger.php') {
    // Lectura segura del JSON enviado por sendBeacon/fetch
    $raw = file_get_contents('php://input') ?: '';
    $json = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
        audit_log('ui', $json['event'] ?? 'ui_event', [
            'element' => $json['element'] ?? null,
            'details' => $json['details'] ?? null,
        ], 204);
    }
    // Respuesta mínima para beacon
    if (!headers_sent()) {
        http_response_code(204);
        header('Content-Type: text/plain');
    }
    exit;
}
