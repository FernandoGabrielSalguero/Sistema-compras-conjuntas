<style>
    /* Compact KPI card style (pantallazo) */
    .sve-kpi-compra-conjunta.compact {
        padding: 12px;
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04);
        height: 200px;
        /* altura solicitada */
        overflow: hidden;
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 12px;
        align-items: stretch;
        position: relative; /* necesario para posicionar filtros */
    }

    @media (max-width:900px) {
        .sve-kpi-compra-conjunta.compact {
            grid-template-columns: 1fr;
            height: auto
        }
    }

    .mini-stats {
        display: flex;
        gap: 10px;
    }

    .mini-stat {
        flex: 1;
        background: #f8fafc;
        border-radius: 8px;
        padding: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .mini-stat .value {
        font-weight: 700;
        font-size: 18px;
        color: #111;
    }

    .mini-stat .label {
        font-size: 12px;
        color: #6b7280
    }

    .kpi-charts {
        display: flex;
        flex-direction: column;
        gap: 8px;
        height: 100%;
    }

    .kpi-charts .small-chart {
        flex: 1;
        background: #fff;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 6px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
    }

    .kpi-left {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .kpi-right {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .canvas-small {
        width: 100%;
        height: 86px !important;
    }

    .canvas-compact {
        width: 100%;
        height: 120px !important;
    }

    /* filtros inline junto al título */
    .kpi-filters-inline { display:flex; gap:6px; align-items:center; }
    .kpi-filters-inline input, .kpi-filters-inline select { height:28px; padding:4px 6px; font-size:12px; border-radius:6px; border:1px solid #e5e7eb; background:#fff; }
    .kpi-filters-inline button { height:28px; padding:4px 8px; border-radius:6px; border:1px solid #e5e7eb; background:transparent; color:#6b7280; }
    @media (max-width:600px){ .kpi-filters-inline{ display:none } }
</style>

<div class="sve-kpi-compra-conjunta compact">
    <div class="kpi-left">
        <div style="display:flex;justify-content:space-between;align-items:center">
            <div style="display:flex;align-items:center;gap:8px">
                <strong style="font-size:14px">&nbsp;</strong>
                <div class="kpi-filters-inline" role="group" aria-label="Filtros KPI">
                    <select id="kpiCoopSelect" class="gform-input" style="min-width:160px">
                        <option value="">Cooperativa (Todas)</option>
                    </select>
                    <select id="kpiProdSelect" class="gform-input" style="min-width:160px">
                        <option value="">Productor (Todos)</option>
                    </select>
                    <select id="kpiOperSelect" class="gform-input" style="min-width:160px">
                        <option value="">Operativo (Todos)</option>
                    </select>
                    <input id="kpiCompactStart" type="date" />
                    <input id="kpiCompactEnd" type="date" />
                    <button id="kpiCompactClear" title="Limpiar">✕</button>
                </div>
            </div>
            <div id="sveKpiStatus" style="font-size:12px;color:#6b7280">Cargando...</div>
        </div>

        <div class="mini-stats">
            <div class="mini-stat" id="mini-total-pedidos">
                <div>
                    <div class="value" id="miniTotalPedidos">0</div>
                    <div class="label">Total pedidos</div>
                </div>
            </div>

            <div class="mini-stat" id="mini-total-monto">
                <div>
                    <div class="value" id="miniTotalMonto">$0</div>
                    <div class="label">Monto total</div>
                </div>
            </div>

            <div class="mini-stat" id="mini-unique-productores">
                <div>
                    <div class="value" id="miniUniqueProductores">0</div>
                    <div class="label">Productores</div>
                </div>
            </div>
        </div>

        <div class="kpi-charts">
            <div class="small-chart" style="padding:6px">
                <canvas id="chartPedidosPorMes" class="canvas-compact"></canvas>
            </div>
        </div>
    </div>

    <div class="kpi-right">
        <div class="small-chart">
            <canvas id="chartTopProductos" class="canvas-small"></canvas>
        </div>
        <div class="small-chart">
            <canvas id="chartTopCooperativas" class="canvas-small"></canvas>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (async () => {
        const statusEl = document.getElementById('sveKpiStatus');
        const apiUrl = '../partials/sve_kpi/sve_kpi_compraConjuntaController.php';

        let chartTopProductos = null;
        let chartTopCooperativas = null;
        let chartPedidosPorMes = null;

        // selects y inputs (declarados antes para que loadKpis pueda acceder a ellos)
        const coopSelect = document.getElementById('kpiCoopSelect');
        const prodSelect = document.getElementById('kpiProdSelect');
        const operSelect = document.getElementById('kpiOperSelect');

        const fmtMoney = (v) => (Number(v) ? '$' + Number(v).toLocaleString('es-AR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }) : '$0.00');
        const fmtNum = (v) => (Number(v) ? Number(v).toLocaleString('es-AR') : '0');

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

                // poblar selects si vienen datos (solo la primera vez)
                try {
                    if (data.cooperativas && coopSelect && coopSelect.options.length <= 1) {
                        data.cooperativas.forEach(c => {
                            const o = document.createElement('option'); o.value = c.id; o.textContent = c.nombre; coopSelect.appendChild(o);
                        });
                        // si se pasó filtro inicial, mantenerlo
                        if (filters && filters.cooperativa) {
                            coopSelect.value = filters.cooperativa;
                            // disparar change para cargar productores
                            coopSelect.dispatchEvent(new Event('change'));
                        }
                    }

                    if (data.operativos && operSelect && operSelect.options.length <= 1) {
                        data.operativos.forEach(o => {
                            const opt = document.createElement('option'); opt.value = o.id; opt.textContent = o.nombre; operSelect.appendChild(opt);
                        });
                        if (filters && filters.operativo) operSelect.value = filters.operativo;
                    }
                } catch (e) { console.error('Error poblando selects', e); }

                // actualizar mini-stats
                const resumen = data.resumen || {};
                document.getElementById('miniTotalPedidos').textContent = fmtNum(resumen.total_pedidos || 0);
                document.getElementById('miniTotalMonto').textContent = fmtMoney(resumen.total_monto || 0);
                document.getElementById('miniUniqueProductores').textContent = fmtNum(resumen.unique_productores || 0);

                // Top productos (bar vertical, colores pastel)
                const topProds = data.top_products || [];
                const labelsP = topProds.map(p => p.nombre_producto);
                const valsP = topProds.map(p => Number(p.total_cantidad) || 0);
                const ctxP = document.getElementById('chartTopProductos').getContext('2d');
                if (chartTopProductos) chartTopProductos.destroy();
                chartTopProductos = new Chart(ctxP, {
                    type: 'bar',
                    data: {
                        labels: labelsP,
                        datasets: [{
                            data: valsP,
                            backgroundColor: 'rgba(99,102,241,0.9)',
                            borderRadius: 6
                        }]
                    },
                    options: Object.assign({}, chartDefaults())
                });

                // Top cooperativas (bar horizontal)
                const topCoops = data.top_cooperativas || [];
                const labelsC = topCoops.map(c => c.nombre);
                const valsC = topCoops.map(c => Number(c.pedidos_count) || 0);
                const ctxC = document.getElementById('chartTopCooperativas').getContext('2d');
                if (chartTopCooperativas) chartTopCooperativas.destroy();
                chartTopCooperativas = new Chart(ctxC, {
                    type: 'bar',
                    data: {
                        labels: labelsC,
                        datasets: [{
                            data: valsC,
                            backgroundColor: 'rgba(79,70,229,0.85)',
                            borderRadius: 6
                        }]
                    },
                    options: Object.assign({}, chartDefaults(), {
                        indexAxis: 'y'
                    })
                });

                // Pedidos por mes (linea suave)
                const porMes = data.por_mes || [];
                const labelsM = porMes.map(r => r.ym);
                const valsM = porMes.map(r => Number(r.pedidos_count) || 0);
                const ctxM = document.getElementById('chartPedidosPorMes').getContext('2d');
                if (chartPedidosPorMes) chartPedidosPorMes.destroy();
                chartPedidosPorMes = new Chart(ctxM, {
                    type: 'line',
                    data: {
                        labels: labelsM,
                        datasets: [{
                            data: valsM,
                            borderColor: '#4b5563',
                            backgroundColor: 'rgba(79,70,229,0.12)',
                            tension: 0.4,
                            pointRadius: 2,
                            fill: true
                        }]
                    },
                    options: Object.assign({}, chartDefaults(), {
                        scales: {
                            x: {
                                grid: {
                                    color: 'rgba(200,200,200,0.06)'
                                }
                            },
                            y: {
                                beginAtZero: true
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

        // fecha + selects: validacion y eventos (debounce)
        function debounce(fn, wait = 450){ let t; return function(...args){ clearTimeout(t); t = setTimeout(()=>fn.apply(this,args), wait); }; }

        const startInput = document.getElementById('kpiCompactStart');
        const endInput = document.getElementById('kpiCompactEnd');
        const clearBtn = document.getElementById('kpiCompactClear');

        // coopSelect/prodSelect/operSelect ya están declarados arriba y disponibles aquí

        function validateAndApply(){
            const start = startInput.value || null;
            const end = endInput.value || null;
            const coop = coopSelect.value || null;
            const productor = prodSelect.value || null;
            const operativo = operSelect.value || null;

            if (start && end && end < start){
                statusEl.textContent = 'Rango inválido: "Hasta" debe ser >= "Desde"';
                return;
            }

            statusEl.textContent = 'Aplicando filtros...';
            loadKpis({ start_date: start, end_date: end, cooperativa: coop, productor: productor, operativo: operativo });
        }

        const applyDebounced = debounce(validateAndApply, 500);

        startInput.addEventListener('change', applyDebounced);
        endInput.addEventListener('change', applyDebounced);

        coopSelect.addEventListener('change', async () => {
            const coop = coopSelect.value || null;
            // cargar productores for cooperativa
            prodSelect.innerHTML = '<option value="">Productor (Todos)</option>';
            if (coop) {
                try {
                    const res = await fetch(apiUrl, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ action:'productores', cooperativa: coop }) });
                    const json = await res.json();
                    if (res.ok && json.ok && Array.isArray(json.data)) {
                        json.data.forEach(p => {
                            const o = document.createElement('option'); o.value = p.id; o.textContent = p.nombre; prodSelect.appendChild(o);
                        });
                    }
                } catch (e) { console.error('Error cargando productores', e); }
            }
            applyDebounced();
        });

        prodSelect.addEventListener('change', applyDebounced);
        operSelect.addEventListener('change', applyDebounced);

        clearBtn.addEventListener('click', () => {
            startInput.value = '';
            endInput.value = '';
            coopSelect.value = '';
            prodSelect.innerHTML = '<option value="">Productor (Todos)</option>';
            operSelect.value = '';
            statusEl.textContent = 'Filtros eliminados';
            loadKpis();
        });

        // carga inicial (también pobla cooperativas y operativos en background)
        loadKpis();
    })();
</script>