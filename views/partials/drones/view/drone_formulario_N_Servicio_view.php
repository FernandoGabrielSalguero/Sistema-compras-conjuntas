<?php
declare(strict_types=1);
?>

<div class="content">
  <div class="card" style="background-color:#5b21b6;">
    <h3 style="color:white;">Módulo: Registro nueva solicitud de servicio de pulverización con drones</h3>
    <p style="color:white;margin:0;">Formulario limpio, accesible y listo para guardar.</p>
  </div>

  <div id="calendar-root" class="card">
    <h4>Completa el formulario para cargar una nueva solicitud de drones</h4>

    <form id="form-solicitud" class="form-modern" novalidate>
      <div class="form-grid grid-4">

        <!-- Nombre del productor (autocomplete) -->
        <div class="input-group">
          <label for="nombre">Nombre del productor</label>
          <div class="input-icon input-icon-name">
            <input type="text" id="nombre" name="nombre" placeholder="Juan Pérez" autocomplete="off" aria-autocomplete="list" aria-controls="lista-nombres" required />
            <input type="hidden" id="productor_id_real" name="productor_id_real" />
          </div>
          <ul id="lista-nombres" class="card" role="listbox" aria-label="Coincidencias" style="margin-top:.25rem; display:none;"></ul>
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
          <label for="forma_pago_id">Metodo de pago*</label>
          <div class="input-icon">
            <select id="forma_pago_id" name="forma_pago_id" required>
              <option value="">Cargando...</option>
            </select>
          </div>
        </div>




        <!-- coop_descuento_id_real (solo si forma_pago_id = 6) -->
        <div class="input-group" id="coop-group" style="display:none;">
          <label for="coop_descuento_id_real">Cooperativa (solo si aplica)</label>
          <div class="input-icon input-icon-globe">
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
        <div class="input-group" style="grid-column: 1 / -1;">
          <label for="productos-grid">Productos sugeridos según patología *</label>
          <div class="input-icon">
            <div id="productos-grid" class="card tabla-card" aria-live="polite">
              <div class="tabla-wrapper">
                <table class="data-table">
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
              <p id="productos-help" style="margin:.5rem 0 0 0;">Marcá el/los productos y elegí quién los aporta por fila.</p>
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
        <div class="input-group" style="grid-column: 1 / -1;">
          <label for="observaciones">Observaciones</label>
          <div class="input-icon input-icon-comment">
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

<style>
  /* ====== Estilos locales (sin CDN) ====== */
  :root { --gap: 1rem; --radius: 12px; --card-bg: #fff; --card-bd: #e5e7eb; --text: #111827; --muted:#6b7280; }
  * { box-sizing: border-box; }
  body { color: var(--text); font-family: system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, "Helvetica Neue", Arial, "Noto Sans", "Apple Color Emoji","Segoe UI Emoji", "Segoe UI Symbol"; }
  .content{ padding: 16px; max-width: 1200px; margin: 0 auto; }
  .card{ background: var(--card-bg); border: 1px solid var(--card-bd); border-radius: var(--radius); padding: 16px; box-shadow: 0 1px 2px rgba(0,0,0,.04); }
  .form-grid.grid-4{ display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: var(--gap); }
  .input-group label{ display:block; font-weight:600; margin-bottom:.35rem; }
  .input-icon input, .input-icon select, .input-icon textarea{ width:100%; padding:.6rem .7rem; border:1px solid #d1d5db; border-radius:10px; background:#fff; }
  .input-icon select{ background:#fff; }
  .form-buttons{ display:flex; gap:.5rem; justify-content:flex-end; margin-top:1rem; }
  .btn{ padding:.6rem 1rem; border:1px solid transparent; border-radius:10px; cursor:pointer; }
  .btn-aceptar{ background:#16a34a; color:#fff; }
  .btn-cancelar{ background:#f3f4f6; color:#111827; }
  .btn:disabled{ opacity:.6; cursor:not-allowed; }

  /* tabla */
  #productos-grid .tabla-wrapper{ overflow-x:auto; -webkit-overflow-scrolling:touch; }
  #productos-grid table.data-table { width: 100%; border-collapse: collapse; table-layout: auto; }
  #productos-grid thead th { text-align:center; font-weight:600; padding:.5rem; background:#f9fafb; }
  #productos-grid tbody td { padding:.5rem; text-align:center; vertical-align:middle; border-top:1px solid #f3f4f6; }
  #productos-grid tbody td:nth-child(2) { text-align:left; font-weight:500; } /* izquierda (corregido) */
  #productos-grid th:nth-child(1), #productos-grid td:nth-child(1){ width:40px; }   /* check */
  #productos-grid th:nth-child(3), #productos-grid td:nth-child(3){ width:80px; }  /* SVE */
  #productos-grid th:nth-child(4), #productos-grid td:nth-child(4){ width:100px; } /* Productor */

  /* modal */
  .modal.hidden{ display:none; }
  .modal .modal-content{ background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:16px; width:90vw; max-width:960px; margin:10vh auto; box-shadow: 0 10px 30px rgba(0,0,0,.2); }

  /* Autocomplete */
  #lista-nombres.card{ padding:0; overflow:hidden; }
  #lista-nombres li{ list-style:none; padding:.4rem .6rem; cursor:pointer; }
  #lista-nombres li[aria-selected="true"], #lista-nombres li:hover{ background:#eef2ff; }

  /* responsive */
  @media (max-width: 640px) {
    html, body { overflow-x: hidden; }
    #productos-grid table.data-table { width: 100%; min-width: 0; }
    #productos-grid tbody td { font-size: .9rem; padding: .4rem; word-break: break-word; white-space: normal; }
  }
</style>

<script>
(function(){
  'use strict';
  const API = '/views/partials/drones/controller/drone_formulario_N_Servicio_controller.php';

  // --------- helpers ----------
  const $ = (s)=>document.querySelector(s);
  const formaPago = $('#forma_pago_id');
  const patologia = $('#patologia_id');
  const coopSelect = $('#coop_descuento_id_real');
  const coopGroup  = $('#coop-group');
  const productosBody = $('#productos-body');
  const form = $('#form-solicitud');

  const dbg = {
    panel: null,
    log(obj, title='') {
      if (!this.panel) return;
      const card = document.createElement('div');
      card.style.cssText = 'border:1px solid #e5e7eb;border-radius:8px;padding:10px;margin:8px 0;background:#fff';
      const h = document.createElement('div');
      h.textContent = title || 'DEBUG';
      h.style.cssText = 'font-weight:700;margin-bottom:6px';
      const pre = document.createElement('pre');
      pre.style.margin='0';
      pre.textContent = JSON.stringify(obj, null, 2);
      card.appendChild(h); card.appendChild(pre);
      this.panel.appendChild(card);
    },
    ensure() {
      if (this.panel) return;
      const wrap = document.createElement('div');
      wrap.className = 'card';
      wrap.style.cssText = 'margin:16px 0;background:#f8fafc;border:1px dashed #94a3b8';
      const h = document.createElement('div');
      h.textContent = 'Panel de comprobación (datos crudos del backend)';
      h.style.cssText = 'font-weight:700;margin-bottom:8px';
      const btns = document.createElement('div');
      btns.style.cssText = 'display:flex;gap:8px;flex-wrap:wrap;margin-bottom:8px';
      btns.innerHTML = `
        <button id="btn-reload-all" class="btn">Recargar todos</button>
        <button id="btn-force-options" class="btn">FORZAR CARGA DE OPCIONES</button>
        <span style="color:#64748b">Si “forzar” muestra opciones, el problema es de datos; si no, es de UI/DOM.</span>
      `;
      const panel = document.createElement('div');
      panel.id = 'debug-panel';
      wrap.appendChild(h); wrap.appendChild(btns); wrap.appendChild(panel);
      document.querySelector('.content')?.appendChild(wrap);
      this.panel = panel;

      $('#btn-reload-all').addEventListener('click', async ()=>{
        this.panel.innerHTML='';
        await renderAllDebug();
      });
      $('#btn-force-options').addEventListener('click', ()=>{
        forceFillOptions();
      });
    }
  };

  async function fetchJSON(url, options = {}) {
    const res = await fetch(url, { cache:'no-store', ...options });
    const text = await res.text();
    let json = null, parseErr = null;
    try { json = JSON.parse(text); } catch(e){ parseErr = String(e); }
    return { okHTTP: res.ok, status: res.status, url, raw: text, json, parseErr };
  }

  function clearSelect(sel) {
    while (sel.options.length) sel.remove(0);
  }
  function fillSelect(sel, arr, valueKey, labelKey) {
    clearSelect(sel);
    sel.add(new Option('Seleccionar', ''), undefined);
    arr.forEach(o => {
      sel.add(new Option(String(o[labelKey]), String(o[valueKey])), undefined);
    });
  }

  function forceFillOptions() {
    clearSelect(formaPago);
    formaPago.add(new Option('Seleccionar',''), undefined);
    formaPago.add(new Option('Descuento por cooperativa', '6'), undefined);
    formaPago.add(new Option('E-chek', '4'), undefined);
    formaPago.add(new Option('Transferencia Bancaria', '5'), undefined);
    formaPago.dispatchEvent(new Event('change', {bubbles:true}));
    alert('Cargué 3 opciones de PRUEBA en el select de métodos de pago.');
  }

  // --------- cargas reales ----------
  async function loadFormasPago() {
    const r = await fetchJSON(API + '?action=formas_pago');
    dbg.log(r, 'GET formas_pago (respuesta cruda)');
    if (!r.okHTTP) {
      showError('formas_pago devolvió HTTP ' + r.status);
      return false;
    }
    if (!r.json || !r.json.ok || !Array.isArray(r.json.data)) {
      showError('formas_pago: respuesta no válida (parseErr=' + (r.parseErr || 'ok') + ')');
      return false;
    }
    fillSelect(formaPago, r.json.data, 'id', 'nombre');
    formaPago.dispatchEvent(new Event('change', {bubbles:true}));
    return true;
  }

  async function loadPatologias() {
    const r = await fetchJSON(API + '?action=patologias');
    dbg.log(r, 'GET patologias (respuesta cruda)');
    if (r.okHTTP && r.json && r.json.ok && Array.isArray(r.json.data)) {
      fillSelect(patologia, r.json.data, 'id', 'nombre');
      patologia.dispatchEvent(new Event('change', {bubbles:true}));
      return true;
    }
    showError('patologias: error ' + r.status);
    return false;
  }

  async function loadCooperativas() {
    const r = await fetchJSON(API + '?action=cooperativas');
    dbg.log(r, 'GET cooperativas (respuesta cruda)');
    if (r.okHTTP && r.json && r.json.ok && Array.isArray(r.json.data)) {
      fillSelect(coopSelect, r.json.data, 'id_real', 'usuario');
      return true;
    }
    showError('cooperativas: error ' + r.status);
    return false;
  }

  // --------- UI y eventos ----------
  function showError(msg){ console.error('[ERROR]', msg); alert(msg); }

  formaPago.addEventListener('change', async ()=>{
    const id = Number(formaPago.value || 0);
    if (id === 6) {
      coopGroup.style.display = 'block';
      coopSelect.disabled = false;
      coopSelect.setAttribute('aria-disabled','false');
      if (coopSelect.options.length <= 1) await loadCooperativas();
    } else {
      coopGroup.style.display = 'none';
      coopSelect.disabled = true;
      coopSelect.setAttribute('aria-disabled','true');
      coopSelect.value = '';
    }
  });

  patologia.addEventListener('change', async ()=>{
    const val = patologia.value;
    productosBody.innerHTML = '';
    if (!val) return;
    const r = await fetchJSON(API + '?action=productos_por_patologia&patologia_id=' + encodeURIComponent(val));
    dbg.log(r, 'GET productos_por_patologia (respuesta cruda)');
    if (!r.okHTTP || !r.json || !r.json.ok) { showError('productos_por_patologia error'); return; }
    const data = r.json.data || [];
    if (!data.length) {
      productosBody.innerHTML = `<tr><td colspan="4">No hay productos sugeridos para esta patología.</td></tr>`;
      return;
    }
    productosBody.innerHTML = data.map(p => `
      <tr>
        <td style="text-align:center;">
          <input type="checkbox" class="prod-check" id="prod_${p.id}" data-pid="${p.id}">
        </td>
        <td><label for="prod_${p.id}">${p.nombre}</label></td>
        <td style="text-align:center;"><input type="radio" name="fuente_${p.id}" value="sve" disabled></td>
        <td style="text-align:center;"><input type="radio" name="fuente_${p.id}" value="productor" disabled></td>
      </tr>
    `).join('');
    productosBody.querySelectorAll('.prod-check').forEach(chk=>{
      chk.addEventListener('change', (e)=>{
        const pid = e.target.dataset.pid;
        productosBody.querySelectorAll(`input[name="fuente_${pid}"]`).forEach(r=>{
          r.disabled = !e.target.checked;
          if (!e.target.checked) r.checked = false;
        });
      });
    });
  });

  // --------- panel de debug y arranque ----------
  dbg.ensure();

  async function renderAllDebug(){
    await loadFormasPago();
    await loadPatologias();
    // cooperativas solo para mostrar crudo
    await loadCooperativas();
  }

  (async function init(){
    await renderAllDebug();
  })();

})();
</script>

