<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once '../../middleware/authMiddleware.php';
checkAccess('sve');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SVE</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="https://framework.impulsagroup.com/assets/css/framework.css">
    <script src="https://framework.impulsagroup.com/assets/javascript/framework.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/papaparse@5.4.1/papaparse.min.js" defer></script>
    <style>
        .massive-table-wrap {
            max-height: 58vh;
            overflow: auto;
            border: 1px solid rgba(0, 0, 0, .08);
            border-radius: 10px;
            margin-top: 12px;
        }

        .massive-status {
            margin-top: 12px;
            font-size: 14px;
            white-space: pre-wrap;
        }

        .massive-local-spinner {
            margin-top: 12px;
            display: none;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border: 1px solid rgba(0, 0, 0, .12);
            border-radius: 10px;
            background: #f8fafc;
            font-size: 13px;
        }

        .massive-local-spinner.is-active {
            display: flex;
        }

        .massive-local-spinner .dot {
            width: 16px;
            height: 16px;
            border-radius: 999px;
            border: 2px solid #cbd5e1;
            border-top-color: #2563eb;
            animation: spinMassive .8s linear infinite;
        }

        @keyframes spinMassive {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .massive-warnings {
            margin-top: 12px;
            color: #b45309;
            font-size: 13px;
            white-space: pre-wrap;
            max-height: 120px;
            overflow: auto;
        }

        .modal .modal-content {
            /* AJUSTE ANCHO MODAL: modificá este valor para hacerlo más angosto/ancho */
            width: min(1450px, 96vw);
            max-height: 88vh;
            max-width: 1200px;
            overflow: auto;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 14px;
        }

        #previewTable {
            font-size: 13px;
        }

        #previewTable thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
            white-space: nowrap;
        }

        #previewTable tbody tr:nth-child(even) {
            background: rgba(0, 0, 0, .02);
        }

        #previewTable td {
            vertical-align: top;
        }

        .result-pill {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .result-pill.ok {
            background: #dcfce7;
            color: #166534;
        }

        .result-pill.skip {
            background: #fef3c7;
            color: #92400e;
        }

        .cell-detail {
            max-width: 320px;
            white-space: normal;
            word-break: break-word;
        }

        .changes-count {
            font-weight: 700;
            color: #1d4ed8;
        }

        .preview-detail-box {
            margin-top: 14px;
            border: 1px solid rgba(0, 0, 0, .08);
            border-radius: 10px;
            padding: 10px;
            background: #fafafa;
        }

        .preview-detail-box h4 {
            margin: 0 0 8px 0;
            font-size: 14px;
        }

        .field-changed {
            background: #ecfeff;
            color: #155e75;
            font-weight: 600;
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
                    <li onclick="location.href='sve_dashboard.php'"><span class="material-icons" style="color: #5b21b6;">home</span><span class="link-text">Inicio</span></li>
                    <li onclick="location.href='sve_consolidado.php'"><span class="material-icons" style="color: #5b21b6;">analytics</span><span class="link-text">Consolidado</span></li>
                    <li onclick="location.href='sve_altausuarios.php'"><span class="material-icons" style="color: #5b21b6;">person</span><span class="link-text">Alta usuarios</span></li>
                    <li onclick="location.href='sve_asociarProductores.php'"><span class="material-icons" style="color: #5b21b6;">link</span><span class="link-text">Asociaciones</span></li>
                    <li onclick="location.href='sve_cargaMasiva.php'"><span class="material-icons" style="color: #5b21b6;">upload_file</span><span class="link-text">Carga masiva</span></li>
                    <li onclick="location.href='sve_registro_login.php'"><span class="material-icons" style="color: #5b21b6;">login</span><span class="link-text">Ingresos</span></li>
                    <li onclick="location.href='sve_operativos.php'"><span class="material-icons" style="color: #5b21b6;">assignment</span><span class="link-text">Operativos</span></li>
                    <li onclick="location.href='sve_mercadodigital.php'"><span class="material-icons" style="color: #5b21b6;">shopping_cart</span><span class="link-text">Mercado Digital</span></li>
                    <li onclick="location.href='sve_listadoPedidos.php'"><span class="material-icons" style="color: #5b21b6;">assignment_turned_in</span><span class="link-text">Listado Pedidos</span></li>
                    <li onclick="location.href='sve_productos.php'"><span class="material-icons" style="color: #5b21b6;">inventory</span><span class="link-text">Productos</span></li>
                    <li onclick="location.href='sve_pulverizacionDrone.php'"><span class="material-symbols-outlined" style="color:#5b21b6;">drone</span><span class="link-text">Drones</span></li>
                    <li onclick="location.href='sve_cosechaMecanica.php'"><span class="material-icons" style="color:#5b21b6;">agriculture</span><span class="link-text">Cosecha Mecánica</span></li>
                    <li onclick="location.href='sve_serviciosVendimiales.php'"><span class="material-icons" style="color:#5b21b6;">wine_bar</span><span class="link-text">Servicios Auxiliares Enológicos</span></li>
                    <li onclick="location.href='sve_publicaciones.php'"><span class="material-icons" style="color: #5b21b6;">menu_book</span><span class="link-text">Biblioteca Virtual</span></li>
                    <li onclick="location.href='../../../logout.php'"><span class="material-icons" style="color: red;">logout</span><span class="link-text">Salir</span></li>
                </ul>
            </nav>

            <div class="sidebar-footer">
                <button class="btn-icon" onclick="toggleSidebar()"><span class="material-icons" id="collapseIcon">chevron_left</span></button>
            </div>
        </aside>

        <div class="main">
            <header class="navbar">
                <button class="btn-icon" onclick="toggleSidebar()"><span class="material-icons">menu</span></button>
                <div class="navbar-title">Actualización masiva por CSV</div>
            </header>

            <section class="content">
                <div class="card">
                    <h2>Actualización de productores (CSV UTF-8, delimitado por comas)</h2>
                    <p>Seleccioná el archivo CSV, indicá la cooperativa y previsualizá antes de confirmar.</p>

                    <div class="form-grid grid-3">
                        <div class="input-group">
                            <label for="csvFile">Archivo CSV</label>
                            <div class="input-icon input-icon-file-upload">
                                <input id="csvFile" type="file" accept=".csv,text/csv" />
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="coopIdReal">ID real cooperativa</label>
                            <div class="input-icon input-icon-id-card">
                                <input id="coopIdReal" type="text" placeholder="Ej: 30001" />
                            </div>
                        </div>
                        <div class="input-group" style="display:flex;align-items:flex-end;gap:10px;">
                            <button class="btn btn-info" id="btnPreview" type="button">Previsualizar cambios</button>
                            <button class="btn btn-aceptar" id="btnApply" type="button" disabled>Confirmar actualización</button>
                        </div>
                    </div>

                    <div id="statusBox" class="massive-status"></div>
                    <div id="localSpinner" class="massive-local-spinner">
                        <div class="dot"></div>
                        <div id="localSpinnerText">Procesando...</div>
                    </div>
                    <div id="warningsBox" class="massive-warnings"></div>
                </div>
            </section>
        </div>
    </div>

    <div id="previewModal" class="modal hidden">
        <div class="modal-content">
            <h3>Confirmación de cambios</h3>
            <div id="modalSummary" style="font-size:14px; white-space:pre-wrap;"></div>
            <div class="massive-table-wrap">
                <table class="table" id="previewTable">
                    <thead>
                        <tr>
                            <th>Línea</th>
                            <th>CUIT</th>
                            <th>ID real</th>
                            <th>Finca</th>
                            <th>Cuartel</th>
                            <th>Resultado</th>
                            <th>Usuario</th>
                            <th>ID real</th>
                            <th>Relación</th>
                            <th>Finca</th>
                            <th>Cuartel</th>
                            <th>Cambios</th>
                            <th>Ver</th>
                            <th>Detalle</th>
                        </tr>
                    </thead>
                    <tbody id="previewBody"></tbody>
                </table>
            </div>
            <div class="preview-detail-box" id="rowDetailBox" style="display:none;">
                <h4 id="rowDetailTitle">Detalle de cambios</h4>
                <div class="massive-table-wrap" style="max-height:35vh; margin-top:0;">
                    <table class="table" id="detailTable">
                        <thead>
                            <tr>
                                <th>Tabla</th>
                                <th>Campo</th>
                                <th>Valor actual</th>
                                <th>Valor nuevo</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody id="detailBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-actions">
                <button class="btn btn-cancelar" type="button" id="btnCloseModal">Cancelar</button>
                <button class="btn btn-aceptar" type="button" id="btnConfirmModal">Sí, aplicar cambios</button>
            </div>
        </div>
    </div>

    <script>
        (() => {
            const APPLY_BATCH_SIZE = 50;
            const REQUIRED_HEADERS = [
                'rol', 'permiso_ingreso', 'cuit', 'razon_social', 'id_real',
                'estado_asociacion_cooperativa',
                'nombre', 'direccion', 'telefono', 'correo', 'fecha_nacimiento',
                'categorizacion', 'tipo_relacion', 'zona_asignada',
                'codigo_finca', 'nombre_finca', 'variedad',
                'departamento', 'localidad', 'calle', 'numero', 'latitud', 'longitud',
                'codigo_cuartel', 'sistema_conduccion', 'superficie_ha',
                'porcentaje_cepas_produccion', 'forma_cosecha_actual',
                'porcentaje_malla_buen_estado', 'edad_promedio_encepado_anios',
                'estado_estructura_sistema', 'labores_mecanizables', 'numero_inv'
            ];

            const csvFile = document.getElementById('csvFile');
            const coopIdReal = document.getElementById('coopIdReal');
            const btnPreview = document.getElementById('btnPreview');
            const btnApply = document.getElementById('btnApply');
            const statusBox = document.getElementById('statusBox');
            const warningsBox = document.getElementById('warningsBox');
            const localSpinner = document.getElementById('localSpinner');
            const localSpinnerText = document.getElementById('localSpinnerText');

            const previewModal = document.getElementById('previewModal');
            const modalSummary = document.getElementById('modalSummary');
            const previewBody = document.getElementById('previewBody');
            const btnCloseModal = document.getElementById('btnCloseModal');
            const btnConfirmModal = document.getElementById('btnConfirmModal');
            const rowDetailBox = document.getElementById('rowDetailBox');
            const rowDetailTitle = document.getElementById('rowDetailTitle');
            const detailBody = document.getElementById('detailBody');

            let parsedRows = [];
            let lastPreviewResponse = null;
            let lastSimulationResponse = null;
            let renderedPreviewRows = [];

            function normalizeHeader(h) {
                const normalized = String(h || '')
                    .trim()
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '');

                const aliases = {
                    'direccion': 'direccion',
                    'telefono': 'telefono',
                    'fecha_nacimiento': 'fecha_nacimiento',
                    'echa_nacimiento': 'fecha_nacimiento',
                    'relacion_cooperativa': 'estado_asociacion_cooperativa',
                    'relacion_con_cooperativa': 'estado_asociacion_cooperativa',
                    'variedad': 'variedad'
                };

                return aliases[normalized] || normalized;
            }

            function setStatus(text) {
                statusBox.textContent = text || '';
            }

            function setWarnings(items) {
                if (!Array.isArray(items) || items.length === 0) {
                    warningsBox.textContent = '';
                    return;
                }
                warningsBox.textContent = 'Advertencias:\n- ' + items.join('\n- ');
            }

            function setLocalSpinner(active, text = 'Procesando...') {
                localSpinnerText.textContent = text;
                localSpinner.classList.toggle('is-active', !!active);
            }

            function escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function ensureInputs() {
                if (!csvFile.files || !csvFile.files[0]) {
                    throw new Error('Seleccioná un archivo CSV.');
                }
                if (!coopIdReal.value.trim()) {
                    throw new Error('Indicá el id_real de la cooperativa.');
                }
            }

            function parseCsv(file) {
                return new Promise((resolve, reject) => {
                    const reader = new FileReader();
                    reader.onload = () => {
                        const text = String(reader.result || '');
                        const firstLine = (text.split(/\r\n|\n|\r/)[0] || '').replace(/^\uFEFF/, '');
                        const commaCount = (firstLine.match(/,/g) || []).length;
                        const semicolonCount = (firstLine.match(/;/g) || []).length;
                        const delimiter = semicolonCount > commaCount ? ';' : ',';

                        console.log('[CargaMasiva][CSV_DELIMITER_DETECT]', {
                            file: file?.name || null,
                            firstLine,
                            commaCount,
                            semicolonCount,
                            chosenDelimiter: delimiter
                        });

                        Papa.parse(file, {
                            header: true,
                            delimiter,
                            skipEmptyLines: true,
                            encoding: 'utf-8',
                            transformHeader: (h) => String(h || '').trim(),
                            complete: (results) => {
                                if (results.errors && results.errors.length) {
                                    const sampleErrors = results.errors.slice(0, 8).map((e) => ({
                                        type: e.type,
                                        code: e.code,
                                        message: e.message,
                                        row: e.row,
                                        index: e.index
                                    }));
                                    const details = {
                                        file: {
                                            name: file?.name || null,
                                            size: file?.size || null,
                                            type: file?.type || null,
                                            lastModified: file?.lastModified || null
                                        },
                                        parserMeta: results.meta || {},
                                        sampleErrors
                                    };
                                    console.error('[CargaMasiva][CSV_PARSE_ERROR]', details);
                                    const err = new Error(results.errors[0].message || 'Error parseando CSV.');
                                    err.details = details;
                                    reject(err);
                                    return;
                                }
                                resolve(Array.isArray(results.data) ? results.data : []);
                            },
                            error: (err) => {
                                console.error('[CargaMasiva][CSV_FATAL_ERROR]', err);
                                reject(err);
                            }
                        });
                    };
                    reader.onerror = () => reject(reader.error || new Error('No se pudo leer el CSV.'));
                    reader.readAsText(file, 'utf-8');
                });
            }

            function validateHeaders(rows) {
                if (!rows.length) {
                    throw new Error('El CSV no tiene filas.');
                }
                const headers = Object.keys(rows[0]).map(normalizeHeader);
                const missing = REQUIRED_HEADERS.filter((h) => !headers.includes(h));
                if (missing.length) {
                    throw new Error('Faltan columnas requeridas: ' + missing.join(', '));
                }
            }

            async function postToController(action, rows, extra = {}) {
                const response = await fetch('../../controllers/sve_cargaMasivaController.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action,
                        cooperativa_id_real: coopIdReal.value.trim(),
                        rows,
                        ...extra
                    })
                });

                const text = await response.text();
                let json;
                try {
                    json = JSON.parse(text);
                } catch (e) {
                    throw new Error('Respuesta no JSON: ' + text);
                }

                if (!response.ok || !json.ok) {
                    const err = new Error((json && json.error) ? json.error : ('HTTP ' + response.status));
                    err.detail = json?.detail || null;
                    err.raw = json || null;
                    throw err;
                }

                return json.data;
            }

            function renderSummary(summary) {
                if (!summary) return '';
                const coopNombre = summary.cooperativa?.nombre || summary.cooperativa?.razon_social || 'Sin nombre';
                const lines = [
                    `Cooperativa: ${summary.cooperativa?.id_real || '-'} (${coopNombre})`,
                    `Filas totales CSV: ${summary.rows_total || 0}`,
                    `Filas a procesar: ${summary.rows_processable || 0}`,
                    `Filas omitidas: ${summary.rows_omitted || 0}`,
                    `Usuarios nuevos a crear: ${summary.usuarios_nuevos_estimados || 0}`,
                    `Usuarios -> revisado = "Esta revisado": ${summary.usuarios_a_revisado_si || 0}`,
                    `Usuarios -> revisado = "No esta revisado": ${summary.usuarios_a_revisado_no || 0}`
                ];

                const related = Array.isArray(summary.tablas_relacionadas) ? summary.tablas_relacionadas : [];
                if (related.length) {
                    lines.push('');
                    lines.push('Tablas relacionadas (cooperativa/productor):');
                    for (const item of related) {
                        lines.push(`- ${item?.tabla || '-'}: ${Number(item?.filas_existentes || 0)} filas actuales (${item?.accion || 'actualizar'})`);
                    }
                }

                return lines.join('\n');
            }

            function renderStrictSimulation(sim) {
                if (!sim || !sim.strict_sync) return '';
                const strict = sim.strict_sync || {};
                const lines = [];
                lines.push('');
                lines.push('Simulación sincronización estricta (sin escribir en BD):');
                lines.push(`- Relaciones productor-coop a eliminar: ${Number(strict.rel_productor_coop_to_delete || 0)}`);
                lines.push(`- Fincas a eliminar: ${Number(strict.fincas_to_delete || 0)}`);
                lines.push(`- Cuarteles a eliminar: ${Number(strict.cuarteles_to_delete || 0)}`);
                lines.push(`- Estado: ${strict.blocked ? 'BLOQUEADA' : 'OK para aplicar'}`);
                if (strict.reason) {
                    lines.push(`- Motivo: ${strict.reason}`);
                }
                return lines.join('\n');
            }

            function renderPreviewTable(rows) {
                previewBody.innerHTML = '';
                renderedPreviewRows = Array.isArray(rows) ? rows : [];
                const max = Math.min(rows.length, 300);

                for (let i = 0; i < max; i++) {
                    const r = rows[i] || {};
                    const resultado = String(r.resultado || '');
                    const pillClass = resultado === 'procesar' ? 'ok' : 'skip';
                    const detalle = r.detalle || '-';
                    const accionUsuario = r.accion_usuario || (resultado === 'procesar' ? 'actualizar' : '-');
                    const accionIdReal = r.accion_id_real || '-';
                    const accionRelacion = r.accion_relacion || '-';
                    const accionFinca = r.accion_finca || '-';
                    const accionCuartel = r.accion_cuartel || '-';
                    const cambios = Number(r.changes_count || 0);
                    const tr = document.createElement('tr');
                    const hasDetail = Array.isArray(r.changes_flat) && r.changes_flat.length > 0;
                    tr.innerHTML = `
                        <td>${escapeHtml(r.linea ?? '')}</td>
                        <td>${escapeHtml(r.cuit ?? '')}</td>
                        <td>${escapeHtml(r.id_real_usuario ?? '')}</td>
                        <td>${escapeHtml(r.codigo_finca ?? '')}</td>
                        <td>${escapeHtml(r.codigo_cuartel ?? '')}</td>
                        <td><span class="result-pill ${pillClass}">${escapeHtml(resultado || '-')}</span></td>
                        <td>${escapeHtml(accionUsuario)}</td>
                        <td>${escapeHtml(accionIdReal)}</td>
                        <td>${escapeHtml(accionRelacion)}</td>
                        <td>${escapeHtml(accionFinca)}</td>
                        <td>${escapeHtml(accionCuartel)}</td>
                        <td><span class="changes-count">${escapeHtml(cambios)}</span></td>
                        <td>${hasDetail ? `<button type="button" class="btn btn-info btn-sm" data-row-idx="${i}">Ver</button>` : '-'}</td>
                        <td class="cell-detail">${escapeHtml(detalle)}</td>
                    `;
                    previewBody.appendChild(tr);
                }

                rowDetailBox.style.display = 'none';
                detailBody.innerHTML = '';
            }

            function renderRowDetail(row) {
                const changes = Array.isArray(row?.changes_flat) ? row.changes_flat : [];
                rowDetailTitle.textContent = `Detalle de fila ${row?.linea ?? '-'} - CUIT ${row?.cuit ?? '-'}`;
                detailBody.innerHTML = '';

                if (!changes.length) {
                    detailBody.innerHTML = '<tr><td colspan="5">Sin detalle de cambios.</td></tr>';
                    rowDetailBox.style.display = 'block';
                    return;
                }

                for (const ch of changes) {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${escapeHtml(ch.tabla ?? '')}</td>
                        <td>${escapeHtml(ch.campo ?? '')}</td>
                        <td>${escapeHtml(ch.actual ?? '')}</td>
                        <td>${escapeHtml(ch.nuevo ?? '')}</td>
                        <td class="${ch.cambia ? 'field-changed' : ''}">${ch.cambia ? 'Actualiza' : 'Sin cambios'}</td>
                    `;
                    detailBody.appendChild(tr);
                }
                rowDetailBox.style.display = 'block';
            }

            function openPreviewModal() {
                previewModal.classList.remove('hidden');
            }

            function closePreviewModal() {
                previewModal.classList.add('hidden');
            }

            csvFile.addEventListener('change', () => {
                if (!coopIdReal.value.trim()) {
                    setStatus('Archivo seleccionado. Ahora indicá el id_real de la cooperativa para continuar.');
                    coopIdReal.focus();
                }
            });

            btnPreview.addEventListener('click', async () => {
                try {
                    ensureInputs();
                    setWarnings([]);
                    setStatus('Leyendo CSV y generando previsualización...');
                    setLocalSpinner(true, 'Parseando CSV y armando previsualización...');
                    btnApply.disabled = true;

                    parsedRows = await parseCsv(csvFile.files[0]);
                    validateHeaders(parsedRows);

                    lastPreviewResponse = await postToController('preview', parsedRows);
                    lastSimulationResponse = await postToController('simulate', parsedRows);

                    setStatus('Previsualización lista. Revisá el modal y confirmá si querés aplicar los cambios.');
                    setWarnings(lastPreviewResponse.warnings || []);
                    modalSummary.textContent =
                        renderSummary(lastPreviewResponse.summary || {}) +
                        renderStrictSimulation(lastSimulationResponse || {});
                    renderPreviewTable(lastPreviewResponse.preview_rows || []);
                    btnApply.disabled = !!(lastSimulationResponse?.strict_sync?.blocked);
                    openPreviewModal();
                } catch (err) {
                    console.error('[CargaMasiva][PREVIEW_ERROR]', {
                        message: String(err?.message || err),
                        error: err,
                        details: err?.details || null,
                        cooperativa_id_real: coopIdReal.value.trim(),
                        archivo: csvFile?.files?.[0]?.name || null
                    });
                    setStatus('ERROR: ' + String(err.message || err));
                    setWarnings([]);
                    btnApply.disabled = true;
                } finally {
                    setLocalSpinner(false);
                }
            });

            btnApply.addEventListener('click', () => {
                if (!lastPreviewResponse) {
                    setStatus('Primero hacé la previsualización.');
                    return;
                }
                if (lastSimulationResponse?.strict_sync?.blocked) {
                    setStatus('No se puede aplicar: la simulación bloqueó la sincronización estricta. Corregí las filas omitidas y volvé a previsualizar.');
                    return;
                }
                openPreviewModal();
            });

            btnConfirmModal.addEventListener('click', async () => {
                try {
                    if (lastSimulationResponse?.strict_sync?.blocked) {
                        throw new Error('Simulación bloqueada: no se permite aplicar hasta corregir filas omitidas.');
                    }
                    btnConfirmModal.disabled = true;
                    setLocalSpinner(true, 'Aplicando cambios por bloques...');
                    const totalRows = parsedRows.length;
                    const totalBatches = Math.max(1, Math.ceil(totalRows / APPLY_BATCH_SIZE));
                    const allCsvCuits = [...new Set(parsedRows.map(r => String(r?.cuit ?? '').replace(/\D+/g, '')).filter(Boolean))];
                    let aggregated = {
                        usuarios_created: 0,
                        usuarios_updated: 0,
                        usuarios_info_upserted: 0,
                        fincas_inserted: 0,
                        fincas_updated: 0,
                        fincas_deleted: 0,
                        cuarteles_inserted: 0,
                        cuarteles_updated: 0,
                        cuarteles_deleted: 0,
                        cuartel_limitantes_null_reset: 0,
                        cuartel_rendimientos_null_reset: 0,
                        cuartel_riesgos_null_reset: 0,
                        rel_productor_coop_adjusted: 0,
                        rel_productor_coop_deleted: 0,
                        revisado_to_no: 0
                    };
                    let finalWarnings = [];

                    for (let batchIndex = 0; batchIndex < totalBatches; batchIndex++) {
                        const start = batchIndex * APPLY_BATCH_SIZE;
                        const end = start + APPLY_BATCH_SIZE;
                        const batchRows = parsedRows.slice(start, end);
                        const isFinal = batchIndex === (totalBatches - 1);

                        setStatus(`Aplicando cambios en base de datos... Bloque ${batchIndex + 1}/${totalBatches} (${batchRows.length} filas)`);
                        setLocalSpinner(true, `Procesando bloque ${batchIndex + 1}/${totalBatches}...`);

                        const result = await postToController('apply_batch', batchRows, {
                            finalize: isFinal ? 1 : 0,
                            all_csv_cuits: isFinal ? allCsvCuits : [],
                            all_csv_rows: isFinal ? parsedRows : []
                        });
                        const applied = result.applied || {};
                        aggregated.usuarios_created += Number(applied.usuarios_created || 0);
                        aggregated.usuarios_updated += Number(applied.usuarios_updated || 0);
                        aggregated.usuarios_info_upserted += Number(applied.usuarios_info_upserted || 0);
                        aggregated.fincas_inserted += Number(applied.fincas_inserted || 0);
                        aggregated.fincas_updated += Number(applied.fincas_updated || 0);
                        aggregated.fincas_deleted += Number(applied.fincas_deleted || 0);
                        aggregated.cuarteles_inserted += Number(applied.cuarteles_inserted || 0);
                        aggregated.cuarteles_updated += Number(applied.cuarteles_updated || 0);
                        aggregated.cuarteles_deleted += Number(applied.cuarteles_deleted || 0);
                        aggregated.cuartel_limitantes_null_reset += Number(applied.cuartel_limitantes_null_reset || 0);
                        aggregated.cuartel_rendimientos_null_reset += Number(applied.cuartel_rendimientos_null_reset || 0);
                        aggregated.cuartel_riesgos_null_reset += Number(applied.cuartel_riesgos_null_reset || 0);
                        aggregated.rel_productor_coop_adjusted += Number(applied.rel_productor_coop_adjusted || 0);
                        aggregated.rel_productor_coop_deleted += Number(applied.rel_productor_coop_deleted || 0);
                        aggregated.revisado_to_no += Number(applied.revisado_to_no || 0);
                        finalWarnings = result.warnings || finalWarnings;
                    }
                    closePreviewModal();
                    setStatus(
                        'Actualización finalizada.\n' +
                        `Usuarios creados: ${aggregated.usuarios_created || 0}\n` +
                        `Usuarios actualizados: ${aggregated.usuarios_updated || 0}\n` +
                        `Usuarios_info upsert: ${aggregated.usuarios_info_upserted || 0}\n` +
                        `Fincas insertadas/actualizadas/eliminadas: ${aggregated.fincas_inserted || 0}/${aggregated.fincas_updated || 0}/${aggregated.fincas_deleted || 0}\n` +
                        `Cuarteles insertados/actualizados/eliminados: ${aggregated.cuarteles_inserted || 0}/${aggregated.cuarteles_updated || 0}/${aggregated.cuarteles_deleted || 0}\n` +
                        `Cuartel limitantes reseteado a NULL: ${aggregated.cuartel_limitantes_null_reset || 0}\n` +
                        `Cuartel rendimientos reseteado a NULL: ${aggregated.cuartel_rendimientos_null_reset || 0}\n` +
                        `Cuartel riesgos reseteado a NULL: ${aggregated.cuartel_riesgos_null_reset || 0}\n` +
                        `Relaciones productor-coop ajustadas/eliminadas: ${aggregated.rel_productor_coop_adjusted || 0}/${aggregated.rel_productor_coop_deleted || 0}\n` +
                        `Usuarios marcados "No esta revisado": ${aggregated.revisado_to_no || 0}`
                    );
                    setWarnings(finalWarnings || []);
                } catch (err) {
                    console.error('[CargaMasiva][APPLY_ERROR]', {
                        message: String(err?.message || err),
                        error: err,
                        detail: err?.detail || null,
                        raw: err?.raw || null,
                        cooperativa_id_real: coopIdReal.value.trim(),
                        archivo: csvFile?.files?.[0]?.name || null
                    });
                    const detailText = err?.detail ? ('\nDetalle: ' + String(err.detail)) : '';
                    setStatus('ERROR al aplicar: ' + String(err.message || err) + detailText);
                } finally {
                    btnConfirmModal.disabled = false;
                    setLocalSpinner(false);
                }
            });

            btnCloseModal.addEventListener('click', closePreviewModal);
            previewModal.addEventListener('click', (e) => {
                if (e.target === previewModal) closePreviewModal();
            });

            previewBody.addEventListener('click', (e) => {
                const btn = e.target.closest('button[data-row-idx]');
                if (!btn) return;
                const idx = Number(btn.getAttribute('data-row-idx'));
                if (!Number.isInteger(idx)) return;
                const row = renderedPreviewRows[idx];
                if (!row) return;
                renderRowDetail(row);
            });
        })();
    </script>

    <script src="../../views/partials/spinner-global.js"></script>
</body>

</html>
