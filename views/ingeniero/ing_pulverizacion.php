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
$rol = $_SESSION['rol'] ?? 'Sin ROL';
$id_real = $_SESSION['id_real'] ?? 'Sin ROL';
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

        .oculto {
            display: none !important;
        }

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

        .js-ready .tab-panel {
            display: none;
        }

        .js-ready .tab-panel.active {
            display: block;
        }

        /* Título pequeño de sección (similar a “APPS”) */
        .sidebar-section-title {
            margin: 12px 16px 6px;
            font-size: .72rem;
            font-weight: 600;
            letter-spacing: .08em;
            text-transform: uppercase;
            opacity: .7;
        }

        /* Lista simple de subitems */
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

    <!-- 🔲 CONTENEDOR PRINCIPAL -->
    <div class="layout">

        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <span class="material-icons logo-icon">dashboard</span>
                <span class="logo-text">SVE</span>
            </div>
            <nav class="sidebar-menu">

            <!-- Título de sección -->
                <div class="sidebar-section-title">Menú</div>

                <!-- Grupo superior -->
                <ul>
                    <li onclick="location.href='ing_dashboard.php'">
                        <span class="material-icons" style="color:#5b21b6;">home</span>
                        <span class="link-text">Inicio</span>
                    </li>
                </ul>

                <!-- Título de sección -->
                <div class="sidebar-section-title">Drones</div>

                <!-- Lista directa de páginas de Drones (sin acordeón) -->
                <ul class="submenu-root">
                    <li>
                        <a href="ing_servicios.php">
                            <span class="material-symbols-outlined">add</span>
                            <span class="link-text">Solicitar Servicio</span>
                        </a>
                    </li>

                    <li>
                        <a href="ing_pulverizacion.php">
                            <span class="material-symbols-outlined">drone</span>
                            <span class="link-text">Servicios Solicitados</span>
                        </a>
                    </li>

                    <!-- Agregá más ítems aquí cuando existan nuevas hojas de Drones -->
                </ul>

                <!-- Resto de opciones -->
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

                            <!-- Botón Tutorial -->
                            <button type="button" id="btnIniciarTutorial" class="btn btn-info" aria-label="Iniciar tutorial" style="margin-left:auto">Tutorial</button>

                            <!-- Botón de actualización on-demand -->
                            <button type="button" id="btn-refresh" class="btn btn-aceptar tutorial-BotonActualizar">Actualizar</button>
                        </div>
                    </div>
                </div>

                <!-- 🧩 Tarjeta separada para el contenido del tab -->
                <div class="card" id="tab-content-card" style="margin-top: 12px;">

                    <!-- Panel: Solicitudes -->
                    <div class="tab-panel active tutorial-PanelSolicitudes" id="panel-solicitudes" role="tabpanel" aria-labelledby="tab-solicitudes" tabindex="0">
                        <div class="tutorial-TablaSolicitudes">
                            <?php
                            $viewFile = __DIR__ . '/../partials/drones/view/drone_list_view.php';
                            if (is_file($viewFile)) {
                                require $viewFile;
                            } else {
                                echo '<p>No se encontró la vista <code>drone_list_view.php</code>.</p>';
                            }
                            ?>
                        </div>
                    </div>

                    <!-- Panel: Formulario -->
                    <div class="tab-panel tutorial-PanelFormulario" id="panel-formulario" role="tabpanel" aria-labelledby="tab-formulario" tabindex="0" hidden>
                        <div class="tutorial-FormularioNuevoServicio">
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

                </div>

                <!-- contenedor del toastify -->
                <div id="toast-container"></div>
                <div id="toast-container-boton"></div>
                <!-- Spinner Global -->
                <script src="../../views/partials/spinner-global.js"></script>
                <!-- Debug de sesión (solo campos no sensibles) -->
                <script>
                    (function() {
                        try {
                            // Datos de sesión expuestos de forma controlada
                            const sessionData = <?= json_encode([
                                                    'nombre'         => $nombre,
                                                    'correo'         => $correo,
                                                    'cuit'           => $cuit,
                                                    'rol'            => $rol,
                                                    'telefono'       => $telefono,
                                                    'observaciones'  => $observaciones,
                                                    'id_real'        => $_SESSION['id_real'] ?? null,
                                                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

                            // Variable global de solo lectura (convención para depurar)
                            Object.defineProperty(window, '__SVE_SESSION__', {
                                value: Object.freeze(sessionData),
                                writable: false,
                                configurable: false,
                                enumerable: true
                            });

                            // Log amigable
                            console.info('[SVE] Sesión cargada:', sessionData);
                        } catch (err) {
                            console.error('[SVE] Error al exponer la sesión:', err);
                        }
                    })();
                </script>
            </section>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Señal para CSS: ya puede mostrar solo el activo
            document.documentElement.classList.add('js-ready');

            const STORAGE_KEY = 'sve_drone_tab';
            const buttons = document.querySelectorAll('.tab-buttons .tab-button[data-target]');
            const panels = document.querySelectorAll('#tab-content-card .tab-panel');

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
                    if (isActive) b.focus({
                        preventScroll: true
                    });
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
    <script src="../partials/tutorials/cooperativas/pulverizacion.js?v=<?= time() ?>" defer></script>

</body>

</html>