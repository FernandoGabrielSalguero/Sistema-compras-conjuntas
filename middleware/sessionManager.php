<?php
// middleware/sessionManager.php
// Configuración y control centralizados de sesión (timeout deslizante)

// ===== Config =====
define('SESSION_INACTIVITY', 3600); // 1 hora

// Detección de HTTPS detrás de proxy/CDN si aplica
$secure =
  (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
  || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

// Endurecer sesión
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.gc_maxlifetime', SESSION_INACTIVITY);

// Cookie con lifetime = SESSION_INACTIVITY (la vamos a reemitir en cada request)
session_set_cookie_params([
    'lifetime' => SESSION_INACTIVITY,
    'path'     => '/',
    'domain'   => '',
    'secure'   => $secure,
    'httponly' => true,
    'samesite' => 'Lax',
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Reemite la cookie de sesión para lograr expiración deslizante
function refreshSessionCookie(): void {
    if (headers_sent()) return;
    $p = session_get_cookie_params();

    // En algunos PHP, session_get_cookie_params no incluye samesite: lo conservamos si está
    $samesite = $p['samesite'] ?? 'Lax';

    setcookie(session_name(), session_id(), [
        'expires'  => time() + SESSION_INACTIVITY,
        'path'     => $p['path'],
        'domain'   => $p['domain'],
        'secure'   => $p['secure'],
        'httponly' => $p['httponly'],
        'samesite' => $samesite,
    ]);
}

// Llamar en cada request de páginas protegidas
function enforceSession(?string $requiredRole = null): void {
    // Timeout por inactividad
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_INACTIVITY)) {
        session_unset();
        session_destroy();
        header('Location: /index.php?expired=1');
        exit;
    }

    // Actualizamos actividad y reemitimos cookie (sliding)
    $_SESSION['LAST_ACTIVITY'] = time();
    refreshSessionCookie();

    // Si se requiere rol, validamos login y rol
    if ($requiredRole !== null) {
        if (!isset($_SESSION['cuit'])) {
            header('Location: /index.php');
            exit;
        }

        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== $requiredRole) {
            http_response_code(403);
            echo "🚫 Acceso restringido: esta sección es solo para el rol <strong>" . htmlspecialchars($requiredRole) . "</strong>.";
            exit;
        }
    }
}
