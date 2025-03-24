<?php
session_start();
$rol = $_SESSION['user_role'] ?? 'Productor'; // Por defecto, Productor
?>

<div id="sidebar" class="sidebar">
    <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
    <ul>
        <?php if ($rol === 'Productor'): ?>
            <li><a href="/views/productor/dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="/views/productor/mercado_digital.php"><i class="fas fa-shopping-cart"></i> Mercado Digital</a></li>
            <li><a href="/views/productor/perfil.php"><i class="fas fa-user"></i> Perfil</a></li>
        <?php elseif ($rol === 'Cooperativa'): ?>
            <li><a href="/views/cooperativa/dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="/views/cooperativa/mercado_digital.php"><i class="fas fa-shopping-cart"></i> Mercado Digital</a></li>
            <li><a href="/views/cooperativa/alta_usuarios.php"><i class="fas fa-users"></i> Alta Usuarios</a></li>
        <?php elseif ($rol === 'SVE'): ?>
            <li><a href="/views/sve/dashboard.php"><i class="fas fa-chart-bar"></i> Dashboard</a></li>
            <li><a href="/views/sve/pedidos.php"><i class="fas fa-list"></i> Pedidos</a></li>
            <li><a href="/views/sve/alta_usuarios.php"><i class="fas fa-user-plus"></i> Alta Usuarios</a></li>
            <li><a href="/views/sve/alta_fincas.php"><i class="fas fa-map"></i> Alta Fincas</a></li>
            <li><a href="/views/sve/mercado_digital.php"><i class="fas fa-store"></i> Mercado Digital</a></li>
            <li><a href="/views/sve/solicitud_modificaciones.php"><i class="fas fa-edit"></i> Solicitudes Modificaciones</a></li>
        <?php endif; ?>
    </ul>
</div>
