<?php

?>
<!-- <script defer src="https://www.fernandosalguero.com/cdn/assets/javascript/framework.js"></script> -->
<link rel="stylesheet" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css">


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

        <!-- forma_pago_id (campo sencillo) -->
        <div class="input-group">
          <label for="forma_pago_id">Método de pago *</label>
          <div class="input-icon">
<select id="forma_pago_id" name="forma_pago_id" required>
  <option value="">Seleccionar</option>
</select>
          </div>
        </div>

        <!-- coop_descuento_id_real (se muestra solo si forma_pago_id = 6) -->
        <div class="input-group" id="wrap-cooperativa" style="display:none;">
          <label for="coop_descuento_id_real">Seleccionar cooperativa</label>
          <div class="input-icon">
            <select id="coop_descuento_id_real" name="coop_descuento_id_real" aria-hidden="true">
              <option value="">Seleccionar</option>
            </select>
          </div>
        </div>


        <!-- patologia_id -->
        <div class="input-group">
          <label for="patologia_id">Motivo del servicio *</label>
          <div class="input-icon">
<select id="patologia_id" name="patologia_id" required>
  <option value="">Seleccionar</option>
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
  /* grilla fluida para inputs */
  .form-grid.grid-4 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1rem;
  }

  /* inputs cómodos para táctil (base) */
  .form-modern input,
  .form-modern select,
  .form-modern textarea {
    min-height: 42px;
  }

  /* tabla (desktop base) */
  #productos-grid .tabla-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }

  #productos-grid table.data-table {
    width: 100%;
  }

  /* sin min-width para permitir ajuste */

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

  .modal.hidden {
    display: none;
  }

  /* ===== Matriz de productos tipo Google Forms ===== */
  #productos-grid table.data-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: auto;
    /* deja fluir las columnas */
  }

  #productos-grid thead th {
    text-align: center;
    font-weight: 600;
    padding: .5rem;
  }

  #productos-grid tbody td {
    padding: .5rem;
    text-align: center;
    vertical-align: middle;
  }

  #productos-grid tbody td:nth-child(2) {
    text-align: center;
    /* la columna de Producto va alineada a la izquierda */
    font-weight: 500;
  }

  /* Columnas fijas para radios */
  #productos-grid th:nth-child(1),
  #productos-grid td:nth-child(1) {
    width: 40px;
  }

  /* check */
  #productos-grid th:nth-child(3),
  #productos-grid td:nth-child(3) {
    width: 80px;
  }

  /* SVE */
  #productos-grid th:nth-child(4),
  #productos-grid td:nth-child(4) {
    width: 100px;
  }

  @media (max-width: 640px) {

    html,
    body {
      overflow-x: hidden;
    }

    #productos-grid table.data-table {
      width: 100%;
      min-width: 0;
    }

    #productos-grid tbody td {
      font-size: .9rem;
      padding: .4rem;
      word-break: break-word;
      white-space: normal;
    }
  }
</style>



<script>
  (function () {
    'use strict';

    const API_URL = '../partials/drones/controller/drone_formulario_N_Servicio_controller.php';

    /* ========= Helpers ========= */
    const byId = (id) => document.getElementById(id);
    const logGroup = (title, payload) => {
      try { console.group(`API ▶ ${title}`); console.log(payload); console.groupEnd(); }
      catch { console.log(`API ▶ ${title}`, payload); }
    };
    const onReady = (fn) => (document.readyState === 'loading'
      ? document.addEventListener('DOMContentLoaded', fn, { once: true })
      : fn());

    const apiGet = async (action, params = {}) => {
      const qs = new URLSearchParams({ action, ...params });
      const res = await fetch(`${API_URL}?${qs.toString()}`, {
        headers: { 'Accept': 'application/json' },
        credentials: 'same-origin',
        cache: 'no-store'
      });
      let json;
      try { json = await res.json(); }
      catch { json = { ok: false, error: 'Respuesta no JSON', status: res.status }; }
      logGroup(action, json);
      return json;
    };

    const clearSelect = (sel, placeholder = 'Seleccionar') => {
      sel.innerHTML = '';
      const opt = document.createElement('option');
      opt.value = '';
      opt.textContent = placeholder;
      sel.appendChild(opt);
    };

    const refreshSelectUI = (sel) => {
      // Disparamos eventos reales para wrappers no reactivos (sin clonar nodos)
      sel.dispatchEvent(new Event('input',  { bubbles: true }));
      sel.dispatchEvent(new Event('change', { bubbles: true }));
      // micro reflow
      sel.style.outline = '0'; void sel.offsetHeight; sel.style.outline = '';
      return sel;
    };

    const fillSelect = (sel, items, mapValue = (x)=>x.id, mapText = (x)=>x.nombre) => {
      if (!sel) return sel;
      clearSelect(sel);
      if (Array.isArray(items)) {
        for (const it of items) {
          const o = document.createElement('option');
          o.value = String(mapValue(it) ?? '');
          o.textContent = String(mapText(it) ?? '');
          sel.appendChild(o);
        }
      }
      console.debug(`Select#${sel.id} opciones:`, sel.options.length);
      return refreshSelectUI(sel);
    };

    /* ========= Matriz de productos ========= */
    const renderProductos = (productos = []) => {
      const tbody = byId('productos-body');
      tbody.innerHTML = '';

      if (!Array.isArray(productos) || productos.length === 0) {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td colspan="4" style="text-align:center;">No hay productos sugeridos para la patología seleccionada.</td>`;
        tbody.appendChild(tr);
        return;
      }

      for (const p of productos) {
        const pid = parseInt(p.id, 10);
        const row = document.createElement('tr');
        row.setAttribute('data-producto-id', String(pid));
        const chkId = `prod_${pid}`;

        row.innerHTML = `
          <td>
            <input type="checkbox" class="chk-prod" id="${chkId}" aria-label="Seleccionar ${p.nombre}">
          </td>
          <td style="text-align:left;">
            <label for="${chkId}">${p.nombre}</label>
          </td>
          <td>
            <input type="radio" name="fuente_${pid}" value="sve" disabled aria-label="Aporta SVE">
          </td>
          <td>
            <input type="radio" name="fuente_${pid}" value="productor" disabled aria-label="Aporta productor">
          </td>
        `;
        tbody.appendChild(row);
      }

      // Habilitar radios solo si está chequeado el producto
      tbody.querySelectorAll('.chk-prod').forEach(chk => {
        chk.addEventListener('change', (e) => {
          const tr = e.target.closest('tr');
          const pid = tr.getAttribute('data-producto-id');
          const radios = tr.querySelectorAll(`input[name="fuente_${pid}"]`);
          radios.forEach(r => { r.disabled = !chk.checked; if (!chk.checked) r.checked = false; });
        }, { passive: true });
      });
    };

    // ✅ Opción B: cargar framework.js **después** de poblar (o no cargarlo)
    const loadFrameworkJS = () => new Promise((resolve, reject) => {
      const src = 'https://www.fernandosalguero.com/cdn/assets/javascript/framework.js';
      const existing = document.querySelector(`script[src="${src}"]`);
      if (existing) { resolve(); return; }
      const s = document.createElement('script');
      s.src = src; s.defer = true;
      s.onload = () => { console.debug('framework.js cargado tardíamente'); resolve(); };
      s.onerror = (e) => { console.warn('framework.js no cargó (continuamos nativo)', e); resolve(); };
      document.head.appendChild(s);
    });

    /* ========= Carga y bindings ========= */
    const init = async () => {
      // Referencias
      const selFormaPago = byId('forma_pago_id');
      const wrapCoop     = byId('wrap-cooperativa');
      const selCoop      = byId('coop_descuento_id_real');
      const selPat       = byId('patologia_id');

      // 1) Data en paralelo
      const [fpRes, patRes, coopRes] = await Promise.all([
        apiGet('formas_pago'),
        apiGet('patologias'),
        apiGet('cooperativas')
      ]);

      // 2) Poblar selects (nativos)
      if (fpRes.ok) fillSelect(selFormaPago, fpRes.data, x => x.id,      x => x.nombre);
      if (coopRes.ok) fillSelect(selCoop,     coopRes.data, x => x.id_real, x => x.usuario);
      if (patRes.ok)  fillSelect(selPat,      patRes.data,  x => x.id,      x => x.nombre);

      // 3) Visibilidad cooperativas
      const updateCoopVisibility = () => {
        const isCoop = String(selFormaPago.value) === '6';
        wrapCoop.style.display = isCoop ? '' : 'none';
        selCoop.toggleAttribute('required', isCoop);
        selCoop.setAttribute('aria-hidden', String(!isCoop));
        if (!isCoop) selCoop.value = '';
      };
      selFormaPago.addEventListener('change', updateCoopVisibility, { passive: true });
      updateCoopVisibility();

      // 4) Matriz por patología
      const loadProductos = async (patologiaId) => {
        if (!patologiaId) { renderProductos([]); return; }
        const res = await apiGet('productos_por_patologia', { patologia_id: patologiaId });
        renderProductos(res.ok ? res.data : []);
      };

      let pid = parseInt(selPat.value || '0', 10);
      if (!pid && patRes.ok && Array.isArray(patRes.data) && patRes.data.length) {
        pid = parseInt(patRes.data[0].id, 10) || 0;
        if (pid) selPat.value = String(pid);
      }
      await loadProductos(pid);

      // 5) Cambio de patología
      selPat.addEventListener('change', async () => {
        const nuevo = parseInt(selPat.value || '0', 10);
        await loadProductos(nuevo);
      }, { passive: true });

      // 6) (Opcional) cargar framework **después** de que todo está poblado
      //    Si preferís NO usarlo en esta vista, comentá la línea siguiente.
      await loadFrameworkJS();
    };

    onReady(init);
  })();
</script>
