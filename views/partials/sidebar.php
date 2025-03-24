<?php
if (!isset($_SESSION)) {
    session_start();
}
?>

<div id="sidebar" class="sidebar">
    <div class="toggle-btn" onclick="toggleSidebar()">
        ☰
    </div>
    <ul>
        <li><a href="/views/productor/dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
        <li><a href="/views/productor/perfil.php"><i class="fas fa-user"></i> Perfil</a></li>
        <li><a href="/views/productor/mercado_digital.php"><i class="fas fa-shopping-cart"></i> Mercado Digital</a></li>
    </ul>
</div>

<style>
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: 250px;
        height: 100%;
        background-color: #673ab7;
        transition: all 0.3s;
        padding-top: 20px;
    }

    .sidebar.collapsed {
        width: 80px;
    }

    .sidebar .toggle-btn {
        margin-left: 15px;
        margin-bottom: 20px;
        color: white;
        cursor: pointer;
    }

    .sidebar ul {
        list-style: none;
        padding: 0;
    }

    .sidebar ul li {
        margin-bottom: 10px;
    }

    .sidebar ul li a {
        color: white;
        text-decoration: none;
        padding: 10px;
        display: flex;
        align-items: center;
        transition: background-color 0.3s;
    }

    .sidebar ul li a i {
        margin-right: 10px;
    }

    .sidebar ul li a:hover {
        background-color: #5e35b1;
    }

    body {
        transition: margin-left 0.3s;
    }

    body.collapsed {
        margin-left: 80px;
    }
</style>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const body = document.body;

        if (sidebar.classList.contains('collapsed')) {
            sidebar.classList.remove('collapsed');
            body.classList.remove('collapsed');
        } else {
            sidebar.classList.add('collapsed');
            body.classList.add('collapsed');
        }
    }
</script>
