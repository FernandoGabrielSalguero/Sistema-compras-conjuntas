<?php
declare(strict_types=1);
?>
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
  /* ====== Responsive & UX mínimos (mobile-first) ====== */

  /* grilla fluida para inputs */
  .form-grid.grid-4 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1rem;
  }

  /* inputs cómodos para táctil */
  .form-modern input,
  .form-modern select,
  .form-modern textarea {
    min-height: 42px;
  }

  /* tabla con scroll horizontal en móviles */
  #productos-grid .tabla-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }
  #productos-grid table.data-table {
    width: 100%;
    min-width: 520px; /* evita que colapse en pantallas angostas */
  }

  /* modal adaptativo */
  #modal-resumen .modal-content {
    max-width: 960px;
    width: 90vw;
  }

  /* Lista autocomplete */
  #lista-nombres li {
    padding: .25rem .5rem;
    cursor: pointer;
  }
  #lista-nombres li[aria-selected="true"],
  #lista-nombres li:hover {
    background: #eef2ff;
  }

  .modal.hidden { display: none; }

  /* Mejora visual de la matriz de productos */
  #productos-grid .data-table th,
  #productos-grid .data-table td { vertical-align: middle; }

  #productos-help { font-size: .9rem; color: #555; }

  /* breakpoints suaves (si tu framework ya define, estos no rompen nada) */
  @media (min-width: 768px) {
    .form-grid.grid-4 { grid-template-columns: repeat(3, 1fr); }
  }
  @media (min-width: 1024px) {
    .form-grid.grid-4 { grid-template-columns: repeat(4, 1fr); }
  }

  /* Inputs un toque más bajos SOLO en pantallas chicas */
@media (max-width: 480px){
  .form-modern input,
  .form-modern select,
  .form-modern textarea {
    min-height: 38px;
  }
}

/* La matriz entra completa en móvil (sin scroll horizontal) */
@media (max-width: 480px){
  #productos-grid .tabla-wrapper { overflow-x: visible; }
  #productos-grid table.data-table {
    min-width: 0;            /* quita el mínimo de 520px */
    table-layout: fixed;     /* reparte columnas por ancho fijo */
    width: 100%;
  }
  #productos-grid .data-table th,
  #productos-grid .data-table td{
    padding: .5rem .5rem;
    font-size: .9rem;        /* texto un poco más chico */
    white-space: normal;     /* permite salto de línea en nombre de producto */
  }
  /* Columna del check más estrecha */
  #productos-grid .data-table th:first-child,
  #productos-grid .data-table td:first-child{ width: 44px; }

  /* Columnas SVE / Productor con ancho fijo y centradas */
  #productos-grid .data-table th:nth-child(3),
  #productos-grid .data-table td:nth-child(3),
  #productos-grid .data-table th:nth-child(4),
  #productos-grid .data-table td:nth-child(4){
    width: 64px;
    text-align: center;
  }
}

</style>

<script>
  (function() {
    'use strict';
    const API = '/partials/drones/controller/drone_formulario_N_Servicio_controller.php';

    const $ = (sel) => document.querySelector(sel);
    const $$ = (sel) => Array.from(document.querySelectorAll(sel));

    const nombreInput = $('#nombre');
    const listaNombres = $('#lista-nombres');
    const productorIdReal = $('#productor_id_real');
    const formaPago = $('#forma_pago_id');
    const coopSelect = $('#coop_descuento_id_real');
    const coopGroup = $('#coop-group');
    const patologia = $('#patologia_id');
    const productosBody = $('#productos-body');

    const btnPrev = $('#btn-previsualizar');
    const btnReset = $('#btn-reset');
    const modal = $('#modal-resumen');
    const btnConfirmar = $('#btn-confirmar');
    const btnCerrarModal = $('#btn-cerrar-modal');
    const resumen = $('#resumen-detalle');
    const form = $('#form-solicitud');

    // ===== Helpers generales =====
    function debugLog(...args){ console.log('[DEBUG]', ...args); }

    function openModal() {
      modal.classList.remove('hidden');
      modal.setAttribute('aria-hidden', 'false');
      debugLog('Modal abierto');
    }
    function closeModal() {
      modal.classList.add('hidden');
      modal.setAttribute('aria-hidden', 'true');
      debugLog('Modal cerrado');
    }
    btnCerrarModal.addEventListener('click', closeModal);

    btnReset.addEventListener('click', () => {
      debugLog('Formulario reseteado manualmente');
      // reset cooperativa
      coopGroup.style.display = 'none';
      coopSelect.required = false;
      coopSelect.disabled = true;
      coopSelect.setAttribute('aria-disabled', 'true');
      coopSelect.value = '';
    });

    // Wrapper fetch con logs
    async function fetchJSON(url, options = {}) {
      debugLog('Fetch ->', url, options);
      const res = await fetch(url, options);
      const ct = res.headers.get('content-type');
      let json;
      try {
        json = ct && ct.includes('application/json') ? await res.json() : null;
      } catch (err) {
        debugLog('Error parseando JSON de', url, err);
      }
      debugLog('Fetch <- status:', res.status, 'ok:', res.ok, 'json:', json);
      if (!res.ok) {
        throw new Error('HTTP ' + res.status + ' al solicitar ' + url);
      }
      return json;
    }

    // ===== Cargar combos iniciales =====
    init();
    async function init() {
      try {
        const [fp, pats] = await Promise.all([
          fetchJSON(API + '?action=formas_pago'),
          fetchJSON(API + '?action=patologias'),
        ]);

        debugLog('Formas de pago recibidas:', fp);
        if (fp && fp.ok && Array.isArray(fp.data)) {
          const opts = fp.data.map(o => `<option value="${o.id}">${o.nombre}</option>`).join('');
          formaPago.innerHTML = '<option value="">Seleccionar</option>' + opts;
        } else {
          formaPago.innerHTML = '<option value="">(sin datos)</option>';
        }

        debugLog('Patologías recibidas:', pats);
        if (pats && pats.ok && Array.isArray(pats.data)) {
          patologia.innerHTML = '<option value="">Seleccionar</option>' + pats.data.map(o => `<option value="${o.id}">${o.nombre}</option>`).join('');
        } else {
          patologia.innerHTML = '<option value="">(sin datos)</option>';
        }

      } catch (e) {
        debugLog('Error en init():', e);
        showAlert('error', 'No se pudieron cargar opciones iniciales.');
        // fallback visible
        formaPago.innerHTML = '<option value="">(sin datos)</option>';
        patologia.innerHTML = '<option value="">(sin datos)</option>';
      }
    }

    // ===== Autocomplete de productor =====
    let acTimer;
    nombreInput.addEventListener('input', () => {
      productorIdReal.value = '';
      const q = nombreInput.value.trim();
      if (acTimer) clearTimeout(acTimer);
      if (q.length < 2) {
        listaNombres.style.display = 'none';
        listaNombres.innerHTML = '';
        return;
      }
      acTimer = setTimeout(async () => {
        const url = API + '?action=buscar_usuarios&q=' + encodeURIComponent(q);
        try {
          const json = await fetchJSON(url);
          if (!json || !json.ok) throw new Error('Respuesta inválida');
          listaNombres.innerHTML = json.data.map((u, idx) => `<li role="option" data-id="${u.id_real}" aria-selected="${idx===0?'true':'false'}">${u.usuario}</li>`).join('');
          listaNombres.style.display = json.data.length ? 'block' : 'none';
          debugLog('Autocomplete nombres q=', q, 'data=', json.data);
        } catch (e) {
          debugLog('Autocomplete error:', e);
          listaNombres.style.display = 'none';
          listaNombres.innerHTML = '';
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
      debugLog('Productor seleccionado:', { nombre: nombreInput.value, id_real: productorIdReal.value });
    });

    // ===== Forma de pago -> cooperativas (id=6) =====
    formaPago.addEventListener('change', async () => {
      const id = Number(formaPago.value || 0);
      debugLog('Cambio forma_pago_id=', id);
      if (id === 6) {
        coopGroup.style.display = 'block';
        coopSelect.required = true;
        coopSelect.disabled = false;
        coopSelect.setAttribute('aria-disabled', 'false');

        // cargar cooperativas si aún no
        if (coopSelect.options.length <= 1) {
          try {
            const j = await fetchJSON(API + '?action=cooperativas');
            debugLog('Cooperativas recibidas:', j);
            if (j && j.ok && Array.isArray(j.data)) {
              coopSelect.innerHTML = '<option value="">Seleccionar</option>' + j.data.map(c => `<option value="${c.id_real}">${c.usuario}</option>`).join('');
            } else {
              showAlert('error', 'No se pudieron cargar cooperativas.');
            }
          } catch (e) {
            debugLog('Error cargando cooperativas:', e);
            showAlert('error', 'No se pudieron cargar cooperativas.');
          }
        }
      } else {
        coopGroup.style.display = 'none';
        coopSelect.required = false;
        coopSelect.value = '';
        coopSelect.disabled = true;
        coopSelect.setAttribute('aria-disabled', 'true');
      }
    });

    // ===== Patología -> productos relacionados (matriz) =====
    patologia.addEventListener('change', async () => {
      productosBody.innerHTML = '';
      const val = patologia.value;
      debugLog('Cambio patologia_id=', val);
      if (!val) return;
      try {
        const j = await fetchJSON(API + '?action=productos_por_patologia&patologia_id=' + encodeURIComponent(val));
        debugLog('Productos por patología:', j);

        if (!j || !j.ok) throw new Error('Respuesta inválida');
        if (!j.data.length) {
          productosBody.innerHTML = `<tr><td colspan="4">No hay productos sugeridos para esta patología.</td></tr>`;
          return;
        }
        productosBody.innerHTML = j.data.map(p => `
          <tr>
            <td style="text-align:center;">
              <input type="checkbox" class="prod-check" id="prod_${p.id}" data-pid="${p.id}" aria-label="Seleccionar ${p.nombre}">
            </td>
            <td><label for="prod_${p.id}">${p.nombre}</label></td>
            <td style="text-align:center;">
              <input type="radio" name="fuente_${p.id}" value="sve" disabled aria-label="SVE provee ${p.nombre}">
            </td>
            <td style="text-align:center;">
              <input type="radio" name="fuente_${p.id}" value="productor" disabled aria-label="Productor provee ${p.nombre}">
            </td>
          </tr>
        `).join('');

        // Habilitar radios solo cuando se marque el producto
        productosBody.querySelectorAll('.prod-check').forEach(chk => {
          chk.addEventListener('change', (e) => {
            const pid = e.target.dataset.pid;
            const radios = productosBody.querySelectorAll(`input[name="fuente_${pid}"]`);
            radios.forEach(r => {
              r.disabled = !e.target.checked;
              if (!e.target.checked) r.checked = false;
            });
            debugLog('Producto toggled:', { producto_id: Number(pid), checked: e.target.checked });
          });
        });
      } catch (e) {
        debugLog('Error cargando productos por patología:', e);
        showAlert('error', 'Error al cargar productos.');
      }
    });

    // ===== Previsualizar -> abrir modal con resumen =====
    btnPrev.addEventListener('click', (e) => {
      e.preventDefault();

      // Validación mínima
      if (!form.reportValidity()) {
        // focos al primer inválido
        const firstInvalid = form.querySelector(':invalid');
        if (firstInvalid) firstInvalid.focus();
        showAlert('error', 'Completá los campos requeridos.');
        debugLog('Validación fallida: campos requeridos faltantes.');
        return;
      }

      const data = getFormData();
      debugLog('Datos para previsualización:', data);
      resumen.innerHTML = renderResumen(data);
      openModal();
    });

    // ===== Confirmar -> guardar =====
    btnConfirmar.addEventListener('click', async () => {
      const payload = getFormData();
      debugLog('POST payload ->', payload);
      try {
        const res = await fetch(API, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        let json;
        try {
          json = await res.json();
        } catch (err) {
          debugLog('Error parseando JSON al guardar:', err);
        }
        debugLog('POST <- status:', res.status, 'ok:', res.ok, 'json:', json);

        if (res.ok && json && json.ok) {
          closeModal();
          form.reset();
          // reset cooperativa
          coopGroup.style.display = 'none';
          coopSelect.required = false;
          coopSelect.disabled = true;
          coopSelect.setAttribute('aria-disabled', 'true');
          showAlert('success', '¡Solicitud guardada! ID ' + json.data.id);
        } else {
          showAlert('error', (json && json.error) ? json.error : 'No se pudo guardar.');
        }
      } catch (e) {
        debugLog('Error de red al guardar:', e);
        showAlert('error', 'Error de red al guardar.');
      }
    });

    function getFormData() {
      // Construir items [{producto_id, fuente}]
      const items = [];
      productosBody.querySelectorAll('.prod-check:checked').forEach(chk => {
        const pid = Number(chk.dataset.pid);
        const fuenteSel = productosBody.querySelector(`input[name="fuente_${pid}"]:checked`);
        items.push({ producto_id: pid, fuente: fuenteSel ? fuenteSel.value : '' });
      });

      const data = {
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
        coop_descuento_id_real: (coopGroup.style.display === 'block') ? ($('#coop_descuento_id_real').value || null) : null,
        patologia_id: Number($('#patologia_id').value),
        rango: $('#rango').value,
        items, // << matriz de productos con fuente
        dir_provincia: $('#dir_provincia').value.trim(),
        dir_localidad: $('#dir_localidad').value.trim(),
        dir_calle: $('#dir_calle').value.trim(),
        dir_numero: $('#dir_numero').value.trim(),
        observaciones: $('#observaciones').value.trim()
      };
      debugLog('getFormData():', data);
      return data;
    }

    function renderResumen(d) {
      debugLog('renderResumen data:', d);
      const prods = (d.items && d.items.length) ?
        d.items.map(it => {
          const row = productosBody.querySelector(`#prod_${it.producto_id}`)?.closest('tr');
          const nombre = row ? row.querySelector('td:nth-child(2)').textContent.trim() : ('ID ' + it.producto_id);
          return `${nombre} (${it.fuente || 'sin fuente'})`;
        }).join('<br>') :
        '—';

      const formaPagoText = $('#forma_pago_id').selectedOptions[0]?.textContent || '';
      const coopEstaVisible = coopGroup.style.display === 'block' && !coopSelect.disabled;
      const coopText = coopEstaVisible ? (coopSelect.selectedOptions[0]?.textContent || '—') : '—';

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
            <tr><td>Patología</td><td>${$('#patologia_id').selectedOptions[0]?.textContent || ''}</td></tr>
            <tr><td>Rango</td><td>${d.rango}</td></tr>
            <tr><td>Productos</td><td>${prods}</td></tr>
            <tr><td>Provincia</td><td>${d.dir_provincia}</td></tr>
            <tr><td>Localidad</td><td>${d.dir_localidad}</td></tr>
            <tr><td>Calle</td><td>${d.dir_calle} ${d.dir_numero}</td></tr>
            <tr><td>Observaciones</td><td>${d.observaciones || '—'}</td></tr>
          </tbody>
        </table>
      </div>`;
    }

    // Log inicial de estado del DOM y ruta API
    debugLog('View inicializada. API=', API);
  })();
</script>
