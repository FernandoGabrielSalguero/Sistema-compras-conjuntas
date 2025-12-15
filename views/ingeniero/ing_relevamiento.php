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

                <!-- Título de sección -->
                <div class="sidebar-section-title">Relevamiento</div>

                <!-- Lista directa de páginas de Relevamiento -->
                <ul class="submenu-root">
                    <li>
                        <a href="ing_relevamiento.php">
                            <span class="material-symbols-outlined">map</span>
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
        let currentProductor = null;

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

            productores.forEach((p) => {
                PRODUCTORES_MAP[p.id_real] = p;
            });

            const rows = productores.map((p, idx) => {
                const nombre = escapeHtml(p.nombre);
                const idReal = escapeHtml(p.id_real);
                const cuit = escapeHtml(p.cuit ?? 'Sin CUIT');

                return `
                    <tr>
                        <td>${idx + 1}</td>
                        <td>${nombre}</td>
                        <td>${idReal}</td>
                        <td>${cuit}</td>
                        <td class="cell-actions">
                            <button class="btn btn-info" onclick="relevamientoOpenModal('familia','${idReal}')">Familia</button>
                            <button class="btn btn-info" onclick="relevamientoOpenModal('produccion','${idReal}')">Producción</button>
                            <button class="btn btn-info" onclick="relevamientoOpenModal('cuarteles','${idReal}')">Cuarteles</button>
                            <button class="icon-btn" title="Consolidar JSON (Familia + Producción)" onclick="relevamientoLogProductorFull('${idReal}')">
                                <span class="material-symbols-outlined">code</span>
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');

            container.innerHTML = `
                <div class="card tabla-card">
                    <h2>Productores</h2>
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
                            <tbody>
                                ${rows}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
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
                return { table, index, field: part3 };
            }

            if (index !== null && !part3) {
                return { table, index, field: null };
            }

            // Caso fincas[campo]
            return { table, index: null, field: part2 || null };
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

                const { table, index, field } = parsed;

                if (!grouped[table]) grouped[table] = { rows: {}, flat: {} };

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



        function logSectionAsTable(sectionName, data) {
            // Formato uniforme: "Tabla: X" + "Columnas:" + console.table
            console.log(`Tabla: ${sectionName}`);

            if (!data) {
                console.log('Columnas: []');
                console.table([{}]);
                return;
            }

            if (data.__error) {
                console.log('Columnas: []');
                console.table([{ __error: data.__error }]);
                return;
            }

            // Objeto simple => 1 fila para console.table (más legible que console.table(obj))
            if (!Array.isArray(data)) {
                const columns = Object.keys(data);
                console.log('Columnas:', columns);
                console.table([data]);
                return;
            }

            // Array de filas
            const allCols = new Set();
            data.forEach(r => Object.keys(r || {}).forEach(k => allCols.add(k)));
            console.log('Columnas:', Array.from(allCols));
            console.table(data);
        }

        function prettyConsoleLogConsolidado(payload) {
            const productor = payload?.productor || null;
            const familia = payload?.familia || null;
            const produccion = payload?.produccion || null;

            console.log('[Relevamiento] Productor consolidado');

            // "usuarios" (base)
            logSectionAsTable('usuarios (productor base)', productor);

            // "familia" (form)
            logSectionAsTable('relevamiento_familia (form)', familia);

            // "produccion": separar por tablas según keys bracket
            if (produccion && !produccion.__error) {
                const grouped = groupByTables(produccion);

                // Campos no-bracket (ej: productor_id_real)
                if (grouped.__flat) {
                    logSectionAsTable('relevamiento_produccion (flat)', grouped.__flat);
                } else {
                    logSectionAsTable('relevamiento_produccion (flat)', null);
                }

                // Cada "tabla" detectada (ej: fincas)
                Object.keys(grouped).forEach((tableName) => {
                    if (tableName === '__flat') return;

                    const block = grouped[tableName];
                    const rows = block?.rows || {};
                    const flat = block?.flat || {};

                    if (Object.keys(flat).length > 0) {
                        logSectionAsTable(`${tableName} (flat)`, flat);
                    } else {
                        logSectionAsTable(`${tableName} (flat)`, null);
                    }

                    const rowArr = Object.keys(rows)
                        .map(k => ({ __index: Number(k), ...rows[k] }))
                        .sort((a, b) => a.__index - b.__index);

                    logSectionAsTable(`${tableName} (rows)`, rowArr.length ? rowArr : null);
                });
            } else {
                logSectionAsTable('relevamiento_produccion (form)', produccion);
            }
        }





        async function relevamientoLogProductorFull(productorIdReal) {
            const prod = PRODUCTORES_MAP[productorIdReal];
            if (!prod) {
                console.warn('[Relevamiento] No se encontró productor para log:', productorIdReal);
                return;
            }

            try {
                const [familia, produccion] = await Promise.all([
                    fetchFamiliaData(productorIdReal).catch((e) => ({ __error: e.message })),
                    fetchProduccionData(productorIdReal).catch((e) => ({ __error: e.message }))
                ]);

                const payload = {
                    productor: prod,
                    familia: familia,
                    produccion: produccion
                };

                prettyConsoleLogConsolidado(payload);
            } catch (e) {
                console.error('[Relevamiento] Error al consolidar JSON:', e);
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