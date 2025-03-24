<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$nombreUsuario = $_SESSION['user_name'] ?? 'Usuario';
$rolUsuario = $_SESSION['user_role'] ?? 'Desconocido';
?>

<!-- Header -->
<div class="header">
    <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
    <div class="user-info">
        <span><?php echo "$nombreUsuario - $rolUsuario"; ?></span>
    </div>
</div>
