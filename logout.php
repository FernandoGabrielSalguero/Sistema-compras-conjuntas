<?php
// Iniciar sesión
session_start();

// Eliminar todas las variables de sesión
session_unset();

// Destruir la sesión
session_destroy();

// Eliminar la cookie de sesión (por si queda algo)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Instrucciones para desactivar caché del navegador
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Redirigir al login
$redirect = '/index.php';
if (!headers_sent()) {
    header('Location: ' . $redirect);
    exit;
}

// Fallback si los headers ya fueron enviados
echo '<!doctype html><html lang="es"><head>';
echo '<meta charset="utf-8">';
echo '<meta http-equiv="refresh" content="0;url=' . htmlspecialchars($redirect, ENT_QUOTES, 'UTF-8') . '">';
echo '<title>Redireccionando...</title>';
echo '</head><body>';
echo '<script>window.location.href=' . json_encode($redirect) . ';</script>';
echo '</body></html>';
exit;
