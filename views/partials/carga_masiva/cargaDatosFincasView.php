<div style="display:flex;flex-direction:column;gap:12px;">
    <p style="margin:0;opacity:.85;">
        <b>Cargar cuarteles</b> (CSV) — lectura con <b>PapaParse</b> y envío en <b>bloques de 250</b>.
    </p>

    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:end;">
        <div style="display:flex;flex-direction:column;gap:6px;">
            <label style="font-size:12px;opacity:.8;">Archivo CSV</label>
            <input type="file" id="cuartelCsvFile" accept=".csv,text/csv" />
        </div>

        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <button class="btn btn-info" type="button" id="cuartelSchemaBtn">Verificar esquema</button>
            <button class="btn btn-info" type="button" id="cuartelSimBtn">Simular (rollback)</button>
            <button class="btn btn-info" type="button" id="cuartelRunBtn">Cargar (commit)</button>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:6px;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:12px;opacity:.75;" id="cuartelProgressLabel">Listo.</div>
            <div style="font-size:12px;opacity:.75;" id="cuartelProgressPct"></div>
        </div>
        <div style="height:10px;border-radius:999px;background:rgba(0,0,0,.08);overflow:hidden;">
            <div id="cuartelProgressBar" style="height:10px;width:0%;background:rgba(25,135,84,.75);"></div>
        </div>
    </div>

    <pre id="cuartelOut" style="margin:0;padding:10px;border:1px solid rgba(0,0,0,.08);border-radius:10px;white-space:pre-wrap;word-break:break-word;min-height:120px;"></pre>
</div>

<script>
    (() => {
        // IMPORTANTE: ajustá esta ruta si tu view vive en otra carpeta
        const url = '../partials/carga_masiva/cargaDatosFincasController.php';

        const fileEl = document.getElementById('cuartelCsvFile');

        const schemaBtn = document.getElementById('cuartelSchemaBtn');
        const simBtn = document.getElementById('cuartelSimBtn');
        const runBtn = document.getElementById('cuartelRunBtn');

        const out = document.getElementById('cuartelOut');
        const progressLabel = document.getElementById('cuartelProgressLabel');
        const progressPct = document.getElementById('cuartelProgressPct');
        const progressBar = document.getElementById('cuartelProgressBar');

        if (!fileEl || !schemaBtn || !simBtn || !runBtn || !out || !progressLabel || !progressPct || !progressBar) return;

        function setBusy(busy) {
            [schemaBtn, simBtn, runBtn, fileEl].forEach(el => {
                if (el) el.disabled = !!busy;
            });
        }

        function setProgress(done, total) {
            const pct = total > 0 ? Math.round((done / total) * 100) : 0;
            progressBar.style.width = pct + '%';
            progressPct.textContent = total > 0 ? (pct + '%') : '';
        }

        async function postJson(payload) {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const text = await res.text();
            let json;
            try {
                json = JSON.parse(text);
            } catch (e) {
                throw new Error('Respuesta no-JSON: ' + text);
            }

            if (!res.ok || !json.ok) {
                throw new Error((json && json.error) ? json.error : ('Error HTTP ' + res.status));
            }
            return json;
        }

        function ensurePapaParse() {
            return new Promise((resolve, reject) => {
                if (window.Papa && typeof window.Papa.parse === 'function') return resolve();

                const s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/npm/papaparse@5.4.1/papaparse.min.js';
                s.async = true;
                s.onload = () => (window.Papa ? resolve() : reject(new Error('No se pudo cargar PapaParse.')));
                s.onerror = () => reject(new Error('No se pudo cargar PapaParse (error de red/CSP).'));
                document.head.appendChild(s);
            });
        }

        function parseCsv(file) {
            return new Promise((resolve, reject) => {
                window.Papa.parse(file, {
                    header: true,
                    skipEmptyLines: true,
                    transformHeader: (h) => String(h || '')
                        .trim()
                        .toLowerCase()
                        .replace(/\s+/g, '_')
                        .replace(/[^\w]/g, ''),
                    complete: (results) => {
                        if (results && results.errors && results.errors.length) {
                            const first = results.errors[0];
                            reject(new Error('Error parseando CSV: ' + (first.message || 'desconocido')));
                            return;
                        }
                        resolve((results && results.data) ? results.data : []);
                    },
                    error: (err) => reject(err)
                });
            });
        }

        function chunk(arr, size) {
            const out = [];
            for (let i = 0; i < arr.length; i += size) out.push(arr.slice(i, i + size));
            return out;
        }

        async function doSchemaCheck() {
            out.textContent = 'Verificando esquema...';
            const json = await postJson({
                action: 'schema_check'
            });

            if (json.data && json.data.missing && json.data.missing.length) {
                out.textContent =
                    '⚠️ FALTAN COLUMNAS/TABLAS PARA TU MAPEO.\n\n' +
                    'Missing:\n' + JSON.stringify(json.data.missing, null, 2) + '\n\n' +
                    'Ejecutá el SQL que corresponda (solo lo que falte) y volvé a verificar.\n';
            } else {
                out.textContent = '✅ Esquema OK para el mapeo.\n' + JSON.stringify(json.data, null, 2);
            }
        }

        async function run(mode) {
            const file = fileEl.files && fileEl.files[0] ? fileEl.files[0] : null;
            if (!file) {
                out.textContent = 'ERROR: Seleccioná un CSV.';
                return;
            }

            setBusy(true);
            setProgress(0, 0);

            try {
                progressLabel.textContent = 'Cargando PapaParse...';
                await ensurePapaParse();

                progressLabel.textContent = 'Leyendo CSV...';
                const rows = await parseCsv(file);

                if (!rows.length) {
                    out.textContent = 'ERROR: El CSV no tiene filas (o está vacío).';
                    return;
                }

                progressLabel.textContent = 'Verificando esquema (pre-check)...';
                const schema = await postJson({
                    action: 'schema_check'
                });
                if (schema.data && schema.data.missing && schema.data.missing.length) {
                    out.textContent =
                        '⚠️ No avanzo porque faltan columnas/tablas para el mapeo.\n\n' +
                        'Missing:\n' + JSON.stringify(schema.data.missing, null, 2) + '\n\n' +
                        'Ejecutá el SQL correspondiente (solo lo que falte) y reintentá.\n';
                    return;
                }

                const batches = chunk(rows, 250);
                const total = batches.length;

                const totals = {
                    mode,
                    filas: rows.length,
                    bloques: total,
                    cooperativas_creadas: 0,
                    fincas_creadas: 0,
                    fincas_actualizadas: 0,
                    cuarteles_creados: 0,
                    cuarteles_actualizados: 0,
                    rendimientos_actualizados: 0,
                    riesgos_actualizados: 0,
                    limitantes_actualizados: 0,
                    campos_escritos: 0,
                    errores: []
                };

                out.textContent = `Iniciando ${mode === 'simulate' ? 'SIMULACIÓN (rollback)' : 'CARGA (commit)'}...\n`;

                for (let i = 0; i < total; i++) {
                    const b = batches[i];

                    progressLabel.textContent = `Bloque ${i + 1}/${total}: enviando ${b.length} filas...`;
                    setProgress(i, total);

                    const json = await postJson({
                        action: 'ingest_batch',
                        mode,
                        batch_index: i + 1,
                        total_batches: total,
                        rows: b
                    });

                    const d = json.data || {};
                    totals.cooperativas_creadas += (d.cooperativas_creadas || 0);
                    totals.fincas_creadas += (d.fincas_creadas || 0);
                    totals.fincas_actualizadas += (d.fincas_actualizadas || 0);
                    totals.cuarteles_creados += (d.cuarteles_creados || 0);
                    totals.cuarteles_actualizados += (d.cuarteles_actualizados || 0);
                    totals.rendimientos_actualizados += (d.rendimientos_actualizados || 0);
                    totals.riesgos_actualizados += (d.riesgos_actualizados || 0);
                    totals.limitantes_actualizados += (d.limitantes_actualizados || 0);
                    totals.campos_escritos += (d.campos_escritos || 0);

                    if (Array.isArray(d.errores) && d.errores.length) {
                        totals.errores.push(...d.errores);
                    }

                    out.textContent =
                        out.textContent +
                        `✅ Bloque ${i + 1}/${total}: cuarteles_creados=${d.cuarteles_creados || 0}, ` +
                        `cuarteles_actualizados=${d.cuarteles_actualizados || 0}, ` +
                        `errores=${(d.errores && d.errores.length) ? d.errores.length : 0}\n`;
                }

                setProgress(total, total);
                progressLabel.textContent = 'Finalizado.';

                const errPreview = totals.errores.slice(0, 50);

                out.textContent =
                    out.textContent +
                    '\n========================\nRESUMEN TOTAL\n' +
                    JSON.stringify({
                        mode: totals.mode,
                        filas: totals.filas,
                        bloques: totals.bloques,
                        cooperativas_creadas: totals.cooperativas_creadas,
                        fincas_creadas: totals.fincas_creadas,
                        fincas_actualizadas: totals.fincas_actualizadas,
                        cuarteles_creados: totals.cuarteles_creados,
                        cuarteles_actualizados: totals.cuarteles_actualizados,
                        rendimientos_actualizados: totals.rendimientos_actualizados,
                        riesgos_actualizados: totals.riesgos_actualizados,
                        limitantes_actualizados: totals.limitantes_actualizados,
                        campos_escritos: totals.campos_escritos,
                        errores_total: totals.errores.length,
                        errores_preview: errPreview
                    }, null, 2);

            } catch (err) {
                progressLabel.textContent = 'Error.';
                out.textContent = 'ERROR: ' + (err && err.message ? err.message : String(err));
            } finally {
                setBusy(false);
            }
        }

        schemaBtn.addEventListener('click', async () => {
            setBusy(true);
            try {
                await doSchemaCheck();
            } catch (err) {
                out.textContent = 'ERROR: ' + (err && err.message ? err.message : String(err));
            } finally {
                setBusy(false);
            }
        });

        simBtn.addEventListener('click', () => run('simulate'));
        runBtn.addEventListener('click', () => run('commit'));
    })();
</script>