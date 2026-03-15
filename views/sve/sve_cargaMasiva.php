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
                            <th>Detalle</th>
                        </tr>
                    </thead>
                    <tbody id="previewBody"></tbody>
                </table>
            </div>
            <div class="modal-actions">
                <button class="btn btn-cancelar" type="button" id="btnCloseModal">Cancelar</button>
                <button class="btn btn-aceptar" type="button" id="btnConfirmModal">Sí, aplicar cambios</button>
            </div>
        </div>
    </div>

    <script>
        (() => {
            const REQUIRED_HEADERS = [
                'rol', 'permiso_ingreso', 'cuit', 'razon_social', 'id_real',
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

            const previewModal = document.getElementById('previewModal');
            const modalSummary = document.getElementById('modalSummary');
            const previewBody = document.getElementById('previewBody');
            const btnCloseModal = document.getElementById('btnCloseModal');
            const btnConfirmModal = document.getElementById('btnConfirmModal');

            let parsedRows = [];
            let lastPreviewResponse = null;

            function normalizeHeader(h) {
                return String(h || '').trim().toLowerCase();
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

            async function postToController(action, rows) {
                const response = await fetch('../../controllers/sve_cargaMasivaController.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action,
                        cooperativa_id_real: coopIdReal.value.trim(),
                        rows
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
                    throw new Error((json && json.error) ? json.error : ('HTTP ' + response.status));
                }

                return json.data;
            }

            function renderSummary(summary) {
                if (!summary) return '';
                const coopNombre = summary.cooperativa?.nombre || summary.cooperativa?.razon_social || 'Sin nombre';
                return [
                    `Cooperativa: ${summary.cooperativa?.id_real || '-'} (${coopNombre})`,
                    `Filas totales CSV: ${summary.rows_total || 0}`,
                    `Filas a procesar: ${summary.rows_processable || 0}`,
                    `Filas omitidas: ${summary.rows_omitted || 0}`,
                    `Usuarios nuevos a crear: ${summary.usuarios_nuevos_estimados || 0}`,
                    `Usuarios -> revisado = "Esta revisado": ${summary.usuarios_a_revisado_si || 0}`,
                    `Usuarios -> revisado = "No esta revisado": ${summary.usuarios_a_revisado_no || 0}`
                ].join('\n');
            }

            function renderPreviewTable(rows) {
                previewBody.innerHTML = '';
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
                    const tr = document.createElement('tr');
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
                        <td class="cell-detail">${escapeHtml(detalle)}</td>
                    `;
                    previewBody.appendChild(tr);
                }
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
                    btnApply.disabled = true;

                    parsedRows = await parseCsv(csvFile.files[0]);
                    validateHeaders(parsedRows);

                    lastPreviewResponse = await postToController('preview', parsedRows);

                    setStatus('Previsualización lista. Revisá el modal y confirmá si querés aplicar los cambios.');
                    setWarnings(lastPreviewResponse.warnings || []);
                    modalSummary.textContent = renderSummary(lastPreviewResponse.summary || {});
                    renderPreviewTable(lastPreviewResponse.preview_rows || []);
                    btnApply.disabled = false;
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
                }
            });

            btnApply.addEventListener('click', () => {
                if (!lastPreviewResponse) {
                    setStatus('Primero hacé la previsualización.');
                    return;
                }
                openPreviewModal();
            });

            btnConfirmModal.addEventListener('click', async () => {
                try {
                    btnConfirmModal.disabled = true;
                    setStatus('Aplicando cambios en base de datos...');

                    const result = await postToController('apply', parsedRows);
                    closePreviewModal();

                    const applied = result.applied || {};
                    setStatus(
                        'Actualización finalizada.\n' +
                        `Usuarios creados: ${applied.usuarios_created || 0}\n` +
                        `Usuarios actualizados: ${applied.usuarios_updated || 0}\n` +
                        `Usuarios_info upsert: ${applied.usuarios_info_upserted || 0}\n` +
                        `Fincas insertadas/actualizadas: ${applied.fincas_inserted || 0}/${applied.fincas_updated || 0}\n` +
                        `Cuarteles insertados/actualizados: ${applied.cuarteles_inserted || 0}/${applied.cuarteles_updated || 0}\n` +
                        `Relaciones productor-coop ajustadas: ${applied.rel_productor_coop_adjusted || 0}\n` +
                        `Usuarios marcados "No esta revisado": ${applied.revisado_to_no || 0}`
                    );
                    setWarnings(result.warnings || []);
                } catch (err) {
                    console.error('[CargaMasiva][APPLY_ERROR]', {
                        message: String(err?.message || err),
                        error: err,
                        cooperativa_id_real: coopIdReal.value.trim(),
                        archivo: csvFile?.files?.[0]?.name || null
                    });
                    setStatus('ERROR al aplicar: ' + String(err.message || err));
                } finally {
                    btnConfirmModal.disabled = false;
                }
            });

            btnCloseModal.addEventListener('click', closePreviewModal);
            previewModal.addEventListener('click', (e) => {
                if (e.target === previewModal) closePreviewModal();
            });
        })();
    </script>

    <script src="../../views/partials/spinner-global.js"></script>
</body>

</html>
