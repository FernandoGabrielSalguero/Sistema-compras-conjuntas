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

<style>
    /* === Filtros responsive (solo esta vista) === */
    #card-filtros .filters-grid {
        display: grid;
        gap: 12px;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        align-items: end;
    }

    /* Tablet */
    @media (max-width: 900px) {
        #card-filtros .filters-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    /* Mobile */
    @media (max-width: 600px) {
        #card-filtros .filters-grid {
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }
    }
</style>


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
        <div class="main" id="main" aria-live="polite">

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
                    <h2>Hola!</h2>
                    <p>En esta página, vas a poder solicitar los servicios disponibles para los productores asociados a tus cooperativas</p>

                    <!-- 🔘 Tarjeta con los botones del tab -->
                    <div class="tabs">
                        <div class="tab-buttons" role="tablist" aria-label="Secciones de pulverización">
                            <!-- Botón Tutorial -->
                            <button type="button" id="btnIniciarTutorial" class="btn btn-info" aria-label="Iniciar tutorial" style="margin-left:auto">Tutorial</button>
                        </div>
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
                    <div class="filters-grid">
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
                        <div>
                            <button class="btn btn-info" type="button" id="btnLimpiarFiltros" style="width:100%;">Limpiar filtros</button>
                        </div>
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
                            <button class="btn btn-aceptar" type="button" onclick="sveCloseModal('modalTractor')">Aceptar</button>
                            <button class="btn btn-cancelar" type="button" onclick="sveCloseModal('modalTractor')">Cancelar</button>


                        </div>
                    </div>
                </div>

                <div id="modalDrone" class="modal hidden" role="dialog" aria-modal="true" aria-labelledby="modalDroneTitle">
                    <div class="modal-content">
                        <h3 id="modalDroneTitle">Detalle Drone</h3>
                        <p id="modalDroneBody">Información del productor seleccionada aparecerá aquí.</p>
                        <div class="form-buttons">
                            <button class="btn btn-aceptar" type="button" onclick="sveCloseModal('modalDrone')">Aceptar</button>
                            <button class="btn btn-cancelar" type="button" onclick="sveCloseModal('modalDrone')">Cancelar</button>


                        </div>
                    </div>
                </div>

                <div id="modalFamilia" class="modal hidden" role="dialog" aria-modal="true" aria-labelledby="modalFamiliaTitle">
                    <div class="modal-content">
                        <h3 id="modalFamiliaTitle">Grupo Familiar</h3>
                        <p id="modalFamiliaBody">Información del productor seleccionada aparecerá aquí.</p>
                        <div class="form-buttons">
                            <button class="btn btn-aceptar" type="button" onclick="sveCloseModal('modalFamilia')">Aceptar</button>
                            <button class="btn btn-cancelar" type="button" onclick="sveCloseModal('modalFamilia')">Cancelar</button>


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

    <!-- toast + lógica de cooperativas/productores -->
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            console.log('Datos de sesión', <?php echo json_encode($_SESSION); ?>);

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

            inicializarCooperativasYFiltros();
        });

        async function inicializarCooperativasYFiltros() {
            const selectCoop = document.getElementById('selectCooperativa');
            const resumen = document.getElementById('coopResumen');
            const tbody = document.getElementById('tbodyProductores');
            const filaVacia = document.getElementById('filaVacia');
            const filtroNombre = document.getElementById('filtroNombre');
            const filtroCuit = document.getElementById('filtroCuit');
            const filtroZona = document.getElementById('filtroZona');

            // Cargar cooperativas del ingeniero
            try {
                const res = await fetch('../../controllers/ing_ServiciosController.php?action=cooperativas_del_ingeniero', {
                    credentials: 'include'
                });
                const json = await res.json();
                console.log('cooperativas_del_ingeniero →', json);

                if (json.ok && Array.isArray(json.data)) {
                    resumen.value = `${json.data.length} cooperativa(s)`;
                    json.data.forEach(c => {
                        const opt = document.createElement('option');
                        opt.value = c.cooperativa_id_real;
                        opt.textContent = `${c.nombre} (${c.cuit ?? 'sin CUIT'})`;
                        selectCoop.appendChild(opt);
                    });
                } else {
                    showAlert('error', json.error || 'No se pudieron cargar las cooperativas.');
                }
            } catch (e) {
                console.error('Error cargando cooperativas:', e);
                showAlert('error', 'Error cargando cooperativas.');
            }

            // Cambio de cooperativa → carga productores
            selectCoop.addEventListener('change', async () => {
                const coopId = selectCoop.value;
                console.log('Cooperativa seleccionada:', coopId);
                await cargarProductores(coopId);
                aplicarFiltros(); // inicial para aplicar filtros con la lista cargada
            });

            // Filtros en vivo
            ['input', 'keyup', 'change'].forEach(evt => {
                filtroNombre.addEventListener(evt, aplicarFiltros);
                filtroCuit.addEventListener(evt, aplicarFiltros);
                filtroZona.addEventListener(evt, aplicarFiltros);
            });

            document.getElementById('btnLimpiarFiltros').addEventListener('click', () => {
                filtroNombre.value = '';
                filtroCuit.value = '';
                filtroZona.value = '';
                aplicarFiltros();
            });

            async function cargarProductores(coopId) {
                tbody.innerHTML = '';
                if (!coopId) {
                    tbody.appendChild(filaVaciaTemplate());
                    return;
                }
                try {
                    const res = await fetch(`../../controllers/ing_ServiciosController.php?action=productores_por_coop&cooperativa_id_real=${encodeURIComponent(coopId)}`, {
                        credentials: 'include'
                    });
                    const json = await res.json();
                    console.log('productores_por_coop →', json);

                    if (!(json.ok && Array.isArray(json.data))) {
                        showAlert('info', json.error || 'No se encontraron productores.');
                        tbody.appendChild(filaVaciaTemplate());
                        return;
                    }

                    if (json.data.length === 0) {
                        tbody.appendChild(filaVaciaTemplate('La cooperativa seleccionada no tiene productores asociados'));
                        return;
                    }

                    json.data.forEach((p, idx) => {
                        const tr = document.createElement('tr');
                        tr.dataset.nombre = (p.nombre || '').toLowerCase();
                        tr.dataset.cuit = String(p.cuit || '');
                        tr.dataset.zona = (p.zona || '').toLowerCase();

                        tr.innerHTML = `
    <td>${idx + 1}</td>
    <td>${p.nombre || '-'}</td>
    <td>${p.cuit || '-'}</td>
    <td>${p.telefono || '-'}</td>
    <td>${p.zona || '-'}</td>
    <td>
    <!-- TOOLTIP: botón Tractor -->
    <button class="btn-icon" aria-label="Tractor" title="Tractor" onclick="openModalId('modalTractor')">
        <span class="material-symbols-outlined" style="color:green;">agriculture</span>
    </button>

    <!-- TOOLTIP: botón Drone -->
    <button class="btn-icon" aria-label="Drone" title="Drone" onclick="openModalId('modalDrone')">
        <span class="material-symbols-outlined" style="color:green;">drone</span>
    </button>

    <!-- TOOLTIP: botón Familia -->
    <button class="btn-icon" aria-label="Familia" title="Familia" onclick="openModalId('modalFamilia')">
        <span class="material-icons" style="color:green;">diversity_3</span>
    </button>
</td>


`;


                        tbody.appendChild(tr);
                    });
                } catch (e) {
                    console.error('Error cargando productores:', e);
                    showAlert('error', 'Error cargando productores.');
                    tbody.appendChild(filaVaciaTemplate());
                }
            }

            function aplicarFiltros() {
                const nombre = (filtroNombre.value || '').toLowerCase();
                const cuit = (filtroCuit.value || '').replace(/\D/g, '');
                const zona = (filtroZona.value || '').toLowerCase();
                const rows = Array.from(document.querySelectorAll('#tbodyProductores tr'));

                if (rows.length === 1 && rows[0].id === 'filaVacia') return; // nada que filtrar

                let visibles = 0;
                rows.forEach(r => {
                    if (r.id === 'filaVacia') return;
                    const matchNombre = !nombre || (r.dataset.nombre || '').includes(nombre);
                    const matchCuit = !cuit || (r.dataset.cuit || '').includes(cuit);
                    const matchZona = !zona || (r.dataset.zona || '').includes(zona);
                    const visible = matchNombre && matchCuit && matchZona;
                    r.style.display = visible ? '' : 'none';
                    if (visible) visibles++;
                });

                // mensaje si no hay resultados
                const existente = document.getElementById('filaSinResultados');
                if (existente) existente.remove();
                if (visibles === 0) {
                    const tr = document.createElement('tr');
                    tr.id = 'filaSinResultados';
                    tr.innerHTML = `<td colspan="6">No hay resultados para los filtros aplicados</td>`;
                    document.getElementById('tbodyProductores').appendChild(tr);
                }
            }

            function filaVaciaTemplate(msg = 'Selecciona una cooperativa para poder ver a sus productores asociados') {
                const tr = document.createElement('tr');
                tr.id = 'filaVacia';
                tr.innerHTML = `<td colspan="6">${msg}</td>`;
                return tr;
            }
        }

        // Modales simples: un botón → un modal (sin payload)
        function openModalId(id) {
            console.log('openModalId', id);
            const el = document.getElementById(id);
            el && el.classList.remove('hidden');
        }

        // Namespacing para apertura también, si en el futuro hay colisión:
        window.openModalId = window.openModalId || function(id) {
            console.log('openModalId', id);
            const el = document.getElementById(id);
            if (el) el.classList.remove('hidden');
        };

        // Namespacing para no chocar con framework.js
        function sveCloseModal(id) {
            console.log('sveCloseModal', id);
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.add('hidden');
            }
        }
    </script>


</body>


</html>