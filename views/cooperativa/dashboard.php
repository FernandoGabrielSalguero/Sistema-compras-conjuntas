<?php
session_start();

// Proteger la sesión
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Cooperativa') {
    header("Location: ../../index.php");
    exit();
}

$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Cooperativa</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #F3F4F6;
            display: flex;
        }

        /* Sidebar */
        #sidebar {
            background-color: white;
            width: 250px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
        }

        #sidebar.collapsed {
            width: 80px;
        }

        #sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        #sidebar ul li a {
            padding: 15px;
            display: block;
            color: #4B5563;
            text-decoration: none;
            padding-left: 20px;
        }

        #sidebar ul li a:hover {
            background-color: #E5E7EB;
        }

        /* Header */
        #header {
            background-color: white;
            width: 100%;
            padding: 10px 20px;
            position: fixed;
            top: 0;
            left: 250px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: left 0.3s;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .collapsed-header {
            left: 80px;
        }

        .content {
            margin-top: 60px;
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s;
            width: 100%;
        }

        .content.collapsed {
            margin-left: 80px;
        }

        /* Cards */
        .card {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .card h2 {
            margin: 0;
            font-size: 20px;
            color: #6B7280;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div id="sidebar">
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="mercado_digital.php">Mercado Digital</a></li>
            <li><a href="alta_usuarios.php">Alta Usuarios</a></li>
        </ul>
    </div>

    <!-- Header -->
    <div id="header" class="header">
        <button onclick="toggleSidebar()">☰</button>
        <div><?php echo $user_name; ?> - <?php echo $user_role; ?></div>
    </div>

    <!-- Contenido Principal -->
    <div id="content" class="content">
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

    <!-- JavaScript -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const header = document.getElementById('header');
            const content = document.getElementById('content');

            sidebar.classList.toggle('collapsed');
            header.classList.toggle('collapsed-header');
            content.classList.toggle('collapsed');
        }
    </script>
</body>
</html>
