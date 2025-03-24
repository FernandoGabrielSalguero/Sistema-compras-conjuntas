<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$rol = $_SESSION['user_role'] ?? 'Productor';
$nombreUsuario = $_SESSION['user_name'] ?? 'Usuario';
?>

<!-- Sidebar -->
<div id="sidebar" class="sidebar expanded">
    <ul>
        <?php if ($rol === 'Productor'): ?>
            <li><a href="/views/productor/dashboard.php">Dashboard</a></li>
            <li><a href="/views/productor/mercado_digital.php">Mercado Digital</a></li>
            <li><a href="/views/productor/perfil.php">Perfil</a></li>
        <?php elseif ($rol === 'Cooperativa'): ?>
            <li><a href="/views/cooperativa/dashboard.php">Dashboard</a></li>
            <li><a href="/views/cooperativa/mercado_digital.php">Mercado Digital</a></li>
            <li><a href="/views/cooperativa/alta_usuarios.php">Alta Usuarios</a></li>
        <?php elseif ($rol === 'SVE'): ?>
            <li><a href="/views/sve/dashboard.php">Dashboard</a></li>
            <li><a href="/views/sve/pedidos.php">Pedidos</a></li>
            <li><a href="/views/sve/alta_usuarios.php">Alta Usuarios</a></li>
            <li><a href="/views/sve/alta_fincas.php">Alta Fincas</a></li>
            <li><a href="/views/sve/mercado_digital.php">Mercado Digital</a></li>
            <li><a href="/views/sve/solicitud_modificaciones.php">Solicitudes Modificaciones</a></li>
        <?php endif; ?>
    </ul>
</div>
