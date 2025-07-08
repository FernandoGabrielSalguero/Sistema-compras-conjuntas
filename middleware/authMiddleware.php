<?php
// middleware/authMiddleware.php

// Configuración segura para la sesión (sólo la primera vez)
ini_set('session.gc_maxlifetime', 1200);
session_set_cookie_params([
    'lifetime' => 1200,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica que el usuario esté logueado y tenga el rol adecuado.
 * También controla la expiración por inactividad.
 *
 * @param string $requiredRole El rol requerido para acceder (ej: 'sve', 'cooperativa', 'productor')
 */
function checkAccess($requiredRole) {
    // Expiración por inactividad
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1200)) {
        session_unset();
        session_destroy();
        header("Location: /index.php?expired=1");
        exit;
    }

    // Actualizar timestamp
    $_SESSION['LAST_ACTIVITY'] = time();

    // Verificar sesión iniciada
    if (!isset($_SESSION['cuit'])) {
        header('Location: /index.php');
        exit;
    }

    // Verificar rol correcto
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== $requiredRole) {
        echo "🚫 Acceso restringido: esta sección es solo para el rol <strong>$requiredRole</strong>.";
        exit;
    }
}
