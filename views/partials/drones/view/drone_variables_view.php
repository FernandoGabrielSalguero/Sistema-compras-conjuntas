<?php // views/partials/drones/view/drone_variables_view.php ?>
<div class="content">
  <div class="card" style="background-color:#5b21b6;">
    <h3 style="color:white;">Variables del sistema</h3>
    <p style="color:white;margin:0;">Gestioná catálogos reutilizables por todo el sistema.</p>
  </div>

  <!-- Patologías -->
  <div id="card-patologias" class="card" aria-labelledby="h-patologias">
    <h4 id="h-patologias">Patologías</h4>
    <p class="muted" style="margin-top:-6px;color:#64748b;">CRUD de patologías (tabla: <code>dron_patologias</code>).</p>

    <form id="form-patologias" class="form-grid grid-3" autocomplete="off" aria-describedby="msg-patologias">
      <input type="hidden" name="id" id="p-id" value="">
      <div class="input-group">
        <label for="p-nombre">Nombre</label>
        <div class="input-icon input-icon-name">
          <input type="text" id="p-nombre" name="nombre" placeholder="Ej.: Mildiu" required maxlength="100" />
        </div>
      </div>
      <div class="input-group">
        <label for="p-desc">Descripción</label>
        <div class="input-icon input-icon-description">
          <input type="text" id="p-desc" name="descripcion" placeholder="Opcional (máx. 255)" maxlength="255" />
        </div>
      </div>
      <div class="input-group">
        <label for="p-submit" class="sr-only">Acciones</label>
        <div>
          <button id="p-submit" type="submit" class="btn primary">Guardar</button>
          <button id="p-cancel" type="button" class="btn" aria-label="Cancelar edición">Cancelar</button>
        </div>
      </div>
    </form>

    <div class="form-grid grid-3" style="margin-top:8px;">
      <div class="input-group">
        <label for="p-q">Buscar</label>
        <div class="input-icon input-icon-search">
          <input type="text" id="p-q" placeholder="Filtrar por nombre/descr." />
        </div>
      </div>
      <div class="input-group">
        <label for="p-inactivos">Ver inactivos</label>
        <div class="input-icon">
          <input type="checkbox" id="p-inactivos" />
        </div>
      </div>
    </div>

    <div id="p-msg" role="status" aria-live="polite" class="muted" style="margin:6px 0;"></div>

    <div class="table-responsive" style="margin-top:8px;">
      <table class="table" aria-describedby="h-patologias">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">Nombre</th>
            <th scope="col">Descripción</th>
            <th scope="col">Estado</th>
            <th scope="col" style="width:170px;">Acciones</th>
          </tr>
        </thead>
        <tbody id="p-tbody"></tbody>
      </table>
    </div>
  </div>

  <!-- Producción -->
  <div id="card-produccion" class="card" aria-labelledby="h-produccion">
    <h4 id="h-produccion">Producción</h4>
    <p class="muted" style="margin-top:-6px;color:#64748b;">CRUD de producción (tabla: <code>dron_produccion</code>).</p>

    <form id="form-produccion" class="form-grid grid-3" autocomplete="off" aria-describedby="msg-produccion">
      <input type="hidden" name="id" id="r-id" value="">
      <div class="input-group">
        <label for="r-nombre">Nombre</label>
        <div class="input-icon input-icon-name">
          <input type="text" id="r-nombre" name="nombre" placeholder="Ej.: Uva, Olivo, etc." required maxlength="100" />
        </div>
      </div>
      <div class="input-group">
        <label for="r-desc">Descripción</label>
        <div class="input-icon input-icon-description">
          <input type="text" id="r-desc" name="descripcion" placeholder="Opcional (máx. 255)" maxlength="255" />
        </div>
      </div>
      <div class="input-group">
        <label for="r-submit" class="sr-only">Acciones</label>
        <div>
          <button id="r-submit" type="submit" class="btn primary">Guardar</button>
          <button id="r-cancel" type="button" class="btn" aria-label="Cancelar edición">Cancelar</button>
        </div>
      </div>
    </form>

    <div class="form-grid grid-3" style="margin-top:8px;">
      <div class="input-group">
        <label for="r-q">Buscar</label>
        <div class="input-icon input-icon-search">
          <input type="text" id="r-q" placeholder="Filtrar por nombre/descr." />
        </div>
      </div>
      <div class="input-group">
        <label for="r-inactivos">Ver inactivos</label>
        <div class="input-icon">
          <input type="checkbox" id="r-inactivos" />
        </div>
      </div>
    </div>

    <div id="r-msg" role="status" aria-live="polite" class="muted" style="margin:6px 0;"></div>

    <div class="table-responsive" style="margin-top:8px;">
      <table class="table" aria-describedby="h-produccion">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">Nombre</th>
            <th scope="col">Descripción</th>
            <th scope="col">Estado</th>
            <th scope="col" style="width:170px;">Acciones</th>
          </tr>
        </thead>
        <tbody id="r-tbody"></tbody>
      </table>
    </div>
  </div>
</div>

<style>
  /* Ajustes mínimos, no rompe el CDN */
  .table-responsive { overflow-x:auto; }
  .btn.primary { transition: transform .08s ease; }
  .btn.primary:active { transform: scale(0.98); }
  .sr-only {
    position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0;
  }
</style>

<script>
(function () {
  // Ruta ABSOLUTA para evitar 404 al resolver desde distintas vistas
  const DVAR_API = '/views/partials/drones/controller/drone_variables_controller.php';

  // Utilidades
  const $ = (sel, ctx=document) => ctx.querySelector(sel);
  const el = (tag, props={}) => Object.assign(document.createElement(tag), props);
  const escape = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const debounce = (fn, ms=280) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms);} };

  function renderRow(item, tbody, entity) {
    const tr = el('tr');
    const estado = item.activo === 'si' ? 'Activo' : 'Inactivo';
    tr.append(
      el('td', { textContent: item.id }),
      el('td', { textContent: item.nombre }),
      el('td', { textContent: item.descripcion || '' }),
      el('td', { textContent: estado }),
    );
    const tdAcc = el('td');
    const btnEdit = el('button', { className:'btn', type:'button', textContent:'Editar', 'aria-label':'Editar' });
    const btnDel  = el('button', { className:'btn', type:'button', textContent: item.activo==='si' ? 'Eliminar' : 'Reactivar', 'aria-label': item.activo==='si' ? 'Eliminar' : 'Reactivar' });

    btnEdit.addEventListener('click', () => {
      if (entity==='patologias') {
        $('#p-id').value = item.id; $('#p-nombre').value = item.nombre; $('#p-desc').value = item.descripcion || '';
        $('#p-nombre').focus();
      } else {
        $('#r-id').value = item.id; $('#r-nombre').value = item.nombre; $('#r-desc').value = item.descripcion || '';
        $('#r-nombre').focus();
      }
    });

    btnDel.addEventListener('click', async () => {
      if (item.activo==='si') {
        if (!confirm('¿Eliminar este registro? Quedará inactivo.')) return;
      }
      const res = await fetch(DVAR_API+'?action=delete&entity='+entity, {
        method:'POST',
        headers:{ 'Content-Type':'application/json' },
        body: JSON.stringify({ id: item.id })
      }).then(r=>r.json()).catch(()=>({ok:false,error:'Error de red'}));
      const msgEl = entity==='patologias' ? $('#p-msg') : $('#r-msg');
      msgEl.textContent = res.ok ? 'Actualizado correctamente.' : ('Error: '+ (res.error||''));
      await (entity==='patologias' ? loadPatologias() : loadProduccion());
    });

    tdAcc.append(btnEdit, document.createTextNode(' '), btnDel);
    tr.append(tdAcc);
    tbody.append(tr);
  }

  async function list(entity, q, inactivos) {
    const url = new URL(DVAR_API, location.origin);
    url.searchParams.set('action','list');
    url.searchParams.set('entity', entity);
    url.searchParams.set('q', q||'');
    url.searchParams.set('inactivos', inactivos ? '1' : '0');
    url.searchParams.set('t', Date.now());
    return fetch(url, { cache:'no-store' }).then(r=>r.json());
  }

  async function save(entity, payload) {
    const url = DVAR_API+'?action='+(payload.id ? 'update':'create')+'&entity='+entity;
    return fetch(url, {
      method:'POST',
      headers:{ 'Content-Type':'application/json' },
      body: JSON.stringify(payload)
    }).then(r=>r.json());
  }

  // Patologías
  const loadPatologias = async () => {
    const q = $('#p-q').value.trim();
    const ina = $('#p-inactivos').checked;
    const tbody = $('#p-tbody'); tbody.innerHTML='';
    const data = await list('patologias', q, ina);
    if (!data.ok) { $('#p-msg').textContent = 'Error: '+(data.error||'No se pudo cargar'); return; }
    (data.data||[]).forEach(it => renderRow(it, tbody, 'patologias'));
    $('#p-msg').textContent = (data.data||[]).length ? '' : 'Sin resultados.';
  };

  $('#form-patologias').addEventListener('submit', async (e)=>{
    e.preventDefault();
    const id = parseInt($('#p-id').value||'0',10) || null;
    const nombre = $('#p-nombre').value.trim();
    const descripcion = $('#p-desc').value.trim();
    const res = await save('patologias', { id, nombre, descripcion });
    $('#p-msg').textContent = res.ok ? 'Guardado correctamente.' : ('Error: '+(res.error||''));
    if (res.ok) { $('#p-id').value=''; $('#p-nombre').value=''; $('#p-desc').value=''; await loadPatologias(); }
  });
  $('#p-cancel').addEventListener('click', ()=>{ $('#p-id').value=''; $('#p-nombre').value=''; $('#p-desc').value=''; $('#p-msg').textContent=''; });
  $('#p-q').addEventListener('input', debounce(loadPatologias, 300));
  $('#p-inactivos').addEventListener('change', loadPatologias);

  // Producción
  const loadProduccion = async () => {
    const q = $('#r-q').value.trim();
    const ina = $('#r-inactivos').checked;
    const tbody = $('#r-tbody'); tbody.innerHTML='';
    const data = await list('produccion', q, ina);
    if (!data.ok) { $('#r-msg').textContent = 'Error: '+(data.error||'No se pudo cargar'); return; }
    (data.data||[]).forEach(it => renderRow(it, tbody, 'produccion'));
    $('#r-msg').textContent = (data.data||[]).length ? '' : 'Sin resultados.';
  };

  $('#form-produccion').addEventListener('submit', async (e)=>{
    e.preventDefault();
    const id = parseInt($('#r-id').value||'0',10) || null;
    const nombre = $('#r-nombre').value.trim();
    const descripcion = $('#r-desc').value.trim();
    const res = await save('produccion', { id, nombre, descripcion });
    $('#r-msg').textContent = res.ok ? 'Guardado correctamente.' : ('Error: '+(res.error||''));
    if (res.ok) { $('#r-id').value=''; $('#r-nombre').value=''; $('#r-desc').value=''; await loadProduccion(); }
  });
  $('#r-cancel').addEventListener('click', ()=>{ $('#r-id').value=''; $('#r-nombre').value=''; $('#r-desc').value=''; $('#r-msg').textContent=''; });
  $('#r-q').addEventListener('input', debounce(loadProduccion, 300));
  $('#r-inactivos').addEventListener('change', loadProduccion);

  (async function init(){
    try {
      await fetch(DVAR_API+'?action=health&t='+Date.now(), {cache:'no-store'}).then(r=>r.json());
    } catch(_) {}
    await loadPatologias();
    await loadProduccion();
  })();
})();
</script>
