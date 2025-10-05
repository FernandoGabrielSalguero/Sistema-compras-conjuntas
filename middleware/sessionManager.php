<?php

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





/**
 * ================================================================
 *  Session Manager (middleware/sessionManager.php)
 *  ---------------------------------------------------------------
 *  Propósito
 *  ---------------------------------------------------------------
 *  Centraliza TODA la configuración y control de la sesión de PHP
 *  para el proyecto SVE. Implementa:
 *   - Timeout de inactividad con expiración deslizante (sliding).
 *   - Reemisión de la cookie de sesión en cada request válido.
 *   - Verificación de acceso por rol.
 *
 *  Con esto se evita configurar la sesión en múltiples archivos y
 *  se resuelve el problema de expiraciones "antes de tiempo".
 *
 *  ---------------------------------------------------------------
 *  Archivos que lo usan / Integración
 *  ---------------------------------------------------------------
 *  1) middleware/authMiddleware.php  (fachada)
 *     ------------------------------------------------------------
 *     require_once __DIR__ . '/sessionManager.php';
 *     function checkAccess(string $requiredRole) { enforceSession($requiredRole); }
 *
 *     Todas las vistas protegidas incluyen:
 *       require_once '../../middleware/authMiddleware.php';
 *       checkAccess('<rol>'); // 'sve' | 'cooperativa' | 'productor' | 'ingeniero'
 *
 *  2) index.php (login)
 *     ------------------------------------------------------------
 *     - Incluye primero: require_once __DIR__ . '/middleware/sessionManager.php';
 *     - Al validar credenciales:
 *          $_SESSION['LAST_ACTIVITY'] = time();
 *          refreshSessionCookie(); // arranca ventana deslizante
 *     - Redirige por rol.
 *
 *  3) ping.php (heartbeat opcional)
 *     ------------------------------------------------------------
 *     - Incluye: require_once __DIR__ . '/middleware/sessionManager.php';
 *     - Actualiza actividad y cookie:
 *          $_SESSION['LAST_ACTIVITY'] = time();
 *          refreshSessionCookie();
 *     - Responde 204 (No Content). Útil si en frontend hay un setInterval
 *       que golpea /ping.php cada X minutos para mantener viva la sesión
 *       cuando el usuario permanece mucho tiempo en una misma vista.
 *
 *  ---------------------------------------------------------------
 *  Constantes y configuración
 *  ---------------------------------------------------------------
 *  - SESSION_INACTIVITY = 3600  // segundos (1 hora). Cambiar aquí la duración.
 *  - ini_set('session.gc_maxlifetime', SESSION_INACTIVITY)
 *  - session_set_cookie_params([... 'lifetime' => SESSION_INACTIVITY, ...])
 *  - Flags de seguridad: use_strict_mode, use_only_cookies, httponly, samesite.
 *  - Detección de HTTPS (también detrás de proxy: HTTP_X_FORWARDED_PROTO).
 *
 *  ---------------------------------------------------------------
 *  Funciones principales
 *  ---------------------------------------------------------------
 *  refreshSessionCookie(): void
 *    - Reemite la cookie de sesión con 'expires = now + SESSION_INACTIVITY'.
 *    - Implementa la "expiración deslizante": cada request válido renueva
 *      el vencimiento de la cookie en el navegador.
 *
 *  enforceSession(?string $requiredRole = null): void
 *    - Si pasó más de SESSION_INACTIVITY desde $_SESSION['LAST_ACTIVITY'],
 *      destruye la sesión y redirige a /index.php?expired=1.
 *    - Si no expiró:
 *        * Actualiza $_SESSION['LAST_ACTIVITY'] = time()
 *        * Llama refreshSessionCookie() (sliding)
 *    - Si $requiredRole no es null:
 *        * Verifica que el usuario esté logueado (existe $_SESSION['cuit'])
 *        * Verifica rol exacto ($_SESSION['rol'] === $requiredRole)
 *        * En caso de fallo: 403 y mensaje de acceso restringido.
 *
 *  ---------------------------------------------------------------
 *  Comportamiento esperado (flujo)
 *  ---------------------------------------------------------------
 *  - Login exitoso (index.php):
 *      * Se setean datos de usuario + LAST_ACTIVITY.
 *      * Se llama refreshSessionCookie() para iniciar la ventana de 1h.
 *  - En cada request a páginas protegidas (vistas):
 *      * checkAccess('<rol>') -> enforceSession('<rol>')
 *      * Si no venció: se renueva LAST_ACTIVITY y la cookie (deslizante).
 *      * Si venció por inactividad (> SESSION_INACTIVITY): logout forzado
 *        y redirección a /index.php?expired=1.
 *  - Heartbeat opcional desde frontend:
 *      * Un setInterval que hace fetch('/ping.php') cada 5–10 min ayuda a
 *        mantener la actividad cuando el usuario no interactúa.
 *
 *  ---------------------------------------------------------------
 *  Cómo cambiar la duración de la sesión
 *  ---------------------------------------------------------------
 *  - Editar SOLO esta constante:
 *        define('SESSION_INACTIVITY', NUEVOS_SEGUNDOS);
 *    Ej.: 2 horas -> 7200.
 *  - No cambiar valores de sesión en otros archivos. Éste es el
 *    único punto de verdad para timeout y cookie.
 *
 *  ---------------------------------------------------------------
 *  Buenas prácticas / notas
 *  ---------------------------------------------------------------
 *  - No mezclar lógica de sesión en otros archivos (no session_start ni
 *    session_set_cookie_params fuera de este manager).
 *  - Si hay proxy/CDN, asegurarse que llegue HTTPS en HTTP_X_FORWARDED_PROTO
 *    o ajustar la detección del flag $secure.
 *  - Evitar echo/HTML aquí: es middleware, no vista.
 *  - Si el hosting limpia archivos de sesión agresivamente, el hecho de
 *    actualizar actividad frecuentemente y reemitir la cookie reduce los
 *    falsos expirados. Para casos extremos, considerar guardar sesiones en
 *    Redis/Memcached o base de datos (handler personalizado).
 *
 *  ---------------------------------------------------------------
 *  Debug rápido (opcional)
 *  ---------------------------------------------------------------
 *  - Loguear en vistas: (SESSION_INACTIVITY - (time() - $_SESSION['LAST_ACTIVITY']))
 *    para ver segundos restantes.
 *  - Crear un endpoint de status (session_status.php) si se necesita inspección
 *    vía AJAX sin tocar ping.php de producción.
 *
 *  Última edición: <9-9-2025>
 * ================================================================
 */
