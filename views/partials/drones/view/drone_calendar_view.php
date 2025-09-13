<?php // views/partials/drones/view/drone_calendar_view.php ?>
<div class="content">
  <div class="card" style="background-color:#5b21b6;">
    <h3 style="color:white;">Módulo: Calendario</h3>
    <p style="color:white;margin:0;">Visitas programadas por mes.</p>
  </div>

  <div id="calendar-root" class="card" aria-live="polite">
    <div class="calendar-header" style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
      <div style="display:flex;align-items:center;gap:8px;">
        <button id="btn-today" class="btn btn-info" type="button" aria-label="Ir al mes actual">Hoy</button>
      </div>
      <h2 id="cal-title" style="margin:0;font-weight:700;"></h2>
      <div class="form-grid grid-3" style="gap:8px;">
        <button id="btn-prev" class="btn btn-cancelar" type="button" aria-label="Mes anterior">‹</button>
        <button id="btn-next" class="btn btn-aceptar" type="button" aria-label="Mes siguiente">›</button>
      </div>
    </div>

    <div id="calendar" class="calendar-grid" role="grid" aria-labelledby="cal-title" style="margin-top:12px;"></div>

    <div id="calendar-health" style="margin-top:8px;color:#64748b;">Verificando conexión…</div>
  </div>
</div>

<style>
/* CSS mínimo para el calendario, respetando tu framework */
.calendar-grid{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:.25rem}
.cal-cell{border:1px solid #e5e7eb;border-radius:.5rem;min-height:120px;padding:.5rem;position:relative;background:#fff}
.cal-cell .daynum{position:absolute;top:.4rem;right:.5rem;font-size:.85rem;color:#3b0764;font-weight:700}
.cal-cell.muted{background:#fafafa;color:#9ca3af}
.cal-cell.today{outline:2px solid #5b21b6;outline-offset:0}
.cal-events{margin:1.25rem 0 0 0;display:flex;flex-direction:column;gap:.25rem;max-height:90px;overflow:auto}
.cal-event{font-size:.85rem;line-height:1.15;background:#ede9fe;border-left:3px solid #5b21b6;border-radius:.25rem;padding:.15rem .35rem}
@media (max-width: 800px){
  .calendar-grid{grid-template-columns:repeat(7,1fr)}
  .cal-cell{min-height:100px}
}
</style>

<script>
(function () {
  'use strict';
  const API = '../partials/drones/controller/drone_calendar_controller.php';
  const root = document.getElementById('calendar');
  const titleEl = document.getElementById('cal-title');
  const healthEl = document.getElementById('calendar-health');
  const btnPrev = document.getElementById('btn-prev');
  const btnNext = document.getElementById('btn-next');
  const btnToday = document.getElementById('btn-today');

  // Estado
  let viewDate = new Date(); // mes visible

  // Utilidades
  const pad2 = n => String(n).padStart(2,'0');
  const monthLabel = (y,m)=> {
    const meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    return `${meses[m]} ${y}`;
  };
  const sameDay = (a,b)=> a.getFullYear()===b.getFullYear() && a.getMonth()===b.getMonth() && a.getDate()===b.getDate();

  // Render
  function renderSkeleton(date){
    const y = date.getFullYear();
    const m = date.getMonth(); // 0-11
    titleEl.textContent = monthLabel(y,m);
    root.innerHTML = '';

    // Encabezados (dom-lun-mar-...)
    const headers = ['Dom','Lun','Martes','Miércoles','Jue','Vie','Sábado'];
    headers.forEach(h=>{
      const th = document.createElement('div');
      th.className = 'cal-cell muted';
      th.setAttribute('role','columnheader');
      th.style.minHeight = 'auto';
      th.innerHTML = `<strong>${h}</strong>`;
      root.appendChild(th);
    });

    // Primer día a mostrar (domingo) y último
    const firstOfMonth = new Date(y,m,1);
    const start = new Date(firstOfMonth);
    start.setDate(firstOfMonth.getDate() - firstOfMonth.getDay()); // domingo antes o igual

    const lastOfMonth = new Date(y, m + 1, 0);
    const end = new Date(lastOfMonth);
    end.setDate(lastOfMonth.getDate() + (6 - lastOfMonth.getDay())); // sábado luego o igual

    // Crear celdas vacías (se llenan con eventos más tarde)
    for (let d = new Date(start); d <= end; d.setDate(d.getDate()+1)){
      const cell = document.createElement('div');
      const inMonth = d.getMonth() === m;
      cell.className = 'cal-cell' + (inMonth ? '' : ' muted') + (sameDay(d,new Date()) ? ' today' : '');
      cell.setAttribute('role','gridcell');
      cell.setAttribute('aria-label', `${d.toLocaleDateString('es-AR')}`);
      cell.innerHTML = `<span class="daynum">${d.getDate()}</span><div class="cal-events" data-date="${d.getFullYear()}-${pad2(d.getMonth()+1)}-${pad2(d.getDate())}"></div>`;
      root.appendChild(cell);
    }
  }

  // Cargar eventos del mes
  async function loadEvents(date){
    const y = date.getFullYear();
    const m = date.getMonth() + 1;
    try{
      const res = await fetch(`${API}?year=${y}&month=${m}`, {cache:'no-store'});
      const json = await res.json();
      if(!json || !json.ok) throw new Error(json?.error || 'No se pudo cargar el calendario');
      // Pintar
      json.data.forEach(ev=>{
        const c = root.querySelector(`.cal-events[data-date="${ev.fecha}"]`);
        if(!c) return;
        const item = document.createElement('div');
        item.className = 'cal-event';
        item.title = `${ev.nombre} · ${ev.hora_desde || ''}${ev.hora_hasta ? ' - ' + ev.hora_hasta : ''}`;
        const rango = (ev.hora_desde && ev.hora_hasta) ? `${ev.hora_desde}–${ev.hora_hasta}` : (ev.hora_desde || '');
        item.innerHTML = `<strong>${rango}</strong> · ${ev.nombre}`;
        c.appendChild(item);
      });
      healthEl.textContent = '';
    }catch(e){
      healthEl.innerHTML = '<strong style="color:#b91c1c;">Error:</strong> ' + (e?.message || e);
    }
  }

  function render(date){
    renderSkeleton(date);
    loadEvents(date);
  }

  // Listeners
  btnPrev.addEventListener('click', ()=>{ viewDate.setMonth(viewDate.getMonth()-1); render(viewDate); });
  btnNext.addEventListener('click', ()=>{ viewDate.setMonth(viewDate.getMonth()+1); render(viewDate); });
  btnToday.addEventListener('click', ()=>{ viewDate = new Date(); render(viewDate); });

  // Init
  render(viewDate);
})();
</script>
