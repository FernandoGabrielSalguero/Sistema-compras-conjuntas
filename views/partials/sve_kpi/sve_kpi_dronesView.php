<style>
    /* Reutiliza estilos compactos de los KPI */
    .sve-kpi-drones.compact {
        padding: 12px;
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04);
        height: auto;
        min-height: 0;
        overflow: visible;
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 12px;
        align-items: stretch;
        position: relative;
    }
    @media (max-width:900px) { .sve-kpi-drones.compact { grid-template-columns: 1fr; height:auto } }

    .mini-stats { display:flex; gap:10px }
    .mini-stat { flex:1; background:#f8fafc; border-radius:8px; padding:10px; display:flex; align-items:center; gap:10px }
    .mini-stat .value { font-weight:700; font-size:18px; color:#111 }
    .mini-stat .label { font-size:12px; color:#6b7280 }

    .kpi-charts { display:flex; flex-direction:column; gap:8px; height:100% }
    .small-chart { flex:1; background:#fff; border-radius:8px; display:flex; align-items:center; justify-content:center; padding:6px; box-shadow:0 1px 2px rgba(0,0,0,0.02) }
    /* Ajusta la altura de los canvas compactos aquí. */
    .canvas-small{ width:100%; height:120px !important }
    .canvas-compact{ width:100%; height:120px !important }



    .kpi-filters-inline { display:flex; gap:6px; align-items:center }
    .kpi-filters-inline input, .kpi-filters-inline select { height:28px; padding:4px 6px; font-size:12px; border-radius:6px; border:1px solid #e5e7eb; background:#fff }
    .kpi-filters-inline button { height:28px; padding:4px 8px; border-radius:6px; border:1px solid #e5e7eb; background:transparent; color:#6b7280 }
    @media (max-width:600px){ .kpi-filters-inline{ display:none } }
</style>

<div class="sve-kpi-drones compact">
    <div class="kpi-left">
        <div style="display:flex;justify-content:space-between;align-items:center">
            <div style="display:flex;align-items:center;gap:8px">
                <div class="kpi-filters-inline" role="group" aria-label="Filtros KPI Drones">
                    <!-- <select id="kpiProdSelect" class="gform-input" style="min-width:160px">
                        <option value="">Productor (Todos)</option>
                    </select> -->
                    <select id="kpiGroupBy" class="gform-input" style="min-width:140px">
                        <option value="month" >Agrupar por: Mes</option>
                        <option value="date" selected>Agrupar por: Fecha</option>
                    </select>
                    <select id="kpiEstadoSelect" class="gform-input" style="min-width:160px">
                        <option value="">Estado (Todos)</option>
                        <option value="ingresada">Ingresada</option>
                        <option value="procesando">Procesando</option>
                        <option value="aprobada_coop">Aprobada coop</option>
                        <option value="cancelada">Cancelada</option>
                        <option value="completada">Completada</option>
                        <option value="visita_realizada">Visita realizada</option>
                    </select>
                    <input id="kpiCompactStart" type="date" />
                    <input id="kpiCompactEnd" type="date" />
                    <button id="kpiCompactClear" title="Limpiar">✕</button>
                </div>
            </div>
            <div id="sveKpiDronesStatus" style="font-size:12px;color:#6b7280">Cargando...</div>
        </div>

        <div class="mini-stats">
            <div class="mini-stat" id="mini-total-solicitudes">
                <div>
                    <div class="value" id="miniTotalSolicitudes">0</div>
                    <div class="label">Total solicitudes</div>
                </div>
            </div>

            <div class="mini-stat" id="mini-total-completadas">
                <div>
                    <div class="value" id="miniTotalCompletadas">0</div>
                    <div class="label">Completadas</div>
                </div>
            </div>

            <div class="mini-stat" id="mini-total-total">
                <div>
                    <div class="value" id="miniTotal">$0.00</div>
                    <div class="label">Total</div>
                </div>
            </div>
        </div> 

        <div class="kpi-charts">
            <div class="small-chart" style="padding:6px">
                <canvas id="chartSolicitudesPorMes" class="canvas-compact"></canvas>
            </div>
        </div>
    </div>

    <div class="kpi-right">
        <div class="small-chart">
            <canvas id="chartTopProductos" class="canvas-small"></canvas>
        </div>
    </div> 
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (async () => {
        const statusEl = document.getElementById('sveKpiDronesStatus');
        const apiUrl = '../partials/sve_kpi/sve_kpi_dronesController.php';

        let chartTopProductos = null;
        let chartSolicitudesPorMes = null;

        const prodSelect = document.getElementById('kpiProdSelect');
        const groupSelect = document.getElementById('kpiGroupBy');
        const estadoSelect = document.getElementById('kpiEstadoSelect');

        const fmtMoney = (v) => (Number(v) ? '$' + Number(v).toLocaleString('es-AR', { minimumFractionDigits:2, maximumFractionDigits:2 }) : '$0.00');
        const fmtNum = (v) => (Number(v) ? Number(v).toLocaleString('es-AR') : '0');

        function chartDefaults() {
            return {
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: 'rgba(200,200,200,0.15)', borderDash: [4,3] }, ticks: { color:'#6b7280', font:{size:11} } },
                    y: { grid: { color: 'rgba(200,200,200,0.06)', borderDash:[4,3] }, ticks: { color:'#6b7280', font:{size:11} } }
                }
            };
        }

        async function loadKpis(filters = {}) {
            try {
                statusEl.textContent = 'Cargando...';
                const payload = Object.assign({ action: 'kpis', limit: 6, months: 6 }, filters);
                const res = await fetch(apiUrl, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
                const json = await res.json();
                if (!res.ok || !json.ok) throw new Error(json.error || 'Error al obtener KPIs');

                const data = json.data || {};

                // poblar select de productores (solo primera vez)
                try {
                    if (data.productores && prodSelect && prodSelect.options.length <= 1) {
                        data.productores.forEach(p => { const o = document.createElement('option'); o.value = p.id; o.textContent = p.nombre; prodSelect.appendChild(o); });
                        if (filters && filters.productor) prodSelect.value = filters.productor;
                    }
                    if (filters && filters.group_by && groupSelect) groupSelect.value = filters.group_by;
                } catch (e) { console.error('Error poblando selects', e); }

                // mini-stats
                const resumen = data.resumen || {};
                document.getElementById('miniTotalSolicitudes').textContent = fmtNum(resumen.total_solicitudes || 0);
                document.getElementById('miniTotalCompletadas').textContent = fmtNum(resumen.completadas_count || 0);
                document.getElementById('miniTotal').textContent = fmtMoney(resumen.total_monto || 0);
                // top productos (vertical bar)
                const topProds = data.top_products || [];
                const labelsP = topProds.map(p => p.nombre_producto);
                const valsP = topProds.map(p => Number(p.usos_count) || 0);
                const canvasP = document.getElementById('chartTopProductos');
                const ctxP = canvasP.getContext('2d');
                const existingP = Chart.getChart(canvasP) || Chart.getChart('chartTopProductos');
                if (existingP) existingP.destroy();
                if (chartTopProductos) try { chartTopProductos.destroy(); } catch(e){}
                chartTopProductos = new Chart(ctxP, { type:'bar', data:{ labels:labelsP, datasets:[{ data:valsP, backgroundColor:'rgba(99,102,241,0.9)', borderRadius:6 }] }, options: Object.assign({}, chartDefaults()) });

                // doughnut de estados eliminado (removido) - ya no se envía ni se renderiza en esta vista

                // solicitudes por mes/fecha (line) - tooltip con cantidad y etiquetas de fecha verticales
                const porMes = data.por_mes || [];
                const labelsM = porMes.map(r => r.ym);
                const valsM = porMes.map(r => Number(r.solicitudes_count) || 0);
                const canvasM = document.getElementById('chartSolicitudesPorMes');
                const ctxM = canvasM.getContext('2d');
                const existingM = Chart.getChart(canvasM) || Chart.getChart('chartSolicitudesPorMes');
                if (existingM) existingM.destroy();
                if (chartSolicitudesPorMes) try { chartSolicitudesPorMes.destroy(); } catch(e){}

                // Formatea la etiqueta del eje X en varias líneas: [día, mes, año].
                // Para cambiar el tamaño de las fechas, modifica `ticks.font.size` más abajo.
                function formatTickLabel(label){
                    if(!label) return [''];
                    const parts = String(label).split('-'); // espera 'YYYY-MM-DD' o 'YYYY-MM'
                    if(parts.length===3){ const [y,m,d]=parts; return [d, m, y]; }
                    if(parts.length===2){ const [y,m]=parts; return [m, y]; }
                    return [label];
                }

                chartSolicitudesPorMes = new Chart(ctxM, {
                    type:'line',
                    data:{ labels:labelsM, datasets:[{ data:valsM, borderColor:'#4b5563', backgroundColor:'rgba(79,70,229,0.12)', tension:0.4, pointRadius:3, pointHoverRadius:6, fill:true }] },
                    options: Object.assign({}, chartDefaults(), {
                        scales: {
                            x: {
                                grid:{ color:'rgba(200,200,200,0.06)' },
                                ticks: {
                                    callback: function(value, index){ const lab = this.chart.data.labels[index]; return formatTickLabel(lab); },
                                    color: '#6b7280',
                                    font: { size: 10 },
                                    maxRotation: 0,
                                    autoSkip: true
                                }
                            },
                            y: { beginAtZero:true }
                        },
                        plugins: {
                            tooltip: {
                                enabled: true,
                                callbacks: {
                                    title: function(items){ const idx = items[0].dataIndex; const lab = labelsM[idx] || items[0].label; const parts = String(lab).split('-'); if(parts.length===3){ return `${parts[2]}/${parts[1]}/${parts[0]}` } if(parts.length===2){ return `${parts[1]}/${parts[0]}` } return lab; },
                                    label: function(context){ return `Cantidad: ${context.formattedValue}`; }
                                }
                            },
                            legend: { display: false }
                        }
                    })
                });

                // Top label eliminado: mostramos solo estado básico
                statusEl.textContent = 'Actualizado';
            } catch (e) {
                statusEl.textContent = 'Error';
                console.error(e);
            }
        }

        function debounce(fn, wait = 450){ let t; return function(...args){ clearTimeout(t); t = setTimeout(()=>fn.apply(this,args), wait); }; }

        const startInput = document.getElementById('kpiCompactStart');
        const endInput = document.getElementById('kpiCompactEnd');
        const clearBtn = document.getElementById('kpiCompactClear');

        function validateAndApply(){
            const start = startInput.value || null;
            const end = endInput.value || null;
            const productor = prodSelect.value || null;
            const estado = estadoSelect.value || null;
            const group = groupSelect ? groupSelect.value : 'month';

            if (start && end && end < start){ statusEl.textContent = 'Rango inválido: "Hasta" debe ser >= "Desde"'; return; }
            statusEl.textContent = 'Aplicando filtros...';
            loadKpis({ start_date: start, end_date: end, productor: productor, estado: estado, group_by: group });
        }

        const applyDebounced = debounce(validateAndApply, 500);
        startInput.addEventListener('change', applyDebounced);
        endInput.addEventListener('change', applyDebounced);

        prodSelect.addEventListener('change', applyDebounced);
        estadoSelect.addEventListener('change', applyDebounced);
        if (groupSelect) groupSelect.addEventListener('change', applyDebounced);

        clearBtn.addEventListener('click', () => {
            startInput.value = '';
            endInput.value = '';
            if (groupSelect) groupSelect.value = 'month';
            prodSelect.value = '';
            estadoSelect.value = '';
            statusEl.textContent = 'Filtros eliminados';
            loadKpis();
        });

        // carga inicial
        loadKpis({ group_by: (groupSelect ? groupSelect.value : 'month') });
    })();
</script>