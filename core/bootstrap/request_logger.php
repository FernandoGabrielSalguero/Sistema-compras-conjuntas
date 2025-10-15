<?php
// core/bootstrap/request_logger.php
declare(strict_types=1);

/**
 * Escuchador global:
 *  - Registra inicio/fin de cada request.
 *  - Captura errores, excepciones y fatales.
 *  - Acepta eventos de UI via POST /logger.php (sendBeacon/fetch).
 * Requisitos: definir $pdo en config.php (PDO MySQL).
 */

$__REQUEST_LOGGER_STARTED = microtime(true);

// Cargar config del proyecto (debe exponer $pdo = new PDO(...))
$__root = dirname(__DIR__, 2);
require_once $__root . '/config.php';
$__pdo = isset($pdo) && $pdo instanceof PDO ? $pdo : null;

// Sesión solo si aún no está activa (no toca ini si ya existe)
if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
}

/* ---------- helpers ---------- */
function rqid(): string {
    static $id = null;
    if ($id === null) {
        $id = bin2hex(random_bytes(8));
        if (!headers_sent()) header('X-Request-Id: '.$id);
    }
    return $id;
}
function uctx(): array {
    return [
        'usuario_id' => $_SESSION['usuario_id'] ?? ($_SESSION['id'] ?? null),
        'rol'        => $_SESSION['rol'] ?? null,
        'email'      => $_SESSION['correo'] ?? null,
        'nombre'     => $_SESSION['nombre'] ?? null,
    ];
}
function rip(): string {
    foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','REMOTE_ADDR'] as $k) {
        if (!empty($_SERVER[$k])) return $k==='HTTP_X_FORWARDED_FOR' ? trim(explode(',', $_SERVER[$k])[0]) : $_SERVER[$k];
    }
    return '0.0.0.0';
}
function scrub(array $a): array {
    $deny = ['password','pass','pwd','contrasena','token','csrf','file','archivo','image','foto','signature','firma'];
    $o = [];
    foreach ($a as $k=>$v) {
        $lk = strtolower((string)$k);
        if (in_array($lk,$deny,true)) { $o[$k]='[REDACTED]'; continue; }
        if (is_array($v)) $o[$k]=scrub($v);
        elseif (is_scalar($v)) {
            $s=(string)$v; $o[$k]=mb_strlen($s)>2000?mb_substr($s,0,2000).'…':$s;
        } else $o[$k]='[NON_SCALAR]';
    }
    return $o;
}
function log_db(array $row): void {
    global $__pdo;
    if (!($__pdo instanceof PDO)) return;
    static $st=null;
    if ($st===null) {
        $st=$__pdo->prepare("INSERT INTO system_audit_log
            (ts,request_id,usuario_id,rol,ip,ua,method,uri,action_type,action,status_code,duration_ms,meta)
            VALUES (NOW(),:rid,:uid,:rol,:ip,:ua,:m,:uri,:t,:a,:sc,:d,:meta)");
    }
    $st->execute([
        ':rid'=>$row['request_id']??null,
        ':uid'=>$row['usuario_id']??null,
        ':rol'=>$row['rol']??null,
        ':ip'=>$row['ip']??null,
        ':ua'=>$row['ua']??null,
        ':m'=>$row['method']??null,
        ':uri'=>$row['uri']??null,
        ':t'=>$row['action_type']??null,
        ':a'=>$row['action']??null,
        ':sc'=>$row['status_code']??null,
        ':d'=>$row['duration_ms']??null,
        ':meta'=>json_encode($row['meta']??new stdClass(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
    ]);
}
function audit(string $type,string $action,array $meta=[],?int $status=null): void {
    global $__REQUEST_LOGGER_STARTED;
    $u = uctx();
    $row = [
        'request_id'=>rqid(),
        'usuario_id'=>$u['usuario_id'],
        'rol'=>$u['rol'],
        'ip'=>rip(),
        'ua'=>$_SERVER['HTTP_USER_AGENT']??'',
        'method'=>$_SERVER['REQUEST_METHOD']??'',
        'uri'=>(($_SERVER['REQUEST_SCHEME']??'http').'://'.($_SERVER['HTTP_HOST']??'').($_SERVER['REQUEST_URI']??'')),
        'action_type'=>$type,
        'action'=>$action,
        'status_code'=>$status ?? (http_response_code() ?: null),
        'duration_ms'=>isset($__REQUEST_LOGGER_STARTED)?(int)round((microtime(true)-$__REQUEST_LOGGER_STARTED)*1000):null,
        'meta'=>$meta,
    ];
    try { log_db($row); } catch (Throwable $e) { /* no romper */ }
}

/* ---------- inicio de request ---------- */
audit('request','begin',[
    'get'=>scrub($_GET ?? []),
    'post'=>scrub($_POST ?? []),
    'headers'=>[
        'referer'=>$_SERVER['HTTP_REFERER'] ?? null,
        'x-requested-with'=>$_SERVER['HTTP_X_REQUESTED_WITH'] ?? null,
    ],
]);

/* ---------- handlers ---------- */
set_error_handler(function(int $sev,string $msg,string $file='',int $line=0): bool {
    audit('error',$msg,['severity'=>$sev,'file'=>$file,'line'=>$line],500);
    return false; // dejar manejo normal de PHP
});
set_exception_handler(function(Throwable $ex): void {
    audit('exception',$ex->getMessage(),[
        'type'=>get_class($ex),'file'=>$ex->getFile(),'line'=>$ex->getLine(),
        'trace'=>explode("\n",$ex->getTraceAsString())
    ],500);
});
register_shutdown_function(function(): void {
    $err = error_get_last();
    if ($err && in_array($err['type'],[E_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR],true)) {
        audit('shutdown',$err['message'],['type'=>$err['type'],'file'=>$err['file'],'line'=>$err['line']],500);
    } else {
        audit('request','end',[],http_response_code());
    }
});

/* ---------- endpoint UI opcional (/logger.php) ---------- */
if (($_SERVER['REQUEST_METHOD'] ?? '')==='POST'
    && (parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) === '/logger.php')) {
    $raw = file_get_contents('php://input') ?: '';
    $json = json_decode($raw,true);
    if (json_last_error()===JSON_ERROR_NONE && is_array($json)) {
        audit('ui',$json['event'] ?? 'ui_event',[
            'element'=>$json['element'] ?? null,
            'details'=>$json['details'] ?? null,
        ],204);
    }
    if (!headers_sent()) { http_response_code(204); header('Content-Type: text/plain'); }
    exit;
}
