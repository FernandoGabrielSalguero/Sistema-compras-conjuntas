<?php
if (!isset($_SESSION)) {
    session_start();
}
?>

<div class="sidebar">
    <ul>
        <li><a href="/views/productor/dashboard.php">Dashboard</a></li>
        <li><a href="/views/productor/perfil.php">Perfil</a></li>
        <li><a href="/views/productor/mercado_digital.php">Mercado Digital</a></li>
    </ul>
</div>

<style>
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: 200px;
        height: 100%;
        background-color: #673ab7;
        padding-top: 20px;
    }
    .sidebar ul {
        list-style-type: none;
        padding: 0;
    }
    .sidebar ul li {
        margin-bottom: 10px;
    }
    .sidebar ul li a {
        color: white;
        text-decoration: none;
        padding: 10px;
        display: block;
        transition: background-color 0.3s;
    }
    .sidebar ul li a:hover {
        background-color: #5e35b1;
    }
    body {
        margin-left: 200px;
    }
</style>
