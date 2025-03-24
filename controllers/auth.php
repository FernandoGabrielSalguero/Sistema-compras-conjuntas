<?php
// Activar la visualización de errores en pantalla
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Comprobar si el usuario está logueado
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_role"])) {
    // Si no está autenticado, redirigir al login
    header("Location: /index.php");
    exit();
}

// Comprobar si el usuario tiene permisos para acceder a la página
function verificarAcceso($rolesPermitidos) {
    if (!in_array($_SESSION["user_role"], $rolesPermitidos)) {
        // Si el rol del usuario no está permitido, redirigir al login
        header("Location: /index.php");
        exit();
    }
}
