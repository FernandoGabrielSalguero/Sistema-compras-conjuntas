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

    <style>
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

        /* ===== Tabla Relevamiento (estructura estándar) ===== */
        .tabla-wrapper {
            margin-top: 1rem;
            overflow-x: auto;
        }

        .row-clickable {
            cursor: pointer;
        }

        .cell-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            align-items: center;
        }

        .table-tools {
            margin-top: 0.75rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: center;
            justify-content: space-between;
        }

        .table-tools input[type="search"] {
            width: min(460px, 100%);
            max-width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid rgba(15, 23, 42, 0.2);
            border-radius: 0.5rem;
        }

        .table-tools small {
            opacity: 0.75;
        }

        .icon-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.35rem 0.5rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(15, 23, 42, 0.12);
            background: #fff;
            cursor: pointer;
        }

        .icon-btn:hover {
            background: rgba(15, 23, 42, 0.04);
        }


        .card-error {
            border: 1px solid #ef4444;
        }

        /* ===== Modales Relevamiento ===== */
        .modal {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, 0.4);
            z-index: 999;
        }

        .modal.hidden {
            display: none;
        }

        .modal-content {
            background: #ffffff;
            border-radius: 0.75rem;
            padding: 1.5rem;
            max-width: 720px;
            width: 100%;
            margin: 1rem;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.25);
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-content h3 {
            margin-top: 0;
            margin-bottom: 0.75rem;
        }

        .modal-body {
            margin-top: 0.75rem;
        }

        .form-buttons {
            margin-top: 1.25rem;
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        .btn-aceptar,
        .btn-cancelar {
            min-width: 96px;
        }

        /* Campos avanzados (ocultos por defecto) */
        .relevamiento-advanced-hidden {
            display: none;
        }

        .form-switch {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .form-switch input[type="checkbox"] {
            cursor: pointer;
        }

        /* ===== Vista Modificar Productor ===== */
        .productor-edit-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.75rem;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .productor-edit-summary {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.5rem 1rem;
            color: rgba(15, 23, 42, 0.8);
        }

        .productor-assets-summary {
            margin-top: 0.85rem;
            border-top: 1px solid rgba(15, 23, 42, 0.1);
            padding-top: 0.85rem;
        }

        .summary-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem 1.25rem;
            margin-bottom: 0.75rem;
            font-weight: 600;
        }

        .summary-columns {
            display: grid;
            grid-template-columns: repeat(2, minmax(260px, 1fr));
            gap: 0.9rem;
        }

        .summary-list {
            display: grid;
            gap: 0.45rem;
        }

        .summary-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 0.5rem;
            padding: 0.45rem 0.55rem;
            background: #fff;
        }

        .summary-item-text {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .summary-empty {
            opacity: 0.8;
            font-size: 0.92rem;
        }

        #productor-modificar-view .table-form-intro {
            margin-bottom: 1rem;
        }

        #productor-modificar-view .input-group {
            margin-bottom: 0;
        }

        #productor-modificar-view .table-section-card {
            margin-bottom: 0.9rem;
        }

        #productor-modificar-view .table-section-head {
            margin-bottom: 0.8rem;
        }

        #productor-modificar-view .table-section-title {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 600;
        }

        #productor-modificar-view .table-section-subtitle {
            margin: 0.2rem 0 0;
            font-size: 0.85rem;
            opacity: 0.75;
        }

        #productor-modificar-view .table-section-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(220px, 1fr));
            gap: 0.75rem 1rem;
        }

        @media (max-width: 1200px) {
            #productor-modificar-view .table-section-grid {
                grid-template-columns: repeat(2, minmax(220px, 1fr));
            }
        }

        @media (max-width: 700px) {
            .summary-columns {
                grid-template-columns: 1fr;
            }

            #productor-modificar-view .table-section-grid {
                grid-template-columns: 1fr;
            }
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

                <!-- Título de sección -->
                <div class="sidebar-section-title">Menú</div>

                <!-- Grupo superior -->
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

                <!-- Título de sección -->
                <div class="sidebar-section-title">Drones</div>

                <!-- Lista directa de páginas de Drones (sin acordeón) -->
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

                    <!-- Agregá más ítems aquí cuando existan nuevas hojas de Drones -->
                </ul>

                <!-- Título de sección -->
                <div class="sidebar-section-title">Relevamiento</div>

                <!-- Lista directa de páginas de Relevamiento -->
                <ul class="submenu-root">
                    <li>
                        <a href="ing_relevamiento.php">
                            <span class="material-symbols-outlined" style="color:#5b21b6">map</span>
                            <span class="link-text">Relevamiento</span>
                        </a>
                    </li>
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

                <!-- Encabezado del módulo de relevamiento -->
                <div class="card">
                    <h2 id="cards-title">Relevamiento</h2>
                    <p>Seleccioná una cooperativa para ver sus productores asociados.</p>
                </div>

                <!-- Contenedor donde se renderizan dinámicamente las tarjetas -->
                <div id="cards-container"></div>

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

    <script>
        const API_RELEVAMIENTO = "../../controllers/ing_relevamientoController.php";
        const RELEVAMIENTO_PARTIAL_BASE = "../partials/relevamiento";
        const MODAL_IDS = {
            familia: 'modal-familia',
            produccion: 'modal-produccion',
            cuarteles: 'modal-cuarteles'
        };

        // Mapa global para recuperar el productor por id_real
        const PRODUCTORES_MAP = {};
        let PRODUCTORES_LIST = [];
        let currentProductor = null;
        let currentCoop = null;

        console.log('[Relevamiento] Script cargado');

        function setCardsTitle(text) {
            const titleEl = document.getElementById('cards-title');
            if (titleEl) {
                titleEl.textContent = text;
            }
        }

        function escapeHtml(str) {
            return String(str ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function renderCooperativasTable(coops) {
            const container = document.getElementById('cards-container');
            if (!container) return;

            const rows = coops.map((c, idx) => {
                const nombre = escapeHtml(c.nombre);
                const idReal = escapeHtml(c.id_real);
                const cuit = escapeHtml(c.cuit ?? 'Sin CUIT');

                return `
                    <tr class="row-clickable" data-coop-id-real="${idReal}">
                        <td>${idx + 1}</td>
                        <td>${nombre}</td>
                        <td>${idReal}</td>
                        <td>${cuit}</td>
                        <td>
                            <button class="btn btn-info" data-action="ver-productores" data-coop-id-real="${idReal}">Ver productores</button>
                        </td>
                    </tr>
                `;
            }).join('');

            container.innerHTML = `
                <div class="card tabla-card">
                    <h2>Cooperativas</h2>
                    <div class="tabla-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nombre</th>
                                    <th>ID real</th>
                                    <th>CUIT</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${rows}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;

            const mapCoops = {};
            coops.forEach(c => {
                mapCoops[String(c.id_real)] = c;
            });

            container.querySelectorAll('[data-action="ver-productores"]').forEach((btn) => {
                btn.addEventListener('click', (ev) => {
                    ev.stopPropagation();
                    const id = btn.getAttribute('data-coop-id-real');
                    const coop = mapCoops[String(id)];
                    if (coop) cargarProductores(coop);
                });
            });

            container.querySelectorAll('tr.row-clickable').forEach((tr) => {
                tr.addEventListener('click', () => {
                    const id = tr.getAttribute('data-coop-id-real');
                    const coop = mapCoops[String(id)];
                    if (coop) cargarProductores(coop);
                });
            });
        }

        function renderProductoresTable(productores) {
            const container = document.getElementById('cards-container');
            if (!container) return;

            PRODUCTORES_LIST = Array.isArray(productores) ? productores : [];
            Object.keys(PRODUCTORES_MAP).forEach((k) => delete PRODUCTORES_MAP[k]);
            PRODUCTORES_LIST.forEach((p) => {
                const idReal = String(p?.id_real ?? '').trim();
                if (idReal !== '') PRODUCTORES_MAP[idReal] = p;
            });

            container.innerHTML = `
                <div class="card tabla-card">
                    <h2>Productores</h2>
                    <div class="table-tools">
                        <input type="search" id="productores-search-input" placeholder="Buscar por CUIT, ID real o nombre" autocomplete="off">
                        <small id="productores-search-count"></small>
                    </div>
                    <div class="tabla-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nombre</th>
                                    <th>ID real</th>
                                    <th>CUIT</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="productores-tbody"></tbody>
                        </table>
                    </div>
                </div>
            `;

            const tbody = container.querySelector('#productores-tbody');
            const searchInput = container.querySelector('#productores-search-input');
            const counter = container.querySelector('#productores-search-count');

            const normalize = (value) =>
                String(value ?? '')
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .trim();

            const buildRows = (list) => {
                const rows = list.map((p, idx) => {
                    const nombreRaw = String(p?.nombre ?? '').trim();
                    const idRealRaw = String(p?.id_real ?? '').trim();
                    const cuitRaw = String(p?.cuit ?? '').trim();
                    const idRealJs = idRealRaw.replaceAll('\\', '\\\\').replaceAll("'", "\\'");

                    const nombre = escapeHtml(nombreRaw || 'Sin nombre');
                    const idReal = escapeHtml(idRealRaw || 'Sin ID');
                    const cuit = escapeHtml(cuitRaw || 'Sin CUIT');

                    return `
                        <tr>
                            <td>${idx + 1}</td>
                            <td>${nombre}</td>
                            <td>${idReal}</td>
                            <td>${cuit}</td>
                            <td class="cell-actions">
                                <button class="btn btn-info" onclick="abrirModificarProductor('${idRealJs}')">Modificar datos</button>
                                <button class="icon-btn" title="Imprimir tablas del productor en consola" onclick="relevamientoLogProductorFull('${idRealJs}')">
                                    <span class="material-symbols-outlined">code</span>
                                </button>
                            </td>
                        </tr>
                    `;
                }).join('');

                if (tbody) {
                    tbody.innerHTML = rows || `<tr><td colspan="5">Sin resultados para la búsqueda.</td></tr>`;
                }
                if (counter) {
                    counter.textContent = `${list.length} de ${PRODUCTORES_LIST.length} productores`;
                }
            };

            const applyFilter = () => {
                const query = normalize(searchInput?.value ?? '');
                if (!query) {
                    buildRows(PRODUCTORES_LIST);
                    return;
                }

                const filtered = PRODUCTORES_LIST.filter((p) => {
                    const nombre = normalize(p?.nombre);
                    const idReal = normalize(p?.id_real);
                    const cuit = normalize(p?.cuit);
                    return nombre.includes(query) || idReal.includes(query) || cuit.includes(query);
                });
                buildRows(filtered);
            };

            if (searchInput) {
                searchInput.addEventListener('input', applyFilter);
            }
            buildRows(PRODUCTORES_LIST);
        }

        function initAdvancedToggleInScope(scopeEl, roleName) {
            if (!scopeEl) return;
            const toggle = scopeEl.querySelector(`[data-role="${roleName}"]`);
            const advancedFields = scopeEl.querySelectorAll('[data-advanced="1"]');

            if (!toggle || !advancedFields.length) return;

            const applyVisibility = () => {
                advancedFields.forEach((el) => {
                    if (toggle.checked) {
                        el.classList.remove('relevamiento-advanced-hidden');
                    } else {
                        el.classList.add('relevamiento-advanced-hidden');
                    }
                });
            };

            toggle.addEventListener('change', applyVisibility);
            toggle.checked = false;
            applyVisibility();
        }

        function initGlobalAdvancedToggle(scopeEl, toggleSelector) {
            if (!scopeEl) return;
            const toggle = document.querySelector(toggleSelector);
            const advancedFields = scopeEl.querySelectorAll('[data-advanced="1"]');
            if (!toggle || !advancedFields.length) return;

            const applyVisibility = () => {
                advancedFields.forEach((el) => {
                    if (toggle.checked) {
                        el.classList.remove('relevamiento-advanced-hidden');
                    } else {
                        el.classList.add('relevamiento-advanced-hidden');
                    }
                });
            };

            toggle.addEventListener('change', applyVisibility);
            toggle.checked = false;
            applyVisibility();
        }

        function parseTableMetaFromTitle(titleRaw) {
            const title = String(titleRaw ?? '').trim();
            const m = title.match(/^(.*)\(([^)]+)\)\s*$/);
            if (!m) {
                return {
                    label: title || 'Tabla',
                    tableName: ''
                };
            }
            return {
                label: String(m[1] ?? '').trim(),
                tableName: String(m[2] ?? '').trim()
            };
        }

        function createTableSectionCard(titleRaw, subtitle = '') {
            const meta = parseTableMetaFromTitle(titleRaw);

            const card = document.createElement('div');
            card.className = 'card table-section-card';

            const head = document.createElement('div');
            head.className = 'table-section-head';

            const h = document.createElement('h4');
            h.className = 'table-section-title';
            h.textContent = meta.label || 'Tabla';
            head.appendChild(h);

            const small = document.createElement('p');
            small.className = 'table-section-subtitle';
            small.textContent = meta.tableName ?
                `${meta.tableName}${subtitle ? ` - ${subtitle}` : ''}` :
                subtitle;
            if (small.textContent.trim() !== '') {
                head.appendChild(small);
            }

            const grid = document.createElement('div');
            grid.className = 'table-section-grid';

            card.appendChild(head);
            card.appendChild(grid);

            return {
                card,
                grid
            };
        }

        function normalizeFragment(html) {
            const tpl = document.createElement('template');
            tpl.innerHTML = String(html ?? '');
            return tpl.content;
        }

        function toElementNodes(nodes) {
            return (nodes || []).filter((n) => n && n.nodeType === Node.ELEMENT_NODE);
        }

        function buildFamiliaFormCards(htmlFamilia) {
            const frag = normalizeFragment(htmlFamilia);
            const sourceForm = frag.querySelector('#familia-form');
            if (!sourceForm) return null;

            const form = document.createElement('form');
            form.id = 'familia-form';

            const children = toElementNodes(Array.from(sourceForm.childNodes));
            const introNodes = [];
            const sections = [];
            let current = null;

            children.forEach((node) => {
                if (node.matches('h4.relevamiento-section-title')) {
                    current = {
                        title: node.textContent || 'Tabla',
                        nodes: []
                    };
                    sections.push(current);
                    return;
                }

                if (node.matches('hr') || node.matches('.form-switch')) {
                    return;
                }

                if (current) {
                    current.nodes.push(node);
                } else {
                    introNodes.push(node);
                }
            });

            if (introNodes.length) {
                const intro = document.createElement('div');
                intro.className = 'table-form-intro';
                introNodes.forEach((n) => intro.appendChild(n));
                form.appendChild(intro);
            }

            sections.forEach((sec) => {
                const {
                    card,
                    grid
                } = createTableSectionCard(sec.title);
                sec.nodes.forEach((n) => grid.appendChild(n));
                form.appendChild(card);
            });

            return form;
        }

        function buildProduccionFormCards(htmlProduccion) {
            const frag = normalizeFragment(htmlProduccion);
            const sourceForm = frag.querySelector('#produccion-form');
            if (!sourceForm) return null;

            const form = document.createElement('form');
            form.id = 'produccion-form';

            const hiddenInputs = Array.from(sourceForm.querySelectorAll(':scope > input[type="hidden"]'));
            hiddenInputs.forEach((hidden) => form.appendChild(hidden));

            const intro = document.createElement('div');
            intro.className = 'table-form-intro';
            Array.from(sourceForm.children).forEach((node) => {
                if (node.matches('input[type="hidden"]') || node.matches('.relevamiento-finca-block') || node.matches('.form-switch')) return;
                intro.appendChild(node);
            });
            if (intro.children.length > 0) {
                form.appendChild(intro);
            }

            const fincaBlocks = Array.from(sourceForm.querySelectorAll('.relevamiento-finca-block'));
            fincaBlocks.forEach((block) => {
                const fincaHeader = (block.querySelector('.relevamiento-finca-header')?.textContent || '').trim();
                const fincaSub = (block.querySelector('.relevamiento-finca-subtitle')?.textContent || '').trim();
                const fincaSubtitle = [fincaHeader, fincaSub].filter(Boolean).join(' - ');

                const sectionNodes = toElementNodes(Array.from(block.childNodes));
                let current = null;
                const sections = [];

                sectionNodes.forEach((node) => {
                    if (
                        node.matches('input[type="hidden"]') ||
                        node.matches('.relevamiento-finca-header') ||
                        node.matches('.relevamiento-finca-subtitle') ||
                        node.matches('hr')
                    ) {
                        if (node.matches('input[type="hidden"]')) {
                            form.appendChild(node);
                        }
                        return;
                    }

                    if (node.matches('h4.relevamiento-section-title')) {
                        current = {
                            title: node.textContent || 'Tabla',
                            nodes: []
                        };
                        sections.push(current);
                        return;
                    }

                    if (current) {
                        current.nodes.push(node);
                    }
                });

                sections.forEach((sec) => {
                    const {
                        card,
                        grid
                    } = createTableSectionCard(sec.title, fincaSubtitle);
                    sec.nodes.forEach((n) => grid.appendChild(n));
                    form.appendChild(card);
                });
            });

            return form;
        }

        function buildCuartelesCards(htmlCuarteles) {
            const frag = normalizeFragment(htmlCuarteles);
            const wrapper = document.createElement('div');
            wrapper.id = 'cuarteles-form';

            const card = document.createElement('div');
            card.className = 'card table-section-card';

            const title = document.createElement('h4');
            title.className = 'table-section-title';
            title.textContent = 'Cuarteles';

            const body = document.createElement('div');
            body.className = 'table-section-grid';

            Array.from(frag.childNodes).forEach((node) => {
                if (node.nodeType === Node.ELEMENT_NODE) {
                    body.appendChild(node);
                }
            });

            card.appendChild(title);
            card.appendChild(body);
            wrapper.appendChild(card);

            return wrapper;
        }

        async function fetchPartialHtml(controllerFile, productorIdReal) {
            const params = new URLSearchParams({
                productor_id_real: productorIdReal
            });
            const resp = await fetch(`${RELEVAMIENTO_PARTIAL_BASE}/${controllerFile}?${params.toString()}`, {
                credentials: 'same-origin'
            });
            if (!resp.ok) {
                throw new Error(`${controllerFile}: Error HTTP ${resp.status}`);
            }
            return resp.text();
        }

        async function fetchResumenActivosProductor(productorIdReal) {
            const params = new URLSearchParams({
                action: 'resumen_activos_productor',
                productor_id_real: productorIdReal
            });

            const resp = await fetch(`${API_RELEVAMIENTO}?${params.toString()}`, {
                credentials: 'same-origin'
            });
            const data = await resp.json();
            if (!resp.ok || !data.ok) {
                throw new Error(data.error || `Error al cargar resumen del productor (${resp.status})`);
            }
            return data.data || null;
        }

        async function eliminarFincaProductor(productorIdReal, fincaId) {
            const body = new URLSearchParams({
                action: 'eliminar_finca_productor',
                productor_id_real: productorIdReal,
                finca_id: String(fincaId)
            });
            const resp = await fetch(API_RELEVAMIENTO, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                },
                body: body.toString()
            });
            const data = await resp.json();
            if (!resp.ok || !data.ok) {
                throw new Error(data.error || 'No se pudo eliminar la finca');
            }
        }

        async function eliminarCuartelProductor(productorIdReal, cuartelId) {
            const body = new URLSearchParams({
                action: 'eliminar_cuartel_productor',
                productor_id_real: productorIdReal,
                cuartel_id: String(cuartelId)
            });
            const resp = await fetch(API_RELEVAMIENTO, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                },
                body: body.toString()
            });
            const data = await resp.json();
            if (!resp.ok || !data.ok) {
                throw new Error(data.error || 'No se pudo eliminar el cuartel');
            }
        }

        function renderResumenActivosProductor(slot, productorIdReal, resumen) {
            if (!slot) return;

            const fincas = Array.isArray(resumen?.fincas) ? resumen.fincas : [];
            const cuarteles = Array.isArray(resumen?.cuarteles) ? resumen.cuarteles : [];

            const renderFincaItems = fincas.length ?
                fincas.map((f) => {
                    const id = Number(f.id || 0);
                    const code = escapeHtml(f.codigo_finca || `ID ${id}`);
                    const name = escapeHtml(f.nombre_finca || 'Sin nombre');
                    return `
                        <div class="summary-item">
                            <span class="summary-item-text">#${id} - ${code} - ${name}</span>
                            <button class="btn btn-cancelar" onclick="confirmarEliminarFinca('${String(productorIdReal).replaceAll('\\', '\\\\').replaceAll("'", "\\'")}', ${id})">Eliminar</button>
                        </div>
                    `;
                }).join('') :
                '<p class="summary-empty">Sin fincas asociadas.</p>';

            const renderCuartelItems = cuarteles.length ?
                cuarteles.map((c) => {
                    const id = Number(c.id || 0);
                    const code = escapeHtml(c.codigo_cuartel || `ID ${id}`);
                    const fincaCode = escapeHtml(c.codigo_finca || 'Sin finca');
                    return `
                        <div class="summary-item">
                            <span class="summary-item-text">#${id} - Cuartel ${code} (Finca ${fincaCode})</span>
                            <button class="btn btn-cancelar" onclick="confirmarEliminarCuartel('${String(productorIdReal).replaceAll('\\', '\\\\').replaceAll("'", "\\'")}', ${id})">Eliminar</button>
                        </div>
                    `;
                }).join('') :
                '<p class="summary-empty">Sin cuarteles asociados.</p>';

            slot.innerHTML = `
                <div class="summary-meta">
                    <span>Fincas: ${fincas.length}</span>
                    <span>Cuarteles: ${cuarteles.length}</span>
                </div>
                <div class="summary-columns">
                    <div>
                        <strong>Fincas</strong>
                        <div class="summary-list">${renderFincaItems}</div>
                    </div>
                    <div>
                        <strong>Cuarteles</strong>
                        <div class="summary-list">${renderCuartelItems}</div>
                    </div>
                </div>
            `;
        }

        async function cargarResumenActivosProductor(productorIdReal) {
            const slot = document.querySelector('#productor-assets-summary');
            if (!slot) return;

            slot.innerHTML = '<p>Cargando resumen de fincas y cuarteles...</p>';
            try {
                const resumen = await fetchResumenActivosProductor(productorIdReal);
                renderResumenActivosProductor(slot, productorIdReal, resumen);
            } catch (e) {
                console.error('[Relevamiento] Error resumen activos:', e);
                slot.innerHTML = `<p class="text-danger">Error al cargar resumen: ${escapeHtml(e.message)}</p>`;
            }
        }

        async function confirmarEliminarFinca(productorIdReal, fincaId) {
            if (!confirm(`Se va a eliminar la finca ID ${fincaId} y sus registros asociados. ¿Continuar?`)) {
                return;
            }
            try {
                await eliminarFincaProductor(productorIdReal, fincaId);
                if (typeof showToastBoton === 'function') {
                    showToastBoton('success', `Finca ${fincaId} eliminada correctamente`);
                }
                await abrirModificarProductor(productorIdReal);
            } catch (e) {
                console.error('[Relevamiento] Error al eliminar finca:', e);
                if (typeof showToastBoton === 'function') {
                    showToastBoton('error', `Error al eliminar finca: ${e.message}`);
                }
            }
        }

        async function confirmarEliminarCuartel(productorIdReal, cuartelId) {
            if (!confirm(`Se va a eliminar el cuartel ID ${cuartelId}. ¿Continuar?`)) {
                return;
            }
            try {
                await eliminarCuartelProductor(productorIdReal, cuartelId);
                if (typeof showToastBoton === 'function') {
                    showToastBoton('success', `Cuartel ${cuartelId} eliminado correctamente`);
                }
                await abrirModificarProductor(productorIdReal);
            } catch (e) {
                console.error('[Relevamiento] Error al eliminar cuartel:', e);
                if (typeof showToastBoton === 'function') {
                    showToastBoton('error', `Error al eliminar cuartel: ${e.message}`);
                }
            }
        }

        async function guardarFormularioParcial(formId, controllerFile, productorIdReal, mensajeExito) {
            const form = document.querySelector(`#productor-modificar-view #${formId}`);
            if (!form) {
                throw new Error(`No se encontró el formulario ${formId}`);
            }

            const formData = new FormData(form);
            formData.append('productor_id_real', productorIdReal);

            const resp = await fetch(`${RELEVAMIENTO_PARTIAL_BASE}/${controllerFile}`, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            });
            const data = await resp.json();
            if (!data.ok) {
                throw new Error(data.error || `Error al guardar ${formId}`);
            }

            if (typeof showToastBoton === 'function') {
                showToastBoton('success', mensajeExito);
            }
        }

        async function abrirModificarProductor(productorIdReal) {
            const container = document.getElementById('cards-container');
            if (!container) return;

            const productor = PRODUCTORES_MAP[productorIdReal] || null;
            currentProductor = productor;
            const idRealJs = String(productorIdReal ?? '').replaceAll('\\', '\\\\').replaceAll("'", "\\'");

            const nombre = escapeHtml(productor?.nombre ?? 'Sin nombre');
            const cuit = escapeHtml(productor?.cuit ?? 'Sin CUIT');
            const idReal = escapeHtml(productorIdReal);

            setCardsTitle(`Modificar productor ${idReal}`);
            container.innerHTML = `
                <div class="card">
                    <div class="productor-edit-toolbar">
                        <button class="btn btn-cancelar" onclick="volverAProductores()">Volver a productores</button>
                        <div class="productor-edit-summary">
                            <strong>${nombre}</strong>
                            <span>ID: ${idReal}</span>
                            <span id="productor-summary-cuit">CUIT: ${cuit}</span>
                        </div>
                        <div class="form-buttons" style="margin-top:0;">
                            <button class="btn btn-aceptar" onclick="guardarTodoDesdeVista('${idRealJs}')">Guardar cambios</button>
                        </div>
                    </div>
                    <div class="form-switch" style="margin-bottom:0;">
                        <label>
                            <input type="checkbox" id="global-advanced-toggle">
                            Mostrar campos avanzados
                        </label>
                    </div>
                    <div class="productor-assets-summary" id="productor-assets-summary">
                        <p>Cargando resumen de fincas y cuarteles...</p>
                    </div>
                </div>
                <div id="productor-modificar-view"></div>
            `;

            const slotView = container.querySelector('#productor-modificar-view');

            try {
                const [htmlFamilia, htmlProduccion, htmlCuarteles] = await Promise.all([
                    fetchPartialHtml('relevamiento_familia_controller.php', productorIdReal),
                    fetchPartialHtml('relevamiento_produccion_controller.php', productorIdReal),
                    fetchPartialHtml('relevamiento_cuarteles_controller.php', productorIdReal)
                ]);

                const familiaForm = buildFamiliaFormCards(htmlFamilia);
                const produccionForm = buildProduccionFormCards(htmlProduccion);
                const cuartelesForm = buildCuartelesCards(htmlCuarteles);

                if (slotView) {
                    slotView.innerHTML = '';
                    if (familiaForm) slotView.appendChild(familiaForm);
                    if (produccionForm) slotView.appendChild(produccionForm);
                    if (cuartelesForm) slotView.appendChild(cuartelesForm);
                }

                initGlobalAdvancedToggle(slotView, '#global-advanced-toggle');

                const cuitInput = slotView?.querySelector('#familia-form input[name="cuit"]');
                const cuitSpan = container.querySelector('#productor-summary-cuit');
                const cuitValue = String(cuitInput?.value ?? '').trim();
                if (cuitSpan && cuitValue !== '') {
                    cuitSpan.textContent = `CUIT: ${cuitValue}`;
                }

                await cargarResumenActivosProductor(productorIdReal);
            } catch (e) {
                console.error('[Relevamiento] Error al cargar vista de modificación:', e);
                container.innerHTML = `<div class="card card-error">Error al cargar formulario del productor: ${escapeHtml(e.message)}</div>`;
            }
        }

        function volverAProductores() {
            if (currentCoop) {
                cargarProductores(currentCoop);
                return;
            }
            cargarCooperativas();
        }

        async function guardarFamiliaDesdeVista(productorIdReal) {
            try {
                await guardarFormularioParcial(
                    'familia-form',
                    'relevamiento_familia_controller.php',
                    productorIdReal,
                    'Datos de familia guardados correctamente'
                );
            } catch (e) {
                console.error('[Relevamiento] Error al guardar familia:', e);
                if (typeof showToastBoton === 'function') {
                    showToastBoton('error', `Error al guardar familia: ${e.message}`);
                }
            }
        }

        async function guardarProduccionDesdeVista(productorIdReal) {
            try {
                await guardarFormularioParcial(
                    'produccion-form',
                    'relevamiento_produccion_controller.php',
                    productorIdReal,
                    'Datos de producción guardados correctamente'
                );
            } catch (e) {
                console.error('[Relevamiento] Error al guardar producción:', e);
                if (typeof showToastBoton === 'function') {
                    showToastBoton('error', `Error al guardar producción: ${e.message}`);
                }
            }
        }

        async function guardarTodoDesdeVista(productorIdReal) {
            try {
                await guardarFormularioParcial(
                    'familia-form',
                    'relevamiento_familia_controller.php',
                    productorIdReal,
                    'Tablas personales guardadas correctamente'
                );

                await guardarFormularioParcial(
                    'produccion-form',
                    'relevamiento_produccion_controller.php',
                    productorIdReal,
                    'Cambios guardados correctamente'
                );

                if (typeof showToastBoton === 'function') {
                    showToastBoton('success', 'Todos los cambios fueron guardados');
                }
            } catch (e) {
                console.error('[Relevamiento] Error al guardar todo:', e);
                if (typeof showToastBoton === 'function') {
                    showToastBoton('error', `Error al guardar cambios: ${e.message}`);
                }
            }
        }

        function getFormValuesAsObject(doc, formSelector) {
            const form = doc.querySelector(formSelector);
            if (!form) return null;

            const out = {};
            const elements = Array.from(form.querySelectorAll('input, select, textarea'));

            elements.forEach((el) => {
                const name = el.getAttribute('name');
                if (!name) return;

                const tag = (el.tagName || '').toLowerCase();
                const type = (el.getAttribute('type') || '').toLowerCase();

                if (type === 'password' || type === 'file') return;

                if (type === 'checkbox') {
                    if (!out[name]) out[name] = [];
                    if (el.checked) out[name].push(el.value ?? 'on');
                    if (out[name].length === 0) out[name] = [];
                    return;
                }

                if (type === 'radio') {
                    if (el.checked) out[name] = el.value;
                    if (out[name] === undefined) out[name] = null;
                    return;
                }

                if (tag === 'select' && el.multiple) {
                    out[name] = Array.from(el.selectedOptions).map(o => o.value);
                    return;
                }

                out[name] = el.value;
            });

            return out;
        }

        async function fetchFamiliaData(productorIdReal) {
            const params = new URLSearchParams({
                productor_id_real: productorIdReal
            });
            const resp = await fetch(`${RELEVAMIENTO_PARTIAL_BASE}/relevamiento_familia_controller.php?${params.toString()}`, {
                credentials: 'same-origin'
            });
            if (!resp.ok) {
                throw new Error(`Familia: Error HTTP ${resp.status}`);
            }
            const html = await resp.text();
            const doc = new DOMParser().parseFromString(html, 'text/html');
            return getFormValuesAsObject(doc, '#familia-form');
        }

        async function fetchProduccionData(productorIdReal) {
            const params = new URLSearchParams({
                productor_id_real: productorIdReal
            });
            const resp = await fetch(`${RELEVAMIENTO_PARTIAL_BASE}/relevamiento_produccion_controller.php?${params.toString()}`, {
                credentials: 'same-origin'
            });
            if (!resp.ok) {
                throw new Error(`Producción: Error HTTP ${resp.status}`);
            }
            const html = await resp.text();
            const doc = new DOMParser().parseFromString(html, 'text/html');
            return getFormValuesAsObject(doc, '#produccion-form');
        }

        async function fetchTablasDumpData(productorIdReal) {
            const params = new URLSearchParams({
                action: 'dump_tablas_productor',
                productor_id_real: productorIdReal
            });

            const resp = await fetch(`${API_RELEVAMIENTO}?${params.toString()}`, {
                credentials: 'same-origin'
            });

            const data = await resp.json();
            if (!data.ok) {
                throw new Error(data.error || 'Error al obtener tablas del productor');
            }

            return data.data || {};
        }

        function parseBracketKey(key) {
            // Soporta: fincas[0][campo] / fincas[campo] / fincas[0]
            const re = /^([^\[]+)\[(.*?)\](?:\[(.*?)\])?$/;
            const m = String(key).match(re);
            if (!m) return null;

            const table = m[1];
            const part2 = m[2];
            const part3 = m[3];

            const isIndex = part2 !== '' && !Number.isNaN(Number(part2));
            const index = isIndex ? Number(part2) : null;

            if (index !== null && part3) {
                return {
                    table,
                    index,
                    field: part3
                };
            }

            if (index !== null && !part3) {
                return {
                    table,
                    index,
                    field: null
                };
            }

            // Caso fincas[campo]
            return {
                table,
                index: null,
                field: part2 || null
            };
        }

        function groupByTables(obj) {
            // Convierte un objeto plano (con keys bracket) en estructura por "tablas"
            const grouped = {
                __flat: {}
            };

            Object.entries(obj || {}).forEach(([k, v]) => {
                const parsed = parseBracketKey(k);
                if (!parsed) {
                    grouped.__flat[k] = v;
                    return;
                }

                const {
                    table,
                    index,
                    field
                } = parsed;

                if (!grouped[table]) grouped[table] = {
                    rows: {},
                    flat: {}
                };

                if (index !== null) {
                    if (!grouped[table].rows[index]) grouped[table].rows[index] = {};
                    if (field) grouped[table].rows[index][field] = v;
                    else grouped[table].rows[index]['__value'] = v;
                } else {
                    if (field) grouped[table].flat[field] = v;
                    else grouped[table].flat['__value'] = v;
                }
            });

            if (Object.keys(grouped.__flat).length === 0) delete grouped.__flat;
            return grouped;
        }


        function toKeyValueRows(obj) {
            const rows = [];
            Object.entries(obj || {}).forEach(([k, v]) => {
                rows.push({
                    columna_sql: k,
                    valor: v
                });
            });
            return rows;
        }

        function logSectionAsTable(sectionName, data) {
            console.log(`Tabla: ${sectionName}`);

            if (!data) {
                console.table([{
                    columna_sql: '(vacío)',
                    valor: ''
                }]);
                return;
            }

            if (data.__error) {
                console.table([{
                    columna_sql: '__error',
                    valor: data.__error
                }]);
                return;
            }

            // Caso 1: objeto simple => columna/valor
            if (!Array.isArray(data)) {
                console.table(toKeyValueRows(data));
                return;
            }

            // Caso 2: array de filas => una tabla columna/valor por fila
            if (data.length === 0) {
                console.table([{
                    columna_sql: '(sin filas)',
                    valor: ''
                }]);
                return;
            }

            data.forEach((rowObj, i) => {
                const idx = rowObj && rowObj.__index !== undefined ? rowObj.__index : i;
                console.log(`Tabla: ${sectionName}[${idx}]`);
                console.table(toKeyValueRows(rowObj || {}));
            });
        }

        function buildRowMap(rows, keyField) {
            const map = new Map();
            (Array.isArray(rows) ? rows : []).forEach((row) => {
                const key = row?.[keyField];
                if (key === null || key === undefined || key === '') return;
                map.set(String(key), row);
            });
            return map;
        }

        function buildRowListMap(rows, keyField) {
            const map = new Map();
            (Array.isArray(rows) ? rows : []).forEach((row) => {
                const key = row?.[keyField];
                if (key === null || key === undefined || key === '') return;
                const normalizedKey = String(key);
                if (!map.has(normalizedKey)) map.set(normalizedKey, []);
                map.get(normalizedKey).push(row);
            });
            return map;
        }

        function logSingleRowTable(title, row) {
            console.log(title);
            console.table(toKeyValueRows(row || {}));
        }

        function logMultiRowTable(title, rows) {
            const safeRows = Array.isArray(rows) ? rows : [];
            console.log(`${title} (${safeRows.length})`);
            if (!safeRows.length) {
                console.table([{
                    columna_sql: '(sin filas)',
                    valor: ''
                }]);
            } else {
                safeRows.forEach((rowObj, idx) => {
                    console.log(`${title}[${idx}]`);
                    console.table(toKeyValueRows(rowObj || {}));
                });
            }
        }

        function logCompactTable(title, rows) {
            const safeRows = Array.isArray(rows) ? rows : [];
            console.log(`${title} (${safeRows.length})`);
            if (!safeRows.length) {
                console.table([{
                    estado: 'sin filas'
                }]);
            } else {
                console.table(safeRows);
            }
        }

        function buildProductorSummary(usuario, usuariosInfo, relProductorCoop, prodFincas, prodCuartel, cuartelesSinFinca) {
            const info = usuariosInfo[0] || {};
            return [{
                id_real: usuario?.id_real ?? '',
                razon_social: usuario?.razon_social ?? '',
                nombre: info?.nombre ?? '',
                cuit: usuario?.cuit ?? '',
                estado_asociacion_cooperativa: usuario?.estado_asociacion_cooperativa ?? '',
                cooperativas_relacionadas: relProductorCoop.length,
                fincas: prodFincas.length,
                cuarteles: prodCuartel.length,
                cuarteles_sin_finca: cuartelesSinFinca.length,
            }];
        }

        function buildFincasSummaryRows(prodFincas, direccionesByFincaId, cuartelesByFincaId) {
            return prodFincas.map((finca) => {
                const fincaId = String(finca?.id ?? '');
                const direccion = direccionesByFincaId.get(fincaId) || {};
                const cuarteles = cuartelesByFincaId.get(fincaId) || [];
                return {
                    finca_id: finca?.id ?? '',
                    codigo_finca: finca?.codigo_finca ?? '',
                    nombre_finca: finca?.nombre_finca ?? '',
                    departamento: direccion?.departamento ?? '',
                    localidad: direccion?.localidad ?? '',
                    calle: direccion?.calle ?? '',
                    numero: direccion?.numero ?? '',
                    cuarteles: cuarteles.length,
                };
            });
        }

        function buildCuartelesSummaryRows(prodCuartel) {
            return prodCuartel.map((cuartel) => ({
                cuartel_id: cuartel?.id ?? '',
                finca_id: cuartel?.finca_id ?? '',
                codigo_finca: cuartel?.codigo_finca ?? '',
                nombre_finca: cuartel?.nombre_finca ?? '',
                codigo_cuartel: cuartel?.codigo_cuartel ?? '',
                variedad: cuartel?.variedad ?? '',
                superficie_ha: cuartel?.superficie_ha ?? '',
            }));
        }

        function getFincaLabel(finca) {
            const codigo = String(finca?.codigo_finca ?? '').trim() || 'Sin codigo';
            const nombre = String(finca?.nombre_finca ?? '').trim() || 'Sin nombre';
            const id = String(finca?.id ?? '').trim() || '-';
            return `Finca ${codigo} | ${nombre} | id ${id}`;
        }

        function getCuartelLabel(cuartel) {
            const codigo = String(cuartel?.codigo_cuartel ?? '').trim() || 'Sin codigo';
            const variedad = String(cuartel?.variedad ?? '').trim() || 'Sin variedad';
            const id = String(cuartel?.id ?? '').trim() || '-';
            return `Cuartel ${codigo} | ${variedad} | id ${id}`;
        }


        function prettyConsoleLogConsolidado(payload) {
            const usuario = payload?.usuario || null;
            const usuariosInfo = Array.isArray(payload?.usuarios_info) ? payload.usuarios_info : [];
            const relProductorCoop = Array.isArray(payload?.rel_productor_coop) ? payload.rel_productor_coop : [];
            const prodFincas = Array.isArray(payload?.prod_fincas) ? payload.prod_fincas : [];
            const prodFincaDireccion = Array.isArray(payload?.prod_finca_direccion) ? payload.prod_finca_direccion : [];
            const relProductorFinca = Array.isArray(payload?.rel_productor_finca) ? payload.rel_productor_finca : [];
            const prodCuartel = Array.isArray(payload?.prod_cuartel) ? payload.prod_cuartel : [];
            const prodCuartelLimitantes = Array.isArray(payload?.prod_cuartel_limitantes) ? payload.prod_cuartel_limitantes : [];
            const prodCuartelRendimientos = Array.isArray(payload?.prod_cuartel_rendimientos) ? payload.prod_cuartel_rendimientos : [];
            const prodCuartelRiesgos = Array.isArray(payload?.prod_cuartel_riesgos) ? payload.prod_cuartel_riesgos : [];

            const direccionesByFincaId = buildRowMap(prodFincaDireccion, 'finca_id');
            const relFincaByFincaId = buildRowListMap(relProductorFinca, 'finca_id');
            const cuartelesByFincaId = buildRowListMap(prodCuartel.filter(c => c?.finca_id !== null && c?.finca_id !== undefined), 'finca_id');
            const cuartelesSinFinca = prodCuartel.filter(c => c?.finca_id === null || c?.finca_id === undefined || c?.finca_id === '');
            const limitantesByCuartelId = buildRowMap(prodCuartelLimitantes, 'cuartel_id');
            const rendimientosByCuartelId = buildRowMap(prodCuartelRendimientos, 'cuartel_id');
            const riesgosByCuartelId = buildRowMap(prodCuartelRiesgos, 'cuartel_id');
            const productorSummary = buildProductorSummary(usuario, usuariosInfo, relProductorCoop, prodFincas, prodCuartel, cuartelesSinFinca);
            const fincasSummaryRows = buildFincasSummaryRows(prodFincas, direccionesByFincaId, cuartelesByFincaId);
            const cuartelesSummaryRows = buildCuartelesSummaryRows(prodCuartel);

            const productorLabel = String(usuario?.id_real ?? 'sin_id_real');
            console.groupCollapsed(`[Relevamiento] Productor ${productorLabel} | Fincas: ${prodFincas.length} | Cuarteles: ${prodCuartel.length}`);

            logCompactTable('Resumen general', productorSummary);

            console.groupCollapsed('Datos del productor');
            logSingleRowTable('usuarios', usuario);
            logMultiRowTable('usuarios_info', usuariosInfo);
            logMultiRowTable('rel_productor_coop', relProductorCoop);
            console.groupEnd();

            logCompactTable('Resumen de fincas', fincasSummaryRows);
            logCompactTable('Resumen de cuarteles', cuartelesSummaryRows);

            console.groupCollapsed(`Fincas (${prodFincas.length})`);
            if (!prodFincas.length) {
                console.table([{
                    columna_sql: '(sin fincas)',
                    valor: ''
                }]);
            } else {
                prodFincas.forEach((finca) => {
                    const fincaId = String(finca?.id ?? '');
                    const cuarteles = cuartelesByFincaId.get(fincaId) || [];
                    console.groupCollapsed(`${getFincaLabel(finca)} | Cuarteles: ${cuarteles.length}`);
                    logSingleRowTable('prod_fincas', finca);
                    logSingleRowTable('prod_finca_direccion', direccionesByFincaId.get(fincaId) || null);
                    logMultiRowTable('rel_productor_finca', relFincaByFincaId.get(fincaId) || []);

                    console.groupCollapsed(`Cuarteles de finca (${cuarteles.length})`);
                    if (!cuarteles.length) {
                        console.table([{
                            columna_sql: '(sin cuarteles)',
                            valor: ''
                        }]);
                    } else {
                        cuarteles.forEach((cuartel) => {
                            const cuartelId = String(cuartel?.id ?? '');
                            console.groupCollapsed(getCuartelLabel(cuartel));
                            logSingleRowTable('prod_cuartel', cuartel);
                            logSingleRowTable('prod_cuartel_limitantes', limitantesByCuartelId.get(cuartelId) || null);
                            logSingleRowTable('prod_cuartel_rendimientos', rendimientosByCuartelId.get(cuartelId) || null);
                            logSingleRowTable('prod_cuartel_riesgos', riesgosByCuartelId.get(cuartelId) || null);
                            console.groupEnd();
                        });
                    }
                    console.groupEnd();
                    console.groupEnd();
                });
            }
            console.groupEnd();

            if (cuartelesSinFinca.length) {
                console.groupCollapsed(`Cuarteles sin finca vinculada (${cuartelesSinFinca.length})`);
                cuartelesSinFinca.forEach((cuartel) => {
                    const cuartelId = String(cuartel?.id ?? '');
                    console.groupCollapsed(`${getCuartelLabel(cuartel)} | Sin finca vinculada`);
                    logSingleRowTable('prod_cuartel', cuartel);
                    logSingleRowTable('prod_cuartel_limitantes', limitantesByCuartelId.get(cuartelId) || null);
                    logSingleRowTable('prod_cuartel_rendimientos', rendimientosByCuartelId.get(cuartelId) || null);
                    logSingleRowTable('prod_cuartel_riesgos', riesgosByCuartelId.get(cuartelId) || null);
                    console.groupEnd();
                });
                console.groupEnd();
            }

            console.groupEnd();
        }





        async function relevamientoLogProductorFull(productorIdReal) {
            const prod = PRODUCTORES_MAP[productorIdReal];
            if (!prod) {
                console.warn('[Relevamiento] No se encontró productor para log:', productorIdReal);
                return;
            }

            try {
                const payload = await fetchTablasDumpData(productorIdReal);
                prettyConsoleLogConsolidado(payload);
            } catch (e) {
                console.error('[Relevamiento] Error al obtener dump de tablas:', e);
            }
        }


        async function cargarCooperativas() {
            const container = document.getElementById('cards-container');
            if (!container) return;

            container.innerHTML = '<div class="card">Cargando cooperativas...</div>';

            try {
                const resp = await fetch(`${API_RELEVAMIENTO}?action=cooperativas`, {
                    credentials: 'same-origin'
                });

                const data = await resp.json();

                if (!data.ok) {
                    throw new Error(data.error || 'Error al cargar cooperativas');
                }

                const coops = Array.isArray(data.data) ? data.data : [];

                if (coops.length === 0) {
                    setCardsTitle('No tenés cooperativas asociadas');
                    container.innerHTML = '<div class="card">No se encontraron cooperativas.</div>';
                    return;
                }

                setCardsTitle('Seleccioná una cooperativa');
                renderCooperativasTable(coops);
            } catch (e) {
                console.error(e);
                container.innerHTML = `<div class="card card-error">Error al cargar cooperativas: ${e.message}</div>`;
            }
        }

        async function cargarProductores(coop) {
            const container = document.getElementById('cards-container');
            if (!container) return;

            currentCoop = coop;
            setCardsTitle(`Productores de ${coop.nombre}`);
            container.innerHTML = '<div class="card">Cargando productores...</div>';

            try {
                const params = new URLSearchParams({
                    action: 'productores',
                    coop_id_real: coop.id_real
                });

                const resp = await fetch(`${API_RELEVAMIENTO}?${params.toString()}`, {
                    credentials: 'same-origin'
                });

                const data = await resp.json();

                if (!data.ok) {
                    throw new Error(data.error || 'Error al cargar productores');
                }

                const productores = Array.isArray(data.data) ? data.data : [];

                if (productores.length === 0) {
                    container.innerHTML = '<div class="card">No se encontraron productores para esta cooperativa.</div>';
                    return;
                }

                renderProductoresTable(productores);
            } catch (e) {
                console.error(e);
                container.innerHTML = `<div class="card card-error">Error al cargar productores: ${e.message}</div>`;
            }
        }

        // ===== Helpers simples de modales =====
        // Usamos nombres específicos para evitar conflicto con funciones globales del framework.

        function relevamientoGetModalElement(tipo) {
            const modalId = MODAL_IDS[tipo];
            if (!modalId) {
                console.warn('[Relevamiento] Tipo de modal desconocido:', tipo);
                return null;
            }
            const modal = document.getElementById(modalId);
            if (!modal) {
                console.warn('[Relevamiento] No se encontró el elemento modal con id', modalId);
            }
            return modal;
        }

        function relevamientoCloseModal(tipo) {
            const modal = relevamientoGetModalElement(tipo);
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        // Alias por compatibilidad: apunta al logger consolidado
        window.relevamientoLogProductor = relevamientoLogProductorFull;

        async function loadFamiliaForm(productorIdReal) {
            const modal = relevamientoGetModalElement('familia');
            if (!modal) return;

            const body = modal.querySelector('[data-modal-body="familia"]');
            if (!body) return;

            body.innerHTML = '<p>Cargando datos de familia...</p>';
            // Guardamos el id_real también en el dataset del modal
            modal.dataset.productorIdReal = productorIdReal;

            try {
                const params = new URLSearchParams({
                    productor_id_real: productorIdReal
                });

                const resp = await fetch(
                    `${RELEVAMIENTO_PARTIAL_BASE}/relevamiento_familia_controller.php?${params.toString()}`, {
                        credentials: 'same-origin'
                    }
                );

                if (!resp.ok) {
                    throw new Error(`Error HTTP ${resp.status}`);
                }

                const html = await resp.text();
                body.innerHTML = html;

                initFamiliaModal(productorIdReal);
            } catch (e) {
                console.error('[Relevamiento] Error al cargar familia:', e);
                body.innerHTML = `<p class="text-danger">Error al cargar datos de familia: ${e.message}</p>`;
            }
        }


        function initFamiliaModal(productorIdReal) {

            const modal = relevamientoGetModalElement('familia');
            if (!modal) return;

            const toggle = modal.querySelector('[data-role="familia-advanced-toggle"]');
            const advancedFields = modal.querySelectorAll('[data-advanced="1"]');

            if (!toggle || !advancedFields.length) {
                return;
            }

            const applyVisibility = () => {
                advancedFields.forEach((el) => {
                    if (toggle.checked) {
                        el.classList.remove('relevamiento-advanced-hidden');
                    } else {
                        el.classList.add('relevamiento-advanced-hidden');
                    }
                });
            };

            toggle.addEventListener('change', applyVisibility);
            // Estado inicial: ocultos
            toggle.checked = false;
            applyVisibility();
        }

        async function loadProduccionForm(productorIdReal) {
            const modal = relevamientoGetModalElement('produccion');
            if (!modal) return;

            const body = modal.querySelector('[data-modal-body="produccion"]');
            if (!body) return;

            body.innerHTML = '<p>Cargando formulario de producción...</p>';
            // Guardamos el id_real también en el dataset del modal (por si luego queremos guardar)
            modal.dataset.productorIdReal = productorIdReal;

            try {
                const params = new URLSearchParams({
                    productor_id_real: productorIdReal
                });

                const resp = await fetch(
                    `${RELEVAMIENTO_PARTIAL_BASE}/relevamiento_produccion_controller.php?${params.toString()}`, {
                        credentials: 'same-origin'
                    }
                );

                if (!resp.ok) {
                    throw new Error(`Error HTTP ${resp.status}`);
                }

                const html = await resp.text();
                body.innerHTML = html;

                initProduccionModal();
            } catch (e) {
                console.error('[Relevamiento] Error al cargar producción:', e);
                body.innerHTML = `<p class="text-danger">Error al cargar datos de producción: ${e.message}</p>`;
            }
        }

        function initProduccionModal() {
            const modal = relevamientoGetModalElement('produccion');
            if (!modal) return;

            const toggle = modal.querySelector('[data-role="produccion-advanced-toggle"]');
            const advancedFields = modal.querySelectorAll('[data-advanced="1"]');

            if (!toggle || !advancedFields.length) {
                return;
            }

            const applyVisibility = () => {
                advancedFields.forEach((el) => {
                    if (toggle.checked) {
                        el.classList.remove('relevamiento-advanced-hidden');
                    } else {
                        el.classList.add('relevamiento-advanced-hidden');
                    }
                });
            };

            toggle.addEventListener('change', applyVisibility);
            toggle.checked = false;
            applyVisibility();
        }

        async function guardarFamilia() {
            const modal = relevamientoGetModalElement('familia');
            if (!modal) {
                return;
            }

            const form = modal.querySelector('#familia-form');
            if (!form) {
                // Si no hay formulario, simplemente cerramos
                relevamientoCloseModal('familia');
                return;
            }

            const productorIdReal =
                (modal.dataset.productorIdReal) ||
                (currentProductor && currentProductor.id_real) ||
                '';

            const formData = new FormData(form);
            formData.append('productor_id_real', productorIdReal);

            try {
                const resp = await fetch(
                    `${RELEVAMIENTO_PARTIAL_BASE}/relevamiento_familia_controller.php`, {
                        method: 'POST',
                        credentials: 'same-origin',
                        body: formData
                    }
                );

                const data = await resp.json();

                if (!data.ok) {
                    throw new Error(data.error || 'Error al guardar datos de familia');
                }

                // Si tenés showToastBoton / showToast podés usarlo:
                if (typeof showToastBoton === 'function') {
                    showToastBoton('success', 'Datos de familia guardados correctamente');
                } else {
                    alert('Datos de familia guardados correctamente');
                }

                relevamientoCloseModal('familia');
            } catch (e) {
                console.error('[Relevamiento] Error al guardar familia:', e);
                if (typeof showToastBoton === 'function') {
                    showToastBoton('error', 'Error al guardar datos de familia: ' + e.message);
                } else {
                    alert('Error al guardar datos de familia: ' + e.message);
                }
            }
        }

        async function guardarProduccion() {
            const modal = relevamientoGetModalElement('produccion');
            if (!modal) {
                return;
            }

            const form = modal.querySelector('#produccion-form');
            if (!form) {
                // Si todavía no hay formulario definido, simplemente cerramos el modal
                relevamientoCloseModal('produccion');
                return;
            }

            const productorIdReal =
                (modal.dataset.productorIdReal) ||
                (currentProductor && currentProductor.id_real) ||
                '';

            const formData = new FormData(form);
            formData.append('productor_id_real', productorIdReal);

            try {
                const resp = await fetch(
                    `${RELEVAMIENTO_PARTIAL_BASE}/relevamiento_produccion_controller.php`, {
                        method: 'POST',
                        credentials: 'same-origin',
                        body: formData
                    }
                );

                const data = await resp.json();

                if (!data.ok) {
                    throw new Error(data.error || 'Error al guardar datos de producción');
                }

                if (typeof showToastBoton === 'function') {
                    showToastBoton('success', 'Datos de producción guardados correctamente');
                } else {
                    alert('Datos de producción guardados correctamente');
                }

                relevamientoCloseModal('produccion');
            } catch (e) {
                console.error('[Relevamiento] Error al guardar producción:', e);
                if (typeof showToastBoton === 'function') {
                    showToastBoton('error', 'Error al guardar datos de producción: ' + e.message);
                } else {
                    alert('Error al guardar datos de producción: ' + e.message);
                }
            }
        }

        function relevamientoOpenModal(tipo, productorIdReal) {
            console.log('[Relevamiento] relevamientoOpenModal', {
                tipo,
                productorIdReal
            });

            // Guardamos referencia por si después queremos usarla
            const productor = PRODUCTORES_MAP[productorIdReal];
            if (!productor) {
                console.warn('[Relevamiento] PRODUCTORES_MAP sin entrada para', productorIdReal);
            } else {
                currentProductor = productor;
            }

            const modal = relevamientoGetModalElement(tipo);
            if (!modal) {
                alert('No se encontró el modal para: ' + tipo);
                return;
            }

            // Mostramos el modal
            modal.classList.remove('hidden');

            // Cargar contenido específico según tipo
            if (tipo === 'familia') {
                loadFamiliaForm(productorIdReal);
            } else if (tipo === 'produccion') {
                loadProduccionForm(productorIdReal);
            } else if (tipo === 'cuarteles') {
                // futuro: loadCuartelesForm(productorIdReal);
            }
        }

        // Exponer en window por si hace falta desde HTML inline
        window.guardarFamilia = guardarFamilia;
        window.guardarProduccion = guardarProduccion;
        window.relevamientoOpenModal = relevamientoOpenModal;
        window.relevamientoCloseModal = relevamientoCloseModal;
        window.relevamientoLogProductorFull = relevamientoLogProductorFull;
        window.abrirModificarProductor = abrirModificarProductor;
        window.guardarFamiliaDesdeVista = guardarFamiliaDesdeVista;
        window.guardarProduccionDesdeVista = guardarProduccionDesdeVista;
        window.guardarTodoDesdeVista = guardarTodoDesdeVista;
        window.volverAProductores = volverAProductores;
        window.confirmarEliminarFinca = confirmarEliminarFinca;
        window.confirmarEliminarCuartel = confirmarEliminarCuartel;

        // Cargar cooperativas una vez que el DOM esté listo
        window.addEventListener('DOMContentLoaded', () => {
            cargarCooperativas();
        });
    </script>

    <!-- ===== Modales Relevamiento ===== -->

    <!-- Modal Familia -->
    <div id="modal-familia" class="modal hidden">
        <div class="modal-content">
            <h3>Familia del productor</h3>
            <div class="modal-body" data-modal-body="familia">
                <p>Cargando formulario de familia...</p>
            </div>
            <div class="form-buttons">
                <button class="btn btn-aceptar" onclick="guardarFamilia()">Aceptar</button>
                <button class="btn btn-cancelar" onclick="relevamientoCloseModal('familia')">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- Modal Producción -->
    <div id="modal-produccion" class="modal hidden">
        <div class="modal-content">
            <h3>Producción del productor</h3>
            <div class="modal-body" data-modal-body="produccion">
                <p>Cargando formulario de producción...</p>
            </div>
            <div class="form-buttons">
                <button class="btn btn-aceptar" onclick="guardarProduccion()">Aceptar</button>
                <button class="btn btn-cancelar" onclick="relevamientoCloseModal('produccion')">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- Modal Cuarteles -->
    <div id="modal-cuarteles" class="modal hidden">
        <div class="modal-content">
            <h3>Cuarteles del productor</h3>
            <div class="modal-body" data-modal-body="cuarteles">
                <p>Cargando formulario de cuarteles...</p>
            </div>
            <div class="form-buttons">
                <button class="btn btn-aceptar" onclick="relevamientoCloseModal('cuarteles')">Aceptar</button>
                <button class="btn btn-cancelar" onclick="relevamientoCloseModal('cuarteles')">Cancelar</button>
            </div>
        </div>
    </div>


</body>


</html>