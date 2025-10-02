<?php // views/partials/drones/view/drone_calendar_view.php 
?>
<div class="content">
  <div class="card" style="background-color:#5b21b6;">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
      <div>
        <h3 style="color:white;margin:0;">Calendario</h3>
        <p style="color:white;margin:0;">Visitas programadas. Vista por <span id="view-label">mes</span>.</p>
      </div>

      <!-- Filtros -->
      <form id="cal-filters" class="form-grid grid-3" style="gap:8px;min-width:320px;">
        <select id="filtro-piloto" class="input" aria-label="Filtrar por piloto">
          <option value="">Todos los pilotos</option>
        </select>
        <select id="filtro-zona" class="input" aria-label="Filtrar por zona">
          <option value="">Todas las zonas</option>
        </select>
        <div class="form-grid grid-2" style="gap:8px;">
          <button type="button" id="btn-view-month" class="btn btn-aceptar" aria-pressed="true">Mes</button>
          <button type="button" id="btn-view-week" class="btn btn-info" aria-pressed="false">Semana</button>
        </div>
      </form>
    </div>
  </div>

  <div id="calendar-root" class="card" aria-live="polite">
    <div class="calendar-header" style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
      <div style="display:flex;align-items:center;gap:8px;">
        <button id="btn-today" class="btn btn-info" type="button" aria-label="Ir a hoy">Hoy</button>
        <button id="btn-add-note-today" class="btn btn-aceptar" type="button" aria-label="Agregar nota hoy">+ Nota hoy</button>
      </div>
      <h2 id="cal-title" style="margin:0;font-weight:700;"></h2>
      <div class="form-grid grid-3" style="gap:8px;">
        <button id="btn-prev" class="btn btn-cancelar" type="button" aria-label="Anterior">‹</button>
        <button id="btn-next" class="btn btn-aceptar" type="button" aria-label="Siguiente">›</button>
      </div>
    </div>

    <div id="calendar" class="calendar-grid" role="grid" aria-labelledby="cal-title" style="margin-top:12px;"></div>

    <div id="calendar-health" style="margin-top:8px;color:#64748b;">Verificando conexión…</div>
  </div>

  <!-- Modal Nueva Nota -->
  <div id="modal" class="modal hidden" role="dialog" aria-modal="true" aria-labelledby="modal-title">
    <div class="modal-content">
      <h3 id="modal-title">Nueva nota</h3>
      <div class="input-group">
        <label for="modal-date">Fecha</label>
        <div class="input-icon input-icon-name">
          <input type="text" id="modal-date" name="fecha" placeholder="YYYY-MM-DD" readonly />
        </div>
      </div>
      <div class="input-group" style="margin-top:.5rem;">
        <label for="modal-text">Texto</label>
        <div class="input-icon input-icon-name">
          <input type="text" id="modal-text" name="texto" placeholder="Escribí la nota…" />
        </div>
      </div>
      <div class="form-buttons">
        <button class="btn btn-aceptar" id="btn-modal-accept" type="button">Aceptar</button>
        <button class="btn btn-cancelar" id="btn-modal-cancel" type="button">Cancelar</button>
      </div>
    </div>
  </div>


</div>

<style>
  /* CSS mínimo para el calendario, respetando tu framework */
  .calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
    gap: .25rem
  }

  .cal-cell {
    border: 1px solid #e5e7eb;
    border-radius: .5rem;
    min-height: 140px;
    padding: .5rem;
    position: relative;
    background: #fff
  }

  .cal-cell .daynum {
    position: absolute;
    top: .4rem;
    right: .5rem;
    font-size: .85rem;
    color: #3b0764;
    font-weight: 700
  }

  .cal-cell.muted {
    background: #fafafa;
    color: #9ca3af
  }

  .cal-cell.today {
    outline: 2px solid #5b21b6;
    outline-offset: 0
  }

  .cal-events {
    margin: 1.25rem 0 0 0;
    display: flex;
    flex-direction: column;
    gap: .25rem;
    max-height: 100px;
    overflow: auto
  }

  .cal-event {
    font-size: .85rem;
    line-height: 1.15;
    background: #ede9fe;
    border-left: 3px solid #5b21b6;
    border-radius: .25rem;
    padding: .15rem .35rem
  }

  .cal-note {
    font-size: .85rem;
    line-height: 1.15;
    background: #fef3c7;
    border-left: 3px solid #f59e0b;
    border-radius: .25rem;
    padding: .15rem .35rem;
    display: flex;
    justify-content: space-between;
    gap: .35rem
  }

  .cal-note .actions a {
    font-size: .8rem;
    text-decoration: underline;
    cursor: pointer
  }

  .cal-add {
    position: absolute;
    left: .5rem;
    bottom: .5rem
  }

  /* Semana: más espacio para tarjetas */
  .week-grid {
    display: grid;
    grid-template-columns: 100px repeat(7, 1fr);
    gap: .5rem
  }

  .week-grid .cal-cell {
    min-height: 240px
  }

  .week-grid .cal-events {
    max-height: 200px
  }

  .week-time {
    border: 1px solid #e5e7eb;
    border-radius: .5rem;
    background: #fff;
    min-height: 40px;
    padding: .25rem;
    font-size: .8rem;
    color: #64748b
  }

  /* Modal básico compatible con tu framework */
  .modal {
    position: fixed;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, .45);
    z-index: 1000
  }

  .modal.hidden {
    display: none
  }

  .modal-content {
    background: #fff;
    border-radius: .75rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, .2);
    padding: 1rem;
    max-width: 480px;
    width: 100%
  }

  .modal-content h3 {
    margin: .25rem 0 .5rem 0
  }

  .form-buttons {
    display: flex;
    gap: .5rem;
    justify-content: flex-end;
    margin-top: .75rem
  }

  /* Botón + compacto en celdas */
  .btn.btn-xs.cal-add {
    padding: .15rem .4rem;
    line-height: 1
  }

  @media (max-width: 800px) {
    .calendar-grid {
      grid-template-columns: repeat(7, 1fr)
    }

    .cal-cell {
      min-height: 120px
    }
  }
</style>

<script>
  (function() {
    'use strict';
    const API = '../partials/drones/controller/drone_calendar_controller.php';
    const root = document.getElementById('calendar');
    const titleEl = document.getElementById('cal-title');
    const healthEl = document.getElementById('calendar-health');
    const btnPrev = document.getElementById('btn-prev');
    const btnNext = document.getElementById('btn-next');
    const btnToday = document.getElementById('btn-today');
    const btnAddNoteToday = document.getElementById('btn-add-note-today');
    const filtroPiloto = document.getElementById('filtro-piloto');
    const filtroZona = document.getElementById('filtro-zona');
    const btnViewMonth = document.getElementById('btn-view-month');
    const btnViewWeek = document.getElementById('btn-view-week');
    const viewLabel = document.getElementById('view-label');

    let viewDate = new Date();
    let viewMode = 'month';
    let meta = { pilotos: [], zonas: [] };
    let currentData = { visitas: [], notas: [] };

    const pad2 = n => String(n).padStart(2, '0');
    const dISO = d => `${d.getFullYear()}-${pad2(d.getMonth()+1)}-${pad2(d.getDate())}`;
    const monthLabel = (y,m)=>['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'][m]+' '+y;
    const sameDay = (a,b)=>a.getFullYear()===b.getFullYear()&&a.getMonth()===b.getMonth()&&a.getDate()===b.getDate();
    const startOfWeek = d=>{const x=new Date(d);x.setDate(x.getDate()-x.getDay());x.setHours(0,0,0,0);return x;};
    const formatWeekRange = d0=>{const s=startOfWeek(d0);const e=new Date(s);e.setDate(s.getDate()+6);return `${s.toLocaleDateString('es-AR')} – ${e.toLocaleDateString('es-AR')}`;};

    async function fetchJSON(url, opts={}){const res=await fetch(url,opts);const json=await res.json();if(!json.ok) throw new Error(json.error||'Error de red');return json;}

    async function loadMeta(){
      const json = await fetchJSON(`${API}?action=meta`);
      meta = json.data;
      filtroPiloto.innerHTML = `<option value="">Todos los pilotos</option>` + meta.pilotos.map(p=>`<option value="${p.id}">${p.nombre} (ID ${p.id})</option>`).join('');
      filtroZona.innerHTML = `<option value="">Todas las zonas</option>` + meta.zonas.map(z=>`<option value="${z}">${z}</option>`).join('');
    }

    async function loadCalendar(date){
      const y=date.getFullYear(), m=date.getMonth()+1;
      const params=new URLSearchParams({year:String(y),month:String(m)});
      if(filtroPiloto.value) params.append('piloto_id', filtroPiloto.value);
      if(filtroZona.value) params.append('zona', filtroZona.value);
      const json = await fetchJSON(`${API}?${params.toString()}`);
      currentData = { visitas: json.data.visitas, notas: json.data.notas };
    }

    function renderMonth(date){
      const y=date.getFullYear(), m=date.getMonth();
      titleEl.textContent = monthLabel(y,m);
      root.className='calendar-grid'; root.innerHTML='';
      ['Dom','Lun','Martes','Miércoles','Jue','Vie','Sábado'].forEach(h=>{
        const th=document.createElement('div'); th.className='cal-cell muted'; th.style.minHeight='auto'; th.setAttribute('role','columnheader'); th.innerHTML=`<strong>${h}</strong>`; root.appendChild(th);
      });
      const first=new Date(y,m,1), start=new Date(first); start.setDate(first.getDate()-first.getDay());
      const last=new Date(y,m+1,0), end=new Date(last); end.setDate(last.getDate()+(6-last.getDay()));
      for(let d=new Date(start); d<=end; d.setDate(d.getDate()+1)){
        const iso=dISO(d), inMonth=d.getMonth()===m;
        const cell=document.createElement('div');
        cell.className='cal-cell'+(inMonth?'':' muted')+(sameDay(d,new Date())?' today':'');
        cell.setAttribute('role','gridcell'); cell.setAttribute('aria-label', d.toLocaleDateString('es-AR'));
        cell.innerHTML=`<span class="daynum">${d.getDate()}</span><div class="cal-events" data-date="${iso}"></div><button class="btn btn-xs cal-add btn-aceptar" data-date="${iso}" type="button">+</button>`;
        root.appendChild(cell);
      }
      paintData();
    }

    function renderWeek(date){
      const start=startOfWeek(date);
      titleEl.textContent=`Semana: ${formatWeekRange(start)}`;
      root.className='week-grid'; root.innerHTML='';
      const blank=document.createElement('div'); blank.className='cal-cell muted'; blank.style.minHeight='auto'; blank.innerHTML='<strong>Hora</strong>'; root.appendChild(blank);
      const dias=['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
      for(let i=0;i<7;i++){ const d=new Date(start); d.setDate(start.getDate()+i);
        const th=document.createElement('div'); th.className='cal-cell muted'; th.style.minHeight='auto'; th.innerHTML=`<strong>${dias[i]} ${d.getDate()}/${pad2(d.getMonth()+1)}</strong>`; root.appendChild(th);
      }
      const timeRow=document.createElement('div'); timeRow.className='week-time'; timeRow.textContent='Eventos y notas de la semana'; root.appendChild(timeRow);
      for(let i=0;i<7;i++){ const d=new Date(start); d.setDate(start.getDate()+i); const iso=dISO(d);
        const cell=document.createElement('div'); cell.className='cal-cell';
        cell.innerHTML=`<div class="cal-events" data-date="${iso}"></div><button class="btn btn-xs cal-add btn-aceptar" data-date="${iso}" type="button">+</button>`;
        if(sameDay(d,new Date())) cell.classList.add('today');
        root.appendChild(cell);
      }
      paintData();
    }

    function paintData(){
      root.querySelectorAll('.cal-events').forEach(el=>el.innerHTML='');
      currentData.visitas.forEach(ev=>{
        const c=root.querySelector(`.cal-events[data-date="${ev.fecha}"]`); if(!c) return;
        const item=document.createElement('div'); item.className='cal-event';
        const rango=(ev.hora_desde&&ev.hora_hasta)?`${ev.hora_desde}–${ev.hora_hasta}`:(ev.hora_desde||'');
        const meta=[ev.nombre, ev.piloto?`Piloto: ${ev.piloto}`:'', ev.zona?`Zona: ${ev.zona}`:''].filter(Boolean).join(' · ');
        item.title=`${ev.nombre}${ev.piloto?' · Piloto: '+ev.piloto:''}${ev.zona?' · Zona: '+ev.zona:''}`;
        item.innerHTML=`<strong>${rango}</strong> · ${meta}`;
        c.appendChild(item);
      });
      currentData.notas.forEach(n=>{
        const c=root.querySelector(`.cal-events[data-date="${n.fecha}"]`); if(!c) return;
<!-- Modal Confirmación -->
<div id="confirm-modal" class="modal hidden" role="dialog" aria-modal="true" aria-labelledby="confirm-title">
  <div class="modal-content">
    <h3 id="confirm-title">Confirmar acción</h3>
    <p id="confirm-message">¿Seguro que deseas continuar?</p>
    <div class="form-buttons">
      <button class="btn btn-aceptar" id="btn-confirm-yes" type="button">Sí</button>
      <button class="btn btn-cancelar" id="btn-confirm-no" type="button">Cancelar</button>
    </div>
  </div>
</div>
      });
      root.querySelectorAll('.cal-add').forEach(btn=>{ btn.onclick=()=>createNote(btn.dataset.date); });
      root.querySelectorAll('.cal-note .actions a').forEach(a=>{
        const id=a.getAttribute('data-id'), action=a.getAttribute('data-action');
        a.onclick=()=>{ if(action==='edit') editNote(id); if(action==='del') deleteNote(id); };
      });
    }

    async function createNote(fecha){ openModal(fecha, '', null); }

    async function editNote(id){
      const actual=currentData.notas.find(n=>String(n.id)===String(id)); if(!actual) return;
      openModal(actual.fecha, actual.texto, actual.id);
    }

    async function deleteNote(id){
      const actual=currentData.notas.find(n=>String(n.id)===String(id));
      const textoPreview = actual ? (actual.texto.length>60 ? actual.texto.slice(0,60)+'…' : actual.texto) : '';
      openConfirm(`¿Eliminar la nota${textoPreview ? `: “${textoPreview}”` : ''}?`, async ()=>{
        const body=new FormData(); body.append('action','note_delete'); body.append('id',String(id));
        await fetchJSON(API,{method:'POST', body});
        closeConfirm();
        await reloadAndRender();
      });
    }

    async function reloadAndRender(){ await loadCalendar(viewDate); render(); healthEl.textContent=''; }
    function render(){ viewMode==='month'?renderMonth(viewDate):renderWeek(viewDate); }

    btnPrev.addEventListener('click', async ()=>{ viewMode==='month'?viewDate.setMonth(viewDate.getMonth()-1):viewDate.setDate(viewDate.getDate()-7); await reloadAndRender(); });
    btnNext.addEventListener('click', async ()=>{ viewMode==='month'?viewDate.setMonth(viewDate.getMonth()+1):viewDate.setDate(viewDate.getDate()+7); await reloadAndRender(); });
    btnToday.addEventListener('click', async ()=>{ viewDate=new Date(); await reloadAndRender(); });
    btnAddNoteToday.addEventListener('click', ()=> createNote(dISO(new Date())));
    filtroPiloto.addEventListener('change', reloadAndRender);
    filtroZona.addEventListener('change', reloadAndRender);

    function setView(mode){
      viewMode=mode; viewLabel.textContent=mode==='month'?'mes':'semana';
      btnViewMonth.classList.toggle('btn-aceptar', mode==='month'); btnViewMonth.classList.toggle('btn-info', mode!=='month');
      btnViewWeek.classList.toggle('btn-aceptar', mode==='week'); btnViewWeek.classList.toggle('btn-info', mode!=='week');
      render();
    }
    btnViewMonth.addEventListener('click', ()=> setView('month'));
    btnViewWeek.addEventListener('click', ()=> setView('week'));

    // ===== Modal dentro del IIFE =====
    const modal=document.getElementById('modal');
    const modalText=document.getElementById('modal-text');
    const modalDate=document.getElementById('modal-date');
    const btnModalAccept=document.getElementById('btn-modal-accept');
    const btnModalCancel=document.getElementById('btn-modal-cancel');
    const modalTitle=document.getElementById('modal-title');

    // Estado del modal (null = crear)
    let editNoteId = null;

    function openModal(fecha, textoInicial = '', noteId = null){
      editNoteId = noteId;
      modalDate.value = fecha;
      modalText.value = textoInicial || '';
      modalTitle.textContent = editNoteId ? 'Actualizar nota' : 'Nueva nota';
      btnModalAccept.textContent = editNoteId ? 'Actualizar' : 'Aceptar';
      modal.classList.remove('hidden');
      setTimeout(()=> modalText.focus(), 0);
    }
    function closeModal(){
      modal.classList.add('hidden');
      editNoteId = null;
    }
    btnModalCancel.addEventListener('click', closeModal);
    modal.addEventListener('click', e=>{ if(e.target===modal) closeModal(); });
    document.addEventListener('keydown', e=>{ if(!modal.classList.contains('hidden') && e.key==='Escape') closeModal(); });

    btnModalAccept.addEventListener('click', async ()=>{
      const fecha = modalDate.value.trim();
      const texto = modalText.value.trim();
      if(!fecha || !texto){ alert('Completá el texto de la nota.'); return; }

      const body = new FormData();

      if(editNoteId){
        body.append('action','note_update');
        body.append('id', String(editNoteId));
        body.append('texto', texto);
      }else{
        body.append('action','note_create');
        body.append('fecha', fecha);
        body.append('texto', texto);
        if(filtroPiloto.value) body.append('piloto_id', filtroPiloto.value);
        if(filtroZona.value) body.append('zona', filtroZona.value);
      }

      try{
        await fetchJSON(API,{ method:'POST', body });
        closeModal();
        await reloadAndRender();
      }catch(e){
        alert(e?.message || 'No se pudo guardar la nota');
      }
    });

    // ===== Modal de Confirmación =====
    const confirmModal = document.getElementById('confirm-modal');
    const confirmMsg   = document.getElementById('confirm-message');
    const btnConfirmYes= document.getElementById('btn-confirm-yes');
    const btnConfirmNo = document.getElementById('btn-confirm-no');
    let confirmCb = null;

    function openConfirm(message, onYes){
      confirmMsg.textContent = message || '¿Seguro que deseas continuar?';
      confirmCb = typeof onYes === 'function' ? onYes : null;
      confirmModal.classList.remove('hidden');
    }
    function closeConfirm(){
      confirmModal.classList.add('hidden');
      confirmCb = null;
    }
    btnConfirmNo.addEventListener('click', closeConfirm);
    confirmModal.addEventListener('click', e=>{ if(e.target === confirmModal) closeConfirm(); });
    document.addEventListener('keydown', e=>{ if(!confirmModal.classList.contains('hidden') && e.key==='Escape') closeConfirm(); });
    btnConfirmYes.addEventListener('click', async ()=>{
      try{ if(confirmCb) await confirmCb(); }
      finally{ /* confirmCb ejecuta closeConfirm para evitar doble cierre */ }
    });

    function openConfirm(message, onYes){
      confirmMsg.textContent = message || '¿Seguro que deseas continuar?';
      confirmCb = typeof onYes === 'function' ? onYes : null;
      confirmModal.classList.remove('hidden');
    }
    function closeConfirm(){
      confirmModal.classList.add('hidden');
      confirmCb = null;
    }
    btnConfirmNo.addEventListener('click', closeConfirm);
    confirmModal.addEventListener('click', e=>{ if(e.target === confirmModal) closeConfirm(); });
    document.addEventListener('keydown', e=>{ if(!confirmModal.classList.contains('hidden') && e.key==='Escape') closeConfirm(); });
    btnConfirmYes.addEventListener('click', async ()=>{
      try{ if(confirmCb) await confirmCb(); }
      finally{ /* confirmCb ejecuta closeConfirm para evitar doble cierre */ }
    });


    (async function init(){
      try{ await loadMeta(); await loadCalendar(viewDate); render(); healthEl.textContent=''; }
      catch(e){ healthEl.innerHTML='<strong style="color:#b91c1c;">Error:</strong> '+(e?.message || e); }
    })();
  })();
</script>
