<?php declare(strict_types=1); ?>
<link rel="stylesheet" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css">
<script defer src="https://www.fernandosalguero.com/cdn/assets/javascript/framework.js"></script>

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
          <label for="forma_pago_id">Método de pago *</label>
          <div class="input-icon input-icon-globe">
            <select id="forma_pago_id" name="forma_pago_id" required>
              <option value="">Cargando...</option>
            </select>
          </div>
        </div>

        <!-- coop_descuento_id_real (solo si forma_pago_id = 6) -->
        <div class="input-group">
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
              <option value="enero_q1">enero_q1</option>
              <option value="enero_q2">enero_q2</option>
              <option value="febrero_q1">febrero_q1</option>
              <option value="febrero_q2">febrero_q2</option>
              <option value="octubre_q1">octubre_q1</option>
              <option value="octubre_q2">octubre_q2</option>
              <option value="noviembre_q1">noviembre_q1</option>
              <option value="noviembre_q2">noviembre_q2</option>
              <option value="diciembre_q1">diciembre_q1</option>
              <option value="diciembre_q2">diciembre_q2</option>
            </select>
          </div>
        </div>

        <!-- nombre_producto (según patologia) -->
        <div class="input-group">
          <label for="nombre_producto">Productos sugeridos según patología *</label>
          <div class="input-icon">
            <select id="nombre_producto" name="nombre_producto[]" multiple size="4" aria-multiselectable="true" required>
              <!-- opciones dinámicas -->
            </select>
          </div>
        </div>

        <!-- fuente productos -->
        <div class="input-group">
          <label>¿Quién aporta los productos?</label>
          <div class="input-icon">
            <div role="radiogroup" aria-labelledby="fuente_label" class="form-grid grid-2">
              <label><input type="radio" name="fuente" value="sve" required> SVE</label>
              <label><input type="radio" name="fuente" value="productor" required> Productor</label>
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
        <button class="btn btn-cancelar" type="reset">Cancelar</button>
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
  /* Ajustes mínimos sin romper el CDN */
  #lista-nombres li { padding: .25rem .5rem; cursor: pointer; }
  #lista-nombres li[aria-selected="true"], #lista-nombres li:hover { background: #eef2ff; }
  .modal.hidden { display: none; }
</style>

<script>
(function () {
  'use strict';
  const API = '../partials/drones/controller/drone_formulario_N_Servicio_controller.php';

  const $ = (sel) => document.querySelector(sel);
  const $$ = (sel) => Array.from(document.querySelectorAll(sel));

  const nombreInput = $('#nombre');
  const listaNombres = $('#lista-nombres');
  const productorIdReal = $('#productor_id_real');
  const formaPago = $('#forma_pago_id');
  const coopSelect = $('#coop_descuento_id_real');
  const patologia = $('#patologia_id');
  const productos = $('#nombre_producto');
  const btnPrev = $('#btn-previsualizar');
  const modal = $('#modal-resumen');
  const btnConfirmar = $('#btn-confirmar');
  const btnCerrarModal = $('#btn-cerrar-modal');
  const resumen = $('#resumen-detalle');
  const form = $('#form-solicitud');

  // Helpers UI
  function openModal(){ modal.classList.remove('hidden'); modal.setAttribute('aria-hidden', 'false'); }
  function closeModal(){ modal.classList.add('hidden'); modal.setAttribute('aria-hidden', 'true'); }
  btnCerrarModal.addEventListener('click', closeModal);

  // Cargar combos iniciales
  init();
  async function init() {
    try {
      const [fp, pats] = await Promise.all([
        fetch(API + '?action=formas_pago').then(r => r.json()),
        fetch(API + '?action=patologias').then(r => r.json()),
      ]);
      if (fp.ok) {
        formaPago.innerHTML = '<option value="">Seleccionar</option>' + fp.data.map(o => `<option value="${o.id}">${o.nombre}</option>`).join('');
      }
      if (pats.ok) {
        patologia.innerHTML = '<option value="">Seleccionar</option>' + pats.data.map(o => `<option value="${o.id}">${o.nombre}</option>`).join('');
      }
    } catch (e) {
      showAlert('error', 'No se pudieron cargar opciones iniciales.');
    }
  }

  // Autocomplete de productor
  let acTimer;
  nombreInput.addEventListener('input', () => {
    productorIdReal.value = '';
    const q = nombreInput.value.trim();
    if (acTimer) clearTimeout(acTimer);
    if (q.length < 2) { listaNombres.style.display = 'none'; listaNombres.innerHTML=''; return; }
    acTimer = setTimeout(async () => {
      try {
        const res = await fetch(API + '?action=buscar_usuarios&q=' + encodeURIComponent(q));
        const json = await res.json();
        if (!json.ok) throw new Error();
        listaNombres.innerHTML = json.data.map((u, idx) => `<li role="option" data-id="${u.id_real}" aria-selected="${idx===0?'true':'false'}">${u.usuario}</li>`).join('');
        listaNombres.style.display = json.data.length ? 'block' : 'none';
      } catch (e) {
        listaNombres.style.display = 'none';
      }
    }, 220);
  });

  listaNombres.addEventListener('click', (ev) => {
    const li = ev.target.closest('li[data-id]');
    if (!li) return;
    nombreInput.value = li.textContent;
    productorIdReal.value = li.dataset.id;
    listaNombres.style.display = 'none';
    listaNombres.innerHTML = '';
  });

  // Forma de pago -> habilitar coop si id=6
  formaPago.addEventListener('change', async () => {
    const id = Number(formaPago.value || 0);
    if (id === 6) {
      coopSelect.disabled = false; coopSelect.setAttribute('aria-disabled', 'false');
      // cargar cooperativas si aún no
      if (coopSelect.options.length <= 1) {
        const r = await fetch(API + '?action=cooperativas');
        const j = await r.json();
        if (j.ok) {
          coopSelect.innerHTML = '<option value="">Seleccionar</option>' + j.data.map(c => `<option value="${c.id_real}">${c.usuario}</option>`).join('');
        }
      }
    } else {
      coopSelect.value = '';
      coopSelect.disabled = true; coopSelect.setAttribute('aria-disabled', 'true');
    }
  });

  // Patología -> cargar productos relacionados
  patologia.addEventListener('change', async () => {
    productos.innerHTML = '';
    const val = patologia.value;
    if (!val) return;
    const r = await fetch(API + '?action=productos_por_patologia&patologia_id=' + encodeURIComponent(val));
    const j = await r.json();
    if (j.ok) {
      if (!j.data.length) {
        productos.innerHTML = '<option value="">No hay productos sugeridos</option>';
        return;
      }
      productos.innerHTML = j.data.map(p => `<option value="${p.id}">${p.nombre}</option>`).join('');
    } else {
      showAlert('error', 'Error al cargar productos.');
    }
  });

  // Previsualizar -> abrir modal con resumen
  btnPrev.addEventListener('click', (e) => {
    e.preventDefault();
    // Validación mínima
    if (!form.reportValidity()) {
      showAlert('error', 'Completá los campos requeridos.');
      return;
    }
    const data = getFormData();
    resumen.innerHTML = renderResumen(data);
    openModal();
  });

  // Confirmar -> guardar
  btnConfirmar.addEventListener('click', async () => {
    const payload = getFormData();
    try {
      const res = await fetch(API, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
      });
      const json = await res.json();
      if (json.ok) {
        closeModal();
        form.reset();
        coopSelect.disabled = true; coopSelect.setAttribute('aria-disabled','true');
        showAlert('success', '¡Solicitud guardada! ID ' + json.data.id);
      } else {
        showAlert('error', json.error || 'No se pudo guardar.');
      }
    } catch (e) {
      showAlert('error', 'Error de red al guardar.');
    }
  });

  function getFormData() {
    const selProductos = $$('#nombre_producto option:checked').map(o => Number(o.value));
    const fuente = (new FormData(form)).get('fuente') || '';
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
      forma_pago_id: Number($('#forma_pago_id').value),
      coop_descuento_id_real: $('#coop_descuento_id_real').disabled ? null : ($('#coop_descuento_id_real').value || null),
      patologia_id: Number($('#patologia_id').value),
      rango: $('#rango').value,
      productos: selProductos, // ids
      productos_fuente: fuente, // 'sve'|'productor'
      dir_provincia: $('#dir_provincia').value.trim(),
      dir_localidad: $('#dir_localidad').value.trim(),
      dir_calle: $('#dir_calle').value.trim(),
      dir_numero: $('#dir_numero').value.trim(),
      observaciones: $('#observaciones').value.trim()
    };
  }

  function renderResumen(d) {
    const prods = d.productos.length ? d.productos.map(id => {
      const opt = $('#nombre_producto').querySelector(`option[value="${id}"]`);
      return opt ? opt.textContent : ('ID ' + id);
    }).join(', ') : '—';
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
            <tr><td>Forma pago</td><td>${$('#forma_pago_id').selectedOptions[0]?.textContent || ''}</td></tr>
            <tr><td>Cooperativa</td><td>${$('#coop_descuento_id_real').disabled ? '—' : ($('#coop_descuento_id_real').selectedOptions[0]?.textContent || '')}</td></tr>
            <tr><td>Patología</td><td>${$('#patologia_id').selectedOptions[0]?.textContent || ''}</td></tr>
            <tr><td>Rango</td><td>${d.rango}</td></tr>
            <tr><td>Productos</td><td>${prods}</td></tr>
            <tr><td>Fuente productos</td><td>${d.productos_fuente || '—'}</td></tr>
            <tr><td>Provincia</td><td>${d.dir_provincia}</td></tr>
            <tr><td>Localidad</td><td>${d.dir_localidad}</td></tr>
            <tr><td>Calle</td><td>${d.dir_calle} ${d.dir_numero}</td></tr>
            <tr><td>Observaciones</td><td>${d.observaciones || '—'}</td></tr>
          </tbody>
        </table>
      </div>`;
  }
})();
</script>
