<?php
include('../../partials/header.php');
include('../../partials/sidebar.php');
?>

<!-- Contenido Principal -->
<div class="content">
    <div class="container">
        <h1>Bienvenido, <?php echo $_SESSION['user_name']; ?> - <?php echo $_SESSION['user_role']; ?></h1>
        
        <div class="card">
            <h2>Total de Pedidos</h2>
            <p>25 Pedidos realizados</p>
        </div>

        <div class="card">
            <h2>Compras Pendientes</h2>
            <p>5 Compras en proceso</p>
        </div>

        <div class="card">
            <h2>Historial de Compras</h2>
            <p>10 Compras pasadas</p>
        </div>
    </div>
</div>

<!-- Enlace al JavaScript -->
<script src="/assets/js/sidebar.js"></script>
