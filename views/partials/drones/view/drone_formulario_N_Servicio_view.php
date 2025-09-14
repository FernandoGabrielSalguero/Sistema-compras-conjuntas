<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>Registro nueva solicitud de pulverización con drones</title>

<!-- ================== CSS ENCÁPSULADO (sin CDN) ================== -->
<style>
  :root{
    --bg:#0b0b10; --surface:#13131a; --card:#181824; --muted:#a3a3b2; --text:#e7e7f0;
    --primary:#5b21b6; --primary-700:#4a148c; --primary-300:#8b5cf6;
    --success:#15803d; --warning:#ca8a04; --error:#b91c1c;
    --border:#2a2a36; --focus:#60a5fa;
    --radius:12px; --radius-sm:10px; --shadow:0 8px 24px rgba(0,0,0,.35);
    --font: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, "Helvetica Neue", Arial, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
  }
  *,*::before,*::after{ box-sizing:border-box }
  html,body{ height:100% }
  body{
    margin:0; font: 16px/1.45 var(--font); color:var(--text); background:linear-gradient(180deg,#0a0a0f, #0f0f18 40%, #121222);
    -webkit-font-smoothing:antialiased; -moz-osx-font-smoothing:grayscale;
  }
  .content{ max-width:1200px; margin:24px auto; padding:0 16px 48px }
  .card{
    background:var(--card);
    border:1px solid var(--border);
    border-radius:var(--radius);
    box-shadow:var(--shadow);
    padding:16px 18px;
  }
  .header{
    background:linear-gradient(135deg,var(--primary),var(--primary-700));
    color:#fff; border:none;
  }
  .header h3{ margin:.25rem 0 .35rem 0; font-weight:700 }
  .header p{ margin:0; opacity:.9 }

  /* Formulario */
  form.form-modern{ display:block }
  .form-grid{
    display:grid; gap:16px;
    grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
  }
  .input-group{ display:flex; flex-direction:column; gap:6px }
  .input-group label{ font-size:.94rem; color:#c9c9d6 }
  .input-icon{
    position:relative; display:flex; align-items:center; background:var(--surface);
    border:1px solid var(--border); border-radius:var(--radius-sm);
    transition:border-color .15s ease, box-shadow .15s ease;
  }
  .input-icon:focus-within{
    border-color:var(--focus);
    box-shadow:0 0 0 3px rgba(96,165,250,.25);
  }
  input[type="text"], input[type="number"], textarea, select{
    appearance:none; -webkit-appearance:none; -moz-appearance:none;
    width:100%; background:transparent; border:0; outline:0; color:var(--text);
    padding:12px 14px; border-radius:var(--radius-sm); min-height:42px; font-size:16px;
  }
  textarea{ resize:vertical }

  /* Select flecha */
  select{
    background-image:linear-gradient(45deg,transparent 50%, #9ea4bd 50%), linear-gradient(135deg,#9ea4bd 50%, transparent 50%);
    background-position: calc(100% - 18px) calc(1em + 2px), calc(100% - 13px) calc(1em + 2px);
    background-size:5px 5px, 5px 5px; background-repeat:no-repeat;
    padding-right:34px;
  }

  /* Autocomplete (lista) */
  #lista-nombres{
    list-style:none; margin:.25rem 0 0 0; padding:0; border-radius:var(--radius-sm);
    border:1px solid var(--border); background:var(--surface); max-height:220px; overflow:auto;
  }
  #lista-nombres li{ padding:.4rem .6rem; cursor:pointer; }
  #lista-nombres li[aria-selected="true"], #lista-nombres li:hover{ background:#1f1f2d }

  /* Tabla */
  .tabla-card h2{ margin:.2rem 0 1rem }
  .tabla-wrapper{ overflow-x:auto; -webkit-overflow-scrolling:touch }
  table.data-table{ width:100%; border-collapse:collapse; min-width:520px }
  table.data-table th, table.data-table td{ padding:.55rem .6rem; border-bottom:1px solid var(--border); text-align:center }
  table.data-table th{ color:#cdd1e6; font-weight:700; background:#12121b }
  table.data-table tr:hover td{ background:#161625 }
  .badge{ display:inline-block; padding:.22rem .5rem; border-radius:999px; font-size:.78rem; border:1px solid var(--border) }
  .badge.success{ background:rgba(21,128,61,.15); color:#86efac; border-color:rgba(21,128,61,.35) }
  .badge.warning{ background:rgba(202,138,4,.15); color:#fde68a; border-color:rgba(202,138,4,.35) }

  /* Botones */
  .form-buttons{ display:flex; gap:10px; flex-wrap:wrap; margin-top:16px }
  .btn{
    cursor:pointer; border:1px solid transparent; border-radius:12px; padding:10px 14px;
    font-weight:600; letter-spacing:.2px; transition:transform .05s ease, background .2s ease, filter .2s ease;
    user-select:none; -webkit-tap-highlight-color:transparent;
  }
  .btn:active{ transform:translateY(1px) }
  .btn-aceptar{ background:linear-gradient(180deg,var(--primary-300),var(--primary)); color:white }
  .btn-aceptar:hover{ filter:brightness(1.05) }
  .btn-cancelar{ background:#232336; color:#d5d8f0; border-color:#2f3048 }
  .btn-cancelar:hover{ background:#272742 }
  .btn-info{ background:#1f2937; color:#c9d1ee }
  .btn[disabled], .btn:disabled{ opacity:.6; cursor:not-allowed }

  /* Modal */
  .modal{ position:fixed; inset:0; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,.55); z-index:30 }
  .modal.hidden{ display:none }
  .modal .modal-content{ background:var(--card); border:1px solid var(--border); border-radius:16px; width:min(960px,92vw); max-height:80vh; overflow:auto; padding:16px 18px; box-shadow:var(--shadow) }
  .modal .modal-content h3{ margin-top:0 }

  /* Alertas (toast) */
  .toast{
    position:fixed; top:16px; right:16px; display:flex; gap:10px; align-items:center;
    padding:12px 14px; border-radius:12px; color:#fff; z-index:50; box-shadow:var(--shadow); opacity:0; transform:translateY(-8px);
    transition:opacity .18s ease, transform .18s ease;
  }
  .toast.show{ opacity:1; transform:translateY(0) }
  .toast.success{ background:linear-gradient(180deg,#16a34a,#166534) }
  .toast.error{ background:linear-gradient(180deg,#ef4444,#991b1b) }
  .toast.info{ background:linear-gradient(180deg,#3b82f6,#1e40af) }

  /* Util */
  .sr-only{ position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0 }
  @media (max-width:640px){
    html,body{ overflow-x:hidden }
    table.data-table{ min-width:460px }
  }
</style>
</head>
<body>
<div class="content">
  <div class="card header">
    <h3>Módulo: Registro nueva solicitud de servicio de pulverización con drones</h3>
    <p>Formulario limpio, accesible y listo para guardar.</p>
  </div>

  <div id="calendar-root" class="card" aria-live="polite">
    <h4 style="margin-top:0">Completa el formulario para cargar una nueva solicitud de drones</h4>

    <form id="form-solicitud" class="form-modern" novalidate>
      <div class="form-grid">

        <!-- Nombre del productor (autocomplete) -->
        <div class="input-group">
          <label for="nombre">Nombre del productor</label>
          <div class="input-icon">
            <input type="text" id="nombre" name="nombre" placeholder="Juan Pérez" autocomplete="off" aria-autocomplete="list" aria-controls="lista-nombres" required />
            <input type="hidden" id="productor_id_real" name="productor_id_real" />
          </div>
          <ul id="lista-nombres" role="listbox" aria-label="Coincidencias" style="display:none"></ul>
        </div>

        <!-- representante -->
        <div class="input-group">
          <label for="representante">¿Podremos contar con un representante en la finca? *</label>
          <div class="input-icon">
            <select id="representante" name="representante" required aria-required="true">
              <option value="">Seleccionar</option>
              <option value="si">Si</option>
              <option value="no">No</option>
            </select>
          </div>
        </div>

        <!-- linea_tension -->
        <div class="input-group">
          <label for="linea_tension">¿Hay líneas de media/alta tensión a &lt; 30m? *</label>
          <div class="input-icon">
            <select id="linea_tension" name="linea_tension" required>
              <option value="">Seleccionar</option>
              <option value="si">Si</option>
              <option value="no">No</option>
            </select>
          </div>
        </div>

        <!-- zona_restringida -->
        <div class="input-group">
          <label for="zona_restringida">¿Está a &lt; 3km de aeropuerto o zona restringida? *</label>
          <div class="input-icon">
            <select id="zona_restringida" name="zona_restringida" required>
              <option value="">Seleccionar</option>
              <option value="si">Si</option>
              <option value="no">No</option>
            </select>
          </div>
        </div>

        <!-- corriente_electrica -->
        <div class="input-group">
          <label for="corriente_electrica">¿Disponibilidad de corriente eléctrica? *</label>
          <div class="input-icon">
            <select id="corriente_electrica" name="corriente_electrica" required>
              <option value="">Seleccionar</option>
              <option value="si">Si</option>
              <option value="no">No</option>
            </select>
          </div>
        </div>

        <!-- agua_potable -->
        <div class="input-group">
          <label for="agua_potable">¿Disponibilidad de agua potable? *</label>
          <div class="input-icon">
            <select id="agua_potable" name="agua_potable" required>
              <option value="">Seleccionar</option>
              <option value="si">Si</option>
              <option value="no">No</option>
            </select>
          </div>
        </div>

        <!-- libre_obstaculos -->
        <div class="input-group">
          <label for="libre_obstaculos">¿Cuarteles libres de obstáculos? *</label>
          <div class="input-icon">
            <select id="libre_obstaculos" name="libre_obstaculos" required>
              <option value="">Seleccionar</option>
              <option value="si">Si</option>
              <option value="no">No</option>
            </select>
          </div>
        </div>

        <!-- area_despegue -->
        <div class="input-group">
          <label for="area_despegue">¿Área de despegue apropiada? *</label>
          <div class="input-icon">
            <select id="area_despegue" name="area_despegue" required>
              <option value="">Seleccionar</option>
              <option value="si">Si</option>
              <option value="no">No</option>
            </select>
          </div>
        </div>

        <!-- superficie_ha -->
        <div class="input-group">
          <label for="superficie_ha">¿Cuántas hectáreas vamos a pulverizar? *</label>
          <div class="input-icon">
            <input type="number" id="superficie_ha" name="superficie_ha" min="0" step="0.01" placeholder="20" required />
          </div>
        </div>

        <!-- forma_pago_id -->
        <div class="input-group">
          <label for="forma_pago_id">Método de pago *</label>
          <div class="input-icon">
            <select id="forma_pago_id" name="forma_pago_id" required>
              <option value="">Cargando...</option>
            </select>
          </div>
        </div>

        <!-- coop_descuento_id_real (solo si forma_pago_id = 6) -->
        <div class="input-group" id="coop-group" style="display:none;">
          <label for="coop_descuento_id_real">Cooperativa (solo si aplica)</label>
          <div class="input-icon">
            <select id="coop_descuento_id_real" name="coop_descuento_id_real" disabled aria-disabled="true">
              <option value="">Seleccionar</option>
            </select>
          </div>
        </div>

        <!-- patologia_id -->
        <div class="input-group">
          <label for="patologia_id">Motivo del servicio *</label>
          <div class="input-icon">
            <select id="patologia_id" name="patologia_id" required>
              <option value="">Cargando...</option>
            </select>
          </div>
        </div>

        <!-- rango -->
        <div class="input-group">
          <label for="rango">Momento deseado *</label>
          <div class="input-icon">
            <select id="rango" name="rango" required>
              <option value="">Seleccionar</option>
              <option value="octubre_q1">Primer quincena de octubre</option>
              <option value="octubre_q2">Segunda quincena de octubre</option>
              <option value="noviembre_q1">Primer quincena de noviembre</option>
              <option value="noviembre_q2">Segunda quincena de noviembre</option>
              <option value="diciembre_q1">Primer quincena de diciembre</option>
              <option value="diciembre_q2">Segunda quincena de diciembre</option>
              <option value="enero_q1">Primer quincena de enero</option>
              <option value="enero_q2">Segunda quincena de enero</option>
              <option value="febrero_q1">Primer quincena de febrero</option>
              <option value="febrero_q2">Segunda quincena de febrero</option>
            </select>
          </div>
        </div>

        <!-- nombre_producto (matriz por producto) -->
        <div class="input-group" style="grid-column:1/-1;">
          <label for="productos-grid">Productos sugeridos según patología *</label>
          <div class="input-icon" style="padding:0">
            <div id="productos-grid" class="card tabla-card" style="border:none; box-shadow:none">
              <div class="tabla-wrapper">
                <table class="data-table" aria-describedby="productos-help">
                  <thead>
                    <tr>
                      <th>✔</th>
                      <th>Producto</th>
                      <th>SVE</th>
                      <th>Productor</th>
                    </tr>
                  </thead>
                  <tbody id="productos-body">
                    <!-- filas dinámicas -->
                  </tbody>
                </table>
              </div>
              <p id="productos-help" style="margin:.6rem 0 0 0; color:var(--muted)">
                Marcá el/los productos y elegí quién los aporta por fila.
              </p>
            </div>
          </div>
        </div>

        <!-- dir_provincia -->
        <div class="input-group">
          <label for="dir_provincia">Provincia *</label>
          <div class="input-icon">
            <input type="text" id="dir_provincia" name="dir_provincia" placeholder="Provincia" required />
          </div>
        </div>

        <!-- dir_localidad -->
        <div class="input-group">
          <label for="dir_localidad">Localidad *</label>
          <div class="input-icon">
            <input type="text" id="dir_localidad" name="dir_localidad" placeholder="Localidad" required />
          </div>
        </div>

        <!-- dir_calle -->
        <div class="input-group">
          <label for="dir_calle">Calle *</label>
          <div class="input-icon">
            <input type="text" id="dir_calle" name="dir_calle" placeholder="Calle" required />
          </div>
        </div>

        <!-- dir_numero -->
        <div class="input-group">
          <label for="dir_numero">Número *</label>
          <div class="input-icon">
            <input type="text" id="dir_numero" name="dir_numero" placeholder="Número" required />
          </div>
        </div>

        <!-- observaciones -->
        <div class="input-group" style="grid-column:1/-1;">
          <label for="observaciones">Observaciones</label>
          <div class="input-icon">
            <textarea id="observaciones" name="observaciones" maxlength="233" rows="3" placeholder="Escribí un comentario..."></textarea>
          </div>
        </div>

      </div>

      <!-- Botones -->
      <div class="form-buttons">
        <button class="btn btn-aceptar" type="button" id="btn-previsualizar">Previsualizar</button>
        <button class="btn btn-cancelar" type="reset" id="btn-reset">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal de confirmación -->
<div id="modal-resumen" class="modal hidden" aria-hidden="true" role="dialog" aria-modal="true">
  <div class="modal-content">
    <h3>Confirmar solicitud</h3>
    <div id="resumen-detalle" class="card" style="max-height:40vh; overflow:auto;"></div>
    <div class="form-buttons">
      <button class="btn btn-aceptar" id="btn-confirmar">Confirmar y guardar</button>
      <button class="btn btn-cancelar" id="btn-cerrar-modal">Cancelar</button>
    </div>
  </div>
</div>

<!-- Toast -->
<div id="toast" class="toast" role="status" aria-live="polite" aria-atomic="true" style="display:none"></div>

<!-- ================== JS ENCÁPSULADO (sin CDN) ================== -->
<script>
(function(){
  'use strict';

  const API = '/views/partials/drones/controller/drone_formulario_N_Servicio_controller.php';

  // ===== Helpers =====
  const DEBUG = false;
  const $  = (s,ctx=document)=>ctx.querySelector(s);
  const $$ = (s,ctx=document)=>Array.from(ctx.querySelectorAll(s));
  const on = (el,ev,fn)=>el.addEventListener(ev,fn,{passive:false});
  const dbg = (...a)=>{ if(DEBUG) console.log('[DEBUG]',...a); };

  function showAlert(type, msg){
    const toast = $('#toast');
    toast.className = 'toast ' + type; // success|error|info
    toast.textContent = msg;
    toast.style.display = 'flex';
    requestAnimationFrame(()=> toast.classList.add('show'));
    setTimeout(()=>{
      toast.classList.remove('show');
      setTimeout(()=>{ toast.style.display='none'; },180);
    }, 2800);
  }

  function openModal(){
    const m = $('#modal-resumen');
    m.classList.remove('hidden');
    m.setAttribute('aria-hidden','false');
  }
  function closeModal(){
    const m = $('#modal-resumen');
    m.classList.add('hidden');
    m.setAttribute('aria-hidden','true');
  }

  // ====== elementos ======
  const form            = $('#form-solicitud');
  const nombreInput     = $('#nombre');
  const listaNombres    = $('#lista-nombres');
  const productorIdReal = $('#productor_id_real');
  const formaPagoSel    = $('#forma_pago_id');
  const coopGroup       = $('#coop-group');
  const coopSelect      = $('#coop_descuento_id_real');
  const patologiaSel    = $('#patologia_id');
  const productosBody   = $('#productos-body');
  const resumen         = $('#resumen-detalle');

  const btnPrev         = $('#btn-previsualizar');
  const btnReset        = $('#btn-reset');
  const btnConfirmar    = $('#btn-confirmar');
  const btnCerrarModal  = $('#btn-cerrar-modal');

  // ===== Fetch JSON robusto =====
  async function fetchJSON(url, options = {}){
    const res  = await fetch(url, { cache:'no-store', ...options });
    const text = await res.text();
    if(!res.ok) throw new Error('HTTP '+res.status+' '+url+'\n'+text);
    let json;
    try{ json = JSON.parse(text); }
    catch(e){ throw new Error('Respuesta no JSON en '+url); }
    return json;
  }

  // ===== Combos =====
  async function loadFormasPago(){
    try{
      const r = await fetchJSON(API+'?action=formas_pago');
      dbg('formas_pago', r);
      formaPagoSel.innerHTML = '<option value="">Seleccionar</option>' +
        (Array.isArray(r.data) ? r.data.map(o=>`<option value="${o.id}">${o.nombre}</option>`).join('') : '');
    }catch(e){
      formaPagoSel.innerHTML = '<option value="">(sin datos)</option>';
      if(DEBUG) console.error(e);
    }
  }

  async function loadPatologias(){
    try{
      const r = await fetchJSON(API+'?action=patologias');
      dbg('patologias', r);
      patologiaSel.innerHTML = '<option value="">Seleccionar</option>' +
        (Array.isArray(r.data) ? r.data.map(o=>`<option value="${o.id}">${o.nombre}</option>`).join('') : '');
    }catch(e){
      patologiaSel.innerHTML = '<option value="">(sin datos)</option>';
      if(DEBUG) console.error(e);
    }
  }

  // ===== Autocomplete productor =====
  let acTimer;
  on(nombreInput,'input', ()=>{
    productorIdReal.value = '';
    const q = nombreInput.value.trim();
    if(acTimer) clearTimeout(acTimer);
    if(q.length < 2){ listaNombres.style.display='none'; listaNombres.innerHTML=''; return; }
    acTimer = setTimeout(async ()=>{
      try{
        const j = await fetchJSON(API+'?action=buscar_usuarios&q='+encodeURIComponent(q));
        const html = (Array.isArray(j.data)?j.data:[]).map((u,i)=>(
          `<li role="option" data-id="${u.id_real}" aria-selected="${i===0?'true':'false'}">${u.usuario}</li>`
        )).join('');
        listaNombres.innerHTML = html;
        listaNombres.style.display = html ? 'block' : 'none';
      }catch(e){
        listaNombres.style.display='none'; listaNombres.innerHTML='';
        if(DEBUG) console.error(e);
      }
    }, 200);
  });

  on(listaNombres, 'click', (ev)=>{
    const li = ev.target.closest('li[data-id]');
    if(!li) return;
    nombreInput.value = li.textContent;
    productorIdReal.value = li.dataset.id;
    listaNombres.style.display='none';
    listaNombres.innerHTML='';
  });

  // ===== Productos por patología =====
  async function cargarProductosPorPatologia(id){
    productosBody.innerHTML = '';
    if(!id) return;
    try{
      const j = await fetchJSON(API+'?action=productos_por_patologia&patologia_id='+encodeURIComponent(id));
      const data = Array.isArray(j.data)?j.data:[];
      if(!data.length){
        productosBody.innerHTML = `<tr><td colspan="4">No hay productos sugeridos para esta patología.</td></tr>`;
        return;
      }
      productosBody.innerHTML = data.map(p=>`
        <tr>
          <td><input type="checkbox" class="prod-check" id="prod_${p.id}" data-pid="${p.id}" aria-label="Seleccionar ${p.nombre}"></td>
          <td style="text-align:center;"><label for="prod_${p.id}">${p.nombre}</label></td>
          <td><input type="radio" name="fuente_${p.id}" value="sve" disabled aria-label="SVE provee ${p.nombre}"></td>
          <td><input type="radio" name="fuente_${p.id}" value="productor" disabled aria-label="Productor provee ${p.nombre}"></td>
        </tr>
      `).join('');

      // Habilitar radios al marcar
      $$('.prod-check', productosBody).forEach(chk=>{
        on(chk,'change', (e)=>{
          const pid = e.target.dataset.pid;
          $$( `input[name="fuente_${pid}"]`, productosBody)
            .forEach(r=>{ r.disabled = !e.target.checked; if(!e.target.checked) r.checked=false; });
        });
      });
    }catch(e){
      showAlert('error','Error al cargar productos.');
      if(DEBUG) console.error(e);
    }
  }

  // ===== Eventos de selects =====
  on(formaPagoSel,'change', async ()=>{
    const id = Number(formaPagoSel.value || 0);
    if(id === 6){
      coopGroup.style.display = 'block';
      coopSelect.required = true;
      coopSelect.disabled = false;
      coopSelect.setAttribute('aria-disabled','false');

      if(coopSelect.options.length <= 1){
        try{
          const j = await fetchJSON(API+'?action=cooperativas');
          coopSelect.innerHTML = '<option value="">Seleccionar</option>' +
            (Array.isArray(j.data)? j.data.map(c=>`<option value="${c.id_real}">${c.usuario}</option>`).join(''):'');
        }catch(e){
          showAlert('error','No se pudieron cargar cooperativas.');
          if(DEBUG) console.error(e);
        }
      }
    }else{
      coopGroup.style.display='none';
      coopSelect.required=false;
      coopSelect.disabled=true;
      coopSelect.setAttribute('aria-disabled','true');
      coopSelect.value='';
    }
  });

  on(patologiaSel,'change', async ()=>{ await cargarProductosPorPatologia(patologiaSel.value); });

  // ===== Reset =====
  on(btnReset,'click', ()=>{
    coopGroup.style.display='none';
    coopSelect.required=false;
    coopSelect.disabled=true;
    coopSelect.setAttribute('aria-disabled','true');
    coopSelect.value='';
    listaNombres.style.display='none';
    listaNombres.innerHTML='';
    productosBody.innerHTML='';
  });

  // ===== Previsualizar =====
  on(btnPrev,'click', (e)=>{
    e.preventDefault();
    if(!form.reportValidity()){
      const firstInvalid = form.querySelector(':invalid');
      if(firstInvalid) firstInvalid.focus();
      showAlert('error','Completá los campos requeridos.');
      return;
    }
    const data = getFormData();
    resumen.innerHTML = renderResumen(data);
    openModal();
  });

  // ===== Confirmar (POST) =====
  on(btnConfirmar,'click', async ()=>{
    const payload = getFormData();
    try{
      const res = await fetch(API, {
        method:'POST',
        headers:{ 'Content-Type':'application/json' },
        body: JSON.stringify(payload)
      });
      let json = null;
      try{ json = await res.json(); } catch(_){}
      if(res.ok && json && json.ok){
        closeModal(); form.reset(); // limpiar UI
        coopGroup.style.display='none';
        coopSelect.required=false; coopSelect.disabled=true; coopSelect.setAttribute('aria-disabled','true');
        showAlert('success','¡Solicitud guardada! ID '+json.data.id);
      }else{
        showAlert('error', (json && json.error) ? json.error : 'No se pudo guardar.');
      }
    }catch(e){
      showAlert('error','Error de red al guardar.');
      if(DEBUG) console.error(e);
    }
  });

  on(btnCerrarModal,'click', ()=> closeModal());

  // ===== Util: obtener payload =====
  function getFormData(){
    const items = [];
    $$('.prod-check:checked', productosBody).forEach(chk=>{
      const pid = Number(chk.dataset.pid);
      const fuenteSel = productosBody.querySelector(`input[name="fuente_${pid}"]:checked`);
      items.push({ producto_id: pid, fuente: fuenteSel ? fuenteSel.value : '' });
    });

    return {
      productor_id_real: productorIdReal.value || null,
      nombre: nombreInput.value.trim(),
      representante: $('#representante').value,
      linea_tension: $('#linea_tension').value,
      zona_restringida: $('#zona_restringida').value,
      corriente_electrica: $('#corriente_electrica').value,
      agua_potable: $('#agua_potable').value,
      libre_obstaculos: $('#libre_obstaculos').value,
      area_despegue: $('#area_despegue').value,
      superficie_ha: parseFloat($('#superficie_ha').value),
      forma_pago_id: Number(formaPagoSel.value),
      coop_descuento_id_real: (coopGroup.style.display==='block') ? (coopSelect.value || null) : null,
      patologia_id: Number(patologiaSel.value),
      rango: $('#rango').value,
      items,
      dir_provincia: $('#dir_provincia').value.trim(),
      dir_localidad: $('#dir_localidad').value.trim(),
      dir_calle: $('#dir_calle').value.trim(),
      dir_numero: $('#dir_numero').value.trim(),
      observaciones: $('#observaciones').value.trim()
    };
  }

  function renderResumen(d){
    const prods = (d.items && d.items.length)
      ? d.items.map(it=>{
          const row = productosBody.querySelector(`#prod_${it.producto_id}`)?.closest('tr');
          const nombre = row ? row.querySelector('td:nth-child(2)').textContent.trim() : ('ID '+it.producto_id);
          return `${nombre} (${it.fuente || 'sin fuente'})`;
        }).join('<br>')
      : '—';
    const formaPagoText = formaPagoSel.selectedOptions[0]?.textContent || '';
    const coopText = (coopGroup.style.display==='block' && !coopSelect.disabled)
      ? (coopSelect.selectedOptions[0]?.textContent || '—') : '—';

    return `
      <div class="tabla-wrapper">
        <table class="data-table">
          <thead><tr><th>Campo</th><th>Valor</th></tr></thead>
          <tbody>
            <tr><td>Productor</td><td>${d.nombre} (${d.productor_id_real || 'sin ID'})</td></tr>
            <tr><td>Representante</td><td>${d.representante}</td></tr>
            <tr><td>Línea tensión</td><td>${d.linea_tension}</td></tr>
            <tr><td>Zona restringida</td><td>${d.zona_restringida}</td></tr>
            <tr><td>Corriente</td><td>${d.corriente_electrica}</td></tr>
            <tr><td>Agua potable</td><td>${d.agua_potable}</td></tr>
            <tr><td>Libre obstáculos</td><td>${d.libre_obstaculos}</td></tr>
            <tr><td>Área despegue</td><td>${d.area_despegue}</td></tr>
            <tr><td>Superficie (ha)</td><td>${d.superficie_ha}</td></tr>
            <tr><td>Forma pago</td><td>${formaPagoText}</td></tr>
            <tr><td>Cooperativa</td><td>${coopText}</td></tr>
            <tr><td>Patología</td><td>${patologiaSel.selectedOptions[0]?.textContent || ''}</td></tr>
            <tr><td>Rango</td><td>${d.rango}</td></tr>
            <tr><td>Productos</td><td>${prods}</td></tr>
            <tr><td>Provincia</td><td>${d.dir_provincia}</td></tr>
            <tr><td>Localidad</td><td>${d.dir_localidad}</td></tr>
            <tr><td>Calle</td><td>${d.dir_calle} ${d.dir_numero}</td></tr>
            <tr><td>Observaciones</td><td>${d.observaciones || '—'}</td></tr>
          </tbody>
        </table>
      </div>
    `;
  }

  // ===== Init =====
  (async function init(){
    await loadFormasPago();
    await loadPatologias();
  })();

})();
</script>
</body>
</html>
