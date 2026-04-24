<?php
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$viewsPos = strpos($scriptName, '/views/');
$appBasePath = $viewsPos !== false ? substr($scriptName, 0, $viewsPos) : '';
$cierreInfo = $cierre_info ?? null;
?>
<style>
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

        #productor-modificar-view textarea {
            width: 100%;
            min-height: 88px;
            resize: vertical;
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

        .asset-workspace {
            display: grid;
            grid-template-columns: minmax(280px, 360px) 1fr;
            gap: 1rem;
            align-items: start;
        }

        .asset-sidebar,
        .asset-detail {
            min-width: 0;
        }

        .asset-sidebar {
            position: sticky;
            top: 1rem;
            max-height: calc(100vh - 2rem);
            overflow: auto;
        }

        .asset-search {
            width: 100%;
            padding: 0.55rem 0.75rem;
            border: 1px solid rgba(15, 23, 42, 0.16);
            border-radius: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .asset-tree {
            display: grid;
            gap: 0.7rem;
        }

        .asset-tree-item {
            display: grid;
            gap: 0.45rem;
        }

        .asset-tree-group {
            border: 1px solid rgba(15, 23, 42, 0.1);
            border-radius: 0.6rem;
            padding: 0.55rem;
            background: rgba(255, 255, 255, 0.75);
        }

        .asset-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 0.45rem;
            align-items: start;
        }

        .asset-actions {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            margin-top: 0.1rem;
        }

        .asset-node,
        .asset-child {
            width: 100%;
            border: 1px solid rgba(15, 23, 42, 0.12);
            background: #fff;
            border-radius: 0.5rem;
            padding: 0.65rem 0.7rem;
            text-align: left;
            cursor: pointer;
        }

        .asset-node:hover,
        .asset-child:hover,
        .asset-node.is-active,
        .asset-child.is-active {
            border-color: #5b21b6;
            background: rgba(91, 33, 182, 0.06);
        }

        .asset-node-title {
            display: block;
            font-weight: 700;
            color: #111827;
        }

        .asset-node-meta {
            display: block;
            margin-top: 0.18rem;
            font-size: 0.84rem;
            color: rgba(15, 23, 42, 0.72);
        }

        .asset-children {
            display: grid;
            gap: 0.55rem;
            margin: 0.35rem 0 0.05rem 0.8rem;
            padding-left: 0.75rem;
            border-left: 2px solid rgba(15, 23, 42, 0.14);
        }

        .asset-children .asset-row {
            position: relative;
        }

        .asset-children .asset-row::before {
            content: '';
            position: absolute;
            left: -0.75rem;
            top: 1rem;
            width: 0.65rem;
            border-top: 1px dashed rgba(15, 23, 42, 0.26);
        }

        .asset-section-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            opacity: 0.7;
            margin: 0.1rem 0 0.2rem;
        }

        .asset-detail-header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 0.8rem;
        }

        .asset-detail-title {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 700;
        }

        .detail-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
            margin-bottom: 0.8rem;
        }

        .detail-tabs button {
            border: 1px solid rgba(15, 23, 42, 0.16);
            background: #fff;
            border-radius: 0.5rem;
            padding: 0.45rem 0.65rem;
            cursor: pointer;
        }

        .detail-tabs button:hover {
            background: rgba(15, 23, 42, 0.04);
        }

        .detail-form-panel[hidden],
        .detail-panel[hidden],
        .detail-section[hidden] {
            display: none !important;
        }

        .asset-empty {
            color: rgba(15, 23, 42, 0.72);
        }

        @media (max-width: 1200px) {
            #productor-modificar-view .table-section-grid {
                grid-template-columns: repeat(2, minmax(220px, 1fr));
            }

            .asset-workspace {
                grid-template-columns: 1fr;
            }

            .asset-sidebar {
                position: static;
                max-height: none;
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
                <script src="<?= htmlspecialchars($appBasePath, ENT_QUOTES, 'UTF-8') ?>/views/partials/spinner-global.js"></script>

            
<!-- toast -->
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            console.log(<?php echo json_encode($_SESSION); ?>);

            <?php if (!empty($cierreInfo)): ?>
                const cierreData = <?= json_encode($cierreInfo, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
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
        const APP_BASE_PATH = <?= json_encode($appBasePath, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        const API_RELEVAMIENTO = `${APP_BASE_PATH}/controllers/ing_relevamientoController.php`;
        const RELEVAMIENTO_PARTIAL_BASE = `${APP_BASE_PATH}/views/partials/relevamiento`;
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
        let currentAssetSelection = {
            type: 'productor',
            id: ''
        };
        let relevamientoShowArchived = false;
        let pendingConfirmAction = null;

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
                const cuitRaw = String(c.cuit ?? '').trim();
                const cuit = escapeHtml(cuitRaw && cuitRaw !== '0' ? cuitRaw : 'Sin CUIT');

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
                    <div class="productor-edit-toolbar">
                        <button class="btn-icon" onclick="volverACooperativas()" title="Volver a cooperativas" aria-label="Volver a cooperativas"><span class="material-symbols-outlined">arrow_back</span></button>
                        <h2 style="margin:0;">Productores</h2>
                        <div class="cell-actions">
                            <button class="btn-icon" onclick="toggleMostrarArchivados()" title="${relevamientoShowArchived ? 'Ocultar archivados' : 'Mostrar archivados'}" aria-label="${relevamientoShowArchived ? 'Ocultar archivados' : 'Mostrar archivados'}"><span class="material-symbols-outlined">${relevamientoShowArchived ? 'visibility_off' : 'visibility'}</span></button>
                            <button class="btn btn-aceptar" onclick="promptCrearProductor()">Nuevo productor</button>
                        </div>
                    </div>
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
                                    <th>Estado</th>
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
                    const archivado = Number(p?.archivado ?? 0) === 1;
                    const estado = archivado ? 'Archivado' : 'Activo';

                    return `
                        <tr>
                            <td>${idx + 1}</td>
                            <td>${nombre}</td>
                            <td>${idReal}</td>
                            <td>${cuit}</td>
                            <td>${estado}</td>
                            <td class="cell-actions">
                                <button class="btn btn-info" onclick="abrirModificarProductor('${idRealJs}')">Modificar datos</button>
                                <button class="btn-icon" onclick="${archivado ? `confirmarDesarchivarProductor('${idRealJs}')` : `confirmarArchivarProductor('${idRealJs}')`}" title="${archivado ? 'Desarchivar' : 'Archivar'}" aria-label="${archivado ? 'Desarchivar' : 'Archivar'}"><span class="material-symbols-outlined">${archivado ? 'unarchive' : 'archive'}</span></button>
                                <button class="icon-btn" title="Imprimir tablas del productor en consola" onclick="relevamientoLogProductorFull('${idRealJs}')">
                                    <span class="material-symbols-outlined">code</span>
                                </button>
                            </td>
                        </tr>
                    `;
                }).join('');

                if (tbody) {
                    tbody.innerHTML = rows || `<tr><td colspan="6">Sin resultados para la búsqueda.</td></tr>`;
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

        async function apiPostAction(action, payload = {}) {
            const body = new URLSearchParams({
                action,
                ...payload
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
                throw new Error(data.error || `Error en accion ${action}`);
            }
            return data.data ?? null;
        }

        function toggleMostrarArchivados() {
            relevamientoShowArchived = !relevamientoShowArchived;
            if (currentCoop) {
                cargarProductores(currentCoop);
            }
        }

        function openSimpleModalById(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) return;
            modal.classList.remove('hidden');
        }

        function closeSimpleModalById(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) return;
            modal.classList.add('hidden');
        }

        function openConfirmActionModal(title, message, onConfirm) {
            const modal = document.getElementById('modal-confirm-action');
            if (!modal) return;
            const titleEl = modal.querySelector('[data-confirm-title]');
            const messageEl = modal.querySelector('[data-confirm-message]');
            if (titleEl) titleEl.textContent = title || 'Confirmar acción';
            if (messageEl) messageEl.textContent = message || '';
            pendingConfirmAction = typeof onConfirm === 'function' ? onConfirm : null;
            openSimpleModalById('modal-confirm-action');
        }

        function closeConfirmActionModal() {
            pendingConfirmAction = null;
            closeSimpleModalById('modal-confirm-action');
        }

        async function runConfirmActionModal() {
            const action = pendingConfirmAction;
            pendingConfirmAction = null;
            closeSimpleModalById('modal-confirm-action');
            if (!action) return;
            await action();
        }

        async function promptCrearProductor() {
            if (!currentCoop?.id_real) {
                return;
            }
            const modal = document.getElementById('modal-crear-productor');
            if (!modal) return;

            const usuarioInput = modal.querySelector('input[name="nuevo_usuario"]');
            const cuitInput = modal.querySelector('input[name="nuevo_cuit"]');
            if (usuarioInput) usuarioInput.value = '';
            if (cuitInput) cuitInput.value = '';

            openSimpleModalById('modal-crear-productor');
            if (usuarioInput) usuarioInput.focus();
        }

        async function guardarNuevoProductorDesdeModal() {
            if (!currentCoop?.id_real) return;
            const modal = document.getElementById('modal-crear-productor');
            if (!modal) return;

            const usuario = String(modal.querySelector('input[name="nuevo_usuario"]')?.value ?? '').trim();
            const cuit = String(modal.querySelector('input[name="nuevo_cuit"]')?.value ?? '').trim();
            if (!usuario || !cuit) {
                if (typeof showToastBoton === 'function') {
                    showToastBoton('error', 'Completa usuario y CUIT');
                }
                return;
            }

            try {
                await apiPostAction('crear_productor', {
                    coop_id_real: String(currentCoop.id_real),
                    usuario,
                    cuit
                });
                closeSimpleModalById('modal-crear-productor');
                if (typeof showToastBoton === 'function') {
                    showToastBoton('success', 'Productor creado correctamente');
                }
                await cargarProductores(currentCoop);
            } catch (e) {
                console.error('[Relevamiento] Error al crear productor:', e);
                if (typeof showToastBoton === 'function') {
                    showToastBoton('error', `Error al crear productor: ${e.message}`);
                }
            }
        }

        async function confirmarArchivarProductor(productorIdReal) {
            openConfirmActionModal(
                'Archivar productor',
                `Se archivara el productor ${productorIdReal} y sus fincas/cuarteles.`,
                async () => {
                    try {
                        await apiPostAction('archivar_productor', {
                            productor_id_real: String(productorIdReal)
                        });
                        if (typeof showToastBoton === 'function') {
                            showToastBoton('success', 'Productor archivado');
                        }
                        if (currentCoop) await cargarProductores(currentCoop);
                    } catch (e) {
                        console.error('[Relevamiento] Error al archivar productor:', e);
                        if (typeof showToastBoton === 'function') {
                            showToastBoton('error', `Error al archivar productor: ${e.message}`);
                        }
                    }
                }
            );
        }

        async function confirmarDesarchivarProductor(productorIdReal) {
            openConfirmActionModal(
                'Desarchivar productor',
                `Se desarchivara el productor ${productorIdReal} y sus fincas/cuarteles.`,
                async () => {
                    try {
                        await apiPostAction('desarchivar_productor', {
                            productor_id_real: String(productorIdReal)
                        });
                        if (typeof showToastBoton === 'function') {
                            showToastBoton('success', 'Productor desarchivado');
                        }
                        if (currentCoop) await cargarProductores(currentCoop);
                    } catch (e) {
                        console.error('[Relevamiento] Error al desarchivar productor:', e);
                        if (typeof showToastBoton === 'function') {
                            showToastBoton('error', `Error al desarchivar productor: ${e.message}`);
                        }
                    }
                }
            );
        }

        async function promptCrearFinca(productorIdReal) {
            const modal = document.getElementById('modal-crear-finca');
            if (!modal) return;

            modal.dataset.productorIdReal = String(productorIdReal ?? '');
            const codigoInput = modal.querySelector('input[name="nuevo_codigo_finca"]');
            const nombreInput = modal.querySelector('input[name="nuevo_nombre_finca"]');
            if (codigoInput) codigoInput.value = '';
            if (nombreInput) nombreInput.value = '';

            openSimpleModalById('modal-crear-finca');
            if (codigoInput) codigoInput.focus();
        }

        async function guardarNuevaFincaDesdeModal() {
            const modal = document.getElementById('modal-crear-finca');
            if (!modal) return;

            const productorIdReal = String(modal.dataset.productorIdReal ?? '').trim();
            const codigoFinca = String(modal.querySelector('input[name="nuevo_codigo_finca"]')?.value ?? '').trim();
            const nombreFinca = String(modal.querySelector('input[name="nuevo_nombre_finca"]')?.value ?? '').trim();
            if (!productorIdReal || !codigoFinca || !nombreFinca) {
                if (typeof showToastBoton === 'function') {
                    showToastBoton('error', 'Completa código y nombre de finca');
                }
                return;
            }

            try {
                await apiPostAction('crear_finca', {
                    productor_id_real: String(productorIdReal),
                    codigo_finca: codigoFinca,
                    nombre_finca: nombreFinca
                });
                closeSimpleModalById('modal-crear-finca');
                if (typeof showToastBoton === 'function') {
                    showToastBoton('success', 'Finca creada correctamente');
                }
                await abrirModificarProductor(productorIdReal);
            } catch (e) {
                console.error('[Relevamiento] Error al crear finca:', e);
                if (typeof showToastBoton === 'function') {
                    showToastBoton('error', `Error al crear finca: ${e.message}`);
                }
            }
        }

        async function promptCrearCuartel(productorIdReal, fincaId) {
            const modal = document.getElementById('modal-crear-cuartel');
            if (!modal) return;

            modal.dataset.productorIdReal = String(productorIdReal ?? '');
            modal.dataset.fincaId = String(fincaId ?? '');

            const variedadInput = modal.querySelector('input[name="nuevo_variedad_cuartel"]');
            const superficieInput = modal.querySelector('input[name="nuevo_superficie_cuartel"]');
            if (variedadInput) variedadInput.value = '';
            if (superficieInput) superficieInput.value = '';

            openSimpleModalById('modal-crear-cuartel');
            if (variedadInput) variedadInput.focus();
        }

        async function guardarNuevoCuartelDesdeModal() {
            const modal = document.getElementById('modal-crear-cuartel');
            if (!modal) return;

            const productorIdReal = String(modal.dataset.productorIdReal ?? '').trim();
            const fincaId = String(modal.dataset.fincaId ?? '').trim();
            const variedad = String(modal.querySelector('input[name="nuevo_variedad_cuartel"]')?.value ?? '').trim();
            const superficieHa = String(modal.querySelector('input[name="nuevo_superficie_cuartel"]')?.value ?? '').trim();
            if (!productorIdReal || !fincaId || !variedad) {
                if (typeof showToastBoton === 'function') {
                    showToastBoton('error', 'Completa al menos variedad del cuartel');
                }
                return;
            }

            try {
                await apiPostAction('crear_cuartel', {
                    productor_id_real: String(productorIdReal),
                    finca_id: String(fincaId),
                    variedad,
                    superficie_ha: superficieHa
                });
                closeSimpleModalById('modal-crear-cuartel');
                if (typeof showToastBoton === 'function') {
                    showToastBoton('success', 'Cuartel creado correctamente');
                }
                await abrirModificarProductor(productorIdReal);
            } catch (e) {
                console.error('[Relevamiento] Error al crear cuartel:', e);
                if (typeof showToastBoton === 'function') {
                    showToastBoton('error', `Error al crear cuartel: ${e.message}`);
                }
            }
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
            form.className = 'detail-panel';
            form.dataset.detailType = 'productor';
            form.dataset.detailId = 'productor';

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
            form.className = 'detail-form-panel';
            form.dataset.detailType = 'finca';

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
                const fincaId = String(block.querySelector('input[name$="[finca_id]"]')?.value ?? '').trim();

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
                    card.classList.add('detail-section');
                    card.dataset.detailType = 'finca';
                    card.dataset.detailId = fincaId;
                    sec.nodes.forEach((n) => grid.appendChild(n));
                    form.appendChild(card);
                });
            });

            return form;
        }

        function buildCuartelesCards(htmlCuarteles) {
            const frag = normalizeFragment(htmlCuarteles);
            const sourceForm = frag.querySelector('#cuarteles-form');
            if (!sourceForm) return null;

            const form = document.createElement('form');
            form.id = 'cuarteles-form';
            form.className = 'detail-form-panel';
            form.dataset.detailType = 'cuartel';

            const hiddenInputs = Array.from(sourceForm.querySelectorAll(':scope > input[type="hidden"]'));
            hiddenInputs.forEach((hidden) => form.appendChild(hidden));

            const cuartelBlocks = Array.from(sourceForm.querySelectorAll('.relevamiento-cuartel-block'));
            cuartelBlocks.forEach((block) => {
                const cuartelHeader = (block.querySelector('.relevamiento-cuartel-header')?.textContent || '').trim();
                const cuartelSub = (block.querySelector('.relevamiento-cuartel-subtitle')?.textContent || '').trim();
                const cuartelSubtitle = [cuartelHeader, cuartelSub].filter(Boolean).join(' - ');
                const cuartelId = String(block.querySelector('input[name$="[cuartel_id]"]')?.value ?? '').trim();

                Array.from(block.querySelectorAll(':scope > input[type="hidden"]')).forEach((hidden) => form.appendChild(hidden));

                const sectionNodes = toElementNodes(Array.from(block.childNodes));
                let current = null;
                const sections = [];

                sectionNodes.forEach((node) => {
                    if (
                        node.matches('input[type="hidden"]') ||
                        node.matches('.relevamiento-cuartel-header') ||
                        node.matches('.relevamiento-cuartel-subtitle') ||
                        node.matches('hr')
                    ) {
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
                    } = createTableSectionCard(sec.title, cuartelSubtitle);
                    card.classList.add('detail-section');
                    card.dataset.detailType = 'cuartel';
                    card.dataset.detailId = cuartelId;
                    sec.nodes.forEach((n) => grid.appendChild(n));
                    form.appendChild(card);
                });
            });

            return form;
        }

        async function fetchPartialHtml(controllerFile, productorIdReal) {
            const params = new URLSearchParams({
                productor_id_real: productorIdReal,
                include_archived: relevamientoShowArchived ? '1' : '0'
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
                productor_id_real: productorIdReal,
                include_archived: relevamientoShowArchived ? '1' : '0'
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

        async function archivarFincaProductor(productorIdReal, fincaId) {
            await apiPostAction('archivar_finca', {
                productor_id_real: String(productorIdReal),
                finca_id: String(fincaId)
            });
        }

        async function desarchivarFincaProductor(productorIdReal, fincaId) {
            await apiPostAction('desarchivar_finca', {
                productor_id_real: String(productorIdReal),
                finca_id: String(fincaId)
            });
        }

        async function archivarCuartelProductor(productorIdReal, cuartelId) {
            await apiPostAction('archivar_cuartel', {
                productor_id_real: String(productorIdReal),
                cuartel_id: String(cuartelId)
            });
        }

        async function desarchivarCuartelProductor(productorIdReal, cuartelId) {
            await apiPostAction('desarchivar_cuartel', {
                productor_id_real: String(productorIdReal),
                cuartel_id: String(cuartelId)
            });
        }

        function safeJsValue(value) {
            return String(value ?? '').replaceAll('\\', '\\\\').replaceAll("'", "\\'");
        }

        function getAssetSelectionTitle(type, id) {
            const root = document.getElementById('asset-nav');
            const btn = Array.from(root?.querySelectorAll('[data-asset-type]') || [])
                .find((item) => item.dataset.assetType === String(type) && String(item.dataset.assetId) === String(id));
            return btn?.querySelector('.asset-node-title')?.textContent?.trim() || btn?.textContent?.trim() || '';
        }

        function buildDetailTabs(cards) {
            const tabs = document.getElementById('detail-tabs');
            if (!tabs) return;

            tabs.innerHTML = '';
            cards.forEach((card, index) => {
                const title = card.querySelector('.table-section-title')?.textContent?.trim() || `Seccion ${index + 1}`;
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = title;
                btn.addEventListener('click', () => {
                    card.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                });
                tabs.appendChild(btn);
            });
        }

        function selectAssetDetail(type, id) {
            currentAssetSelection = {
                type: String(type),
                id: String(id)
            };

            const detailTitle = document.getElementById('asset-detail-title');
            const empty = document.getElementById('asset-empty-state');
            const familiaForm = document.getElementById('familia-form');
            const produccionForm = document.getElementById('produccion-form');
            const cuartelesForm = document.getElementById('cuarteles-form');

            if (familiaForm) familiaForm.hidden = type !== 'productor';
            if (produccionForm) produccionForm.hidden = type !== 'finca';
            if (cuartelesForm) cuartelesForm.hidden = type !== 'cuartel';

            document.querySelectorAll('#productor-modificar-view .detail-section').forEach((card) => {
                card.hidden = !(card.dataset.detailType === type && String(card.dataset.detailId) === String(id));
            });

            const visibleCards = Array.from(document.querySelectorAll('#productor-modificar-view .detail-section:not([hidden])'));
            if (empty) empty.hidden = type === 'productor' || visibleCards.length > 0;

            if (detailTitle) {
                detailTitle.textContent = getAssetSelectionTitle(type, id) || 'Datos del productor';
            }

            buildDetailTabs(type === 'productor' ? Array.from(familiaForm?.querySelectorAll('.table-section-card') || []) : visibleCards);

            document.querySelectorAll('#asset-nav [data-asset-type]').forEach((btn) => {
                btn.classList.toggle('is-active', btn.dataset.assetType === String(type) && String(btn.dataset.assetId) === String(id));
            });
        }

        function filterAssetTree(query) {
            const q = String(query ?? '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
            document.querySelectorAll('#asset-nav [data-search-text]').forEach((row) => {
                const text = String(row.dataset.searchText || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                row.hidden = q !== '' && !text.includes(q);
            });
        }

        function renderResumenActivosProductor(slot, productorIdReal, resumen) {
            if (!slot) return;

            const fincas = Array.isArray(resumen?.fincas) ? resumen.fincas : [];
            const cuarteles = Array.isArray(resumen?.cuarteles) ? resumen.cuarteles : [];

            const cuartelesByFinca = new Map();
            cuarteles.forEach((c) => {
                const fincaId = String(c.finca_id ?? '');
                if (!cuartelesByFinca.has(fincaId)) cuartelesByFinca.set(fincaId, []);
                cuartelesByFinca.get(fincaId).push(c);
            });

            const productorIdJs = safeJsValue(productorIdReal);
            const actionIconBtn = (onclickJs, title, icon) => `
                <button type="button" class="btn-icon" onclick="${onclickJs}" title="${title}" aria-label="${title}">
                    <span class="material-symbols-outlined">${icon}</span>
                </button>
            `;

            const renderFinca = (f) => {
                const id = String(f.id || '');
                const codeRaw = String(f.codigo_finca || `ID ${id}`);
                const nameRaw = String(f.nombre_finca || 'Sin nombre');
                const code = escapeHtml(codeRaw);
                const name = escapeHtml(nameRaw);
                const archivado = Number(f?.archivado ?? 0) === 1;
                const children = cuartelesByFinca.get(id) || [];
                const childSearches = [];
                const childrenHtml = children.map((c) => {
                    const cid = String(c.id || '');
                    const ccodeRaw = String(c.codigo_cuartel || `ID ${cid}`);
                    const variedadRaw = String(c.variedad || 'Sin nombre');
                    const ccode = escapeHtml(ccodeRaw);
                    const variedad = escapeHtml(variedadRaw);
                    const sup = escapeHtml(c.superficie_ha || 'Sin superficie');
                    const cArchivado = Number(c?.archivado ?? 0) === 1;
                    const searchRaw = `${ccodeRaw} ${variedadRaw} ${sup} ${codeRaw} ${nameRaw}`;
                    childSearches.push(searchRaw);
                    const search = escapeHtml(searchRaw);
                    const archiveAction = cArchivado ?
                        actionIconBtn(`confirmarDesarchivarCuartel('${productorIdJs}','${safeJsValue(cid)}')`, 'Desarchivar cuartel', 'unarchive') :
                        actionIconBtn(`confirmarArchivarCuartel('${productorIdJs}','${safeJsValue(cid)}')`, 'Archivar cuartel', 'archive');

                    return `
                        <div class="asset-row" data-search-text="${search}">
                            <button type="button" class="asset-child${cArchivado ? ' is-active' : ''}" data-asset-type="cuartel" data-asset-id="${escapeHtml(cid)}" onclick="selectAssetDetail('cuartel', '${safeJsValue(cid)}')">
                                <span class="asset-node-title">Cuartel: ${variedad}</span>
                                <span class="asset-node-meta">Código: ${ccode} · Superficie: ${sup} ha · ${cArchivado ? 'Archivado' : 'Activo'}</span>
                            </button>
                            <div class="asset-actions">${archiveAction}</div>
                        </div>
                    `;
                }).join('');
                const search = escapeHtml(`${codeRaw} ${nameRaw} ${childSearches.join(' ')}`);
                const fincaArchiveAction = archivado ?
                    actionIconBtn(`confirmarDesarchivarFinca('${productorIdJs}','${safeJsValue(id)}')`, 'Desarchivar finca', 'unarchive') :
                    actionIconBtn(`confirmarArchivarFinca('${productorIdJs}','${safeJsValue(id)}')`, 'Archivar finca', 'archive');
                const fincaCreateCuartelAction = actionIconBtn(`promptCrearCuartel('${productorIdJs}','${safeJsValue(id)}')`, 'Nuevo cuartel', 'add');

                return `
                    <div class="asset-tree-item asset-tree-group" data-search-text="${search}">
                        <div class="asset-row">
                            <button type="button" class="asset-node${archivado ? ' is-active' : ''}" data-asset-type="finca" data-asset-id="${escapeHtml(id)}" onclick="selectAssetDetail('finca', '${safeJsValue(id)}')">
                                <span class="asset-node-title">Finca: ${name}</span>
                                <span class="asset-node-meta">Código: ${code} · ${children.length} cuartel(es) · ${archivado ? 'Archivada' : 'Activa'}</span>
                            </button>
                            <div class="asset-actions">
                                ${fincaCreateCuartelAction}
                                ${fincaArchiveAction}
                            </div>
                        </div>
                        <p class="asset-section-label">Cuarteles</p>
                        <div class="asset-children">
                            ${childrenHtml || '<span class="summary-empty">Sin cuarteles.</span>'}
                        </div>
                    </div>
                `;
            };

            const sinFinca = cuartelesByFinca.get('') || [];
            const sinFincaHtml = sinFinca.map((c) => {
                const cid = String(c.id || '');
                const ccodeRaw = String(c.codigo_cuartel || `ID ${cid}`);
                const variedadRaw = String(c.variedad || 'Sin nombre');
                const ccode = escapeHtml(ccodeRaw);
                const variedad = escapeHtml(variedadRaw);
                const archivado = Number(c?.archivado ?? 0) === 1;
                const search = escapeHtml(`${ccodeRaw} ${variedadRaw}`);
                const archiveAction = archivado ?
                    actionIconBtn(`confirmarDesarchivarCuartel('${productorIdJs}','${safeJsValue(cid)}')`, 'Desarchivar cuartel', 'unarchive') :
                    actionIconBtn(`confirmarArchivarCuartel('${productorIdJs}','${safeJsValue(cid)}')`, 'Archivar cuartel', 'archive');
                return `
                    <div class="asset-row" data-search-text="${search}">
                        <button type="button" class="asset-child${archivado ? ' is-active' : ''}" data-asset-type="cuartel" data-asset-id="${escapeHtml(cid)}" onclick="selectAssetDetail('cuartel', '${safeJsValue(cid)}')">
                            <span class="asset-node-title">Cuartel: ${variedad}</span>
                            <span class="asset-node-meta">Código: ${ccode} · Sin finca vinculada · ${archivado ? 'Archivado' : 'Activo'}</span>
                        </button>
                        <div class="asset-actions">${archiveAction}</div>
                    </div>
                `;
            }).join('');

            const nuevaFincaAction = actionIconBtn(`promptCrearFinca('${productorIdJs}')`, 'Nueva finca', 'add');

            slot.innerHTML = `
                <input class="asset-search" type="search" placeholder="Buscar finca o cuartel" oninput="filterAssetTree(this.value)">
                <div class="summary-meta">
                    <span>${fincas.length} finca(s)</span>
                    <span>${cuarteles.length} cuartel(es)</span>
                </div>
                <div class="asset-actions" style="margin-bottom:.65rem;">
                    ${nuevaFincaAction}
                </div>
                <div class="asset-tree">
                    <div class="asset-tree-item" data-search-text="productor datos personales familia">
                        <button type="button" class="asset-node" data-asset-type="productor" data-asset-id="productor" onclick="selectAssetDetail('productor', 'productor')">
                            <span class="asset-node-title">Datos del productor</span>
                            <span class="asset-node-meta">Familia, contacto y razón social</span>
                        </button>
                    </div>
                    ${fincas.length ? fincas.map(renderFinca).join('') : '<p class="summary-empty">Sin fincas asociadas.</p>'}
                    ${sinFincaHtml ? `<div class="asset-tree-item asset-tree-group"><p class="asset-section-label">Cuarteles sin finca</p><div class="asset-children">${sinFincaHtml}</div></div>` : ''}
                </div>
            `;

            selectAssetDetail(currentAssetSelection.type || 'productor', currentAssetSelection.id || 'productor');
        }

        async function cargarResumenActivosProductor(productorIdReal) {
            const slot = document.querySelector('#asset-nav');
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

        async function confirmarArchivarFinca(productorIdReal, fincaId) {
            openConfirmActionModal(
                'Archivar finca',
                `Se archivara la finca ID ${fincaId} y sus cuarteles.`,
                async () => {
                    try {
                        await archivarFincaProductor(productorIdReal, fincaId);
                        if (typeof showToastBoton === 'function') {
                            showToastBoton('success', `Finca ${fincaId} archivada correctamente`);
                        }
                        await abrirModificarProductor(productorIdReal);
                    } catch (e) {
                        console.error('[Relevamiento] Error al archivar finca:', e);
                        if (typeof showToastBoton === 'function') {
                            showToastBoton('error', `Error al archivar finca: ${e.message}`);
                        }
                    }
                }
            );
        }

        async function confirmarDesarchivarFinca(productorIdReal, fincaId) {
            openConfirmActionModal(
                'Desarchivar finca',
                `Se desarchivara la finca ID ${fincaId}.`,
                async () => {
                    try {
                        await desarchivarFincaProductor(productorIdReal, fincaId);
                        if (typeof showToastBoton === 'function') {
                            showToastBoton('success', `Finca ${fincaId} desarchivada correctamente`);
                        }
                        await abrirModificarProductor(productorIdReal);
                    } catch (e) {
                        console.error('[Relevamiento] Error al desarchivar finca:', e);
                        if (typeof showToastBoton === 'function') {
                            showToastBoton('error', `Error al desarchivar finca: ${e.message}`);
                        }
                    }
                }
            );
        }

        async function confirmarArchivarCuartel(productorIdReal, cuartelId) {
            openConfirmActionModal(
                'Archivar cuartel',
                `Se archivara el cuartel ID ${cuartelId}.`,
                async () => {
                    try {
                        await archivarCuartelProductor(productorIdReal, cuartelId);
                        if (typeof showToastBoton === 'function') {
                            showToastBoton('success', `Cuartel ${cuartelId} archivado correctamente`);
                        }
                        await abrirModificarProductor(productorIdReal);
                    } catch (e) {
                        console.error('[Relevamiento] Error al archivar cuartel:', e);
                        if (typeof showToastBoton === 'function') {
                            showToastBoton('error', `Error al archivar cuartel: ${e.message}`);
                        }
                    }
                }
            );
        }

        async function confirmarDesarchivarCuartel(productorIdReal, cuartelId) {
            openConfirmActionModal(
                'Desarchivar cuartel',
                `Se desarchivara el cuartel ID ${cuartelId}.`,
                async () => {
                    try {
                        await desarchivarCuartelProductor(productorIdReal, cuartelId);
                        if (typeof showToastBoton === 'function') {
                            showToastBoton('success', `Cuartel ${cuartelId} desarchivado correctamente`);
                        }
                        await abrirModificarProductor(productorIdReal);
                    } catch (e) {
                        console.error('[Relevamiento] Error al desarchivar cuartel:', e);
                        if (typeof showToastBoton === 'function') {
                            showToastBoton('error', `Error al desarchivar cuartel: ${e.message}`);
                        }
                    }
                }
            );
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
            const archivado = Number(productor?.archivado ?? 0) === 1;

            setCardsTitle(`Modificar productor ${idReal}`);
            currentAssetSelection = {
                type: 'productor',
                id: 'productor'
            };
            container.innerHTML = `
                <div class="card">
                    <div class="productor-edit-toolbar">
                        <button class="btn-icon" onclick="volverAProductores()" title="Volver a productores" aria-label="Volver a productores"><span class="material-symbols-outlined">arrow_back</span></button>
                        <div class="productor-edit-summary">
                            <strong>${nombre}</strong>
                            <span>ID: ${idReal}</span>
                            <span id="productor-summary-cuit">CUIT: ${cuit}</span>
                            <span>Estado: ${archivado ? 'Archivado' : 'Activo'}</span>
                        </div>
                        <div class="form-buttons" style="margin-top:0;">
                            <button class="btn-icon" onclick="${archivado ? `confirmarDesarchivarProductor('${idRealJs}')` : `confirmarArchivarProductor('${idRealJs}')`}" title="${archivado ? 'Desarchivar productor' : 'Archivar productor'}" aria-label="${archivado ? 'Desarchivar productor' : 'Archivar productor'}"><span class="material-symbols-outlined">${archivado ? 'unarchive' : 'archive'}</span></button>
                            <button class="btn btn-aceptar" onclick="guardarTodoDesdeVista('${idRealJs}')">Guardar cambios</button>
                        </div>
                    </div>
                    <div class="form-switch" style="margin-bottom:0;">
                        <label>
                            <input type="checkbox" id="global-advanced-toggle">
                            Mostrar campos avanzados
                        </label>
                    </div>
                </div>
                <div id="productor-modificar-view" class="asset-workspace">
                    <aside class="card asset-sidebar" id="asset-nav">
                        <p>Cargando fincas y cuarteles...</p>
                    </aside>
                    <section class="asset-detail">
                        <div class="card">
                            <div class="asset-detail-header">
                                <h3 class="asset-detail-title" id="asset-detail-title">Datos del productor</h3>
                            </div>
                            <div class="detail-tabs" id="detail-tabs"></div>
                            <p class="asset-empty" id="asset-empty-state" hidden>Sin datos para mostrar.</p>
                            <div id="asset-detail-slot"></div>
                        </div>
                    </section>
                </div>
            `;

            const slotView = container.querySelector('#asset-detail-slot');

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

        function volverACooperativas() {
            currentCoop = null;
            currentProductor = null;
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

                const cuartelesForm = document.querySelector('#productor-modificar-view #cuarteles-form');
                if (cuartelesForm) {
                    await guardarFormularioParcial(
                        'cuarteles-form',
                        'relevamiento_cuarteles_controller.php',
                        productorIdReal,
                        'Datos de cuarteles guardados correctamente'
                    );
                }

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
                productor_id_real: productorIdReal,
                include_archived: relevamientoShowArchived ? '1' : '0'
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
                productor_id_real: productorIdReal,
                include_archived: relevamientoShowArchived ? '1' : '0'
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
                productor_id_real: productorIdReal,
                include_archived: relevamientoShowArchived ? '1' : '0'
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
                    coop_id_real: coop.id_real,
                    include_archived: relevamientoShowArchived ? '1' : '0'
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
                    container.innerHTML = `
                        <div class="card">
                            <div class="productor-edit-toolbar">
                                <button class="btn-icon" onclick="volverACooperativas()" title="Volver a cooperativas" aria-label="Volver a cooperativas"><span class="material-symbols-outlined">arrow_back</span></button>
                                <h2 style="margin:0;">Productores</h2>
                                <div class="cell-actions">
                                    <button class="btn-icon" onclick="toggleMostrarArchivados()" title="${relevamientoShowArchived ? 'Ocultar archivados' : 'Mostrar archivados'}" aria-label="${relevamientoShowArchived ? 'Ocultar archivados' : 'Mostrar archivados'}"><span class="material-symbols-outlined">${relevamientoShowArchived ? 'visibility_off' : 'visibility'}</span></button>
                                    <button class="btn btn-aceptar" onclick="promptCrearProductor()">Nuevo productor</button>
                                </div>
                            </div>
                            <p>No se encontraron productores para esta cooperativa.</p>
                        </div>
                    `;
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
                    productor_id_real: productorIdReal,
                    include_archived: relevamientoShowArchived ? '1' : '0'
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
                    productor_id_real: productorIdReal,
                    include_archived: relevamientoShowArchived ? '1' : '0'
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
        window.volverACooperativas = volverACooperativas;
        window.toggleMostrarArchivados = toggleMostrarArchivados;
        window.promptCrearProductor = promptCrearProductor;
        window.promptCrearFinca = promptCrearFinca;
        window.guardarNuevoProductorDesdeModal = guardarNuevoProductorDesdeModal;
        window.guardarNuevaFincaDesdeModal = guardarNuevaFincaDesdeModal;
        window.guardarNuevoCuartelDesdeModal = guardarNuevoCuartelDesdeModal;
        window.closeSimpleModalById = closeSimpleModalById;
        window.closeConfirmActionModal = closeConfirmActionModal;
        window.runConfirmActionModal = runConfirmActionModal;
        window.promptCrearCuartel = promptCrearCuartel;
        window.confirmarArchivarProductor = confirmarArchivarProductor;
        window.confirmarDesarchivarProductor = confirmarDesarchivarProductor;
        window.selectAssetDetail = selectAssetDetail;
        window.filterAssetTree = filterAssetTree;
        window.confirmarArchivarFinca = confirmarArchivarFinca;
        window.confirmarDesarchivarFinca = confirmarDesarchivarFinca;
        window.confirmarArchivarCuartel = confirmarArchivarCuartel;
        window.confirmarDesarchivarCuartel = confirmarDesarchivarCuartel;

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

    <!-- Modal Crear Productor -->
    <div id="modal-crear-productor" class="modal hidden">
        <div class="modal-content">
            <h3>Nuevo productor</h3>
            <div class="modal-body">
                <div class="input-group">
                    <label for="nuevo-usuario">Usuario</label>
                    <div class="input-icon input-icon-name">
                        <input id="nuevo-usuario" name="nuevo_usuario" type="text" autocomplete="off" />
                    </div>
                </div>
                <div class="input-group">
                    <label for="nuevo-cuit">CUIT</label>
                    <div class="input-icon input-icon-name">
                        <input id="nuevo-cuit" name="nuevo_cuit" type="text" inputmode="numeric" autocomplete="off" />
                    </div>
                </div>
            </div>
            <div class="form-buttons">
                <button class="btn btn-aceptar" onclick="guardarNuevoProductorDesdeModal()">Crear</button>
                <button class="btn btn-cancelar" onclick="closeSimpleModalById('modal-crear-productor')">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- Modal Crear Finca -->
    <div id="modal-crear-finca" class="modal hidden">
        <div class="modal-content">
            <h3>Nueva finca</h3>
            <div class="modal-body">
                <div class="input-group">
                    <label for="nuevo-codigo-finca">Codigo de finca</label>
                    <div class="input-icon input-icon-name">
                        <input id="nuevo-codigo-finca" name="nuevo_codigo_finca" type="text" autocomplete="off" />
                    </div>
                </div>
                <div class="input-group">
                    <label for="nuevo-nombre-finca">Nombre de finca</label>
                    <div class="input-icon input-icon-name">
                        <input id="nuevo-nombre-finca" name="nuevo_nombre_finca" type="text" autocomplete="off" />
                    </div>
                </div>
            </div>
            <div class="form-buttons">
                <button class="btn btn-aceptar" onclick="guardarNuevaFincaDesdeModal()">Crear</button>
                <button class="btn btn-cancelar" onclick="closeSimpleModalById('modal-crear-finca')">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- Modal Crear Cuartel -->
    <div id="modal-crear-cuartel" class="modal hidden">
        <div class="modal-content">
            <h3>Nuevo cuartel</h3>
            <div class="modal-body">
                <div class="input-group">
                    <label for="nuevo-variedad-cuartel">Variedad</label>
                    <div class="input-icon input-icon-name">
                        <input id="nuevo-variedad-cuartel" name="nuevo_variedad_cuartel" type="text" autocomplete="off" />
                    </div>
                </div>
                <div class="input-group">
                    <label for="nuevo-superficie-cuartel">Superficie (ha)</label>
                    <div class="input-icon input-icon-name">
                        <input id="nuevo-superficie-cuartel" name="nuevo_superficie_cuartel" type="text" inputmode="decimal" autocomplete="off" />
                    </div>
                </div>
            </div>
            <div class="form-buttons">
                <button class="btn btn-aceptar" onclick="guardarNuevoCuartelDesdeModal()">Crear</button>
                <button class="btn btn-cancelar" onclick="closeSimpleModalById('modal-crear-cuartel')">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- Modal Confirmación -->
    <div id="modal-confirm-action" class="modal hidden">
        <div class="modal-content">
            <h3 data-confirm-title>Confirmar acción</h3>
            <div class="modal-body">
                <p data-confirm-message></p>
            </div>
            <div class="form-buttons">
                <button class="btn btn-aceptar" onclick="runConfirmActionModal()">Confirmar</button>
                <button class="btn btn-cancelar" onclick="closeConfirmActionModal()">Cancelar</button>
            </div>
        </div>
    </div>

