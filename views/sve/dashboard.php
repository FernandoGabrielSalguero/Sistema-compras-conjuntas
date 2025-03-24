<?php
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'SVE') {
    header("Location: ../../index.php");
    exit();
}

include '../../views/partials/header.php';
include '../../views/partials/sidebar.php';
?>

<div class="content">
    <div class="kpi-container">
        <div class="kpi-card"><h3>Total de Pedidos</h3><p>0</p></div>
        <div class="kpi-card"><h3>Total de Cooperativas</h3><p>1</p></div>
        <div class="kpi-card"><h3>Total de Productores</h3><p>2727</p></div>
        <div class="kpi-card"><h3>Total de Fincas</h3><p>2372</p></div>
        <div class="kpi-card"><h3>Pedido Cancelado</h3><p>0</p></div>
        <div class="kpi-card"><h3>Pedido OK pendiente de factura</h3><p>0</p></div>
        <div class="kpi-card"><h3>Pedido OK FACTURADO</h3><p>0</p></div>
        <div class="kpi-card"><h3>Pedido pendiente de retito</h3><p>0</p></div>
        <div class="kpi-card"><h3>Pedido en camino al productor</h3><p>0</p></div>
        <div class="kpi-card"><h3>Pedido en camino a la cooperativa</h3><p>0</p></div>
    </div>
</div>
