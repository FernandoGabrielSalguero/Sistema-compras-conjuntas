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
    <link rel="stylesheet" href="https://framework.impulsagroup.com/assets/css/framework.css">
    <script src="https://framework.impulsagroup.com/assets/javascript/framework.js" defer></script>
</head>

<style>
    /* === Modal Drone: dimensiones cómodas para la vista embebida === */
    /* 💡 Ajuste de ancho del modal SOLO en escritorio.
   👉 CAMBIAR este valor para controlar el ancho: */
    #modalDrone .modal-content {
        /* === ANCHO ESCRITORIO DEL MODAL/IFRAME === */
        width: min(1280px, 95vw);
        /* ← ajustá este valor si querés más/menos ancho */
        max-width: none;
        /* anula límites del framework (p.ej. 520px) */
        max-height: 95vh;
        overflow: hidden;
        margin: 0 auto;
        /* centra horizontalmente */
    }

    /* Asegura centrado del modal en la pantalla (overlay) */
    #modalDrone {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
        /* margen de respiración en bordes */
    }

    /* 💡 En móviles, mantener el ancho actual */
    @media (max-width: 900px) {
        #modalDrone .modal-content {
            width: 95vw;
            /* móvil como está ahora */
            max-width: 95vw;
        }
    }

    #modalDrone .modal-body {
        margin-top: 8px;
        border-top: 1px solid #e5e7eb;
        padding-top: 8px;
    }

    #modalDroneIframe {
        background: #fff;
    }

    /* === Grids de tarjetas (cooperativas y productores) === */
    .cards-grid {
        display: grid;
        gap: 12px;
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    @media (max-width: 1200px) {
        .cards-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 900px) {
        .cards-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 600px) {
        .cards-grid {
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }
    }

    .coop-card,
    .producer-card {
        background: #fff;
        border: 1px solid #5b21b6;
        border-radius: 12px;
        padding: 12px;
        display: flex;
        flex-direction: column;
        gap: .5rem;
        transition: box-shadow .15s ease, transform .05s ease;
        cursor: pointer;
    }

    .coop-card:hover,
    .producer-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, .06);
    }

    .coop-card .title,
    .producer-card .title {
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    .producer-card .meta {
        font-size: .9rem;
        color: #475569;
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .producer-card .actions {
        margin-top: auto;
        display: flex;
        gap: .5rem;
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
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .35rem .7rem;
        border-radius: 9999px;
        border: 1px solid #e5e7eb;
        cursor: pointer;
        user-select: none;
        background: #fff;
    }

    .chip.active {
        border-color: #5b21b6;
        background: #f5f3ff;
    }

    .badge {
        display: inline-block;
        padding: .15rem .5rem;
        border-radius: 9999px;
        background: #5b21b6;
    }

    /* Tarjeta de filtros: misma estética que producer-card, sin puntero de link */
    .filter-card {
        cursor: default;
    }

    .filter-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, .06);
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
                    <p>Señor ingeniero, en esta página usted puede solicitar el servicio de pulverización con drone para los productores asociados a sus cooperativas asignadas. <br>
                        Debe seleccionar primero la cooperativa a la que pertenece el productor y luego hacer click en el botón "Solicitar Servicio" que se encuentra en la tarjeta del productor correspondiente.
                    </p>

                    <!-- 🔘 Tarjeta con los botones del tab -->
                    <div class="tabs">
                        <div class="tab-buttons" role="tablist" aria-label="Secciones de pulverización">
                            <!-- Botón Tutorial -->
                            <!-- <button type="button" id="btnIniciarTutorial" class="btn btn-info" aria-label="Iniciar tutorial" style="margin-left:auto">Tutorial</button> -->
                        </div>
                    </div>
                </div>

                <!-- 🧩 Cooperativas del ingeniero (tarjetas) -->
                <div class="card" id="card-cooperativas" aria-labelledby="coops-title">
                    <div style="display:flex; align-items:center; gap:.5rem;">
                        <h2 id="coops-title" style="margin:0;">Tus cooperativas</h2>
                        <span id="coopCountBadge" class="badge">0</span>
                    </div>
                    <!-- <p>Señor ingeniero, para solicitar el servicio de Drone, debe seleccionar la cooperativa a la que pertenece y luego hacer click en el boton "Solicitar servicio"</p> -->

                    <!-- Grid dinámico de cooperativas -->
                    <div id="gridCooperativas" class="cards-grid" role="list" aria-label="Cooperativas del ingeniero"></div>
                </div>

                <!-- 🔎 Productores (tarjetas + buscador por nombre) -->
                <div class="card hidden" id="card-productores-grid" aria-labelledby="prod-title">
                    <!-- Tarjeta de filtros/título (consistente con producer-card) -->
                    <div class="producer-card filter-card" role="region" aria-labelledby="prod-filter-title" style="padding:16px;">
                        <div style="display:flex; align-items:end; gap:.75rem; flex-wrap:wrap;">
                            <h3 id="prod-filter-title" style="margin:0;">Busca al productor por nombre</h3>
                            <div class="spacer" style="flex:1;"></div>
                            <div class="input-group" style="min-width:240px;">
                                <!-- <label for="buscadorNombre">Nombre</label> -->
                                <div class="input-icon input-icon-name">
                                    <input type="text" id="buscadorNombre" name="buscadorNombre" placeholder="Ej: Juan Pérez" aria-label="Buscar productor por nombre" />
                                </div>
                            </div>
                            <button class="btn btn-cancelar" type="button" id="btnVolverCoops" title="Buscar cooperativa">Volver a ver las cooperativas</button>
                        </div>

                    </div>

                    <br>
                    <h3 style="margin:0;">Listado de productores</h3>
                    
                    <!-- Grid dinámico de productores -->
                    <div id="gridProductores" class="cards-grid" role="list" aria-label="Productores de la cooperativa seleccionada" style="margin-top:12px;"></div>
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
            // Referencias UI
            const cardCoops = document.getElementById('card-cooperativas');
            const gridCoops = document.getElementById('gridCooperativas');
            const coopBadge = document.getElementById('coopCountBadge');

            const cardProds = document.getElementById('card-productores-grid');
            const gridProds = document.getElementById('gridProductores');
            const buscadorNombre = document.getElementById('buscadorNombre');
            const btnVolverCoops = document.getElementById('btnVolverCoops');

            // Estado
            let cooperativas = [];
            let coopSeleccionada = null; // id_real seleccionado
            const cacheProductoresPorCoop = {}; // { coop_id_real: [productores] }

            // Cargar cooperativas del ingeniero (tarjetas)
            try {
                const res = await fetch('../../controllers/ing_ServiciosController.php?action=cooperativas_del_ingeniero', {
                    credentials: 'include'
                });
                const json = await res.json();
                console.log('cooperativas_del_ingeniero →', json);

                if (json.ok && Array.isArray(json.data)) {
                    cooperativas = json.data;
                    coopBadge.textContent = String(cooperativas.length);
                    renderTarjetasCooperativas(cooperativas);
                    mostrarCooperativas();
                } else {
                    showAlert('error', json.error || 'No se pudieron cargar las cooperativas.');
                }
            } catch (e) {
                console.error('Error cargando cooperativas:', e);
                showAlert('error', 'Error cargando cooperativas.');
            }

            // Eventos
            ['input', 'keyup', 'change'].forEach(evt => {
                buscadorNombre.addEventListener(evt, aplicarFiltroNombre);
            });
            btnVolverCoops.addEventListener('click', () => {
                buscadorNombre.value = '';
                gridProds.innerHTML = '';
                coopSeleccionada = null;
                mostrarCooperativas();
            });

            // Render de UI
            function renderTarjetasCooperativas(arr) {
                gridCoops.innerHTML = '';
                if (!arr || arr.length === 0) {
                    gridCoops.innerHTML = `<div class="producer-card" role="listitem"><div>No hay cooperativas disponibles.</div></div>`;
                    return;
                }
                const html = arr.map(c => {
                    const id = String(c.cooperativa_id_real || c.id_real || c.id || '');
                    const nombre = String(c.nombre || 'Cooperativa');
                    const cuit = String(c.cuit || 'sin CUIT');
                    return `
                    <article class="coop-card" role="listitem" tabindex="0" data-id="${id}" aria-label="${nombre}">
                        <div class="title"><span class="material-icons">apartment</span> ${nombre}</div>
                        <div class="meta" style="color:#475569;">CUIT: ${cuit}</div>
                    </article>`;
                }).join('');
                gridCoops.insertAdjacentHTML('beforeend', html);

                // Click/Enter para seleccionar
                gridCoops.querySelectorAll('.coop-card').forEach(card => {
                    const select = async () => {
                        coopSeleccionada = card.dataset.id;
                        await cargarYMostrarProductores(coopSeleccionada);
                    };
                    card.addEventListener('click', select);
                    card.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            select();
                        }
                    });
                });
            }

            async function cargarYMostrarProductores(coopId) {
                if (!coopId) return;
                if (!cacheProductoresPorCoop[coopId]) {
                    try {
                        const res = await fetch(`../../controllers/ing_ServiciosController.php?action=productores_por_coop&cooperativa_id_real=${encodeURIComponent(coopId)}`, {
                            credentials: 'include'
                        });
                        const json = await res.json();
                        console.log('productores_por_coop →', json);
                        cacheProductoresPorCoop[coopId] = (json.ok && Array.isArray(json.data)) ? json.data : [];
                    } catch (e) {
                        console.error('Error cargando productores:', e);
                        cacheProductoresPorCoop[coopId] = [];
                        showAlert('error', 'Error cargando productores.');
                    }
                }
                buscadorNombre.value = '';
                renderTarjetasProductores(cacheProductoresPorCoop[coopId]);
                mostrarProductores();
            }

            function renderTarjetasProductores(arr) {
                gridProds.innerHTML = '';
                if (!arr || arr.length === 0) {
                    gridProds.innerHTML = `<div class="producer-card" role="listitem"><div>No hay productores para esta cooperativa.</div></div>`;
                    return;
                }
                const html = arr.map(p => {
                    const id = String(p.usuario_id_real || p.id_real || p.id || '');
                    const nombre = String(p.nombre || '-');
                    const cuit = String(p.cuit || '-');
                    const tel = String(p.telefono || '-');
                    return `
                    <article class="producer-card" role="listitem" tabindex="0" aria-label="${nombre}">
                        <div class="title"><span class="material-symbols-outlined">person</span> ${nombre}</div>
                        <div class="meta"><span>CUIT: ${cuit}</span><span>Tel: ${tel}</span></div>
                        <div class="actions">
                            <button class="btn btn-info" type="button" title="Solicitar Servicio"
                                onclick="seleccionarProductorYAbrirModal('${id}','${nombre.replace(/'/g, "\\'")}','${tel.replace(/'/g, "\\'")}','${cuit.replace(/'/g, "\\'")}')">
                                Solicitar Servicio
                            </button>
                        </div>
                    </article>`;
                }).join('');
                gridProds.insertAdjacentHTML('beforeend', html);
            }

            function aplicarFiltroNombre() {
                const nombre = (buscadorNombre.value || '').toLowerCase().trim();
                const base = cacheProductoresPorCoop[coopSeleccionada] || [];
                if (!nombre) {
                    renderTarjetasProductores(base);
                    return;
                }
                const filtrados = base.filter(p => String(p.nombre || '').toLowerCase().includes(nombre));
                renderTarjetasProductores(filtrados);
            }

            function mostrarCooperativas() {
                cardCoops.classList.remove('hidden');
                cardProds.classList.add('hidden');
            }

            function mostrarProductores() {
                cardCoops.classList.add('hidden');
                cardProds.classList.remove('hidden');
            }
        }

        // Helper para setear el productor seleccionado y abrir el modal
        function seleccionarProductorYAbrirModal(id_real, nombre, telefono, cuit) {
            window.selectedProductor = {
                id_real: String(id_real || ''),
                nombre: String(nombre || ''),
                telefono: String(telefono || ''),
                cuit: String(cuit || '')
            };
            openModalId('modalDrone'); // reutiliza el flujo existente (querystring + postMessage)
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
                                // Limitar origen si conocés el host (mejor seguridad). Aquí se mantiene '*'
                                if (ifr.contentWindow) {
                                    ifr.contentWindow.postMessage(payload, '*');
                                }
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