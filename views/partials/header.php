<?php
if (!isset($_SESSION)) {
    session_start();
}
?>

<div class="header">
    <div class="logo">SVE - Compra Conjunta</div>
    <div class="user-info">
        <span>Bienvenido, <?= $_SESSION["user_name"]; ?></span>
        <a href="/logout.php" class="logout">Cerrar Sesión</a>
    </div>
</div>

<style>
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
        background-color: #673ab7;
        color: white;
    }
    .header .logo {
        font-size: 20px;
    }
    .header .user-info {
        display: flex;
        align-items: center;
    }
    .header .user-info a {
        color: white;
        text-decoration: none;
        margin-left: 20px;
    }
    .header .user-info a:hover {
        text-decoration: underline;
    }
</style>
