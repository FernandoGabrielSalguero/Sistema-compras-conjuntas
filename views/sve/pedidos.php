<?php

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos</title>
    <style>
        body,
        html {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #F0F2F5;
        }

        /* General Styles */
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* Header */
        #header {
            background-color: #ffffff;
            color: #333;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 10;
        }

        /* Sidebar */
        #sidebar {
            background-color: #ffffff;
            color: #333;
            padding: 1rem;
            width: 250px;
            height: calc(100vh - 60px);
            position: fixed;
            top: 60px;
            left: 0;
            overflow-y: auto;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
        }

        /* Body */
        #body {
            margin-left: 250px;
            padding: 2rem;
            background-color: #F0F2F5;
            height: 100vh;
            overflow-y: auto;
            padding-top: 60px;
        }

        /* Card */
        .card {
            background: white;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        /* Mobile Adjustments */
        @media (max-width: 768px) {
            #sidebar {
                transform: translateX(-100%);
                position: fixed;
                top: 0;
                height: 100vh;
                z-index: 9;
            }

            #sidebar.show {
                transform: translateX(0);
            }

            #body {
                margin-left: 0;
            }
        }

        /* Botón de cerrar menú */
        #close-menu-button {
            display: block;
            /* Visible por defecto */
        }

        /* Ocultar el botón en pantallas grandes */
        @media (min-width: 769px) {
            #close-menu-button {
                display: none;
            }
        }

        /* Estilo de botones del menú */
        #sidebar nav a {
            display: flex;
            align-items: center;
            padding: 10px;
            margin: 5px 0;
            background-color: white;
            color: #333;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s, box-shadow 0.3s;
        }

        #sidebar nav a:hover {
            background-color: #E0E0E0;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        /* Iconos */
        #sidebar nav a i {
            margin-right: 8px;
        }

        /* Estilo del botón de cerrar menú */
        #close-menu-button {
            display: block;
            /* Visible por defecto */
            padding: 10px;
            margin-top: 20px;
            border: none;
            background-color: #ff5e57;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        #close-menu-button:hover {
            background-color: #ff3d3d;
        }

        /* Ocultar el botón de cerrar menú en pantallas grandes */
        @media (min-width: 769px) {
            #close-menu-button {
                display: none;
            }
        }

        /* Ajuste para que el sidebar no se superponga al header en móviles */
        @media (max-width: 768px) {
            #sidebar {
                top: 55PX;
                /* Para que ocupe todo el alto de la pantalla */
                height: 100vh;
            }
        }
    </style>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>

<body>

    <!-- Header -->
    <div id="header">
        <div id="menu-icon" onclick="toggleSidebar()">☰</div>
        <div>Pedidos</div>
    </div>

    <!-- Sidebar -->
    <div id="sidebar">
        <nav>
        <a href="sve_dashboard.php"><i class="fa fa-home"></i> Inicio</a><br>
            <a href="alta_usuarios.php"><i class="fa fa-user-plus"></i> Alta Usuarios</a><br>
            <a href="relacionamiento.php"><i class="fa fa-user-plus"></i> Relacionamiento </a><br>
            <a href="alta_productos.php"><i class="fa fa-box"></i> Alta Productos</a><br>
            <a href="mercado_digital.php"><i class="fa fa-shopping-cart"></i> Mercado Digital</a><br>
            <a href="pedidos.php"><i class="fa fa-list"></i> Pedidos</a><br>
            <a href="CargaMasivaUsuarios.php"><i class="fa fa-list"></i> Carga masiva de datos</a><br>
            <a href="base_datos.php"><i class="fa fa-list"></i>  Base de datos </a><br>
            <a href="logout.php"><i class="fa fa-sign-out-alt"></i> Salir</a><br>
        </nav>
        <button id="close-menu-button" onclick="toggleSidebar()">Cerrar Menú</button>
    </div>

    <!-- Body -->
    <div id="body">
        <div class="card">Tarjeta 1 - Información General</div>
        <div class="card">Tarjeta 2 - Estadísticas</div>
    </div>

    <!-- JavaScript -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }
    </script>

</body>

</html>