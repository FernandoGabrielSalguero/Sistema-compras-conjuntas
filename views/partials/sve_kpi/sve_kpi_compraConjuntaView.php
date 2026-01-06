<div class="sve-kpi-compra-conjunta" style="padding:10px;border:1px solid rgba(0,0,0,.08);border-radius:8px;background:#fafafa;">
    <b>KPI Compra Conjunta</b>
    <div id="sveKpiStatus" style="margin-top:6px;font-size:14px;opacity:.9;">Cargando KPIs...</div>

    <!-- filtros -->
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px">
        <div style="min-width:150px">
            <label style="font-size:12px;display:block">Desde</label>
            <input type="date" id="kpiStartDate" style="width:100%" />
        </div>
        <div style="min-width:150px">
            <label style="font-size:12px;display:block">Hasta</label>
            <input type="date" id="kpiEndDate" style="width:100%" />
        </div>
        <div style="min-width:220px">
            <label style="font-size:12px;display:block">Cooperativa</label>
            <select id="kpiCoop" style="width:100%"><option value="">Todas</option></select>
        </div>
        <div style="display:flex;align-items:end;gap:8px">
            <button id="kpiApplyBtn" class="btn">Filtrar</button>
            <button id="kpiClearBtn" class="btn btn-outline">Limpiar</button>
        </div>
    </div>

    <div id="sveKpiGrid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;margin-top:12px;">
        <div class="kpi-card" id="kpi-resumen" style="padding:8px;border-radius:6px;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.04);">
            <h4>Resumen</h4>
            <div id="kpi-resumen-content">Cargando...</div>
        </div>

        <div class="kpi-card" style="padding:8px;border-radius:6px;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.04);">
            <h4>Top Productos</h4>
            <canvas id="chartTopProductos" style="width:100%;height:200px;"></canvas>
        </div>

        <div class="kpi-card" style="padding:8px;border-radius:6px;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.04);">
            <h4>Top Cooperativas</h4>
            <canvas id="chartTopCooperativas" style="width:100%;height:200px;"></canvas>
        </div>

        <div class="kpi-card" style="padding:8px;border-radius:6px;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.04);">
            <h4>Top Productores</h4>
            <div id="topProductoresList">Cargando...</div>
        </div>

        <div class="kpi-card" style="padding:8px;border-radius:6px;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.04);grid-column:1 / -1;">
            <h4>Pedidos por mes</h4>
            <canvas id="chartPedidosPorMes" style="width:100%;height:260px;"></canvas>
        </div>
    </div>
</div>

<!-- CDN Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(async () => {
  const statusEl = document.getElementById('sveKpiStatus');
  const apiUrl = '../partials/sve_kpi/sve_kpi_compraConjuntaController.php';

  let chartTopProductos = null;
  let chartTopCooperativas = null;
  let chartPedidosPorMes = null;

  function handleError(e) {
    statusEl.textContent = 'Error: ' + (e && e.message ? e.message : String(e));
    console.error(e);
  }

  async function loadKpis(filters = {}) {
    try {
      statusEl.textContent = 'Cargando datos...';
      const payload = Object.assign({ action: 'kpis', limit: 10, months: 6 }, filters);
      const res = await fetch(apiUrl, {
        method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
      });

      const json = await res.json();
      if (!res.ok || !json.ok) throw new Error(json.error || 'Error al obtener KPIs');

      const data = json.data || {};

      // poblar select cooperativas si viene
      const coopSelect = document.getElementById('kpiCoop');
      if (data.cooperativas && coopSelect.options.length <= 1) {
        data.cooperativas.forEach(c => {
          const opt = document.createElement('option'); opt.value = c.id; opt.textContent = c.nombre; coopSelect.appendChild(opt);
        });
      }

      // resumen
      const resumen = data.resumen || {};
      const resumenEl = document.getElementById('kpi-resumen-content');
      resumenEl.innerHTML = `
        <div style="display:flex;gap:8px;flex-wrap:wrap">
          <div style="min-width:120px">
            <strong style="font-size:20px">${resumen.total_pedidos ?? 0}</strong><div style="font-size:12px;color:#666">Total pedidos</div>
          </div>
          <div style="min-width:140px">
            <strong style="font-size:20px">$${(parseFloat(resumen.total_monto) || 0).toFixed(2)}</strong><div style="font-size:12px;color:#666">Monto total</div>
          </div>
          <div style="min-width:140px">
            <strong style="font-size:20px">$${(parseFloat(resumen.avg_monto) || 0).toFixed(2)}</strong><div style="font-size:12px;color:#666">Promedio / pedido</div>
          </div>
        </div>
      `;

      // Top Productos
      const topProds = data.top_products || [];
      const labelsP = topProds.map(p => p.nombre_producto);
      const qtysP = topProds.map(p => parseFloat(p.total_cantidad) || 0);
      const ctxP = document.getElementById('chartTopProductos').getContext('2d');
      if (chartTopProductos) { chartTopProductos.destroy(); }
      chartTopProductos = new Chart(ctxP, { type: 'bar', data: { labels: labelsP, datasets: [{ label: 'Cantidad', data: qtysP, backgroundColor: '#5b21b6' }] }, options: { responsive: true, plugins: { legend: { display: false } } } });

      // Top Cooperativas
      const topCoops = data.top_cooperativas || [];
      const labelsC = topCoops.map(c => c.nombre);
      const countsC = topCoops.map(c => parseInt(c.pedidos_count) || 0);
      const ctxC = document.getElementById('chartTopCooperativas').getContext('2d');
      if (chartTopCooperativas) { chartTopCooperativas.destroy(); }
      chartTopCooperativas = new Chart(ctxC, { type: 'bar', data: { labels: labelsC, datasets: [{ label: 'Pedidos', data: countsC, backgroundColor: '#059669' }] }, options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } } } });

      // Top Productores list
      const topProdList = data.top_productores || [];
      const listEl = document.getElementById('topProductoresList');
      if (topProdList.length === 0) {
        listEl.textContent = 'No hay datos';
      } else {
        listEl.innerHTML = '<ol style="padding-left:18px;margin:0">' + topProdList.map(p => `<li style="margin-bottom:6px"><strong>${p.nombre}</strong> — ${p.pedidos_count} pedidos</li>`).join('') + '</ol>';
      }

      // Pedidos por mes
      const porMes = data.por_mes || [];
      const labelsM = porMes.map(r => r.ym);
      const valsM = porMes.map(r => parseInt(r.pedidos_count) || 0);
      const ctxM = document.getElementById('chartPedidosPorMes').getContext('2d');
      if (chartPedidosPorMes) { chartPedidosPorMes.destroy(); }
      chartPedidosPorMes = new Chart(ctxM, { type: 'line', data: { labels: labelsM, datasets: [{ label: 'Pedidos', data: valsM, borderColor: '#1f2937', backgroundColor: 'rgba(31,41,55,0.06)', fill: true }] }, options: { responsive: true, plugins: { legend: { display: false } } } });

      statusEl.textContent = 'KPIs cargados correctamente';
    } catch (e) {
      handleError(e);
    }
  }

  // eventos
  document.getElementById('kpiApplyBtn').addEventListener('click', () => {
    const start = document.getElementById('kpiStartDate').value || null;
    const end = document.getElementById('kpiEndDate').value || null;
    const coop = document.getElementById('kpiCoop').value || null;
    loadKpis({ start_date: start, end_date: end, cooperativa: coop });
  });

  document.getElementById('kpiClearBtn').addEventListener('click', () => {
    document.getElementById('kpiStartDate').value = '';
    document.getElementById('kpiEndDate').value = '';
    document.getElementById('kpiCoop').value = '';
    loadKpis();
  });

  // carga inicial
  loadKpis();
})();
</script>
