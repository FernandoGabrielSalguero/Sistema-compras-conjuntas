<?php
// Mostrar errores en pantalla (útil en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión y configurar parámetros de seguridad
require_once '../../middleware/authMiddleware.php';
checkAccess('ingeniero');

// Datos del usuario en sesión
$nombre = $_SESSION['nombre'] ?? 'Sin nombre';
$correo = $_SESSION['correo'] ?? 'Sin correo';
$cuit = $_SESSION['cuit'] ?? 'Sin CUIT';
$telefono = $_SESSION['telefono'] ?? 'Sin teléfono';
$observaciones = $_SESSION['observaciones'] ?? 'Sin observaciones';

//Cargamos los operativos cerrados
$cierre_info = $_SESSION['cierre_info'] ?? null;
unset($_SESSION['cierre_info']);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SVE</title>

    <!-- Íconos de Material Design -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- Framework Success desde CDN -->
    <link rel="stylesheet" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css">
    <script src="https://www.fernandosalguero.com/cdn/assets/javascript/framework.js" defer></script>
</head>

<body>

    <!-- 🔲 CONTENEDOR PRINCIPAL -->
    <div class="layout">

        <!-- 🧭 SIDEBAR -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <span class="material-icons logo-icon">dashboard</span>
                <span class="logo-text">SVE</span>
            </div>

            <nav class="sidebar-menu">
    <ul>
        <li onclick="location.href='ing_dashboard.php'">
            <span class="material-icons" style="color: #5b21b6;">home</span><span class="link-text">Inicio</span>
        </li>

        <!-- Acordeón: Drone -->
        <li class="accordion" style="list-style: none;">
            <button id="menu-drone-toggle" class="btn-icon" type="button" style="width:100%;display:flex;align-items:center;gap:.5rem;justify-content:space-between;background:transparent;border:none;padding:.75rem 1rem;cursor:pointer;">
                <span style="display:flex;align-items:center;gap:.5rem;">
                    <span class="material-symbols-outlined" style="color:#5b21b6;">drone</span>
                    <span class="link-text">Drone</span>
                </span>
                <span id="menu-drone-expand" class="material-icons">expand_more</span>
            </button>
            <ul id="menu-drone-submenu" class="submenu" style="display:none;margin:0;padding:0 0 0 2.25rem;">
                <li onclick="location.href='ing_pulverizacion.php'" style="list-style:none;cursor:pointer;padding:.5rem 1rem;">
                    <span class="material-symbols-outlined" style="color:#5b21b6;">drone</span>
                    <span class="link-text">Drones</span>
                </li>
                <li onclick="location.href='ing_servicios.php'" style="list-style:none;cursor:pointer;padding:.5rem 1rem;">
                    <span class="material-icons" style="color:#5b21b6;">upload_file</span>
                    <span class="link-text">Servicios</span>
                </li>
            </ul>
        </li>
        <!-- Fin acordeón -->

        <li onclick="location.href='../../../logout.php'">
            <span class="material-icons" style="color: red;">logout</span><span class="link-text">Salir</span>
        </li>
    </ul>
</nav>


            <div class="sidebar-footer">
                <button class="btn-icon" onclick="toggleSidebar()">
                    <span class="material-icons" id="collapseIcon">chevron_left</span>
                </button>
            </div>
        </aside>

        <!-- 🧱 MAIN -->
        <div class="main">

            <!-- 🟪 NAVBAR -->
            <header class="navbar">
                <button class="btn-icon" onclick="toggleSidebar()">
                    <span class="material-icons">menu</span>
                </button>
                <div class="navbar-title">Inicio</div>
            </header>

            <!-- 📦 CONTENIDO -->
            <section class="content">

                <!-- Bienvenida -->
                <div class="card">
                    <h2>Hola</h2>
                    <p>Te presentamos el tablero Power BI. Vas a poder consultar todas las metricas desde esta página</p>
                </div>


                <!-- contenedor del toastify -->
                <div id="toast-container"></div>
                <div id="toast-container-boton"></div>
                <!-- Spinner Global -->
                <script src="../../views/partials/spinner-global.js"></script>

            </section>

        </div>
    </div>

    <!-- toast + acordeón Drone -->
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            console.log(<?php echo json_encode($_SESSION); ?>);

            <?php if (!empty($cierre_info)): ?>
                const cierreData = <?= json_encode($cierre_info, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
                cierreData.pendientes.forEach(op => {
                    const mensaje = `El operativo "${op.nombre}" se cierra en ${op.dias_faltantes} día(s).`;
                    console.log(mensaje);
                    if (typeof showToastBoton === 'function') {
                        showToastBoton('info', mensaje);
                    } else {
                        console.warn('⚠️ showToastBoton no está definido aún.');
                    }
                });
            <?php endif; ?>

            // Acordeón: Drone
            const btn = document.getElementById('menu-drone-toggle');
            const submenu = document.getElementById('menu-drone-submenu');
            const expandIcon = document.getElementById('menu-drone-expand');

            if (btn && submenu) {
                btn.addEventListener('click', () => {
                    const isHidden = (submenu.style.display === '' || submenu.style.display === 'none');
                    submenu.style.display = isHidden ? 'block' : 'none';
                    if (expandIcon) expandIcon.textContent = isHidden ? 'expand_less' : 'expand_more';
                });
            }
        });
    </script>


</body>


</html>