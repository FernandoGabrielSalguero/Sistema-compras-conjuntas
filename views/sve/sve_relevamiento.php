<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../middleware/authMiddleware.php';
checkAccess('sve');

$nombre = $_SESSION['nombre'] ?? 'Sin nombre';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SVE - Relevamiento</title>

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <link rel="stylesheet" href="https://framework.impulsagroup.com/assets/css/framework.css">
    <script src="https://framework.impulsagroup.com/assets/javascript/framework.js" defer></script>

    <style>
        .table-container {
            max-height: 520px;
            overflow: auto;
            border: 1px solid #ddd;
            border-radius: 0.5rem;
        }

        .table-container::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .table-container::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.25);
            border-radius: 4px;
        }

        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .kpi-item {
            border: 1px solid rgba(15, 23, 42, 0.12);
            border-radius: 0.65rem;
            padding: 0.8rem;
            background: #fff;
        }

        .kpi-item .label {
            font-size: 0.78rem;
            color: #475569;
            margin-bottom: 0.25rem;
        }

        .kpi-item .value {
            font-size: 1.3rem;
            font-weight: 700;
            color: #0f172a;
        }

        .table-tools {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 0.75rem;
            align-items: end;
        }

        .table-tools .input-group {
            margin: 0;
        }

        .pagination {
            margin-top: 0.75rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            justify-content: space-between;
            align-items: center;
        }

        .pill-ok,
        .pill-warn {
            display: inline-block;
            padding: 0.2rem 0.5rem;
            border-radius: 999px;
            font-size: 0.74rem;
            font-weight: 700;
        }

        .pill-ok {
            background: #dcfce7;
            color: #166534;
        }

        .pill-warn {
            background: #fee2e2;
            color: #991b1b;
        }

        .acciones-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 0.75rem;
        }

        .acciones-grid .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
        }

        .sve-modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1200;
            padding: 1rem;
        }

        .sve-modal.active {
            display: flex;
        }

        .sve-modal-content {
            width: min(640px, 100%);
            background: #fff;
            border-radius: 0.75rem;
            padding: 1rem;
            box-shadow: 0 18px 38px rgba(2, 6, 23, 0.22);
        }

        .sve-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .sve-modal-header h3 {
            margin: 0;
        }

        .sve-modal-close {
            border: 0;
            background: transparent;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #334155;
        }

        .sve-modal-body {
            min-height: 180px;
            border: 1px dashed rgba(15, 23, 42, 0.2);
            border-radius: 0.5rem;
            padding: 0.75rem;
            background: #f8fafc;
        }

        .sve-modal-footer {
            margin-top: 1rem;
            display: flex;
            justify-content: flex-end;
            gap: 0.65rem;
        }

        .variedades-form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .variedades-section-title {
            margin: 0 0 0.45rem 0;
            font-size: 0.98rem;
            font-weight: 700;
            color: #0f172a;
        }

        .variedades-toolbar {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 0.75rem;
        }

        .variedades-table-wrap {
            max-height: 280px;
            overflow: auto;
            border: 1px solid rgba(15, 23, 42, 0.14);
            border-radius: 0.5rem;
            background: #fff;
        }

        .variedades-table-wrap table {
            width: 100%;
            border-collapse: collapse;
        }

        .variedades-table-wrap th,
        .variedades-table-wrap td {
            padding: 0.45rem 0.55rem;
            border-bottom: 1px solid rgba(15, 23, 42, 0.08);
            text-align: left;
            font-size: 0.9rem;
        }

        .variedades-table-wrap th {
            position: sticky;
            top: 0;
            background: #f1f5f9;
            z-index: 1;
        }

        .acciones-mini {
            display: inline-flex;
            gap: 0.35rem;
            align-items: center;
        }

        .btn-mini {
            border: 1px solid rgba(15, 23, 42, 0.18);
            background: #fff;
            border-radius: 0.4rem;
            padding: 0.25rem 0.45rem;
            cursor: pointer;
            font-size: 0.78rem;
        }

        .btn-mini.danger {
            color: #b91c1c;
            border-color: rgba(185, 28, 28, 0.35);
            background: #fff5f5;
        }

        .variedades-msg {
            min-height: 20px;
            font-size: 0.82rem;
            margin-bottom: 0.5rem;
        }

        .variedades-msg.ok {
            color: #166534;
        }

        .variedades-msg.error {
            color: #b91c1c;
        }
    </style>
</head>

<body>
    <div class="layout">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <span class="material-icons logo-icon">dashboard</span>
                <span class="logo-text">SVE</span>
            </div>

            <nav class="sidebar-menu">
                <ul>
                    <li onclick="location.href='sve_dashboard.php'">
                        <span class="material-icons" style="color: #5b21b6;">home</span><span class="link-text">Inicio</span>
                    </li>
                    <li onclick="location.href='sve_consolidado.php'">
                        <span class="material-icons" style="color: #5b21b6;">analytics</span><span class="link-text">Consolidado</span>
                    </li>
                    <li onclick="location.href='sve_altausuarios.php'">
                        <span class="material-icons" style="color: #5b21b6;">person</span><span class="link-text">Alta usuarios</span>
                    </li>
                    <li onclick="location.href='sve_asociarProductores.php'">
                        <span class="material-icons" style="color: #5b21b6;">link</span><span class="link-text">Asociaciones</span>
                    </li>
                    <li onclick="location.href='sve_cargaMasiva.php'">
                        <span class="material-icons" style="color: #5b21b6;">upload_file</span><span class="link-text">Carga masiva</span>
                    </li>
                    <li onclick="location.href='sve_registro_login.php'">
                        <span class="material-icons" style="color: #5b21b6;">login</span><span class="link-text">Ingresos</span>
                    </li>
                    <li onclick="location.href='sve_operativos.php'">
                        <span class="material-icons" style="color: #5b21b6;">assignment</span><span class="link-text">Operativos</span>
                    </li>
                    <li onclick="location.href='sve_mercadodigital.php'">
                        <span class="material-icons" style="color: #5b21b6;">shopping_cart</span><span class="link-text">Mercado Digital</span>
                    </li>
                    <li onclick="location.href='sve_listadoPedidos.php'">
                        <span class="material-icons" style="color: #5b21b6;">assignment_turned_in</span><span class="link-text">Listado Pedidos</span>
                    </li>
                    <li onclick="location.href='sve_productos.php'">
                        <span class="material-icons" style="color: #5b21b6;">inventory</span><span class="link-text">Productos</span>
                    </li>
                    <li onclick="location.href='sve_pulverizacionDrone.php'">
                        <span class="material-symbols-outlined" style="color:#5b21b6;">drone</span>
                        <span class="link-text">Drones</span>
                    </li>
                    <li onclick="location.href='sve_relevamiento.php'">
                        <span class="material-icons" style="color:#5b21b6;">fact_check</span>
                        <span class="link-text">Relevamiento</span>
                    </li>
                    <li onclick="location.href='sve_cosechaMecanica.php'">
                        <span class="material-icons" style="color:#5b21b6;">agriculture</span>
                        <span class="link-text">Cosecha Mecanica</span>
                    </li>
                    <li onclick="location.href='sve_serviciosVendimiales.php'">
                        <span class="material-icons" style="color:#5b21b6;">wine_bar</span>
                        <span class="link-text">Servicios Auxiliares Enologicos</span>
                    </li>
                    <li onclick="location.href='sve_publicaciones.php'">
                        <span class="material-icons" style="color: #5b21b6;">menu_book</span><span class="link-text">Biblioteca Virtual</span>
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

        <div class="main">
            <header class="navbar">
                <button class="btn-icon" onclick="toggleSidebar()">
                    <span class="material-icons">menu</span>
                </button>
                <div class="navbar-title">Relevamiento</div>
            </header>

            <section class="content">
                <div class="card">
                    <h2>Hola, <?php echo htmlspecialchars((string)$nombre, ENT_QUOTES, 'UTF-8'); ?></h2>
                    <p>Esta vista permite relevar el estado de informacion productiva de todos los productores asociados a cooperativas.</p>

                    <div class="kpi-grid">
                        <div class="kpi-item">
                            <div class="label">Productores totales</div>
                            <div class="value" id="kpiTotal">0</div>
                        </div>
                        <div class="kpi-item">
                            <div class="label">Con fincas</div>
                            <div class="value" id="kpiFincas">0</div>
                        </div>
                        <div class="kpi-item">
                            <div class="label">Con cuarteles</div>
                            <div class="value" id="kpiCuarteles">0</div>
                        </div>
                        <div class="kpi-item">
                            <div class="label">Sin cuarteles</div>
                            <div class="value" id="kpiSinCuarteles">0</div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h2>Herramientas</h2>
                    <p>Acciones auxiliares del relevamiento.</p>
                    <div class="acciones-grid">
                        <button type="button" class="btn btn-info" id="btnCodigosVariedades">
                            <span class="material-icons">code</span>
                            <span>Codigos de variedades</span>
                        </button>
                    </div>
                </div>

                <div class="card">
                    <h2>Filtros</h2>
                    <div class="table-tools">
                        <div class="input-group">
                            <label for="filtroBusqueda">Buscar</label>
                            <div class="input-icon">
                                <span class="material-icons">search</span>
                                <input type="search" id="filtroBusqueda" placeholder="Productor, cooperativa, CUIT o ID real" />
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="filtroCoop">Cooperativa</label>
                            <div class="input-icon">
                                <span class="material-icons">apartment</span>
                                <select id="filtroCoop">
                                    <option value="">Todas las cooperativas</option>
                                </select>
                            </div>
                        </div>

                        <div class="input-group">
                            <button type="button" class="btn btn-aceptar" id="btnBuscar">Aplicar filtros</button>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h2>Resumen por cooperativa</h2>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Cooperativa</th>
                                    <th>ID Real</th>
                                    <th>Productores</th>
                                    <th>Con fincas</th>
                                    <th>Con cuarteles</th>
                                    <th>Sin cuarteles</th>
                                </tr>
                            </thead>
                            <tbody id="tablaResumenBody"></tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <h2>Listado detallado de productores</h2>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Cooperativa</th>
                                    <th>Productor</th>
                                    <th>CUIT</th>
                                    <th>ID Real</th>
                                    <th>Fincas</th>
                                    <th>Cuarteles</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="tablaListadoBody"></tbody>
                        </table>
                    </div>

                    <div class="pagination">
                        <small id="paginacionTexto">Mostrando 0 de 0</small>
                        <div>
                            <button class="btn btn-cancelar" id="btnPrev" type="button">Anterior</button>
                            <button class="btn btn-aceptar" id="btnNext" type="button">Siguiente</button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <div class="sve-modal" id="modalCodigosVariedades" aria-hidden="true" inert>
        <div class="sve-modal-content" role="dialog" aria-modal="true" aria-labelledby="modalCodigosVariedadesTitulo">
            <div class="sve-modal-header">
                <h3 id="modalCodigosVariedadesTitulo">Codigos de variedades</h3>
                <button type="button" class="sve-modal-close" id="btnCerrarModalCodigosX" aria-label="Cerrar">
                    <span class="material-icons">close</span>
                </button>
            </div>
            <div class="sve-modal-body">
                <form id="formVariedad">
                    <input type="hidden" id="variedadId" />
                    <h4 class="variedades-section-title">Agregar nueva variedad</h4>
                    <div class="variedades-form-grid">
                        <div class="input-group">
                            <label for="codigoVariedad">Codigo de variedad</label>
                            <div class="input-icon">
                                <span class="material-icons">tag</span>
                                <input type="number" id="codigoVariedad" min="1" step="1" required />
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="nombreVariedad">Nombre de variedad</label>
                            <div class="input-icon">
                                <span class="material-icons">local_florist</span>
                                <input type="text" id="nombreVariedad" maxlength="160" required />
                            </div>
                        </div>
                    </div>
                </form>
                <h4 class="variedades-section-title">Buscar variedad</h4>
                <div class="variedades-toolbar">
                    <div class="input-group" style="margin:0; flex:1 1 240px;">
                        <div class="input-icon">
                            <span class="material-icons">search</span>
                            <input type="search" id="buscarVariedad" placeholder="Buscar por codigo o nombre" />
                        </div>
                    </div>
                </div>
                <div id="msgVariedades" class="variedades-msg"></div>
                <div class="variedades-table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Codigo</th>
                                <th>Nombre</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaVariedadesBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="sve-modal-footer">
                <button type="button" class="btn btn-cancelar" id="btnCerrarModalCodigos">Cerrar</button>
                <button type="button" class="btn btn-aceptar" id="btnGuardarModalCodigos">Guardar</button>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const API_URL = '../../controllers/sve_relevamientoController.php';
            const perPage = 20;
            let page = 1;
            let totalPages = 1;
            let lastFocusedElement = null;

            const $ = (id) => document.getElementById(id);

            const ui = {
                filtroBusqueda: $('filtroBusqueda'),
                filtroCoop: $('filtroCoop'),
                btnBuscar: $('btnBuscar'),
                tablaResumenBody: $('tablaResumenBody'),
                tablaListadoBody: $('tablaListadoBody'),
                paginacionTexto: $('paginacionTexto'),
                btnPrev: $('btnPrev'),
                btnNext: $('btnNext'),
                kpiTotal: $('kpiTotal'),
                kpiFincas: $('kpiFincas'),
                kpiCuarteles: $('kpiCuarteles'),
                kpiSinCuarteles: $('kpiSinCuarteles'),
                btnCodigosVariedades: $('btnCodigosVariedades'),
                modalCodigosVariedades: $('modalCodigosVariedades'),
                btnCerrarModalCodigosX: $('btnCerrarModalCodigosX'),
                btnCerrarModalCodigos: $('btnCerrarModalCodigos'),
                btnGuardarModalCodigos: $('btnGuardarModalCodigos'),
                formVariedad: $('formVariedad'),
                variedadId: $('variedadId'),
                codigoVariedad: $('codigoVariedad'),
                nombreVariedad: $('nombreVariedad'),
                buscarVariedad: $('buscarVariedad'),
                tablaVariedadesBody: $('tablaVariedadesBody'),
                msgVariedades: $('msgVariedades')
            };

            function escapeHtml(text) {
                return String(text ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            async function getJson(params) {
                const url = new URL(API_URL, window.location.href);
                Object.keys(params).forEach((k) => {
                    if (params[k] !== undefined && params[k] !== null) {
                        url.searchParams.set(k, String(params[k]));
                    }
                });

                const res = await fetch(url.toString(), { credentials: 'same-origin' });
                if (!res.ok) {
                    throw new Error('HTTP ' + res.status);
                }

                const raw = await res.text();
                const normalized = raw.replace(/^\uFEFF/, '');
                let payload;
                try {
                    payload = JSON.parse(normalized);
                } catch (e) {
                    throw new Error('Respuesta JSON inválida del servidor');
                }
                if (!payload.ok) {
                    throw new Error(payload.error || 'No se pudo cargar la informacion');
                }

                return payload;
            }

            async function postJson(params) {
                const body = new URLSearchParams();
                Object.keys(params).forEach((k) => {
                    if (params[k] !== undefined && params[k] !== null) {
                        body.set(k, String(params[k]));
                    }
                });

                const res = await fetch(API_URL, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                    },
                    body: body.toString()
                });

                const raw = await res.text();
                const normalized = raw.replace(/^\uFEFF/, '');
                let payload;
                try {
                    payload = JSON.parse(normalized);
                } catch (e) {
                    throw new Error('Respuesta JSON invalida del servidor');
                }

                if (!payload.ok) {
                    throw new Error(payload.error || 'No se pudo procesar la solicitud');
                }

                return payload;
            }

            function renderResumen(rows) {
                if (!Array.isArray(rows) || rows.length === 0) {
                    ui.tablaResumenBody.innerHTML = '<tr><td colspan="6">No hay datos para mostrar.</td></tr>';
                    return;
                }

                ui.tablaResumenBody.innerHTML = rows.map((row) => {
                    const total = Number(row.productores_total || 0);
                    const conCuarteles = Number(row.productores_con_cuarteles || 0);
                    const sinCuarteles = Math.max(0, total - conCuarteles);

                    return `
                        <tr>
                            <td>${escapeHtml(row.cooperativa_nombre || '-')}</td>
                            <td>${escapeHtml(row.cooperativa_id_real || '-')}</td>
                            <td>${total}</td>
                            <td>${Number(row.productores_con_fincas || 0)}</td>
                            <td>${conCuarteles}</td>
                            <td>${sinCuarteles}</td>
                        </tr>
                    `;
                }).join('');
            }

            function renderListado(rows, pagination) {
                if (!Array.isArray(rows) || rows.length === 0) {
                    ui.tablaListadoBody.innerHTML = '<tr><td colspan="7">No se encontraron productores con esos filtros.</td></tr>';
                } else {
                    ui.tablaListadoBody.innerHTML = rows.map((row) => {
                        const fincas = Number(row.fincas_count || 0);
                        const cuarteles = Number(row.cuarteles_count || 0);
                        const estado = cuarteles > 0
                            ? '<span class="pill-ok">Completo</span>'
                            : '<span class="pill-warn">Incompleto</span>';

                        return `
                            <tr>
                                <td>${escapeHtml(row.cooperativa_nombre || '-')}</td>
                                <td>${escapeHtml(row.productor_nombre || '-')}</td>
                                <td>${escapeHtml(row.productor_cuit || '-')}</td>
                                <td>${escapeHtml(row.productor_id_real || '-')}</td>
                                <td>${fincas}</td>
                                <td>${cuarteles}</td>
                                <td>${estado}</td>
                            </tr>
                        `;
                    }).join('');
                }

                const total = Number(pagination.total || 0);
                const currentPage = Number(pagination.page || 1);
                const pp = Number(pagination.per_page || perPage);
                const from = total === 0 ? 0 : ((currentPage - 1) * pp) + 1;
                const to = Math.min(total, currentPage * pp);

                ui.paginacionTexto.textContent = `Mostrando ${from}-${to} de ${total}`;

                page = currentPage;
                totalPages = Number(pagination.total_pages || 1);
                ui.btnPrev.disabled = page <= 1;
                ui.btnNext.disabled = page >= totalPages;
            }

            function actualizarKpis(rowsResumen) {
                const accum = rowsResumen.reduce((acc, row) => {
                    const total = Number(row.productores_total || 0);
                    const conFincas = Number(row.productores_con_fincas || 0);
                    const conCuarteles = Number(row.productores_con_cuarteles || 0);

                    acc.total += total;
                    acc.conFincas += conFincas;
                    acc.conCuarteles += conCuarteles;
                    return acc;
                }, { total: 0, conFincas: 0, conCuarteles: 0 });

                ui.kpiTotal.textContent = String(accum.total);
                ui.kpiFincas.textContent = String(accum.conFincas);
                ui.kpiCuarteles.textContent = String(accum.conCuarteles);
                ui.kpiSinCuarteles.textContent = String(Math.max(0, accum.total - accum.conCuarteles));
            }

            function poblarSelectCoops(rowsResumen) {
                const currentValue = ui.filtroCoop.value;
                const options = ['<option value="">Todas las cooperativas</option>'];

                rowsResumen.forEach((row) => {
                    options.push(`<option value="${escapeHtml(row.cooperativa_id_real)}">${escapeHtml(row.cooperativa_nombre || row.cooperativa_id_real)}</option>`);
                });

                ui.filtroCoop.innerHTML = options.join('');

                if (currentValue && rowsResumen.some((r) => String(r.cooperativa_id_real) === currentValue)) {
                    ui.filtroCoop.value = currentValue;
                }
            }

            async function cargarResumen() {
                const q = ui.filtroBusqueda.value.trim();
                const payload = await getJson({ action: 'resumen', q });
                const rows = Array.isArray(payload.data) ? payload.data : [];

                renderResumen(rows);
                actualizarKpis(rows);
                poblarSelectCoops(rows);
            }

            async function cargarListado() {
                const q = ui.filtroBusqueda.value.trim();
                const coop = ui.filtroCoop.value.trim();

                const payload = await getJson({
                    action: 'list',
                    q,
                    coop_id_real: coop,
                    page,
                    per_page: perPage,
                });

                renderListado(payload.data || [], payload.pagination || {});
            }

            async function ejecutarBusqueda(resetPage) {
                if (resetPage) {
                    page = 1;
                }

                try {
                    await cargarResumen();
                    await cargarListado();
                } catch (error) {
                    ui.tablaResumenBody.innerHTML = `<tr><td colspan="6">${escapeHtml(error.message)}</td></tr>`;
                    ui.tablaListadoBody.innerHTML = `<tr><td colspan="7">${escapeHtml(error.message)}</td></tr>`;
                }
            }

            function abrirModalCodigosVariedades() {
                lastFocusedElement = document.activeElement;
                ui.modalCodigosVariedades.removeAttribute('inert');
                ui.modalCodigosVariedades.classList.add('active');
                ui.modalCodigosVariedades.setAttribute('aria-hidden', 'false');
                limpiarFormVariedad();
                cargarVariedades();
                setTimeout(() => {
                    ui.codigoVariedad.focus();
                }, 0);
            }

            function cerrarModalCodigosVariedades() {
                if (ui.modalCodigosVariedades.contains(document.activeElement)) {
                    if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
                        lastFocusedElement.focus();
                    } else {
                        ui.btnCodigosVariedades.focus();
                    }
                }
                ui.modalCodigosVariedades.classList.remove('active');
                ui.modalCodigosVariedades.setAttribute('aria-hidden', 'true');
                ui.modalCodigosVariedades.setAttribute('inert', '');
            }

            function setMsgVariedades(msg, type) {
                ui.msgVariedades.className = 'variedades-msg' + (type ? (' ' + type) : '');
                ui.msgVariedades.textContent = msg || '';
            }

            function limpiarFormVariedad() {
                ui.variedadId.value = '';
                ui.codigoVariedad.value = '';
                ui.nombreVariedad.value = '';
                ui.btnGuardarModalCodigos.textContent = 'Guardar';
                setMsgVariedades('', '');
            }

            function renderTablaVariedades(rows) {
                if (!Array.isArray(rows) || rows.length === 0) {
                    ui.tablaVariedadesBody.innerHTML = '<tr><td colspan="3">No hay variedades cargadas.</td></tr>';
                    return;
                }

                ui.tablaVariedadesBody.innerHTML = rows.map((row) => `
                    <tr>
                        <td>${escapeHtml(row.codigo_variedad)}</td>
                        <td>${escapeHtml(row.nombre_variedad)}</td>
                        <td>
                            <div class="acciones-mini">
                                <button type="button" class="btn-mini" data-edit-variedad='${escapeHtml(JSON.stringify(row))}'>Editar</button>
                                <button type="button" class="btn-mini danger" data-delete-variedad="${escapeHtml(row.id)}">Eliminar</button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            }

            async function cargarVariedades() {
                const q = ui.buscarVariedad.value.trim();
                try {
                    const payload = await getJson({ action: 'variedades_list', q });
                    renderTablaVariedades(payload.data || []);
                } catch (error) {
                    ui.tablaVariedadesBody.innerHTML = `<tr><td colspan="3">${escapeHtml(error.message)}</td></tr>`;
                }
            }

            async function guardarVariedad() {
                const id = ui.variedadId.value.trim();
                const codigo = ui.codigoVariedad.value.trim();
                const nombre = ui.nombreVariedad.value.trim();

                if (!codigo || !nombre) {
                    setMsgVariedades('Completa codigo y nombre de variedad.', 'error');
                    return;
                }

                try {
                    if (id) {
                        await postJson({
                            action: 'variedades_update',
                            id,
                            codigo_variedad: codigo,
                            nombre_variedad: nombre
                        });
                        setMsgVariedades('Variedad actualizada correctamente.', 'ok');
                    } else {
                        await postJson({
                            action: 'variedades_create',
                            codigo_variedad: codigo,
                            nombre_variedad: nombre
                        });
                        setMsgVariedades('Variedad creada correctamente.', 'ok');
                    }

                    limpiarFormVariedad();
                    await cargarVariedades();
                } catch (error) {
                    setMsgVariedades(error.message, 'error');
                }
            }

            ui.btnBuscar.addEventListener('click', () => ejecutarBusqueda(true));
            ui.filtroCoop.addEventListener('change', () => ejecutarBusqueda(true));
            ui.filtroBusqueda.addEventListener('keydown', (ev) => {
                if (ev.key === 'Enter') {
                    ev.preventDefault();
                    ejecutarBusqueda(true);
                }
            });

            ui.btnPrev.addEventListener('click', () => {
                if (page > 1) {
                    page -= 1;
                    ejecutarBusqueda(false);
                }
            });

            ui.btnNext.addEventListener('click', () => {
                if (page < totalPages) {
                    page += 1;
                    ejecutarBusqueda(false);
                }
            });

            ui.btnCodigosVariedades.addEventListener('click', abrirModalCodigosVariedades);
            ui.btnCerrarModalCodigosX.addEventListener('click', cerrarModalCodigosVariedades);
            ui.btnCerrarModalCodigos.addEventListener('click', cerrarModalCodigosVariedades);
            ui.btnGuardarModalCodigos.addEventListener('click', guardarVariedad);
            ui.formVariedad.addEventListener('submit', (ev) => {
                ev.preventDefault();
                guardarVariedad();
            });
            ui.buscarVariedad.addEventListener('input', () => {
                const q = ui.buscarVariedad.value.trim();
                if (q.length === 0 || q.length >= 3) {
                    setMsgVariedades('', '');
                    cargarVariedades();
                    return;
                }
                setMsgVariedades('Escribi al menos 3 caracteres para buscar.', '');
            });

            ui.tablaVariedadesBody.addEventListener('click', async (ev) => {
                const editBtn = ev.target.closest('[data-edit-variedad]');
                if (editBtn) {
                    const row = JSON.parse(editBtn.getAttribute('data-edit-variedad'));
                    ui.variedadId.value = String(row.id || '');
                    ui.codigoVariedad.value = String(row.codigo_variedad || '');
                    ui.nombreVariedad.value = String(row.nombre_variedad || '');
                    ui.btnGuardarModalCodigos.textContent = 'Guardar cambios';
                    setMsgVariedades('', '');
                    return;
                }

                const deleteBtn = ev.target.closest('[data-delete-variedad]');
                if (deleteBtn) {
                    const id = deleteBtn.getAttribute('data-delete-variedad');
                    if (!id) return;
                    if (!confirm('¿Eliminar esta variedad?')) return;

                    try {
                        await postJson({
                            action: 'variedades_delete',
                            id
                        });
                        setMsgVariedades('Variedad eliminada correctamente.', 'ok');
                        if (ui.variedadId.value === String(id)) {
                            limpiarFormVariedad();
                        }
                        await cargarVariedades();
                    } catch (error) {
                        setMsgVariedades(error.message, 'error');
                    }
                }
            });

            ui.modalCodigosVariedades.addEventListener('click', (ev) => {
                if (ev.target === ui.modalCodigosVariedades) {
                    cerrarModalCodigosVariedades();
                }
            });

            document.addEventListener('keydown', (ev) => {
                if (ev.key === 'Escape' && ui.modalCodigosVariedades.classList.contains('active')) {
                    cerrarModalCodigosVariedades();
                }
            });

            ejecutarBusqueda(true);
        })();
    </script>

    <script src="../../views/partials/spinner-global.js"></script>
</body>

</html>
