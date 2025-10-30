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

                    <li onclick="location.href='ing_pulverizacion.php'">
                    <span class="material-symbols-outlined" style="color:#5b21b6;">drone</span>
                    <span class="link-text">Drones</span>
                    </li>

                    <li onclick="location.href='ing_servicios.php'">
                        <span class="material-icons" style="color: #5b21b6;">upload_file</span><span class="link-text">Servicios</span>
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
                    <                    <h2>Hola!</h2>
                    <p>En esta página, vas a poder solicitar los servicios disponibles para los productores asociados a tus cooperativas</p>

                    <!-- 🔘 Tarjeta con los botones del tab -->
                    <div class="tabs">
                        <div class="tab-buttons" role="tablist" aria-label="Secciones de pulverización">
                            <!-- Botón Tutorial -->
                            <button type="button" id="btnIniciarTutorial" class="btn btn-info" aria-label="Iniciar tutorial" style="margin-left:auto">Tutorial</button>
                        </div>
                    </div>

                    <!-- 🧩 Cooperativas del ingeniero -->
                    <div class="card" id="card-cooperativas" aria-labelledby="coops-title">
                        <h2 id="coops-title">Tus cooperativas</h2>
                        <p>Seleccioná una cooperativa para ver sus productores asociados.</p>
                        <div class="form-grid grid-2">
                            <div class="input-group">
                                <label for="selectCooperativa">Cooperativa</label>
                                <div class="input-icon input-icon-name">
                                    <select id="selectCooperativa" name="selectCooperativa" aria-label="Seleccionar cooperativa">
                                        <option value="">-- Seleccioná una cooperativa --</option>
                                    </select>
                                </div>
                            </div>
                            <div class="input-group">
                                <label for="coopResumen">Resumen</label>
                                <div class="input-icon input-icon-name">
                                    <input type="text" id="coopResumen" name="coopResumen" placeholder="0 cooperativas" readonly />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 🔎 Filtros -->
                    <div class="card" id="card-filtros" aria-labelledby="filtros-title">
                        <h2 id="filtros-title">Filtros</h2>
                        <div class="form-grid grid-3">
                            <div class="input-group">
                                <label for="filtroNombre">Nombre</label>
                                <div class="input-icon input-icon-name">
                                    <input type="text" id="filtroNombre" name="filtroNombre" placeholder="Ej: Juan Pérez" aria-describedby="ayudaNombre" />
                                </div>
                            </div>
                            <div class="input-group">
                                <label for="filtroCuit">CUIT</label>
                                <div class="input-icon input-icon-name">
                                    <input type="text" id="filtroCuit" name="filtroCuit" placeholder="Ej: 20123456789" inputmode="numeric" />
                                </div>
                            </div>
                            <div class="input-group">
                                <label for="filtroZona">Zona</label>
                                <div class="input-icon input-icon-name">
                                    <input type="text" id="filtroZona" name="filtroZona" placeholder="Ej: Este / Oeste / Valle" />
                                </div>
                            </div>
                        </div>
                        <div class="form-grid grid-3">
                            <button class="btn btn-info" type="button" id="btnLimpiarFiltros">Limpiar filtros</button>
                            <div></div>
                            <div></div>
                        </div>
                    </div>

                    <!-- 📊 Tabla de productores -->
                    <div class="card tabla-card" id="card-productores" aria-labelledby="prod-title">
                        <h2 id="prod-title">Productores asociados</h2>
                        <div class="tabla-wrapper" style="max-height: 460px; overflow: auto;">
                            <!-- 👇 Altura de la tabla: modificar el valor de max-height arriba si necesitás otro alto -->
                            <table class="data-table" id="tablaProductores">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nombre</th>
                                        <th>CUIT</th>
                                        <th>Teléfono</th>
                                        <th>Zona</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyProductores">
                                    <tr id="filaVacia">
                                        <td colspan="6">Selecciona una cooperativa para poder ver a sus productores asociados</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 🪟 Modales -->
                    <div id="modalTractor" class="modal hidden" role="dialog" aria-modal="true" aria-labelledby="modalTractorTitle">
                        <div class="modal-content">
                            <h3 id="modalTractorTitle">Detalle Tractor</h3>
                            <p id="modalTractorBody">Información del productor seleccionada aparecerá aquí.</p>
                            <div class="form-buttons">
                                <button class="btn btn-aceptar" onclick="closeModal('modalTractor')">Aceptar</button>
                                <button class="btn btn-cancelar" onclick="closeModal('modalTractor')">Cancelar</button>
                            </div>
                        </div>
                    </div>

                    <div id="modalDrone" class="modal hidden" role="dialog" aria-modal="true" aria-labelledby="modalDroneTitle">
                        <div class="modal-content">
                            <h3 id="modalDroneTitle">Detalle Drone</h3>
                            <p id="modalDroneBody">Información del productor seleccionada aparecerá aquí.</p>
                            <div class="form-buttons">
                                <button class="btn btn-aceptar" onclick="closeModal('modalDrone')">Aceptar</button>
                                <button class="btn btn-cancelar" onclick="closeModal('modalDrone')">Cancelar</button>
                            </div>
                        </div>
                    </div>

                    <div id="modalFamilia" class="modal hidden" role="dialog" aria-modal="true" aria-labelledby="modalFamiliaTitle">
                        <div class="modal-content">
                            <h3 id="modalFamiliaTitle">Grupo Familiar</h3>
                            <p id="modalFamiliaBody">Información del productor seleccionada aparecerá aquí.</p>
                            <div class="form-buttons">
                                <button class="btn btn-aceptar" onclick="closeModal('modalFamilia')">Aceptar</button>
                                <button class="btn btn-cancelar" onclick="closeModal('modalFamilia')">Cancelar</button>
                            </div>
                        </div>
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

    <!-- toast -->
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
        });
    </script>

</body>


</html>