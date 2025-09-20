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
            border-color: green;
        }

        .user-card.incompleto {
            border-color: red;
        }

        /* ocultar inputs */
        .oculto {
            display: none !important;
        }

        /* --- Tabs: visibilidad y feedback --- */
        .tab-panel {
            display: none;
        }

        .tab-panel.active {
            display: block;
        }

        .tabs .tab-buttons {
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .tab-button.active {
            border-bottom: 2px solid #5b21b6;
        }

        /* Evitar FOUC: ocultar paneles hasta que JS marque el activo */
        .js-ready .tab-panel {
            display: none;
        }

        .js-ready .tab-panel.active {
            display: block;
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
                        <span class="material-icons" style="color: #5b21b6;">agriculture</span><span class="link-text">Productores</span>
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
                        <div class="tab-buttons" role="tablist" aria-label="Secciones de pulverización">
                            <button type="button" id="tab-solicitudes" class="tab-button" role="tab" aria-controls="panel-solicitudes" aria-selected="true" data-target="#panel-solicitudes">Solicitudes</button>
                            <button type="button" id="tab-formulario" class="tab-button" role="tab" aria-controls="panel-formulario" aria-selected="false" data-target="#panel-formulario">Nuevo servicio</button>
                            <!-- Botón de actualización on-demand -->
                            <button type="button" id="btn-refresh" class="btn btn-aceptar" style="margin-left:auto">Actualizar</button>
                        </div>
                    </div>
                </div>

                <!-- 🧩 Tarjeta separada para el contenido del tab -->
                <div class="card" id="tab-content-card" style="margin-top: 12px;">

                    <!-- Panel: Solicitudes -->
                    <div class="tab-panel active" id="panel-solicitudes" role="tabpanel" aria-labelledby="tab-solicitudes" tabindex="0">
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
                    <div class="tab-panel" id="panel-formulario" role="tabpanel" aria-labelledby="tab-formulario" tabindex="0" hidden>
                        <?php
                        $viewFile = __DIR__ . '/../partials/drones/view/drone_formulario_N_Servicio_view.php';
                        if (is_file($viewFile)) {
                            require $viewFile;
                        } else {
                            echo '<p>No se encontró la vista <code>drone_formulario_N_Servicio_view.php</code>.</p>';
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
    document.addEventListener('DOMContentLoaded', function () {
        // Señal para CSS: ya puede mostrar solo el activo
        document.documentElement.classList.add('js-ready');

        const STORAGE_KEY = 'sve_drone_tab';
        const buttons = document.querySelectorAll('.tab-buttons .tab-button[data-target]');
        const panels  = document.querySelectorAll('#tab-content-card .tab-panel');

        function syncHidden(targetSel) {
            panels.forEach(p => {
                const isActive = '#' + p.id === targetSel;
                p.classList.toggle('active', isActive);
                // Sincroniza atributo hidden por accesibilidad y estilos del navegador
                if (isActive) {
                    p.removeAttribute('hidden');
                } else {
                    p.setAttribute('hidden', 'hidden');
                }
            });
        }

        function syncButtons(targetSel) {
            buttons.forEach(b => {
                const isActive = b.dataset.target === targetSel;
                b.classList.toggle('active', isActive);
                b.setAttribute('aria-selected', isActive ? 'true' : 'false');
                if (isActive) b.focus({preventScroll:true});
            });
        }

        function activate(targetSel) {
            syncButtons(targetSel);
            syncHidden(targetSel);
            sessionStorage.setItem(STORAGE_KEY, targetSel);
        }

        // Click en tabs
        buttons.forEach(btn => {
            btn.addEventListener('click', () => {
                const target = btn.dataset.target;
                if (!target) return;
                activate(target);
            });
        });

        // Botón "Actualizar" (recarga manual)
        const refreshBtn = document.getElementById('btn-refresh');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                const activeBtn = document.querySelector('.tab-buttons .tab-button.active[data-target]');
                const current = activeBtn ? activeBtn.dataset.target : '#panel-solicitudes';
                sessionStorage.setItem(STORAGE_KEY, current);
                location.reload();
            });
        }

        // Estado inicial
        const initial = sessionStorage.getItem(STORAGE_KEY) || '#panel-solicitudes';
        activate(initial);
    });
</script>


<!-- Mantener defer; si el tutorial manipula tabs, no debe sobreescribir el estado -->
<script src="../partials/tutorials/cooperativas/productores.js?v=<?= time() ?>" defer></script>

</body>

</html>