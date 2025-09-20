<?php
// Mostrar errores en pantalla (útil en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión y configurar parámetros de seguridad
require_once '../../middleware/authMiddleware.php';
checkAccess('cooperativa');

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

    <style>
        /* Estilos tarjetas */
        .user-card {
            border: 2px solid #5b21b6;
            border-radius: 12px;
            padding: 1rem;
            transition: border 0.3s ease;
        }

        .user-card.completo {
            border: 2px solid green;
        }

        .user-card.incompleto {
            border: 2px solid red;
        }

        /* ocultar imputs */
        .oculto {
            display: none !important;
        }
    </style>
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
                    <li onclick="location.href='coop_dashboard.php'">
                        <span class="material-icons" style="color: #5b21b6;">home</span><span class="link-text">Inicio</span>
                    </li>
                    <li onclick="location.href='coop_mercadoDigital.php'">
                        <span class="material-icons" style="color: #5b21b6;">shopping_cart</span><span class="link-text">Mercado Digital</span>
                    </li>
                    <li onclick="location.href='coop_listadoPedidos.php'">
                        <span class="material-icons" style="color: #5b21b6;">receipt_long</span><span class="link-text">Listado Pedidos</span>
                    </li>
                    <li onclick="location.href='coop_consolidado.php'">
                        <span class="material-icons" style="color: #5b21b6;">analytics</span><span class="link-text">Consolidado</span>
                    </li>
                    <li onclick="location.href='coop_pulverizacion.php'">
                    <span class="material-symbols-outlined" style="color:#5b21b6;">drone</span><span class="link-text">Pulverización con Drone</span>
                    </li>
                    <li onclick="location.href='coop_usuarioInformacion.php'">
                        <ure class="material-icons" style="color: #5b21b6;">agriculture</ure><span class="link-text">Productores</span>
                    </li>
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
                    <h2>Hola! </h2>
                    <p>Te presentamos el gestor de proyectos de vuelo. Desde acá, vas a controlar todo el servicio de pulverización con drones.</p>

                    <!-- 🔘 Tarjeta con los botones del tab -->
                    <div class="tabs">
                        <div class="tab-buttons">
                            <button class="tab-button" data-target="#panel-solicitudes">Solicitudes</button>
                            <button class="tab-button" data-target="#panel-formulario">Nuevo servicio</button>
                            <button class="tab-button" data-target="#panel-protocolo">Protocolo</button>
                            <button class="tab-button" data-target="#panel-calendario">Calendario</button>
                            <button class="tab-button" data-target="#panel-registro">Registro fito sanitario</button>
                            <button class="tab-button" data-target="#panel-stock">Stock</button>
                            <button class="tab-button" data-target="#panel-variables">Variables</button>
                            <!-- Botón de actualización on-demand -->
                            <button id="btn-refresh" class="btn btn-aceptar" style="margin-left:auto">Actualizar</button>
                        </div>
                    </div>
                </div>

                <!-- 🧩 Tarjeta separada para el contenido del tab -->
                <div class="card" id="tab-content-card" style="margin-top: 12px;">

                    <!-- Panel: Solicitudes -->
                    <div class="tab-panel active" id="panel-solicitudes">
                        <?php
                        $viewFile = __DIR__ . '/../partials/drones/view/drone_list_view.php';
                        if (is_file($viewFile)) {
                            require $viewFile;
                        } else {
                            echo '<p>No se encontró la vista <code>drone_list_view.php</code>.</p>';
                        }
                        ?>
                    </div>

                    <!-- Panel: Formulario -->
                    <div class="tab-panel" id="panel-formulario">
                        <?php
                        $viewFile = __DIR__ . '/../partials/drones/view/drone_formulario_N_Servicio_view.php';
                        if (is_file($viewFile)) {
                            require $viewFile;
                        } else {
                            echo '<p>No se encontró la vista <code>drone_formulario_N_Servicio_view.php</code>.</p>';
                        }
                        ?>
                    </div>

                    <!-- Panel: Protocolo -->
                    <div class="tab-panel" id="panel-protocolo">
                        <?php
                        $viewFile = __DIR__ . '/../partials/drones/view/drone_protocol_view.php';
                        if (is_file($viewFile)) {
                            require $viewFile;
                        } else {
                            echo '<p>No se encontró la vista <code>drone_protocol_view.php</code>.</p>';
                        }
                        ?>
                    </div>

                    <!-- Panel: Calendario -->
                    <div class="tab-panel" id="panel-calendario">
                        <?php
                        $viewFile = __DIR__ . '/../partials/drones/view/drone_calendar_view.php';
                        if (is_file($viewFile)) {
                            require $viewFile;
                        } else {
                            echo '<p>No se encontró la vista <code>drone_calendar_view.php</code>.</p>';
                        }
                        ?>
                    </div>

                    <!-- Panel: Registro -->
                    <div class="tab-panel" id="panel-registro">
                        <?php
                        $viewFile = __DIR__ . '/../partials/drones/view/drone_registro_view.php';
                        if (is_file($viewFile)) {
                            require $viewFile;
                        } else {
                            echo '<p>No se encontró la vista <code>drone_registro_view.php</code>.</p>';
                        }
                        ?>
                    </div>

                    <!-- Panel: Stock -->
                    <div class="tab-panel" id="panel-stock">
                        <?php
                        $viewFile = __DIR__ . '/../partials/drones/view/drone_stock_view.php';
                        if (is_file($viewFile)) {
                            require $viewFile;
                        } else {
                            echo '<p>No se encontró la vista <code>drone_stock_view.php</code>.</p>';
                        }
                        ?>
                    </div>

                    <!-- Panel: Variables -->
                    <div class="tab-panel" id="panel-variables">
                        <?php
                        $viewFile = __DIR__ . '/../partials/drones/view/drone_variables_view.php';
                        if (is_file($viewFile)) {
                            require $viewFile;
                        } else {
                            echo '<p>No se encontró la vista <code>drone_variables_view.php</code>.</p>';
                        }
                        ?>
                    </div>

                </div>

                <!-- contenedor del toastify -->
                <div id="toast-container"></div>
                <div id="toast-container-boton"></div>
                <!-- Spinner Global -->
                <script src="../../views/partials/spinner-global.js"></script>

            </section>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('.tab-buttons .tab-button[data-target]');
            const panels = document.querySelectorAll('#tab-content-card .tab-panel');
            const STORAGE_KEY = 'sve_drone_tab';

            function isTabButton(el) {
                return el && el.dataset && el.dataset.target;
            }

            function activate(targetSel) {
                // Limpia estados
                buttons.forEach(b => b.classList.remove('active'));
                panels.forEach(p => p.classList.remove('active'));

                // Activa botón/panel destino
                const btn = Array.from(buttons).find(b => b.dataset.target === targetSel);
                const panel = document.querySelector(targetSel);

                if (btn) btn.classList.add('active');
                if (panel) panel.classList.add('active');

                // Quitar fondo/sombra del contenedor en vistas "planas"
                const wrapper = document.getElementById('tab-content-card');
                if (wrapper) {
                    const sinChrome = ['#panel-variables', '#panel-stock', '#panel-protocolo'].includes(targetSel);
                    wrapper.classList.toggle('no-chrome', sinChrome);
                }
            }

            // Cambiar de pestaña SIN recargar
            buttons.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    if (!isTabButton(btn)) return;
                    const target = btn.dataset.target;
                    if (!target) return;
                    sessionStorage.setItem(STORAGE_KEY, target);
                    activate(target);
                });
            });

            // Botón "Actualizar" (recarga manual)
            const refreshBtn = document.getElementById('btn-refresh');
            if (refreshBtn) {
                refreshBtn.addEventListener('click', () => {
                    // Conserva la pestaña actual al recargar
                    const activeBtn = document.querySelector('.tab-buttons .tab-button.active[data-target]');
                    const current = activeBtn ? activeBtn.dataset.target : '#panel-solicitudes';
                    sessionStorage.setItem(STORAGE_KEY, current);
                    location.reload();
                });
            }

            // Activar la pestaña persistida (o default)
            const initial = sessionStorage.getItem(STORAGE_KEY) || '#panel-solicitudes';
            activate(initial);
        });
    </script>
    <!-- llamada de tutorial -->
    <script src="../partials/tutorials/cooperativas/productores.js?v=<?= time() ?>" defer></script>
</body>

</html>