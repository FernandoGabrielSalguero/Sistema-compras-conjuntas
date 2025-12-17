<!-- sve_cargaMasiva.php -->

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión y configurar parámetros de seguridad
require_once '../../middleware/authMiddleware.php';
checkAccess('sve');

// Datos del usuario en sesión
$nombre = $_SESSION['nombre'] ?? 'Sin nombre';
$correo = $_SESSION['correo'] ?? 'Sin correo';
$cuit = $_SESSION['cuit'] ?? 'Sin CUIT';
$telefono = $_SESSION['telefono'] ?? 'Sin teléfono';
$observaciones = $_SESSION['observaciones'] ?? 'Sin observaciones';
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

    <!-- CSV Parser robusto (soporta comillas / separadores reales) -->
    <script src="https://cdn.jsdelivr.net/npm/papaparse@5.4.1/papaparse.min.js"></script>
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
                    <li onclick="location.href='sve_cosechaMecanica.php'">
                        <span class="material-icons" style="color:#5b21b6;">agriculture</span>
                        <span class="link-text">Cosecha Mecánica</span>
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

        <!-- 🧱 MAIN -->
        <div class="main">

            <!-- 🟪 NAVBAR -->
            <header class="navbar">
                <button class="btn-icon" onclick="toggleSidebar()">
                    <span class="material-icons">menu</span>
                </button>
                <div class="navbar-title">Carga masiva de usuarios</div>
            </header>

            <!-- 📦 CONTENIDO -->
            <section class="content">

                <!-- Bienvenida -->
                <div class="card">
                    <h2>Hola 👋</h2>
                    <p>En esta página vamos a cargar masivamente los usuarios en nuestro sistema. Recordá que solo podemos cargar archivos con extensión CSV.</p>
                </div>

                <div class="card-grid grid-2">
                    <!-- Tarjeta: Carga de Cooperativas -->
                    <div class="card">
                        <h3>Cargar Usuarios</h3>
                        <input type="file" id="csvCooperativas" accept=".csv" />
                        <button class="btn btn-info" onclick="previewCSV('cooperativas')">Previsualizar</button>
                        <div id="previewCooperativas" class="csv-preview"></div>
                        <button class="btn btn-aceptar" onclick="confirmarCarga('cooperativas')">Confirmar carga</button>
                    </div>

                    <!-- Tarjeta: Carga de relaciones -->
                    <div class="card">
                        <h3>Cargar relaciones productores ↔ cooperativas</h3>
                        <input type="file" id="csvRelaciones" accept=".csv" />
                        <button class="btn btn-info" onclick="previewCSV('relaciones')">Previsualizar</button>
                        <div id="previewRelaciones" class="csv-preview"></div>
                        <button class="btn btn-aceptar" onclick="confirmarCarga('relaciones')">Confirmar carga</button>
                    </div>

                    <!-- Tarjeta: Carga de Datos de familia -->
                    <div class="card">
                        <h3>Cargar Datos de familia</h3>
                        <input type="file" id="csvFamilia" accept=".csv" />
                        <button class="btn btn-info" onclick="previewCSV('familia')">Previsualizar</button>

                        <div style="margin-top:10px; display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
                            <label style="display:flex; gap:8px; align-items:center;">
                                <input type="checkbox" id="dryRunFamilia" checked />
                                <span>Simulación (no impacta en la base)</span>
                            </label>

                            <div id="progressFamilia" style="font-size:14px; opacity:0.9;"></div>
                        </div>

                        <div id="previewFamilia" class="csv-preview" style="margin-top:10px;"></div>
                        <div id="logFamilia" class="csv-preview" style="margin-top:10px; max-height:220px; overflow:auto; background:#0b1020; color:#e5e7eb; padding:10px; border-radius:10px;"></div>

                        <button class="btn btn-aceptar" onclick="confirmarCarga('familia')">Confirmar carga (en tandas de 250)</button>
                    </div>

                                      <!-- Tarjeta: Carga de datos de cuarteles -->
                    <div class="card">
                        <h3>Cargar datos de cuarteles</h3>
                        <p>Usa un CSV con columnas de <strong>cuarteles</strong> (y su <strong>codigo_finca</strong>) para impactar en prod_cuartel y tablas asociadas.</p>
                        <input type="file" id="csvCuarteles" accept=".csv" />
                        <button class="btn btn-info" onclick="previewCSV('cuarteles')">Previsualizar</button>

                        <div style="margin-top:10px; display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
                            <label style="display:flex; gap:8px; align-items:center;">
                                <input type="checkbox" id="dryRunCuarteles" checked />
                                <span>Simulación (no impacta en la base)</span>
                            </label>

                            <div id="progressCuarteles" style="font-size:14px; opacity:0.9;"></div>
                        </div>

                        <div id="previewCuarteles" class="csv-preview" style="margin-top:10px;"></div>
                        <div id="logCuarteles" class="csv-preview" style="white-space:pre-wrap; font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace; font-size:12px; max-height:220px; overflow:auto; background:#0b1020; color:#e5e7eb; padding:10px; border-radius:10px;"></div>

                        <button class="btn btn-aceptar" onclick="confirmarCarga('cuarteles')">Confirmar carga (en tandas de 250)</button>
                    </div>

                    <!-- Tarjeta: Carga de diagnóstico de fincas -->

                    <div class="card">
                        <h3>Cargar diagnóstico de fincas</h3>
                        <p>Usa un CSV con las columnas mapeadas por <strong>codigo finca</strong> a las tablas de fincas.</p>
                        <input type="file" id="csvFincas" accept=".csv" />
                        <button class="btn btn-info" onclick="previewCSV('fincas')">Previsualizar</button>

                        <div style="margin-top:10px; display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
                            <label style="display:flex; gap:8px; align-items:center;">
                                <input type="checkbox" id="dryRunFincas" checked />
                                <span>Simulación (no impacta en la base)</span>
                            </label>

                            <div id="progressFincas" style="font-size:14px; opacity:0.9;"></div>
                        </div>

                        <div id="previewFincas" class="csv-preview" style="margin-top:10px;"></div>
                        <div id="logFincas" class="csv-preview" style="margin-top:10px; max-height:220px; overflow:auto; background:#0b1020; color:#e5e7eb; padding:10px; border-radius:10px;"></div>

                        <button class="btn btn-aceptar" onclick="confirmarCarga('fincas')">Confirmar carga (en tandas de 250)</button>
                    </div>

                </div>

            </section>

        </div>
    </div>

    <!-- script principal  -->
    <script>
        const BATCH_SIZE = 250;

        // Robustez de red / server
        const REQUEST_TIMEOUT_MS = 30000; // 30s por tanda
        const RETRY_MAX = 3;
        const RETRY_BASE_MS = 1000; // 1s, 2s, 4s

        // Headers mínimos (no importa el orden)
        // NOTA: usamos sinónimos porque el CSV puede variar.
        const REQUIRED_HEADERS = {
            familia: [
                ['ID PP', 'Id PP', 'id pp', 'IDPP', 'IdPP'],
                ['Cooperativa', 'cooperativa']
            ],
            fincas: [
                ['codigo_finca', 'codigo finca', 'Código Finca', 'CódigoFinca', 'CODIGO FINCA', 'Codigo finca', 'código finca']
            ],
            cuarteles: [
                ['codigo_finca', 'codigo finca', 'Código Finca', 'CódigoFinca', 'CODIGO FINCA', 'Codigo finca', 'código finca'],
                ['codigo_cuartel', 'codigo cuartel', 'Código Cuartel', 'CódigoCuartel', 'CODIGO CUARTEL', 'Codigo cuartel', 'código cuartel']
            ],
            // estos dos ya existen pero dejamos estructura por consistencia
            cooperativas: [
                ['id_real', 'ID REAL', 'Id Real', 'id real'],
                ['contrasena', 'Contraseña', 'contraseña'],
                ['rol', 'Rol', 'ROL'],
                ['cuit', 'CUIT', 'cuit']
            ],
            relaciones: [
                ['id_productor', 'ID PRODUCTOR', 'id productor'],
                ['id_cooperativa', 'ID COOPERATIVA', 'id cooperativa']
            ]
        };


        function capitalize(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text ?? '').replace(/[&<>"']/g, function(m) {
                return map[m];
            });
        }

        function getDryRun(tipo) {
            const id = 'dryRun' + capitalize(tipo);
            const el = document.getElementById(id);
            return el ? !!el.checked : false;
        }

        function setProgress(tipo, txt, percent = null, extra = null) {
            const el = document.getElementById('progress' + capitalize(tipo));
            if (!el) return;

            const safeTxt = escapeHtml(txt ?? '');
            if (percent === null || percent === undefined) {
                el.innerHTML = safeTxt;
                return;
            }

            const p = Math.max(0, Math.min(100, Number(percent)));
            const extraHtml = extra ? `<div style="margin-top:6px; opacity:.9;">${escapeHtml(extra)}</div>` : '';
            el.innerHTML = `
                <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                    <progress value="${p}" max="100" style="width:220px; height:12px;"></progress>
                    <div style="font-size:14px;">${safeTxt} <strong>${p.toFixed(0)}%</strong></div>
                </div>
                ${extraHtml}
            `;
        }

        function logLine(tipo, txt) {
            const el = document.getElementById('log' + capitalize(tipo));
            if (!el) return;
            const now = new Date().toLocaleTimeString();
            el.innerHTML += `<div>[${escapeHtml(now)}] ${escapeHtml(txt)}</div>`;
            el.scrollTop = el.scrollHeight;
        }

        function clearLog(tipo) {
            const el = document.getElementById('log' + capitalize(tipo));
            if (el) el.innerHTML = '';
        }

        function normalizeHeadersRow(obj) {
            // Normalización suave para variaciones reales detectadas en tus CSVs:
            // - "CódigoFinca" -> "Código Finca"
            // - "tipo de Relacion" -> "Tipo de Relación"
            // - "Categorización A, B o C" -> "Categorización (A/B/C)"
            // - Trim de keys
            const out = {};
            for (const k in obj) {
                const key = String(k ?? '').trim();
                out[key] = obj[k];
            }

            if (out['CódigoFinca'] !== undefined && out['Código Finca'] === undefined) {
                out['Código Finca'] = out['CódigoFinca'];
            }
            if (out['tipo de Relacion'] !== undefined && out['Tipo de Relación'] === undefined) {
                out['Tipo de Relación'] = out['tipo de Relacion'];
            }
            if (out['Categorización A, B o C'] !== undefined && out['Categorización (A/B/C)'] === undefined) {
                out['Categorización (A/B/C)'] = out['Categorización A, B o C'];
            }

            // Compatibilidad: algunos CSV usan "Código Finca" sin espacio o con otra capitalización
            if (out['codigo finca'] !== undefined && out['Código Finca'] === undefined) {
                out['Código Finca'] = out['codigo finca'];
            }
            if (out['CODIGO FINCA'] !== undefined && out['Código Finca'] === undefined) {
                out['Código Finca'] = out['CODIGO FINCA'];
            }

            return out;
        }

        function hasAnyHeader(headers, candidates) {
            const set = new Set(headers.map(h => String(h ?? '').trim()));
            for (const c of candidates) {
                if (set.has(String(c ?? '').trim())) return true;
            }
            return false;
        }

        function validateRequiredHeaders(tipo, rows) {
            if (!rows || !rows.length) return {
                ok: false,
                missingGroups: ['CSV vacío']
            };

            const headers = Object.keys(rows[0] || {});
            const req = REQUIRED_HEADERS[tipo];
            if (!req) return {
                ok: true,
                missingGroups: []
            };

            const missingGroups = [];
            for (const group of req) {
                if (!hasAnyHeader(headers, group)) {
                    missingGroups.push(group.join(' / '));
                }
            }

            return {
                ok: missingGroups.length === 0,
                missingGroups
            };
        }

        function sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }

        function parseCsvFile(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();

                reader.onload = () => {
                    try {
                        const text = String(reader.result ?? '');
                        const firstLineRaw = text.split(/\r\n|\n|\r/)[0] ?? '';
                        const firstLine = firstLineRaw.replace(/^\uFEFF/, '');

                        const commaCount = (firstLine.match(/,/g) || []).length;
                        const semicolonCount = (firstLine.match(/;/g) || []).length;

                        const delimiter = commaCount > semicolonCount ? "," : ";";

                        Papa.parse(file, {
                            header: true,
                            delimiter: delimiter,
                            skipEmptyLines: true,
                            worker: false,
                            transformHeader: (h) => String(h ?? '').replace(/^\uFEFF/, '').trim(),
                            transform: (v) => (typeof v === 'string' ? v.trim() : v),
                            complete: (results) => {
                                if (results.errors && results.errors.length) {
                                    reject(results.errors);
                                    return;
                                }
                                const rows = (results.data || []).map(normalizeHeadersRow);
                                resolve(rows);
                            },
                            error: (err) => reject(err)
                        });
                    } catch (e) {
                        reject(e);
                    }
                };

                reader.onerror = () => reject(reader.error);

                reader.readAsText(file, "utf-8");
            });
        }

        function renderPreviewFromObjects(rows, container, maxRows = 20) {
            if (!rows || !rows.length) {
                container.innerHTML = "<p>No se pudo leer el archivo o está vacío.</p>";
                return;
            }

            const headers = Object.keys(rows[0]);
            let html = '<table class="table"><thead><tr>';
            headers.forEach(h => html += `<th>${escapeHtml(h)}</th>`);
            html += '</tr></thead><tbody>';

            const take = Math.min(rows.length, maxRows);
            for (let i = 0; i < take; i++) {
                html += '<tr>';
                headers.forEach(h => {
                    html += `<td>${escapeHtml(rows[i][h] ?? '')}</td>`;
                });
                html += '</tr>';
            }

            html += '</tbody></table>';
            html += `<p style="margin-top:8px; opacity:0.8;">Mostrando ${take} de ${rows.length} filas.</p>`;
            container.innerHTML = html;
        }

        window.previewCSV = async function(tipo) {
            const inputFile = document.getElementById('csv' + capitalize(tipo));
            const previewDiv = document.getElementById('preview' + capitalize(tipo));
            clearLog(tipo);

            if (!inputFile.files.length) {
                alert("Por favor seleccioná un archivo CSV.");
                return;
            }

            try {
                setProgress(tipo, 'Parseando CSV...', 0);
                const file = inputFile.files[0];
                const rows = await parseCsvFile(file);

                const check = validateRequiredHeaders(tipo, rows);
                if (!check.ok) {
                    renderPreviewFromObjects(rows, previewDiv, 10);
                    const msg = `Faltan headers mínimos para "${tipo}":\n- ${check.missingGroups.join('\n- ')}`;
                    setProgress(tipo, 'CSV inválido: faltan headers mínimos.', 0, msg);
                    logLine(tipo, `ERROR headers mínimos: ${check.missingGroups.join(' | ')}`);
                    alert(msg);
                    return;
                }

                renderPreviewFromObjects(rows, previewDiv, 20);
                setProgress(tipo, `CSV OK: ${rows.length} filas detectadas.`, 100);
                logLine(tipo, `CSV parseado correctamente (${rows.length} filas). Headers mínimos OK.`);
            } catch (err) {
                console.error(err);
                setProgress(tipo, 'Error parseando CSV.', 0);
                alert("Error leyendo CSV. Revisá formato/separador/archivo.");
                logLine(tipo, `ERROR parseo CSV: ${JSON.stringify(err)}`);
            }
        }

        async function sendBatch(tipo, batch, batchIndex, totalBatches, dryRun) {
            const payload = {
                tipo,
                dry_run: dryRun ? 1 : 0,
                batch_index: batchIndex,
                total_batches: totalBatches,
                batch
            };

            let lastErr = null;

            for (let attempt = 1; attempt <= RETRY_MAX; attempt++) {
                const controller = new AbortController();
                const timer = setTimeout(() => controller.abort(), REQUEST_TIMEOUT_MS);

                try {
                    const resp = await fetch('../../controllers/sve_cargaMasivaController.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(payload),
                        signal: controller.signal
                    });

                    const text = await resp.text();
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        throw new Error("Respuesta no-JSON del servidor: " + text);
                    }

                    if (!resp.ok || data.error) {
                        const httpRetryable = [502, 503, 504].includes(resp.status);
                        const msg = data.error || `HTTP ${resp.status}`;
                        if (httpRetryable && attempt < RETRY_MAX) {
                            lastErr = new Error(msg);
                            const wait = RETRY_BASE_MS * Math.pow(2, attempt - 1);
                            await sleep(wait);
                            continue;
                        }
                        throw new Error(msg);
                    }

                    return data;
                } catch (err) {
                    const isAbort = (err && (err.name === 'AbortError' || String(err.message || err).includes('AbortError')));
                    const isNetwork = String(err.message || err).toLowerCase().includes('failed to fetch');

                    if ((isAbort || isNetwork) && attempt < RETRY_MAX) {
                        lastErr = err;
                        const wait = RETRY_BASE_MS * Math.pow(2, attempt - 1);
                        await sleep(wait);
                        continue;
                    }
                    throw err;
                } finally {
                    clearTimeout(timer);
                }
            }

            throw lastErr || new Error('Fallo desconocido enviando tanda.');
        }

        window.confirmarCarga = async function(tipo) {
            const inputFile = document.getElementById('csv' + capitalize(tipo));
            const previewDiv = document.getElementById('preview' + capitalize(tipo));
            clearLog(tipo);

            if (!inputFile.files.length) {
                alert("Seleccioná un archivo para cargar.");
                return;
            }

            const dryRun = getDryRun(tipo);
            const modeLabel = dryRun ? 'SIMULACIÓN (rollback)' : 'CARGA REAL (commit)';

            try {
                setProgress(tipo, 'Parseando CSV...', 0);
                logLine(tipo, `Inicio ${modeLabel}.`);

                const file = inputFile.files[0];
                const rows = await parseCsvFile(file);

                renderPreviewFromObjects(rows, previewDiv, 10);

                if (!rows.length) {
                    alert("El CSV no tiene filas.");
                    setProgress(tipo, 'Sin filas.', 0);
                    return;
                }

                const check = validateRequiredHeaders(tipo, rows);
                if (!check.ok) {
                    const msg = `Faltan headers mínimos para "${tipo}":\n- ${check.missingGroups.join('\n- ')}`;
                    setProgress(tipo, 'CSV inválido: faltan headers mínimos.', 0, msg);
                    logLine(tipo, `ERROR headers mínimos: ${check.missingGroups.join(' | ')}`);
                    alert(msg);
                    return;
                }

                const totalBatches = Math.ceil(rows.length / BATCH_SIZE);
                let totalConflictos = 0;
                let totalOk = 0;
                let totalErrores = 0;

                for (let i = 0; i < totalBatches; i++) {
                    const start = i * BATCH_SIZE;
                    const batch = rows.slice(start, start + BATCH_SIZE);

                    const percent = ((i) / totalBatches) * 100;
                    setProgress(
                        tipo,
                        `Enviando tanda ${i + 1}/${totalBatches} (${batch.length} filas) - ${modeLabel}`,
                        percent,
                        `OK: ${totalOk} | Conflictos: ${totalConflictos} | Errores: ${totalErrores}`
                    );
                    logLine(tipo, `→ Tanda ${i + 1}/${totalBatches}: enviando ${batch.length} filas...`);

                    try {
                        const data = await sendBatch(tipo, batch, i + 1, totalBatches, dryRun);

                        const conflictos = Array.isArray(data.conflictos) ? data.conflictos.length : 0;
                        totalConflictos += conflictos;
                        totalOk += batch.length;

                        logLine(tipo, `← Tanda ${i + 1}/${totalBatches}: OK. Conflictos en tanda: ${conflictos}.`);
                        if (data.mensaje) logLine(tipo, `Mensaje: ${data.mensaje}`);

                        // Regla anti-ansiedad: un mini resumen por tanda si viene stats
                        if (data.stats) {
                            logLine(tipo, `Stats tanda: ${JSON.stringify(data.stats)}`);
                        }

                        // frenar ante conflictos en simulación:
                        if (dryRun && conflictos > 0) throw new Error(`Simulación detectó ${conflictos} conflictos en la tanda ${i + 1}.`);
                    } catch (e) {
                        totalErrores += 1;
                        throw e;
                    }
                }

                setProgress(
                    tipo,
                    `Finalizado ${modeLabel}.`,
                    100,
                    `OK: ${totalOk} | Conflictos: ${totalConflictos} | Errores: ${totalErrores}`
                );
                alert(`Finalizado ${modeLabel}.\nOK: ${totalOk}\nConflictos: ${totalConflictos}\nErrores: ${totalErrores}\nRevisá el panel de log.`);

            } catch (err) {
                console.error(err);
                setProgress(tipo, 'ERROR. Proceso detenido.', 0);
                logLine(tipo, `ERROR: ${String(err.message || err)}`);
                alert("Se detuvo la carga por error. Revisá el log.");
            }
        };
    </script>

    <!-- Spinner Global -->
    <script src="../../views/partials/spinner-global.js"></script>
</body>

</html>