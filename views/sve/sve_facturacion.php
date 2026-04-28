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
    <title>SVE - Facturaci&oacute;n</title>

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

    <link rel="stylesheet" href="https://framework.impulsagroup.com/assets/css/framework.css">
    <script src="https://framework.impulsagroup.com/assets/javascript/framework.js" defer></script>

    <style>
        .table-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .table-header h2 {
            margin: 0;
        }

        .icon-export-btn {
            border: none;
            background: rgba(37, 99, 235, 0.08);
            color: #2563eb;
            cursor: pointer;
            padding: 0.35rem;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
        }

        .icon-export-btn:focus-visible {
            outline: 2px solid #5b21b6;
            outline-offset: 2px;
        }

        .icon-export-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.15);
        }

        .icon-export-btn .material-icons {
            font-size: 20px;
        }

        .table-meta {
            margin-top: 0.35rem;
            margin-bottom: 0.6rem;
            color: #4b5563;
            font-size: 0.95rem;
        }

        .table-meta-sep {
            margin: 0 0.35rem;
            color: #9ca3af;
        }

        .table-scroll {
            --row-height: 48px;
            max-height: calc((var(--row-height) * 10) + 56px);
            overflow: auto;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
        }

        .table-scroll .data-table thead th {
            position: sticky;
            top: 0;
            background: #fff;
            z-index: 2;
        }

        .table-scroll .data-table.fincas-operativos-table {
            table-layout: auto;
            width: max-content;
            min-width: 100%;
        }

        .table-scroll .data-table.fincas-operativos-table th,
        .table-scroll .data-table.fincas-operativos-table td {
            white-space: normal;
            vertical-align: top;
        }

        .cell-wrap-3 {
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .badge-tipo {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.2rem 0.55rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-externo {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-interno {
            background: #dcfce7;
            color: #166534;
        }

        .badge-estado {
            display: inline-flex;
            align-items: center;
            padding: 0.2rem 0.55rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-estado-completada {
            background: #dcfce7;
            color: #166534;
        }

        .badge-estado-cancelada {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-estado-proceso {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-estado-pendiente {
            background: #fef3c7;
            color: #92400e;
        }

        @media (max-width: 768px) {
            .table-scroll .data-table {
                min-width: 720px;
            }
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
                        <span class="link-text">Cosecha Mec&aacute;nica</span>
                    </li>
                    <li onclick="location.href='sve_serviciosVendimiales.php'">
                        <span class="material-icons" style="color:#5b21b6;">wine_bar</span>
                        <span class="link-text">Servicios Auxiliares Enol&oacute;gicos</span>
                    </li>
                    <li onclick="location.href='sve_facturacion.php'">
                        <span class="material-icons" style="color:#5b21b6;">receipt_long</span>
                        <span class="link-text">Facturaci&oacute;n</span>
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
                <div class="navbar-title">Facturaci&oacute;n</div>
            </header>

            <section class="content">
                <div class="card">
                    <h2>Facturaci&oacute;n</h2>
                    <p>Hola <?php echo htmlspecialchars($nombre); ?>. Este modulo queda conectado para comenzar a generar la facturacion de servicios.</p>
                    <p id="estadoModulo">Cargando modulo...</p>
                </div>

                <div class="card tabla-card">
                    <div class="table-header">
                        <h2>Cosecha Mecanica</h2>
                        <button id="btnExportFincas" type="button" class="icon-export-btn" title="Descargar Excel" aria-label="Descargar Excel">
                            <span class="material-icons">download</span>
                        </button>
                    </div>
                    <div class="table-meta">
                        <strong>Registros:</strong> <span id="fincas-count">0</span>
                        <span class="table-meta-sep">|</span>
                        <strong>Realizados:</strong> <span id="fincas-done-count">0</span>
                        <span class="table-meta-sep">|</span>
                        <strong>Pendientes:</strong> <span id="fincas-pending-count">0</span>
                    </div>
                    <div class="tabla-wrapper table-scroll">
                        <table class="data-table fincas-operativos-table" aria-label="Fincas participantes de operativos">
                            <thead>
                                <tr>
                                    <th>Variedad</th>
                                    <th>Cooperativa</th>
                                    <th>Productor</th>
                                    <th>Tipo</th>
                                    <th>Finca</th>
                                    <th>Superficie (ha)</th>
                                </tr>
                            </thead>
                            <tbody id="fincas-table-body">
                                <tr>
                                    <td colspan="6">Cargando fincas...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card tabla-card">
                    <div class="table-header">
                        <h2>Pulverizaci&oacute;n con drones</h2>
                        <button id="btnExportDrones" type="button" class="icon-export-btn" title="Descargar Excel" aria-label="Descargar Excel">
                            <span class="material-icons">download</span>
                        </button>
                    </div>
                    <div class="table-meta">
                        <strong>Registros:</strong> <span id="drones-count">0</span>
                        <span class="table-meta-sep">|</span>
                        <strong>Completadas:</strong> <span id="drones-done-count">0</span>
                        <span class="table-meta-sep">|</span>
                        <strong>Pendientes:</strong> <span id="drones-pending-count">0</span>
                    </div>
                    <div class="tabla-wrapper table-scroll">
                        <table class="data-table fincas-operativos-table" aria-label="Solicitudes de pulverizacion con drones">
                            <thead>
                                <tr>
                                    <th>Solicitud</th>
                                    <th>Productor</th>
                                    <th>Estado</th>
                                    <th>Piloto</th>
                                    <th>Fecha servicio</th>
                                    <th>Superficie (ha)</th>
                                    <th>Costo total</th>
                                </tr>
                            </thead>
                            <tbody id="drones-table-body">
                                <tr>
                                    <td colspan="7">Cargando solicitudes...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script>
        const API_URL = '../../controllers/sve_facturacionController.php';
        let latestFincasRows = [];
        let latestDronesRows = [];

        function showUserAlert(type, message) {
            if (typeof showAlert === 'function') {
                showAlert(type, message);
                return;
            }
            console.log(`[${type}] ${message}`);
        }

        function aplicarSaltoTerceraPalabra(td, valor) {
            const texto = String(valor ?? '').trim();
            if (!texto) {
                td.textContent = '-';
                return;
            }

            const palabras = texto.split(/\s+/);
            if (palabras.length <= 3) {
                td.textContent = texto;
                return;
            }

            td.textContent = '';
            td.appendChild(document.createTextNode(palabras.slice(0, 3).join(' ')));
            td.appendChild(document.createElement('br'));
            td.appendChild(document.createTextNode(palabras.slice(3).join(' ')));
        }

        function actualizarContadores(totales, filas) {
            const totalEl = document.getElementById('fincas-count');
            const doneEl = document.getElementById('fincas-done-count');
            const pendingEl = document.getElementById('fincas-pending-count');
            if (!totalEl || !doneEl || !pendingEl) return;

            const total = Number(totales?.total_registros ?? filas.length) || 0;
            const realizados = Number(totales?.realizados ?? filas.filter((fila) => fila.relevamiento_id).length) || 0;
            const pendientes = Number(totales?.pendientes ?? Math.max(0, total - realizados)) || 0;

            totalEl.textContent = String(total);
            doneEl.textContent = String(realizados);
            pendingEl.textContent = String(pendientes);
        }

        function renderFincas(filas) {
            const tbody = document.getElementById('fincas-table-body');
            if (!tbody) return;

            if (!filas.length) {
                tbody.innerHTML = '<tr><td colspan="6">No hay fincas participantes.</td></tr>';
                return;
            }

            tbody.innerHTML = '';
            filas.forEach((fila) => {
                const tr = document.createElement('tr');
                const celdas = [
                    fila.variedad || '-',
                    fila.nom_cooperativa || '-',
                    fila.productor || '-',
                ];

                celdas.forEach((valor) => {
                    const td = document.createElement('td');
                    td.className = 'cell-wrap-3';
                    aplicarSaltoTerceraPalabra(td, valor);
                    tr.appendChild(td);
                });

                const tdTipo = document.createElement('td');
                const badge = document.createElement('span');
                const esExterno = fila.tipo === 'Externo';
                badge.className = `badge-tipo ${esExterno ? 'badge-externo' : 'badge-interno'}`;
                badge.textContent = fila.tipo || 'Interno';
                tdTipo.appendChild(badge);
                tr.appendChild(tdTipo);

                [fila.finca || '-', fila.superficie ?? '-'].forEach((valor) => {
                    const td = document.createElement('td');
                    td.className = 'cell-wrap-3';
                    aplicarSaltoTerceraPalabra(td, valor);
                    tr.appendChild(td);
                });

                tbody.appendChild(tr);
            });
        }

        async function cargarFincasOperativos() {
            const tbody = document.getElementById('fincas-table-body');
            try {
                const res = await fetch(`${API_URL}?action=fincas`, {
                    credentials: 'same-origin',
                    cache: 'no-store'
                });
                const payload = await res.json();
                if (!res.ok || !payload.success) {
                    throw new Error(payload.message || 'No se pudieron cargar las fincas.');
                }

                const data = payload.data || {};
                latestFincasRows = Array.isArray(data.items) ? data.items : [];
                actualizarContadores(data.totales || {}, latestFincasRows);
                renderFincas(latestFincasRows);
            } catch (error) {
                console.error(error);
                latestFincasRows = [];
                actualizarContadores({}, []);
                if (tbody) {
                    tbody.innerHTML = '<tr><td colspan="6">No se pudieron cargar las fincas.</td></tr>';
                }
                showUserAlert('error', error.message || 'No se pudieron cargar las fincas.');
            }
        }

        function actualizarContadoresDrones(totales, filas) {
            const totalEl = document.getElementById('drones-count');
            const doneEl = document.getElementById('drones-done-count');
            const pendingEl = document.getElementById('drones-pending-count');
            if (!totalEl || !doneEl || !pendingEl) return;

            const total = Number(totales?.total_registros ?? filas.length) || 0;
            const completadas = Number(totales?.completadas ?? filas.filter((fila) => fila.estado === 'completada').length) || 0;
            const pendientes = Number(totales?.pendientes ?? filas.filter((fila) => !['completada', 'cancelada'].includes(String(fila.estado || '').toLowerCase())).length) || 0;

            totalEl.textContent = String(total);
            doneEl.textContent = String(completadas);
            pendingEl.textContent = String(pendientes);
        }

        function prettyEstadoDrone(estado) {
            const raw = String(estado || '').toLowerCase();
            const labels = {
                ingresada: 'Ingresada',
                procesando: 'Procesando',
                aprobada_coop: 'Aprobada coop',
                visita_realizada: 'Visita realizada',
                completada: 'Completada',
                cancelada: 'Cancelada'
            };
            return labels[raw] || estado || '-';
        }

        function claseEstadoDrone(estado) {
            const raw = String(estado || '').toLowerCase();
            if (raw === 'completada') return 'badge-estado-completada';
            if (raw === 'cancelada') return 'badge-estado-cancelada';
            if (raw === 'procesando' || raw === 'aprobada_coop' || raw === 'visita_realizada') return 'badge-estado-proceso';
            return 'badge-estado-pendiente';
        }

        function formatearMontoDrone(fila) {
            const total = fila.costo_total ?? '';
            if (total === null || total === '') return '-';
            const num = Number(String(total).replace(',', '.'));
            if (!Number.isFinite(num)) return String(total);
            const moneda = fila.moneda ? `${fila.moneda} ` : '';
            return `${moneda}${num.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        }

        function renderDrones(filas) {
            const tbody = document.getElementById('drones-table-body');
            if (!tbody) return;

            if (!filas.length) {
                tbody.innerHTML = '<tr><td colspan="7">No hay solicitudes de pulverizacion con drones.</td></tr>';
                return;
            }

            tbody.innerHTML = '';
            filas.forEach((fila) => {
                const tr = document.createElement('tr');

                [fila.solicitud_id || '-', fila.productor_nombre || '-'].forEach((valor) => {
                    const td = document.createElement('td');
                    td.className = 'cell-wrap-3';
                    aplicarSaltoTerceraPalabra(td, valor);
                    tr.appendChild(td);
                });

                const tdEstado = document.createElement('td');
                const badge = document.createElement('span');
                badge.className = `badge-estado ${claseEstadoDrone(fila.estado)}`;
                badge.textContent = prettyEstadoDrone(fila.estado);
                tdEstado.appendChild(badge);
                tr.appendChild(tdEstado);

                [fila.piloto || '-', fila.fecha_visita || '-', fila.superficie_ha ?? '-', formatearMontoDrone(fila)].forEach((valor) => {
                    const td = document.createElement('td');
                    td.className = 'cell-wrap-3';
                    aplicarSaltoTerceraPalabra(td, valor);
                    tr.appendChild(td);
                });

                tbody.appendChild(tr);
            });
        }

        async function cargarSolicitudesDrones() {
            const tbody = document.getElementById('drones-table-body');
            try {
                const res = await fetch(`${API_URL}?action=drones`, {
                    credentials: 'same-origin',
                    cache: 'no-store'
                });
                const payload = await res.json();
                if (!res.ok || !payload.success) {
                    throw new Error(payload.message || 'No se pudieron cargar las solicitudes de drones.');
                }

                const data = payload.data || {};
                latestDronesRows = Array.isArray(data.items) ? data.items : [];
                actualizarContadoresDrones(data.totales || {}, latestDronesRows);
                renderDrones(latestDronesRows);
            } catch (error) {
                console.error(error);
                latestDronesRows = [];
                actualizarContadoresDrones({}, []);
                if (tbody) {
                    tbody.innerHTML = '<tr><td colspan="7">No se pudieron cargar las solicitudes de drones.</td></tr>';
                }
                showUserAlert('error', error.message || 'No se pudieron cargar las solicitudes de drones.');
            }
        }

        function descargarExcelXlsx(columns, rows, filename) {
            if (!window.XLSX) {
                showUserAlert('error', 'No se pudo generar el Excel.');
                return;
            }

            const data = rows.map((row) => {
                const out = {};
                columns.forEach((col) => {
                    out[col.label] = row[col.key] ?? '';
                });
                return out;
            });

            const sheet = window.XLSX.utils.json_to_sheet(data, {
                header: columns.map((col) => col.label)
            });
            const wb = window.XLSX.utils.book_new();
            window.XLSX.utils.book_append_sheet(wb, sheet, 'Fincas');
            window.XLSX.writeFile(wb, filename);
        }

        function exportarFincasExcel() {
            if (!latestFincasRows.length) {
                showUserAlert('warning', 'No hay registros para exportar.');
                return;
            }

            const columnas = [
                { key: 'participacion_id', label: 'ID Participacion' },
                { key: 'contrato_nombre', label: 'Contrato' },
                { key: 'variedad', label: 'Variedad' },
                { key: 'nom_cooperativa', label: 'Cooperativa' },
                { key: 'productor', label: 'Productor' },
                { key: 'cuit', label: 'CUIT' },
                { key: 'condicion_pago', label: 'Condicion de pago' },
                { key: 'fecha_servicio', label: 'Fecha del servicio' },
                { key: 'hectareas_cosechadas', label: 'Hectareas cosechadas' },
                { key: 'hectareas_anticipadas', label: 'Hectareas anticipadas' },
                { key: 'bonificacion_aptitud_finca', label: 'Bonificacion por aptitud de finca' },
                { key: 'calificacion_aptitud_finca', label: 'Calificacion aptitud de finca' },
                { key: 'tipo', label: 'Tipo' },
                { key: 'finca', label: 'Finca' },
                { key: 'codigo_finca', label: 'Codigo finca' },
                { key: 'superficie', label: 'Superficie (ha)' },
                { key: 'prod_estimada', label: 'Produccion estimada' },
                { key: 'fecha_estimada', label: 'Fecha estimada' },
                { key: 'km_finca', label: 'Km finca' },
                { key: 'flete', label: 'Flete' },
                { key: 'seguro_flete', label: 'Seguro flete' },
                { key: 'ancho_callejon_norte', label: 'Ancho callejon Norte' },
                { key: 'ancho_callejon_sur', label: 'Ancho callejon Sur' },
                { key: 'promedio_callejon', label: 'Promedio callejon' },
                { key: 'interfilar', label: 'Interfilar' },
                { key: 'cantidad_postes', label: 'Cantidad postes' },
                { key: 'postes_mal_estado', label: 'Postes mal estado' },
                { key: 'porcentaje_postes_mal_estado', label: '% postes mal estado' },
                { key: 'estructura_separadores', label: 'Alambres y separadores' },
                { key: 'agua_lavado', label: 'Agua para lavado' },
                { key: 'preparacion_acequias', label: 'Preparacion suelo acequias' },
                { key: 'preparacion_obstaculos', label: 'Malezas y obstaculos' },
                { key: 'observaciones', label: 'Observaciones' },
                { key: 'fecha_evaluacion', label: 'Fecha evaluacion' },
            ];

            const stamp = new Date().toISOString().slice(0, 10);
            descargarExcelXlsx(columnas, latestFincasRows, `fincas_operativos_facturacion_${stamp}.xlsx`);
            showUserAlert('success', 'Excel generado correctamente.');
        }

        function exportarDronesExcel() {
            if (!latestDronesRows.length) {
                showUserAlert('warning', 'No hay registros para exportar.');
                return;
            }

            const columnas = [
                { key: 'solicitud_id', label: 'ID Solicitud' },
                { key: 'productor_id_real', label: 'ID Real productor' },
                { key: 'productor_nombre', label: 'Productor' },
                { key: 'ses_usuario', label: 'Usuario sesion' },
                { key: 'ses_nombre', label: 'Nombre sesion' },
                { key: 'ses_correo', label: 'Correo' },
                { key: 'ses_telefono', label: 'Telefono' },
                { key: 'ses_direccion', label: 'Direccion sesion' },
                { key: 'ses_cuit', label: 'CUIT' },
                { key: 'ses_rol', label: 'Rol sesion' },
                { key: 'representante', label: 'Representante' },
                { key: 'estado', label: 'Estado' },
                { key: 'motivo_cancelacion', label: 'Motivo cancelacion' },
                { key: 'fecha_visita', label: 'Fecha servicio' },
                { key: 'hora_visita_desde', label: 'Hora desde' },
                { key: 'hora_visita_hasta', label: 'Hora hasta' },
                { key: 'piloto', label: 'Piloto' },
                { key: 'piloto_id', label: 'ID Piloto' },
                { key: 'superficie_ha', label: 'Superficie solicitada (ha)' },
                { key: 'forma_pago', label: 'Forma de pago' },
                { key: 'forma_pago_id', label: 'ID Forma de pago' },
                { key: 'coop_descuento_nombre', label: 'Cooperativa descuento' },
                { key: 'dir_provincia', label: 'Provincia' },
                { key: 'dir_localidad', label: 'Localidad' },
                { key: 'dir_calle', label: 'Calle' },
                { key: 'dir_numero', label: 'Numero' },
                { key: 'en_finca', label: 'En finca' },
                { key: 'ubicacion_lat', label: 'Latitud' },
                { key: 'ubicacion_lng', label: 'Longitud' },
                { key: 'ubicacion_acc', label: 'Precision ubicacion' },
                { key: 'ubicacion_ts', label: 'Fecha ubicacion' },
                { key: 'linea_tension', label: 'Linea tension' },
                { key: 'zona_restringida', label: 'Zona restringida' },
                { key: 'corriente_electrica', label: 'Corriente electrica' },
                { key: 'agua_potable', label: 'Agua potable' },
                { key: 'libre_obstaculos', label: 'Libre obstaculos' },
                { key: 'area_despegue', label: 'Area despegue' },
                { key: 'observaciones_productor', label: 'Observaciones productor' },
                { key: 'moneda', label: 'Moneda' },
                { key: 'costo_base_por_ha', label: 'Costo base por ha' },
                { key: 'base_ha', label: 'Base ha' },
                { key: 'base_total', label: 'Base total' },
                { key: 'productos_total', label: 'Productos total' },
                { key: 'costo_total', label: 'Costo total' },
                { key: 'costo_desglose_json', label: 'Desglose costos JSON' },
                { key: 'rangos', label: 'Rangos' },
                { key: 'motivos', label: 'Motivos' },
                { key: 'productos', label: 'Productos' },
                { key: 'productos_fuente', label: 'Fuente productos' },
                { key: 'productos_costo_ha', label: 'Costo ha productos' },
                { key: 'productos_total_detalle', label: 'Total productos detalle' },
                { key: 'recetas', label: 'Recetas y uso de productos' },
                { key: 'volumen_ha', label: 'Volumen ha' },
                { key: 'velocidad_vuelo', label: 'Velocidad vuelo' },
                { key: 'alto_vuelo', label: 'Alto vuelo' },
                { key: 'ancho_pasada', label: 'Ancho pasada' },
                { key: 'tamano_gota', label: 'Tamano gota' },
                { key: 'observaciones_parametros', label: 'Observaciones parametros' },
                { key: 'observaciones_agua', label: 'Observaciones agua' },
                { key: 'reporte_nom_cliente', label: 'Reporte cliente' },
                { key: 'reporte_nom_piloto', label: 'Reporte piloto' },
                { key: 'reporte_nom_encargado', label: 'Reporte encargado' },
                { key: 'reporte_fecha_visita', label: 'Reporte fecha visita' },
                { key: 'reporte_hora_ingreso', label: 'Reporte hora ingreso' },
                { key: 'reporte_hora_egreso', label: 'Reporte hora egreso' },
                { key: 'reporte_nombre_finca', label: 'Reporte finca' },
                { key: 'reporte_cultivo_pulverizado', label: 'Reporte cultivo pulverizado' },
                { key: 'reporte_cuadro_cuartel', label: 'Reporte cuadro cuartel' },
                { key: 'reporte_sup_pulverizada', label: 'Reporte superficie pulverizada' },
                { key: 'reporte_vol_aplicado', label: 'Reporte volumen aplicado' },
                { key: 'reporte_vel_viento', label: 'Reporte velocidad viento' },
                { key: 'reporte_temperatura', label: 'Reporte temperatura' },
                { key: 'reporte_humedad_relativa', label: 'Reporte humedad relativa' },
                { key: 'reporte_lavado_dron_miner', label: 'Reporte lavado dron/miner' },
                { key: 'reporte_triple_lavado_envases', label: 'Reporte triple lavado envases' },
                { key: 'reporte_observaciones', label: 'Reporte observaciones' },
                { key: 'eventos', label: 'Eventos' },
                { key: 'created_at', label: 'Creado' },
                { key: 'updated_at', label: 'Actualizado' },
            ];

            const stamp = new Date().toISOString().slice(0, 10);
            descargarExcelXlsx(columnas, latestDronesRows, `pulverizacion_drones_facturacion_${stamp}.xlsx`);
            showUserAlert('success', 'Excel generado correctamente.');
        }

        document.addEventListener('DOMContentLoaded', async () => {
            const estado = document.getElementById('estadoModulo');
            const btnExportFincas = document.getElementById('btnExportFincas');
            const btnExportDrones = document.getElementById('btnExportDrones');
            btnExportFincas?.addEventListener('click', exportarFincasExcel);
            btnExportDrones?.addEventListener('click', exportarDronesExcel);

            try {
                const res = await fetch(`${API_URL}?action=estado`, {
                    credentials: 'same-origin',
                    cache: 'no-store'
                });
                const data = await res.json();

                if (!data.success) {
                    throw new Error(data.message || 'No se pudo cargar el modulo.');
                }

                estado.textContent = 'Modulo listo.';
            } catch (error) {
                estado.textContent = error.message;
            }

            cargarFincasOperativos();
            cargarSolicitudesDrones();
        });
    </script>
</body>

</html>
