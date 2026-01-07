<style>
    .sve-kpi-cosecha.compact {
        padding: 12px;
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04);

        /* responsive: no cortar contenido */
        height: auto;
        min-height: 0;
        overflow: visible;

        display: grid;
        grid-template-columns: 1fr;
        gap: 12px;
        align-items: stretch;
        position: relative;
    }

    @media (max-width:900px) {
        .sve-kpi-cosecha.compact {
            grid-template-columns: 1fr;
            min-height: 0;
        }
    }

    .mini-stats {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .mini-stat {
        min-width: 180px;
    }

    @media (max-width:900px) {
        .mini-stat {
            flex: 1 1 calc(50% - 10px);
            min-width: 0;
        }
    }

    @media (max-width:480px) {
        .mini-stat {
            flex: 1 1 100%;
        }
    }

    .mini-stat {
        flex: 1;
        background: #f8fafc;
        border-radius: 8px;
        padding: 10px;
        display: flex;
        align-items: center;
        gap: 10px
    }

    .mini-stat .value {
        font-weight: 700;
        font-size: 18px;
        color: #111
    }

    .mini-stat .label {
        font-size: 12px;
        color: #6b7280
    }

    .kpi-charts {
        display: flex;
        flex-direction: column;
        gap: 8px;
        height: 100%
    }

    .small-chart {
        flex: 1;
        background: #fff;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 6px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02)
    }

    .canvas-small {
        width: 100%;
        height: 120px !important
    }

    .canvas-compact {
        width: 100%;
        height: 260px !important; /* más alto en pantallas grandes para ocupar mejor el ancho */
    }

    @media (max-width:900px) {
        .canvas-compact {
            height: 220px !important;
        }
    }

    .kpi-filters-inline {
        display: flex;
        gap: 6px;
        align-items: center;
        flex-wrap: wrap;
    }

    .kpi-filters-inline input,
    .kpi-filters-inline select {
        height: 28px;
        padding: 4px 6px;
        font-size: 12px;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        background: #fff;
        min-width: 120px;
    }

    .kpi-filters-inline button {
        height: 28px;
        padding: 4px 8px;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        background: transparent;
        color: #6b7280;
    }

    @media (max-width:900px) {
        .kpi-filters-inline {
            width: 100%;
            gap: 8px;
        }

        .kpi-filters-inline select,
        .kpi-filters-inline input,
        .kpi-filters-inline button {
            flex: 1 1 160px;
            min-width: 120px;
        }

        .kpi-filters-inline button {
            flex: 0 0 auto;
            min-width: 44px;
        }
    }

    .kpi-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 8px;
        flex-wrap: wrap;
    }

    /* filtros ocupan toda la fila para usar el ancho completo */
    .kpi-filters-inline {
        display: flex;
        gap: 6px;
        align-items: center;
        flex: 1 1 100%;
        width: 100%;
    }

    .kpi-status {
        font-size: 12px;
        color: #6b7280;
        width: 100%;
        text-align: right;
        margin-top: 6px;
    }

    @media (max-width:600px) {
        .kpi-status {
            width: 100%;
        }
    }
</style>

<div id="sveKpiCosechaCompact" class="sve-kpi-cosecha compact">
    <div class="kpi-left">
        <div class="kpi-header">
            <div style="display:flex;align-items:center;gap:8px">
                <div class="kpi-filters-inline" role="group" aria-label="Filtros KPI Cosecha">
                    <select id="kpiContratoSelect" class="gform-input" style="min-width:180px">
                        <option value="">Contrato (Todos)</option>
                    </select>
                    <select id="kpiCoopSelect" class="gform-input" style="min-width:160px">
                        <option value="">Cooperativa (Todas)</option>
                    </select>
                    <select id="kpiProdSelect" class="gform-input" style="min-width:160px">
                        <option value="">Productor (Todos)</option>
                    </select>

                    <select id="kpiGroupBy" class="gform-input" style="min-width:140px">
                        <option value="month">Agrupar por: Mes</option>
                        <option value="date" selected>Agrupar por: Fecha</option>
                    </select>
                    <select id="kpiEstadoSelect" class="gform-input" style="min-width:140px">
                        <option value="">Estado (Todos)</option>
                        <option value="borrador">Borrador</option>
                        <option value="abierto">Abierto</option>
                        <option value="cerrado">Cerrado</option>
                    </select>
                    <input id="kpiCompactStart" type="date" />
                    <input id="kpiCompactEnd" type="date" />
                    <button id="kpiCompactClear" title="Limpiar">✕</button>
                </div>
            </div>
            <div id="sveKpiCosechaStatus" class="kpi-status">Cargando...</div>
        </div>

        <div class="mini-stats">
            <div class="mini-stat" id="mini-total-contratos">
                <div>
                    <div class="value" id="miniTotalContratos">0</div>
                    <div class="label">Total contratos</div>
                </div>
            </div>

            <div class="mini-stat" id="mini-total-superficie">
                <div>
                    <div class="value" id="miniTotalSuperficie">0</div>
                    <div class="label">Superficie (ha)</div>
                </div>
            </div>

            <div class="mini-stat" id="mini-total-monto">
                <div>
                    <div class="value" id="miniTotalMonto">$0.00</div>
                    <div class="label">Monto estimado</div>
                </div>
            </div>
        </div>

        <div class="kpi-charts">
            <div class="small-chart" style="padding:6px">
                <canvas id="chartContratosPorMes" class="canvas-compact"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (async () => {
        const rootEl = document.getElementById('sveKpiCosechaCompact');
        if (!rootEl) return;

        const statusEl = rootEl.querySelector('#sveKpiCosechaStatus');
        const apiUrl = '../partials/sve_kpi/sve_kpi_cosechaController.php';

        let chartContratosPorMes = null;

        const contratoSelect = rootEl.querySelector('#kpiContratoSelect');
        const coopSelect = rootEl.querySelector('#kpiCoopSelect');
        const prodSelect = rootEl.querySelector('#kpiProdSelect');
        const groupSelect = rootEl.querySelector('#kpiGroupBy');
        const estadoSelect = rootEl.querySelector('#kpiEstadoSelect');

        const fmtMoney = (v) => (Number(v) ? '$' + Number(v).toLocaleString('es-AR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }) : '$0.00');
        const fmtNum = (v) => (Number(v) ? Number(v).toLocaleString('es-AR') : '0');

        const DEBUG_KPI = true;

        function populateSelect(selectEl, placeholderText, items, selectedValue = '') {
            if (!selectEl) return;

            const prev = selectedValue || selectEl.value || '';
            selectEl.innerHTML = '';

            const ph = document.createElement('option');
            ph.value = '';
            ph.textContent = placeholderText;
            selectEl.appendChild(ph);

            (items || []).forEach(it => {
                const o = document.createElement('option');
                o.value = (it.id ?? it.value ?? '');
                o.textContent = (it.nombre ?? it.label ?? String(o.value));
                selectEl.appendChild(o);
            });

            if (prev !== '' && Array.from(selectEl.options).some(o => o.value === prev)) {
                selectEl.value = prev;
            }
        }

        function chartDefaults() {
            return {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(200,200,200,0.15)',
                            borderDash: [4, 3]
                        },
                        ticks: {
                            color: '#6b7280',
                            font: {
                                size: 11
                            }
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(200,200,200,0.06)',
                            borderDash: [4, 3]
                        },
                        ticks: {
                            color: '#6b7280',
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            };
        }

        async function loadKpis(filters = {}) {
            try {
                statusEl.textContent = 'Cargando...';
                const payload = Object.assign({
                    action: 'kpis',
                    limit: 6,
                    months: 6
                }, filters);
                const res = await fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                const json = await res.json();
                if (!res.ok || !json.ok) throw new Error(json.error || 'Error al obtener KPIs');

                const data = json.data || {};

                // poblar selects (siempre, para que dependan del contrato/filters)
                try {
                    if (data.contratos && contratoSelect) {
                        populateSelect(contratoSelect, 'Contrato (Todos)', data.contratos, (filters && filters.contrato_id) ? String(filters.contrato_id) : '');
                    }
                    if (data.cooperativas && coopSelect) {
                        populateSelect(coopSelect, 'Cooperativa (Todas)', data.cooperativas, (filters && filters.cooperativa) ? String(filters.cooperativa) : '');
                    }
                    if (data.productores && prodSelect) {
                        populateSelect(prodSelect, 'Productor (Todos)', data.productores, (filters && filters.productor) ? String(filters.productor) : '');
                    }
                    if (filters && filters.group_by && groupSelect) groupSelect.value = filters.group_by;
                } catch (e) {
                    console.error('Error poblando selects', e);
                }

                // mini-stats
                const resumen = data.resumen || {};

                if (DEBUG_KPI) {
                    const sup = Number(resumen.total_superficie_ha ?? 0) || 0;
                    const base = Number(resumen.costo_base ?? 0) || 0;
                    const calc = sup * base;

                    console.groupCollapsed('[KPI Cosecha] Debug resumen/monto');
                    console.log('payload enviado:', payload);
                    console.log('resumen raw:', resumen);
                    console.log('superficie(total_superficie_ha):', sup);
                    console.log('costo_base:', base);
                    console.log('calc superficie*costo_base:', calc);
                    console.log('total_monto_estimado (backend):', resumen.total_monto_estimado);
                    console.groupEnd();
                }

                rootEl.querySelector('#miniTotalContratos').textContent = fmtNum(resumen.total_contratos || 0);
                rootEl.querySelector('#miniTotalSuperficie').textContent = fmtNum(resumen.total_superficie_ha || 0);
                rootEl.querySelector('#miniTotalMonto').textContent = fmtMoney(resumen.total_monto_estimado || 0);

                // doughnut de estados eliminado (removido) - ya no se envía ni se renderiza en esta vista

                // visitas (participaciones) por fecha_estimada (line)
                const porMes = data.por_mes || [];
                const rowsM = porMes.map(r => {
                    const fecha = (r.ym ?? r.fecha ?? '');
                    const cant = Number(r.count_visitas ?? r.count_contratos ?? r.count ?? 0) || 0;
                    return {
                        fecha,
                        cant
                    };
                });

                const labelsM = rowsM.map(r => `${r.fecha} (${r.cant})`);
                const valsM = rowsM.map(r => r.cant);

                const canvasM = rootEl.querySelector('#chartContratosPorMes');
                const ctxM = canvasM.getContext('2d');
                const existingM = Chart.getChart(canvasM) || Chart.getChart('chartContratosPorMes');
                if (existingM) try {
                    existingM.destroy();
                } catch (e) {}
                if (chartContratosPorMes) try {
                    chartContratosPorMes.destroy();
                } catch (e) {}

                chartContratosPorMes = new Chart(canvasM, {
                    type: 'line',
                    data: {
                        labels: labelsM,
                        datasets: [{
                            data: valsM,
                            borderColor: '#4b5563',
                            backgroundColor: 'rgba(79,70,229,0.12)',
                            tension: 0.4,
                            pointRadius: 3,
                            pointHoverRadius: 6,
                            fill: true
                        }]
                    },
                    options: Object.assign({}, chartDefaults(), {
                        scales: {
                            x: {
                                grid: {
                                    color: 'rgba(200,200,200,0.06)'
                                },
                                ticks: {
                                    color: '#6b7280',
                                    font: {
                                        size: 10
                                    }
                                }
                            },
                            y: {
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    title: function(items) {
                                        return (rowsM[items[0].dataIndex] && rowsM[items[0].dataIndex].fecha) ? rowsM[items[0].dataIndex].fecha : items[0].label;
                                    },
                                    label: function(context) {
                                        const v = rowsM[context.dataIndex] ? rowsM[context.dataIndex].cant : Number(context.formattedValue || 0);
                                        return `Visitas: ${v}`;
                                    }
                                }
                            }
                        }
                    })
                });

                statusEl.textContent = 'Actualizado';
            } catch (e) {
                statusEl.textContent = 'Error';
                console.error(e);
            }
        }

        function debounce(fn, wait = 450) {
            let t;
            return function(...args) {
                clearTimeout(t);
                t = setTimeout(() => fn.apply(this, args), wait);
            };
        }

        const startInput = rootEl.querySelector('#kpiCompactStart');
        const endInput = rootEl.querySelector('#kpiCompactEnd');
        const clearBtn = rootEl.querySelector('#kpiCompactClear');

        function validateAndApply() {
            const start = startInput.value || null;
            const end = endInput.value || null;
            const contratoId = contratoSelect ? (contratoSelect.value || null) : null;
            const coop = coopSelect.value || null;
            const productor = prodSelect.value || null;
            const estado = estadoSelect.value || null;
            const group = groupSelect ? groupSelect.value : 'month';

            if (start && end && end < start) {
                statusEl.textContent = 'Rango inválido: "Hasta" debe ser >= "Desde"';
                return;
            }
            statusEl.textContent = 'Aplicando filtros...';
            loadKpis({
                start_date: start,
                end_date: end,
                contrato_id: contratoId,
                cooperativa: coop,
                productor: productor,
                estado: estado,
                group_by: group
            });
        }

        const applyDebounced = debounce(validateAndApply, 500);
        startInput.addEventListener('change', applyDebounced);
        endInput.addEventListener('change', applyDebounced);
        if (contratoSelect) contratoSelect.addEventListener('change', applyDebounced);
        coopSelect.addEventListener('change', applyDebounced);
        prodSelect.addEventListener('change', applyDebounced);
        estadoSelect.addEventListener('change', applyDebounced);
        if (groupSelect) groupSelect.addEventListener('change', applyDebounced);

        clearBtn.addEventListener('click', () => {
            startInput.value = '';
            endInput.value = '';
            if (groupSelect) groupSelect.value = 'month';
            if (contratoSelect) contratoSelect.value = '';
            coopSelect.value = '';
            prodSelect.value = '';
            estadoSelect.value = '';
            statusEl.textContent = 'Filtros eliminados';
            loadKpis();
        });

        // carga inicial
        loadKpis({
            group_by: (groupSelect ? groupSelect.value : 'month')
        });
    })();
</script>