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
    /* === Modal Drone: dimensiones cómodas para la vista embebida === */
    #modalDrone .modal-content {
        width: min(1200px, 95vw);
        max-height: 95vh;
        overflow: hidden;
    }

    #modalDrone .modal-body {
        margin-top: 8px;
        border-top: 1px solid #e5e7eb;
        padding-top: 8px;
    }

    #modalDroneIframe {
        background: #fff;
    }

    /* === Filtros responsive (solo esta vista) === */
    #card-filtros .filters-grid {
        display: grid;
        gap: 12px;
        grid-template-columns: repeat(3, minmax(0, 1fr));
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

            /* Chips y badge */
    .chip {
        display:inline-flex; align-items:center; gap:.4rem;
        padding:.35rem .7rem; border-radius:9999px;
        border:1px solid #e5e7eb; cursor:pointer; user-select:none;
        background:#fff;
    }
    .chip.active { border-color:#5b21b6; background:#f5f3ff; }
    .badge {
        display:inline-block;
        padding:.15rem .5rem;
        border-radius:9999px;
        background: #5b21b6; }

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
                    <div style="display:flex; align-items:center; gap:.5rem;">
                        <h2 id="coops-title" style="margin:0;">Tus cooperativas</h2>
                        <span id="coopCountBadge" class="badge">0</span>
                    </div>
                    <p>Seleccioná una cooperativa para ver sus productores asociados.</p>

                    <!-- Chips dinámicos -->
                    <div id="chipsCooperativas" class="chips" role="tablist" aria-label="Cooperativas del ingeniero" style="display:flex; flex-wrap:wrap; gap:.5rem;"></div>
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

        </div>

        <div id="modalDrone" class="modal hidden" role="dialog" aria-modal="true" aria-labelledby="modalDroneTitle">
            <div class="modal-content">
                <h3 id="modalDroneTitle">Crear solicitud de pulverización con Drone</h3>

                <!-- Vista embebida en iframe para evitar conflictos de HTML/CSS/JS -->
                <div id="modalDroneBody" class="modal-body">
                    <iframe
                        id="modalDroneIframe"
                        src="./ing_new_pulverizacion_view.php"
                        title="Nueva solicitud de pulverización"
                        style="width:100%; height:80vh; border:0; display:block;"
                        loading="lazy"
                        referrerpolicy="no-referrer"></iframe>
                </div>

                <div class="form-buttons">
                    <button class="btn btn-cancelar" type="button" onclick="sveCloseModal('modalDrone')">Cancelar</button>
                </div>

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
            const chipsWrap = document.getElementById('chipsCooperativas');
            const coopBadge = document.getElementById('coopCountBadge');
            const tbody = document.getElementById('tbodyProductores');
            const filtroNombre = document.getElementById('filtroNombre');
            const filtroCuit = document.getElementById('filtroCuit');

            let cooperativas = [];
            let productoresPorCoop = {};   // cache por cooperativa_id_real
            let productoresTodos = null;   // cache global (todas las coops)
            let coopSeleccionada = null;

            // Cargar cooperativas del ingeniero (chips)
            try {
                const res = await fetch('../../controllers/ing_ServiciosController.php?action=cooperativas_del_ingeniero', { credentials: 'include' });
                const json = await res.json();
                console.log('cooperativas_del_ingeniero →', json);

                if (json.ok && Array.isArray(json.data)) {
                    cooperativas = json.data;
                    coopBadge.textContent = String(cooperativas.length);

                    chipsWrap.innerHTML = '';
                    cooperativas.forEach((c, i) => {
                        const chip = document.createElement('button');
                        chip.type = 'button';
                        chip.className = 'chip';
                        chip.dataset.id = c.cooperativa_id_real;
                        chip.setAttribute('role', 'tab');
                        chip.setAttribute('aria-selected', i === 0 ? 'true' : 'false');
                        chip.innerHTML = `<span class="material-icons" style="font-size:16px;">apartment</span><span>${c.nombre} (${c.cuit ?? 'sin CUIT'})</span>`;
                        chip.addEventListener('click', async () => {
                            document.querySelectorAll('#chipsCooperativas .chip').forEach(el => { el.classList.remove('active'); el.setAttribute('aria-selected','false'); });
                            chip.classList.add('active');
                            chip.setAttribute('aria-selected','true');
                            coopSeleccionada = chip.dataset.id;
                            await cargarYRenderizarPorCoop();
                        });
                        chipsWrap.appendChild(chip);
                    });

                    // Preseleccionar primer chip
                    if (cooperativas.length > 0) {
                        const first = chipsWrap.querySelector('.chip');
                        if (first) {
                            first.classList.add('active');
                            first.setAttribute('aria-selected','true');
                            coopSeleccionada = first.dataset.id;
                            await cargarYRenderizarPorCoop();
                        }
                    } else {
                        renderRows([]); // sin cooperativas
                    }
                } else {
                    showAlert('error', json.error || 'No se pudieron cargar las cooperativas.');
                }
            } catch (e) {
                console.error('Error cargando cooperativas:', e);
                showAlert('error', 'Error cargando cooperativas.');
            }

            // Filtros en vivo
            ['input', 'keyup', 'change'].forEach(evt => {
                filtroNombre.addEventListener(evt, aplicarFiltros);
                filtroCuit.addEventListener(evt, aplicarFiltros);
            });

            document.getElementById('btnLimpiarFiltros').addEventListener('click', () => {
                filtroNombre.value = '';
                filtroCuit.value = '';
                aplicarFiltros();
            });

            async function cargarYRenderizarPorCoop() {
                if (!coopSeleccionada) { renderRows([]); return; }
                if (!productoresPorCoop[coopSeleccionada]) {
                    try {
                        const res = await fetch(`../../controllers/ing_ServiciosController.php?action=productores_por_coop&cooperativa_id_real=${encodeURIComponent(coopSeleccionada)}`, { credentials: 'include' });
                        const json = await res.json();
                        console.log('productores_por_coop →', json);
                        productoresPorCoop[coopSeleccionada] = (json.ok && Array.isArray(json.data)) ? json.data : [];
                    } catch (e) {
                        console.error('Error cargando productores:', e);
                        productoresPorCoop[coopSeleccionada] = [];
                        showAlert('error', 'Error cargando productores.');
                    }
                }
                aplicarFiltros(); // render con dataset actual/cooperativa
            }

            async function getTodosLosProductores() {
                if (productoresTodos) return productoresTodos;
                try {
                    const res = await fetch(`../../controllers/ing_ServiciosController.php?action=productores_del_ingeniero`, { credentials: 'include' });
                    const json = await res.json();
                    console.log('productores_del_ingeniero →', json);
                    productoresTodos = (json.ok && Array.isArray(json.data)) ? json.data : [];
                } catch (e) {
                    console.error('Error cargando productores del ingeniero:', e);
                    productoresTodos = [];
                }
                return productoresTodos;
            }

            function normalizarFila(p, idxBase = 0) {
                return `
    <tr data-usuario-id-real="${String(p.usuario_id_real || p.id_real || p.id || '')}">
        <td>${idxBase + 1}</td>
        <td>${p.nombre || '-'}</td>
<td>${p.cuit || '-'}</td>
<td>${p.telefono || '-'}</td>
<td>
    <button class="btn btn-success" type="button" title="Solicitar Servicio" onclick="openModalId('modalDrone', this)">
        Solicitar Servicio
    </button>
</td>
    </tr>`;
            }

            function renderRows(arr) {
                tbody.innerHTML = '';
                if (!arr || arr.length === 0) {
                    const tr = document.createElement('tr');
                    tr.id = 'filaVacia';
                    tr.innerHTML = `<td colspan="6">No hay resultados</td>`;
                    tbody.appendChild(tr);
                    return;
                }
                const html = arr.map((p, i) => normalizarFila(p, i)).join('');
                tbody.insertAdjacentHTML('beforeend', html);
            }

            async function aplicarFiltros() {
                const nombre = (filtroNombre.value || '').toLowerCase().trim();
                const cuit = (filtroCuit.value || '').replace(/\D/g, '');

                // Base de datos a filtrar:
                // - Si hay "nombre", buscar GLOBAL (todas las cooperativas del ingeniero).
                // - Si no hay "nombre", usar la cooperativa seleccionada.
                let base = [];
                if (nombre) {
                    base = await getTodosLosProductores();
                } else if (coopSeleccionada) {
                    base = productoresPorCoop[coopSeleccionada] || [];
                }

                // Filtrado
                const filtrados = base.filter(p => {
                    const n = String(p.nombre || '').toLowerCase();
                    const c = String(p.cuit || '');
                    const okNombre = !nombre || n.includes(nombre);
                    const okCuit = !cuit || c.includes(cuit);
                    return okNombre && okCuit;
                });

                renderRows(filtrados);
            }
        }

        // Modales simples: un botón → un modal (sin payload)
        async function openModalId(id, btn = null) {
            console.log('openModalId', id);

            // 🔹 Si viene del clic en un botón dentro de la tabla, obtengo el usuario_id_real
            if (btn && btn.closest('tr')) {
                const fila = btn.closest('tr');
                const usuarioIdReal = fila.dataset.usuarioIdReal || '';

                // 🔹 Armo el objeto "visible" ya mostrado en la fila (por coherencia)
                const datosVisibles = {
                    nombre: fila.querySelector('td:nth-child(2)')?.innerText.trim() || '',
                    cuit: fila.querySelector('td:nth-child(3)')?.innerText.trim() || '',
                    telefono: fila.querySelector('td:nth-child(4)')?.innerText.trim() || ''
                };

                // 🔹 Si tengo identificador, pido datos completos (usuarios + usuarios_info)
                if (usuarioIdReal) {
                    try {
                        const res = await fetch(`../../controllers/ing_ServiciosController.php?action=detalle_usuario&usuario_id_real=${encodeURIComponent(usuarioIdReal)}`, {
                            credentials: 'include'
                        });
                        const json = await res.json();

                        if (json?.ok) {
                            // ✅ Imprimo TODO con la leyenda solicitada
                            console.log('Datos usuario seleccionado en tabla:', {
                                visibles: datosVisibles,
                                usuarios: json.data.usuarios || null,
                                usuarios_info: json.data.usuarios_info || null
                            });
                        } else {
                            console.warn('No se pudo obtener detalle_usuario:', json?.error || 'Error desconocido');
                            console.log('Datos usuario seleccionado en tabla:', {
                                visibles: datosVisibles
                            });
                        }
                    } catch (e) {
                        console.error('Error detalle_usuario:', e);
                        console.log('Datos usuario seleccionado en tabla:', {
                            visibles: datosVisibles
                        });
                    }
                } else {
                    // Si no tengo id real, al menos imprimo lo visible
                    console.log('Datos usuario seleccionado en tabla:', {
                        visibles: datosVisibles
                    });
                }

                // Si querés acceder luego en otras partes:
                window.selectedProductor = {
                    id_real: usuarioIdReal,
                    ...datosVisibles
                };

                // Normalizo strings por seguridad
                if (window.selectedProductor) {
                    window.selectedProductor.id_real = String(window.selectedProductor.id_real || '');
                    window.selectedProductor.nombre = String(window.selectedProductor.nombre || '');
                }

            }

            const el = document.getElementById(id);
            if (el) {
                if (id === 'modalDrone') {
                    const ifr = document.getElementById('modalDroneIframe');
                    if (ifr) {
                        // ✅ 1) Pasamos datos por querystring (la vista ya lo soporta)
                        const pid = encodeURIComponent(window.selectedProductor?.id_real || '');
                        const pnom = encodeURIComponent(window.selectedProductor?.nombre || '');
                        const base = './ing_new_pulverizacion_view.php';
                        const url = pid && pnom ? `${base}?prod_id_real=${pid}&prod_nombre=${pnom}` : base;

                        // Forzamos recarga del iframe con los parámetros
                        ifr.src = url;

                        // ✅ 2) Además, al cargar, enviamos postMessage (fallback/robustez)
                        ifr.onload = () => {
                            try {
                                const payload = {
                                    type: 'sve:modal_prefill',
                                    payload: {
                                        id_real: window.selectedProductor?.id_real || '',
                                        nombre: window.selectedProductor?.nombre || ''
                                    }
                                };
                                ifr.contentWindow && ifr.contentWindow.postMessage(payload, '*');
                            } catch (e) {
                                console.warn('postMessage a iframe falló:', e);
                            }
                        };
                    }
                }

                el.classList.remove('hidden');
            }
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