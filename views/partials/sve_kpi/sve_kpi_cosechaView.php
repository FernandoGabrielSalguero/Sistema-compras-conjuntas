<style>
    .sve-kpi-cosecha.compact {
        padding: 12px;
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04);
        height: 420px;
        overflow: hidden;
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 12px;
        align-items: stretch;
        position: relative;
    }

    @media (max-width:900px) {
        .sve-kpi-cosecha.compact {
            grid-template-columns: 1fr;
            height: auto
        }
    }

    .mini-stats {
        display: flex;
        gap: 10px
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
        height: 120px !important
    }

    #chartEstados {
        height: 220px !important
    }

    .kpi-right .small-chart:last-child {
        align-items: flex-start;
        justify-content: flex-start;
        padding-top: 6px;
        padding-bottom: 8px;
    }

    .kpi-filters-inline {
        display: flex;
        gap: 6px;
        align-items: center
    }

    .kpi-filters-inline input,
    .kpi-filters-inline select {
        height: 28px;
        padding: 4px 6px;
        font-size: 12px;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        background: #fff
    }

    .kpi-filters-inline button {
        height: 28px;
        padding: 4px 8px;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        background: transparent;
        color: #6b7280
    }

    @media (max-width:600px) {
        .kpi-filters-inline {
            display: none
        }
    }
</style>

<div class="sve-kpi-cosecha compact">
    <div class="kpi-left">
        <div style="display:flex;justify-content:space-between;align-items:center">
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
            <div id="sveKpiCosechaStatus" style="font-size:12px;color:#6b7280">Cargando...</div>
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

            <div class="mini-stat" id="mini-total-prod">
                <div>
                    <div class="value" id="miniTotalProd">0</div>
                    <div class="label">Prod estimada</div>
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

    <div class="kpi-right">
        <div class="small-chart">
            <canvas id="chartEstados" class="canvas-small"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (async () => {
        const statusEl = document.getElementById('sveKpiCosechaStatus');
        const apiUrl = '../partials/sve_kpi/sve_kpi_cosechaController.php';

        let chartEstados = null;
        let chartContratosPorMes = null;

        const contratoSelect = document.getElementById('kpiContratoSelect');
        const coopSelect = document.getElementById('kpiCoopSelect');
        const prodSelect = document.getElementById('kpiProdSelect');
        const groupSelect = document.getElementById('kpiGroupBy');
        const estadoSelect = document.getElementById('kpiEstadoSelect');

        const fmtMoney = (v) => (Number(v) ? '$' + Number(v).toLocaleString('es-AR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }) : '$0.00');
        const fmtNum = (v) => (Number(v) ? Number(v).toLocaleString('es-AR') : '0');

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
                document.getElementById('miniTotalContratos').textContent = fmtNum(resumen.total_contratos || 0);
                document.getElementById('miniTotalSuperficie').textContent = fmtNum(resumen.total_superficie_ha || 0);
                document.getElementById('miniTotalProd').textContent = fmtNum(resumen.total_prod_estimada || 0);
                document.getElementById('miniTotalMonto').textContent = fmtMoney(resumen.total_monto_estimado || 0);

                // breakdown por estado (doughnut)
                const porEstado = data.por_estado || [];
                const labelsE = porEstado.map(e => e.estado);
                const valsE = porEstado.map(e => Number(e.count) || 0);
                const colorsE = porEstado.map(e => (e.estado === 'cerrado' ? '#10b981' : (e.estado === 'borrador' ? '#f59e0b' : '#60a5fa')));
                const canvasE = document.getElementById('chartEstados');
                const ctxE = canvasE.getContext('2d');
                const existingE = Chart.getChart(canvasE) || Chart.getChart('chartEstados');
                if (existingE) try {
                    existingE.destroy();
                } catch (e) {}
                if (chartEstados) try {
                    chartEstados.destroy();
                } catch (e) {}
                chartEstados = new Chart(canvasE, {
                    type: 'doughnut',
                    data: {
                        labels: labelsE,
                        datasets: [{
                            data: valsE,
                            backgroundColor: colorsE
                        }]
                    },
                    options: Object.assign({}, chartDefaults(), {
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom'
                            }
                        }
                    })
                });

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

                const canvasM = document.getElementById('chartContratosPorMes');
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

        const startInput = document.getElementById('kpiCompactStart');
        const endInput = document.getElementById('kpiCompactEnd');
        const clearBtn = document.getElementById('kpiCompactClear');

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