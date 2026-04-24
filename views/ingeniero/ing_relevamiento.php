<?php
// Mostrar errores en pantalla (útil en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../middleware/authMiddleware.php';
checkAccess('ingeniero');

$nombre = $_SESSION['nombre'] ?? 'Sin nombre';
$correo = $_SESSION['correo'] ?? 'Sin correo';
$cuit = $_SESSION['cuit'] ?? 'Sin CUIT';
$telefono = $_SESSION['telefono'] ?? 'Sin teléfono';
$observaciones = $_SESSION['observaciones'] ?? 'Sin observaciones';

$cierre_info = $_SESSION['cierre_info'] ?? null;
unset($_SESSION['cierre_info']);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SVE</title>

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <link rel="stylesheet" href="https://framework.impulsagroup.com/assets/css/framework.css">
    <script src="https://framework.impulsagroup.com/assets/javascript/framework.js" defer></script>

    <style>
        .sidebar-section-title {
            margin: 12px 16px 6px;
            font-size: .72rem;
            font-weight: 600;
            letter-spacing: .08em;
            text-transform: uppercase;
            opacity: .7;
        }

        .submenu-root {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .submenu-root a {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .4rem 1.5rem;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="layout">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <span class="material-icons logo-icon">dashboard</span>
                <span class="logo-text">SVE</span>
            </div>

            <nav class="sidebar-menu">
                <div class="sidebar-section-title">Menú</div>
                <ul>
                    <li onclick="location.href='ing_dashboard.php'">
                        <span class="material-icons" style="color:#5b21b6;">home</span>
                        <span class="link-text">Inicio</span>
                    </li>
                    <li>
                        <a href="https://compraconjunta.sve.com.ar/publicaciones" target="_blank" rel="noopener noreferrer">
                            <span class="material-icons" style="color:#5b21b6;">menu_book</span>
                            <span class="link-text">Biblioteca Virtual</span>
                        </a>
                    </li>
                </ul>

                <div class="sidebar-section-title">Drones</div>
                <ul class="submenu-root">
                    <li>
                        <a href="ing_servicios.php">
                            <span class="material-symbols-outlined" style="color:#5b21b6">add</span>
                            <span class="link-text">Solicitar Servicio</span>
                        </a>
                    </li>
                    <li>
                        <a href="ing_pulverizacion.php">
                            <span class="material-symbols-outlined" style="color:#5b21b6">drone</span>
                            <span class="link-text">Servicios Solicitados</span>
                        </a>
                    </li>
                </ul>

                <div class="sidebar-section-title">Relevamiento</div>
                <ul class="submenu-root">
                    <li>
                        <a href="ing_relevamiento.php">
                            <span class="material-symbols-outlined" style="color:#5b21b6">map</span>
                            <span class="link-text">Relevamiento</span>
                        </a>
                    </li>
                </ul>

                <ul>
                    <li onclick="location.href='../../../logout.php'">
                        <span class="material-icons" style="color:red;">logout</span>
                        <span class="link-text">Salir</span>
                    </li>
                </ul>
            </nav>

            <div class="sidebar-footer">
                <button class="btn-icon" onclick="toggleSidebar()">
                    <span class="material-icons" id="collapseIcon">chevron_left</span>
                </button>
            </div>
        </aside>

        <div class="main">
            <header class="navbar">
                <button class="btn-icon" onclick="toggleSidebar()">
                    <span class="material-icons">menu</span>
                </button>
                <div class="navbar-title">Relevamiento</div>
            </header>

            <section class="content">
                <?php include __DIR__ . '/../partials/relevamiento/shared/edit_relevamiento.php'; ?>
            </section>
        </div>
    </div>
</body>

</html>
