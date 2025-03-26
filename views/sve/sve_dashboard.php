<?php
session_start();

// Verificación del rol del usuario
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'SVE_USER') {
    header('Location: login.php');  // Redirige al login si no hay sesión válida
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard SVE</title>
    <style>
        body, html { margin: 0; padding: 0; box-sizing: border-box; }
        body { display: flex; flex-direction: column; min-height: 100vh; background-color: #F0F2F5; }

        /* General Styles */
        * { box-sizing: border-box; font-family: Arial, sans-serif; }
        a { text-decoration: none; color: inherit; }

        /* Layout Styles */
        #header {
            background-color: #ffffff;
            color: #333;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }
        #menu-icon {
            cursor: pointer;
            font-size: 24px;
        }
        #sidebar {
            background-color: #ffffff;
            color: #333;
            padding: 1rem;
            width: 250px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            overflow-y: auto;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        #body {
            margin-left: 250px;
            padding: 2rem;
            background-color: #F0F2F5;
            height: 100vh;
            overflow-y: auto;
        }
        /* Card Styles */
        .card {
            background: white;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        /* Mobile Adjustments */
        @media (max-width: 768px) {
            #sidebar {
                transform: translateX(-100%);
                position: fixed;
            }
            #body {
                margin-left: 0;
            }
            #sidebar.show {
                transform: translateX(0);
            }
        }
        /* Modal Styles */
        #modal {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }
        #modal-content {
            background: white;
            padding: 2rem;
            border-radius: 10px;
        }
    </style>
</head>
<body>

<!-- Header -->
<div id="header">
    <div id="menu-icon" onclick="toggleSidebar()">☰</div>
    <div>Dashboard SVE</div>
</div>

<!-- Sidebar -->
<div id="sidebar">
    <nav>
        <a href="dashboard.php">Dashboard</a><br>
        <a href="alta_usuarios.php">Alta Usuarios</a><br>
        <a href="alta_fincas.php">Alta Fincas</a><br>
        <a href="alta_productos.php">Alta Productos</a><br>
        <a href="mercado_digital.php">Mercado Digital</a><br>
        <a href="pedidos.php">Pedidos</a><br>
    </nav>
</div>

<!-- Body -->
<div id="body">
    <div class="card">Tarjeta 1 - Información General</div>
    <div class="card">Tarjeta 2 - Estadísticas</div>
</div>

<!-- Modal -->
<div id="modal">
    <div id="modal-content">
        <p>Sin aplicaciones disponibles por el momento</p>
        <button onclick="toggleModal()">Cerrar</button>
    </div>
</div>

<!-- JavaScript -->
<script>
    function toggleModal() {
        const modal = document.getElementById('modal');
        modal.style.display = (modal.style.display === 'flex') ? 'none' : 'flex';
    }

    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('show');
    }
</script>

</body>
</html>
