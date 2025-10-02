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

  <!-- Modal Crear/Editar Nota -->
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
        <button class="btn btn-aceptar" id="btn-modal-accept" type="button">Guardar</button>
        <button class="btn btn-cancelar" id="btn-modal-cancel" type="button">Cancelar</button>
      </div>
    </div>
  </div>

  <!-- Modal Confirmación genérico -->
  <div id="confirm-modal" class="modal hidden" role="dialog" aria-modal="true" aria-labelledby="confirm-title">
    <div class="modal-content">
      <h3 id="confirm-title">Confirmación</h3>
      <p id="confirm-message" style="margin:.5rem 0 1rem 0;">¿Estás seguro?</p>
      <div class="form-buttons">
        <button class="btn btn-aceptar" id="btn-confirm-accept" type="button">Sí</button>
        <button class="btn btn-cancelar" id="btn-confirm-cancel" type="button">No</button>
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

  /* Altura de las celdas en vista semanal.
     ⬇️ Ajustá estos dos valores para “extender” o “compactar” los días */
  .week-grid .cal-cell {
    min-height: 320px
      /* ← ajustar aquí */
  }

  .week-grid .cal-events {
    max-height: 260px
      /* ← y aquí */
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

  /* Tamaño mínimo para modales de confirmación */
  #confirm-modal .modal-content {
    max-width: 420px;
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

    // Estado
    let viewDate = new Date(); // referencia
    let viewMode = 'month'; // 'month' | 'week'
    let meta = {
      pilotos: [],
      zonas: []
    };
    let currentData = {
      visitas: [],
      notas: []
    };

    // Utilidades
    const pad2 = n => String(n).padStart(2, '0');
    const dISO = (d) => `${d.getFullYear()}-${pad2(d.getMonth()+1)}-${pad2(d.getDate())}`;
    const monthLabel = (y, m) => {
      const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
      return `${meses[m]} ${y}`;
    };
    const formatWeekRange = (d0) => {
      const start = startOfWeek(d0);
      const end = new Date(start);
      end.setDate(start.getDate() + 6);
      return `${start.toLocaleDateString('es-AR')} – ${end.toLocaleDateString('es-AR')}`;
    };
    const sameDay = (a, b) => a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate();
    const startOfWeek = (d) => {
      const x = new Date(d);
      x.setDate(x.getDate() - x.getDay());
      x.setHours(0, 0, 0, 0);
      return x;
    };

    // API helpers
    async function fetchJSON(url, opts = {}) {
      const res = await fetch(url, opts);
      const json = await res.json();
      if (!json.ok) throw new Error(json.error || 'Error de red');
      return json;
    }

    async function loadMeta() {
      const json = await fetchJSON(`${API}?action=meta`);
      meta = json.data;
      // pilotos
      filtroPiloto.innerHTML = `<option value="">Todos los pilotos</option>` +
        meta.pilotos.map(p => `<option value="${p.id}">${p.nombre} (ID ${p.id})</option>`).join('');
      // zonas
      filtroZona.innerHTML = `<option value="">Todas las zonas</option>` +
        meta.zonas.map(z => `<option value="${z}">${z}</option>`).join('');
    }

    async function loadCalendar(date) {
      const y = date.getFullYear();
      const m = date.getMonth() + 1;
      const params = new URLSearchParams({
        year: String(y),
        month: String(m)
      });
      if (filtroPiloto.value) params.append('piloto_id', filtroPiloto.value);
      if (filtroZona.value) params.append('zona', filtroZona.value);
      const json = await fetchJSON(`${API}?${params.toString()}`);
      currentData = {
        visitas: json.data.visitas,
        notas: json.data.notas
      };
    }

    // Render
    function renderMonth(date) {
      const y = date.getFullYear();
      const m = date.getMonth(); // 0-11
      titleEl.textContent = monthLabel(y, m);
      root.className = 'calendar-grid';
      root.innerHTML = '';

      // Encabezados
      const headers = ['Dom', 'Lun', 'Martes', 'Miércoles', 'Jue', 'Vie', 'Sábado'];
      headers.forEach(h => {
        const th = document.createElement('div');
        th.className = 'cal-cell muted';
        th.setAttribute('role', 'columnheader');
        th.style.minHeight = 'auto';
        th.innerHTML = `<strong>${h}</strong>`;
        root.appendChild(th);
      });

      // Rango a mostrar (domingo..sábado)
      const firstOfMonth = new Date(y, m, 1);
      const start = new Date(firstOfMonth);
      start.setDate(firstOfMonth.getDate() - firstOfMonth.getDay());
      const lastOfMonth = new Date(y, m + 1, 0);
      const end = new Date(lastOfMonth);
      end.setDate(lastOfMonth.getDate() + (6 - lastOfMonth.getDay()));

      for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
        const inMonth = d.getMonth() === m;
        const iso = dISO(d);
        const cell = document.createElement('div');
        cell.className = 'cal-cell' + (inMonth ? '' : ' muted') + (sameDay(d, new Date()) ? ' today' : '');
        cell.setAttribute('role', 'gridcell');
        cell.setAttribute('aria-label', `${d.toLocaleDateString('es-AR')}`);
        cell.innerHTML = `
        <span class="daynum">${d.getDate()}</span>
        <div class="cal-events" data-date="${iso}"></div>
        <button class="btn btn-xs cal-add btn-aceptar" data-date="${iso}" type="button">+</button>
      `;
        root.appendChild(cell);
      }

      paintData();
    }

    function renderWeek(date) {
      const start = startOfWeek(date);
      titleEl.textContent = `Semana: ${formatWeekRange(start)}`;
      root.className = 'week-grid';
      root.innerHTML = '';

      // encabezado vacío para columna de horas
      const blank = document.createElement('div');
      blank.className = 'cal-cell muted';
      blank.style.minHeight = 'auto';
      blank.innerHTML = '<strong>Hora</strong>';
      root.appendChild(blank);

      // encabezados días
      const dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
      for (let i = 0; i < 7; i++) {
        const d = new Date(start);
        d.setDate(start.getDate() + i);
        const th = document.createElement('div');
        th.className = 'cal-cell muted';
        th.style.minHeight = 'auto';
        th.innerHTML = `<strong>${dias[i]} ${d.getDate()}/${pad2(d.getMonth()+1)}</strong>`;
        root.appendChild(th);
      }

      // solo 1 bloque de "contenidos" por día (simple, no por hora)
      const timeRow = document.createElement('div');
      timeRow.className = 'week-time';
      timeRow.textContent = 'Eventos y notas de la semana';
      root.appendChild(timeRow);

      for (let i = 0; i < 7; i++) {
        const d = new Date(start);
        d.setDate(start.getDate() + i);
        const iso = dISO(d);
        const cell = document.createElement('div');
        cell.className = 'cal-cell';
        cell.innerHTML = `
        <div class="cal-events" data-date="${iso}"></div>
        <button class="btn btn-xs cal-add btn-aceptar" data-date="${iso}" type="button">+</button>
      `;
        if (sameDay(d, new Date())) cell.classList.add('today');
        root.appendChild(cell);
      }

      paintData();
    }

    function paintData() {
      // Limpio
      root.querySelectorAll('.cal-events').forEach(el => el.innerHTML = '');

      // Visitas
      currentData.visitas.forEach(ev => {
        const c = root.querySelector(`.cal-events[data-date="${ev.fecha}"]`);
        if (!c) return;
        const item = document.createElement('div');
        item.className = 'cal-event';
        item.title = `${ev.nombre}${ev.piloto ? ' · Piloto: '+ev.piloto : ''}${ev.zona ? ' · Zona: '+ev.zona : ''}`;
        const rango = (ev.hora_desde && ev.hora_hasta) ? `${ev.hora_desde}–${ev.hora_hasta}` : (ev.hora_desde || '');
        const meta = [ev.nombre, ev.piloto ? `Piloto: ${ev.piloto}` : '', ev.zona ? `Zona: ${ev.zona}` : ''].filter(Boolean).join(' · ');
        item.innerHTML = `<strong>${rango}</strong> · ${meta}`;
        c.appendChild(item);
      });

      // Notas
      currentData.notas.forEach(n => {
        const c = root.querySelector(`.cal-events[data-date="${n.fecha}"]`);
        if (!c) return;
        const note = document.createElement('div');
        note.className = 'cal-note';
        note.dataset.noteId = String(n.id);
        note.innerHTML = `
        <span>📝 ${n.texto}</span>
        <span class="actions">
          <a data-action="edit" data-id="${n.id}">Editar</a> ·
          <a data-action="del" data-id="${n.id}">Eliminar</a>
        </span>
      `;
        c.appendChild(note);
      });

      // Wire botones + Nota / editar / borrar
      root.querySelectorAll('.cal-add').forEach(btn => {
        btn.onclick = () => createNote(btn.dataset.date);
      });
      root.querySelectorAll('.cal-note .actions a').forEach(a => {
        const id = a.getAttribute('data-id');
        const action = a.getAttribute('data-action');
        a.onclick = () => {
          if (action === 'edit') editNote(id);
          if (action === 'del') deleteNote(id);
        };
      });
    }

    // Notas CRUD (alta con modal estándar)
    async function createNote(fecha) {
      openModal(fecha);
    }

    async function editNote(id) {
      const actual = currentData.notas.find(n => String(n.id) === String(id));
      if (!actual) return;
      // Usamos el mismo modal de "Nueva nota" para editar (prellenado)
      openModal(actual.fecha, actual.texto, { mode: 'edit', id: String(id) });
    }

    async function deleteNote(id) {
      const actual = currentData.notas.find(n => String(n.id) === String(id));
      if (!actual) return;
      openConfirm(
        'Eliminar nota',
        `¿Confirmás eliminar la nota del ${actual.fecha}?`,
        async () => {
          try {
            const body = new FormData();
            body.append('action', 'note_delete');
            body.append('id', String(id));
            await fetchJSON(API, { method: 'POST', body });
            closeConfirm();
            if (typeof showAlert === 'function') showAlert('success', 'Nota eliminada.');
            else alert('Nota eliminada.');
            await reloadAndRender();
          } catch (e) {
            closeConfirm();
            if (typeof showAlert === 'function') showAlert('error', e?.message || 'No se pudo eliminar la nota');
            else alert(e?.message || 'No se pudo eliminar la nota');
          }
        }
      );
    }

    // Render switcher
    function render() {
      if (viewMode === 'month') renderMonth(viewDate);
      else renderWeek(viewDate);
    }

        async function reloadAndRender() {
      await loadCalendar(viewDate);
      render();
      healthEl.textContent = '';
    }

    // === Exponer utilidades al ámbito global para handlers fuera del IIFE ===
    // Evita errores: "filtroPiloto is not defined" y "fetchJSON is not defined"
    window.filtroPiloto = filtroPiloto;
    window.filtroZona = filtroZona;
    window.fetchJSON = fetchJSON;
    window.reloadAndRender = reloadAndRender;


    // Listeners navegación
    btnPrev.addEventListener('click', async () => {
      if (viewMode === 'month') {
        viewDate.setMonth(viewDate.getMonth() - 1);
      } else {
        viewDate.setDate(viewDate.getDate() - 7);
      }
      await reloadAndRender();
    });
    btnNext.addEventListener('click', async () => {
      if (viewMode === 'month') {
        viewDate.setMonth(viewDate.getMonth() + 1);
      } else {
        viewDate.setDate(viewDate.getDate() + 7);
      }
      await reloadAndRender();
    });
    btnToday.addEventListener('click', async () => {
      viewDate = new Date();
      await reloadAndRender();
    });
    btnAddNoteToday.addEventListener('click', () => createNote(dISO(new Date())));

    // Filtros
    filtroPiloto.addEventListener('change', reloadAndRender);
    filtroZona.addEventListener('change', reloadAndRender);

    // Vista
    function setView(mode) {
      viewMode = mode;
      viewLabel.textContent = mode === 'month' ? 'mes' : 'semana';
      btnViewMonth.classList.toggle('btn-aceptar', mode === 'month');
      btnViewMonth.classList.toggle('btn-info', mode !== 'month');
      btnViewWeek.classList.toggle('btn-aceptar', mode === 'week');
      btnViewWeek.classList.toggle('btn-info', mode !== 'week');
      render();
    }
    btnViewMonth.addEventListener('click', () => setView('month'));
    btnViewWeek.addEventListener('click', () => setView('week'));

    // Init
    (async function init() {
      try {
        await loadMeta();
        await loadCalendar(viewDate);
        render();
        healthEl.textContent = '';
      } catch (e) {
        healthEl.innerHTML = '<strong style="color:#b91c1c;">Error:</strong> ' + (e?.message || e);
      }
    })();
  })();


  // ===== Modales (crear/editar y confirmación) =====
  // NOTA: colocamos todo dentro del IIFE para usar fetchJSON/reloadAndRender sin exponer globales.
  const modal = document.getElementById('modal');
  const modalText = document.getElementById('modal-text');
  const modalDate = document.getElementById('modal-date');
  const btnModalAccept = document.getElementById('btn-modal-accept');
  const btnModalCancel = document.getElementById('btn-modal-cancel');

  const confirmModal = document.getElementById('confirm-modal');
  const confirmTitle = document.getElementById('confirm-title');
  const confirmMessage = document.getElementById('confirm-message');
  const btnConfirmAccept = document.getElementById('btn-confirm-accept');
  const btnConfirmCancel = document.getElementById('btn-confirm-cancel');

  let modalState = {
    mode: 'create',
    id: null
  };

  function openModal(fecha, texto = '', state = {
    mode: 'create',
    id: null
  }) {
    modalState = state;
    modalDate.value = fecha;
    modalText.value = texto || '';
    document.getElementById('modal-title').textContent = (modalState.mode === 'edit') ? 'Editar nota' : 'Nueva nota';
    btnModalAccept.textContent = (modalState.mode === 'edit') ? 'Actualizar' : 'Guardar';
    modal.classList.remove('hidden');
    setTimeout(() => modalText.focus(), 0);
  }

  function closeModal() {
    modal.classList.add('hidden');
  }

  function openConfirm(title, message, onAccept) {
    confirmTitle.textContent = title;
    confirmMessage.textContent = message;
    confirmModal.classList.remove('hidden');
    // Limpio listeners previos
    btnConfirmAccept.onclick = null;
    btnConfirmCancel.onclick = null;

    btnConfirmAccept.onclick = () => onAccept && onAccept();
    btnConfirmCancel.onclick = closeConfirm;
    confirmModal.addEventListener('click', backdropCloseConfirm);
    document.addEventListener('keydown', escCloseConfirm);
  }

  function closeConfirm() {
    confirmModal.classList.add('hidden');
    btnConfirmAccept.onclick = null;
    btnConfirmCancel.onclick = null;
    confirmModal.removeEventListener('click', backdropCloseConfirm);
    document.removeEventListener('keydown', escCloseConfirm);
  }

  function backdropCloseConfirm(e) {
    if (e.target === confirmModal) closeConfirm();
  }

  function escCloseConfirm(e) {
    if (!confirmModal.classList.contains('hidden') && e.key === 'Escape') closeConfirm();
  }

  btnModalCancel.addEventListener('click', closeModal);
  modal.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
  });
  document.addEventListener('keydown', (e) => {
    if (!modal.classList.contains('hidden') && e.key === 'Escape') closeModal();
  });

  btnModalAccept.addEventListener('click', async () => {
    const fecha = modalDate.value.trim();
    const texto = modalText.value.trim();
    if (!fecha || !texto) {
      if (typeof showAlert === 'function') showAlert('info', 'Completá la fecha y el texto de la nota.');
      else alert('Completá la fecha y el texto de la nota.');
      return;
    }
    const body = new FormData();
    const isEdit = (modalState.mode === 'edit' && modalState.id);

    if (isEdit) {
      body.append('action', 'note_update');
      body.append('id', String(modalState.id));
      body.append('texto', texto);
    } else {
      body.append('action', 'note_create');
      body.append('fecha', fecha);
      body.append('texto', texto);
      if (filtroPiloto.value) body.append('piloto_id', filtroPiloto.value);
      if (filtroZona.value) body.append('zona', filtroZona.value);
    }
    try {
      await fetchJSON(API, { method: 'POST', body });
      closeModal();
      if (typeof showAlert === 'function') {
        showAlert('success', isEdit ? 'Nota actualizada.' : 'Nota creada.');
      } else {
        alert(isEdit ? 'Nota actualizada.' : 'Nota creada.');
      }
      await reloadAndRender();
    } catch (e) {
      if (typeof showAlert === 'function') showAlert('error', e?.message || 'No se pudo guardar la nota');
      else alert(e?.message || 'No se pudo guardar la nota');
    }
  });

</script>