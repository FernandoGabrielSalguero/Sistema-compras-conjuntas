<?php

?>

<div class="content">
  <div class="card" style="background-color:#5b21b6;">
    <h3 style="color:white;">Módulo: Registro nueva solicitud de servicio de pulverización con drones</h3>
    <p style="color:white;margin:0;">Formulario limpio, accesible y listo para guardar.</p>
  </div>

  <div class="card">
    <h4>Completa el formulario para cargar una nueva solicitud de drones</h4>

    <form id="form-solicitud" class="form-modern" novalidate>
      <div class="form-grid grid-4">

        <!-- Persona (typeahead) -->
        <div class="input-group">
          <label for="form_nuevo_servicio_persona">Productor</label>
          <div class="input-icon input-icon-persona typeahead-wrapper">
            <input
              type="text"
              id="form_nuevo_servicio_persona"
              name="form_nuevo_servicio_persona"
              placeholder="Empezá a escribir un nombre…"
              autocomplete="off"
              class="js-typeahead"
              data-ta="personas"
              aria-autocomplete="list"
              aria-expanded="false"
              aria-controls="ta-list-personas"
              aria-activedescendant=""
              required />
            <ul id="ta-list-personas" class="typeahead-list" role="listbox" hidden></ul>
            <input type="hidden" id="productor_id_real" name="productor_id_real" />
          </div>
        </div>

        <!-- Representante -->
        <div class="input-group">
          <label for="form_nuevo_servicio_representante">¿Contamos con un representante en la finca?</label>
          <div class="input-icon input-icon-representante">
            <select id="form_nuevo_servicio_representante" name="form_nuevo_servicio_representante" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>

        <!-- Líneas de tensión -->
        <div class="input-group">
          <label for="form_nuevo_servicio_lineas_tension">¿Hay líneas de media/alta tensión a menos de 3 km?</label>
          <div class="input-icon input-icon-tension">
            <select id="form_nuevo_servicio_lineas_tension" name="form_nuevo_servicio_lineas_tension" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>

        <!-- Zona restringida (antes Aeropuerto) -->
        <div class="input-group">
          <label for="form_nuevo_servicio_zona_restringida">¿Existen zonas restringidas cercanas (ej: aeropuerto)?</label>
          <div class="input-icon input-icon-airport">
            <select id="form_nuevo_servicio_zona_restringida" name="form_nuevo_servicio_zona_restringida" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>

        <!-- Corriente eléctrica -->
        <div class="input-group">
          <label for="form_nuevo_servicio_corriente">¿Disponibilidad de corriente eléctrica?</label>
          <div class="input-icon input-icon-electric">
            <select id="form_nuevo_servicio_corriente" name="form_nuevo_servicio_corriente" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>

        <!-- Agua potable -->
        <div class="input-group">
          <label for="form_nuevo_servicio_agua">¿Hay agua potable?</label>
          <div class="input-icon input-icon-agua">
            <select id="form_nuevo_servicio_agua" name="form_nuevo_servicio_agua" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>

        <!-- Obstáculos -->
        <div class="input-group">
          <label for="form_nuevo_servicio_cuarteles">¿Los cuarteles están libres de obstáculos?</label>
          <div class="input-icon input-icon-campo">
            <select id="form_nuevo_servicio_cuarteles" name="form_nuevo_servicio_cuarteles" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>

        <!-- Área de despegue -->
        <div class="input-group">
          <label for="form_nuevo_servicio_despegue">¿Hay un área de despegue apropiada?</label>
          <div class="input-icon input-icon-despegue">
            <select id="form_nuevo_servicio_despegue" name="form_nuevo_servicio_despegue" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>

        <!-- Hectáreas -->
        <div class="input-group">
          <label for="form_nuevo_servicio_hectareas">¿Cuántas hectáreas?</label>
          <div class="input-icon input-icon-hectareas">
            <input type="number" id="form_nuevo_servicio_hectareas" name="form_nuevo_servicio_hectareas"
              placeholder="Ej: 12" step="1" min="0" required />

          </div>
        </div>

        <!-- Método de pago -->
        <div class="input-group">
          <label for="form_nuevo_servicio_pago">¿Cómo va a pagar?</label>
          <div class="input-icon input-icon-pago">
            <select id="form_nuevo_servicio_pago" name="form_nuevo_servicio_pago" required>
              <option value="">Seleccionar</option>
              <option>Efectivo</option>
              <option>Transferencia</option>
              <option>Tarjeta</option>
              <option>Cuenta corriente</option>
            </select>
          </div>
        </div>

        <!-- Cooperativa (typeahead) -->
        <div class="input-group" id="grupo-cooperativa" hidden>
          <label for="form_nuevo_servicio_cooperativa">Cooperativa responsable del pago</label>
          <div class="input-icon input-icon-coop">
            <select id="form_nuevo_servicio_cooperativa" name="form_nuevo_servicio_cooperativa">
              <option value="">Seleccionar</option>
              <!-- Opciones dinámicas: value = id_real, label = usuario -->
            </select>
          </div>
        </div>

        <!-- Quincena -->
        <div class="input-group">
          <label for="form_nuevo_servicio_quincena">¿Quincena de visita?</label>
          <div class="input-icon input-icon-date">
            <select id="form_nuevo_servicio_quincena" name="form_nuevo_servicio_quincena" required>
              <option value="">Seleccionar</option>
            </select>
          </div>
        </div>

        <!-- Dirección -->
        <div class="input-group" style="display:none;">
          <label for="form_nuevo_servicio_provincia">Provincia</label>
          <div class="input-icon input-icon-globe">
            <select id="form_nuevo_servicio_provincia" name="form_nuevo_servicio_provincia" required>
              <option value="">Seleccionar</option>
              <option>Buenos Aires</option>
              <option>Ciudad Autónoma de Buenos Aires</option>
              <option>Catamarca</option>
              <option>Chaco</option>
              <option>Chubut</option>
              <option>Córdoba</option>
              <option>Corrientes</option>
              <option>Entre Ríos</option>
              <option>Formosa</option>
              <option>Jujuy</option>
              <option>La Pampa</option>
              <option>La Rioja</option>
              <option selected="selected">Mendoza</option>
              <option>Misiones</option>
              <option>Neuquén</option>
              <option>Río Negro</option>
              <option>Salta</option>
              <option>San Juan</option>
              <option>San Luis</option>
              <option>Santa Cruz</option>
              <option>Santa Fe</option>
              <option>Santiago del Estero</option>
              <option>Tierra del Fuego</option>
              <option>Tucumán</option>
            </select>
          </div>
        </div>

        <div class="input-group">
          <label for="form_nuevo_servicio_localidad">Localidad</label>
          <div class="input-icon input-icon-city">
            <input type="text" id="form_nuevo_servicio_localidad" name="form_nuevo_servicio_localidad" required />
          </div>
        </div>

        <div class="input-group">
          <label for="form_nuevo_servicio_calle">Calle</label>
          <div class="input-icon input-icon-calle">
            <input type="text" id="form_nuevo_servicio_calle" name="form_nuevo_servicio_calle" required />
          </div>
        </div>

        <div class="input-group">
          <label for="form_nuevo_servicio_numero">Número</label>
          <div class="input-icon input-icon-numero">
            <input type="number" id="form_nuevo_servicio_numero" name="form_nuevo_servicio_numero" min="0" step="1" required />
          </div>
        </div>

        <!-- Motivo (multi-selección con checkboxes) -->
        <div class="input-group">
          <label for="form_nuevo_servicio_motivo_toggle">Motivo del servicio</label>
          <div class="input-icon input-icon-motivo">
            <!-- Botón que emula el select -->
            <button type="button"
              id="form_nuevo_servicio_motivo_toggle"
              class="selectlike"
              aria-haspopup="listbox"
              aria-expanded="false"
              aria-controls="form_nuevo_servicio_motivo_list">
              Seleccionar
            </button>
            <!-- Lista desplegable con checkboxes -->
            <ul id="form_nuevo_servicio_motivo_list"
              class="selectlike-list"
              role="listbox"
              aria-multiselectable="true"
              hidden>
              <!-- Opciones dinámicas: li > label > input[type=checkbox data-id] + span(nombre) -->
            </ul>
            <!-- Hidden para compatibilidad (primer motivo seleccionado) -->
            <input type="hidden" id="form_nuevo_servicio_motivo" name="form_nuevo_servicio_motivo" />
            <!-- Hidden con todos los IDs seleccionados (CSV) -->
            <input type="hidden" id="form_nuevo_servicio_motivo_ids" name="form_nuevo_servicio_motivo_ids" />
          </div>
        </div>

        <!-- Observaciones (full width) -->
        <div class="input-group full-span">
          <label for="form_nuevo_servicio_observaciones">Observaciones</label>
          <div class="input-icon input-icon-comment">
            <textarea id="form_nuevo_servicio_observaciones" name="form_nuevo_servicio_observaciones"
              maxlength="233" rows="3" placeholder="Escribí un comentario..."></textarea>
          </div>
        </div>

        <!-- ===== Selector de productos (UX simplificada) ===== -->
        <div class="card full-span" id="productos-wrapper">
          <h2>Productos sugeridos por patología</h2>
          <p class="costos-muted" id="productos-ayuda">Seleccioná qué productos incluir y quién los aporta.</p>

          <!-- Lista de productos sugeridos (se genera dinámicamente) -->
          <ul id="productos-list" class="productos-list" aria-live="polite" aria-label="Productos sugeridos"></ul>

          <!-- Productos custom del productor -->
          <div class="custom-prod mt-3">
            <h3>Producto del productor (custom)</h3>
            <p class="costos-muted">Si el productor ya tiene un producto que no está en el listado, podés agregarlo acá.</p>
            <button type="button" class="btn" id="btn-add-custom-prod">Agregar producto del productor</button>
            <ul id="custom-prods-list" class="productos-list mt-2" aria-live="polite" aria-label="Productos del productor"></ul>
          </div>
        </div>



        <!-- ===== Tarjeta: Costo del servicio ===== -->
        <div class="card full-span" id="card-costos" aria-live="polite">
          <h2>Costo del servicio</h2>
          <div class="tabla-wrapper">
            <table class="data-table" aria-label="Resumen de costos del servicio">
              <thead>
                <tr>
                  <th>Ítem</th>
                  <th>Detalle</th>
                  <th>Importe</th>
                </tr>
              </thead>
              <tbody id="costos-body">
                <!-- Se completa dinámicamente -->
              </tbody>
              <tfoot>
                <tr>
                  <th colspan="2">Precio final</th>
                  <th id="costos-precio-final">$ 0.00</th>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

      </div>

      <!-- Botones -->
      <div class="form-buttons">
        <button class="btn btn-aceptar" type="button" id="btn-solicitar">Solicitar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal de confirmación -->
<div id="modal-resumen" class="modal hidden" aria-hidden="true" role="dialog" aria-modal="true">
  <div class="modal-content">
    <h3>Confirmar solicitud</h3>
    <div id="resumen-detalle" class="card" style="max-height:40vh; overflow:auto;">
      <p>Estás por solicitar el servicio de pulverización con drones. Esta acción solo puede cancelarse por el administrador.</p>
    </div>
    <div class="form-buttons">
      <button class="btn btn-aceptar" id="btn-confirmar">Confirmar y guardar</button>
      <button class="btn btn-cancelar" id="btn-cerrar-modal">Cancelar</button>
    </div>
  </div>
</div>

<style>
  /* Fuerza ocultamiento para elementos con atributo hidden */
  [hidden] {
    display: none !important;
  }

  /* ===== Select-like multi (Motivos) ===== */
  .selectlike {
    width: 100%;
    min-height: 40px;
    border: 1px solid #e5e7eb;
    background: #fff;
    border-radius: 8px;
    padding: 8px 12px;
    text-align: left;
    line-height: 1.2;
    cursor: pointer;
  }

  .selectlike:focus {
    outline: 2px solid var(--primary-color);
    outline-offset: 2px;
  }

  .selectlike-list {
    position: absolute;
    z-index: 20;
    width: 100%;
    max-height: 240px;
    overflow: auto;
    margin-top: 6px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, .06);
    padding: 6px 0;
  }

  .selectlike-item {
    display: flex;
    align-items: center;
    gap: .5rem;
    padding: 8px 12px;
    cursor: pointer;
  }

  .selectlike-item:hover,
  .selectlike-item:focus-within {
    background: #faf5ff;
  }

  .selectlike-item input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: var(--primary-color);
  }

  /* ===== Resumen de costos ===== */
  #card-costos h2 {
    margin-bottom: .5rem;
  }

  #costos-body td,
  #costos-body th {
    vertical-align: middle;
  }

  .costos-muted {
    color: #6b7280;
    font-size: .95rem;
  }

  .costos-right {
    text-align: right;
    white-space: nowrap;
  }

  .badge-prod {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 999px;
    background: #f3e8ff;
  }

  /* ===== Productos (UX simplificada) ===== */
  .productos-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    gap: 12px;
  }

  .prod-card {
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 12px;
    background: #fff;
    display: grid;
    gap: 8px;
  }

  .prod-card .prod-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
  }

  .prod-card .prod-name {
    font-weight: 600;
    color: #111827;
  }

  .prod-card .prod-controls {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: center;
  }

  .prod-card .prod-controls .radio {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
  }

  .prod-card .prod-controls .radio input[type="radio"] {
    accent-color: var(--primary-color);
  }

  .prod-card .prod-controls .chk input[type="checkbox"] {
    accent-color: var(--primary-color);
  }

  .prod-card .prod-input {
    display: none;
  }

  .prod-card[data-fuente="productor"][data-incluir="true"] .prod-input {
    display: block;
  }

  .badge-sve {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 999px;
    background: #eef2ff;
  }

  .badge-custom {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 999px;
    background: #fef3c7;
  }

  .mt-2 {
    margin-top: .5rem;
  }

  .mt-3 {
    margin-top: 1rem;
  }

  /* Fuerza a que las tarjetas de productos y costos sean de ancho completo (una debajo de la otra) */
  .card.full-span {
    grid-column: 1 / -1 !important;
    width: 100% !important;
    flex: 0 0 100% !important;
    display: block;
  }

  /* Si algún contenedor usa flex, evitamos que se achiquen */
  #productos-wrapper,
  #card-costos {
    min-width: 100%;
  }

  /* (opcional) que el contenedor pueda expandir del todo */
  .content {
    max-width: 100%;
  }
</style>

<script>
  (function() {
    const CTRL_URL = '../partials/drones/controller/drone_formulario_N_Servicio_controller.php';

    // Helpers
    const $ = (sel, ctx = document) => ctx.querySelector(sel);
    const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));
    const on = (el, ev, fn) => el.addEventListener(ev, fn);
    const debounce = (fn, ms = 250) => {
      let t;
      return (...a) => {
        clearTimeout(t);
        t = setTimeout(() => fn(...a), ms);
      }
    }

    const fetchJson = async (url) => {
      const r = await fetch(url, {
        credentials: 'same-origin'
      });
      if (!r.ok) throw new Error('HTTP ' + r.status);
      const j = await r.json().catch(() => {
        throw new Error('Respuesta no es JSON');
      });
      if (!j || typeof j.ok === 'undefined') throw new Error('Formato inesperado');
      if (!j.ok) throw new Error(j.error || 'Error');
      return j.data;
    };

    const postJson = async (url, data) => {
      const r = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        credentials: 'same-origin',
        body: JSON.stringify(data)
      });
      const j = await r.json();
      if (!j.ok) throw new Error(j.error || 'Error');
      return j.data;
    };
    const normalizaSiNo = (v) => {
      if (typeof v !== 'string') return null;
      const s = v.trim().toLowerCase();
      if (s === 'si' || s === 'sí') return 'si';
      if (s === 'no') return 'no';
      return null;
    };

    // Referencias UI
    const form = $('#form-solicitud');
    const btnSolicitar = $('#btn-solicitar');
    const modal = $('#modal-resumen');
    const btnConfirmar = $('#btn-confirmar');
    const btnCerrarModal = $('#btn-cerrar-modal');

    // Campos
    const inpPersona = $('#form_nuevo_servicio_persona');
    const hidPersona = $('#productor_id_real');
    const listPersona = $('#ta-list-personas');

    const selRep = $('#form_nuevo_servicio_representante');
    const selLinea = $('#form_nuevo_servicio_lineas_tension');
    const selZonaRes = $('#form_nuevo_servicio_zona_restringida');
    const selCorr = $('#form_nuevo_servicio_corriente');
    const selAgua = $('#form_nuevo_servicio_agua');
    const selCuart = $('#form_nuevo_servicio_cuarteles');
    const selDespegue = $('#form_nuevo_servicio_despegue');
    const inpHect = $('#form_nuevo_servicio_hectareas');

    const selPago = $('#form_nuevo_servicio_pago');
    const grupoCoop = $('#grupo-cooperativa');
    const selCoop = $('#form_nuevo_servicio_cooperativa');

    const selMotivoHidden = $('#form_nuevo_servicio_motivo'); // compatibilidad (primer id)
    const selMotivoHiddenIds = $('#form_nuevo_servicio_motivo_ids'); // CSV de ids
    const btnMotivoToggle = $('#form_nuevo_servicio_motivo_toggle');
    const ulMotivoList = $('#form_nuevo_servicio_motivo_list');
    const selQuincena = $('#form_nuevo_servicio_quincena');

    const selProv = $('#form_nuevo_servicio_provincia');
    const inpLoc = $('#form_nuevo_servicio_localidad');
    const inpCalle = $('#form_nuevo_servicio_calle');
    const inpNum = $('#form_nuevo_servicio_numero');
    const inpObs = $('#form_nuevo_servicio_observaciones');

    // Productos (UX simplificada)
    const productosList = $('#productos-list');
    const customProdsList = $('#custom-prods-list');
    const btnAddCustomProd = $('#btn-add-custom-prod');

    // Estado interno de productos sugeridos (por id)
    let SUGERIDOS = new Map(); // id -> {id, nombre, costo_hectarea, incluir: bool, fuente: 'sve'|'productor', nombre_custom: ''}

    // Renderiza una tarjeta de producto sugerido
    function renderProdCard(p) {
      const li = document.createElement('li');
      li.className = 'prod-card';
      li.dataset.tipo = 'sugerido';
      li.dataset.id = String(p.id);
      li.dataset.incluir = String(!!p.incluir);
      li.dataset.fuente = p.fuente || '';

      li.innerHTML = `
    <div class="prod-head">
      <div class="prod-name">${p.nombre}</div>
      <div class="chk">
        <label><input type="checkbox" class="incluir-chk" ${p.incluir ? 'checked' : ''}> Incluir</label>
      </div>
    </div>
    <div class="prod-controls">
      <label class="radio"><input type="radio" name="fuente_${p.id}" value="sve" ${p.fuente==='sve'?'checked':''} ${p.incluir?'':'disabled'}> Lo aporta <span class="badge-sve">SVE</span></label>
      <label class="radio"><input type="radio" name="fuente_${p.id}" value="productor" ${p.fuente==='productor'?'checked':''} ${p.incluir?'':'disabled'}> Lo aporta el productor</label>
      <span class="costos-muted">Costo/ha: ${fmt(Number(p.costo_hectarea||0))}</span>
    </div>
    <div class="prod-input">
      <div class="input-group">
        <label for="prod_nombre_${p.id}">Nombre del producto del productor</label>
        <div class="input-icon input-icon-name">
          <input type="text" id="prod_nombre_${p.id}" class="prod-nombre-input" placeholder="Ej: Herbicida XYZ" value="${p.nombre_custom ? p.nombre_custom.replace(/"/g,'&quot;') : ''}" ${p.fuente==='productor' && p.incluir ? '' : 'disabled'}>
        </div>
      </div>
    </div>
  `;

      // Listeners
      const chk = li.querySelector('.incluir-chk');
      const radios = li.querySelectorAll(`input[name="fuente_${p.id}"]`);
      const input = li.querySelector('.prod-nombre-input');

      chk.addEventListener('change', () => {
        const inc = chk.checked;
        li.dataset.incluir = String(inc);
        radios.forEach(r => r.disabled = !inc);
        if (!inc) {
          input.value = '';
          input.disabled = true;
          li.dataset.fuente = '';
          SUGERIDOS.get(p.id).fuente = '';
          SUGERIDOS.get(p.id).nombre_custom = '';
        }
        recalcCostos();
      });
      radios.forEach(r => r.addEventListener('change', () => {
        li.dataset.fuente = r.value;
        SUGERIDOS.get(p.id).fuente = r.value;
        if (r.value === 'productor' && chk.checked) {
          input.disabled = false;
        } else {
          input.disabled = true;
          input.value = '';
          SUGERIDOS.get(p.id).nombre_custom = '';
        }
        recalcCostos();
      }));
      input.addEventListener('input', debounce(() => {
        SUGERIDOS.get(p.id).nombre_custom = input.value.trim();
      }, 150));

      return li;
    }

    // Render de lista completa de sugeridos
    function renderProductosSugeridos(arr) {
      SUGERIDOS = new Map(arr.map(p => [p.id, {
        ...p,
        incluir: false,
        fuente: '',
        nombre_custom: ''
      }]));
      productosList.innerHTML = '';
      if (!arr.length) {
        productosList.innerHTML = `<li class="costos-muted">No hay productos asociados a la patología seleccionada.</li>`;
        return;
      }
      arr.forEach(p => productosList.appendChild(renderProdCard(p)));
    }

    // Agregar fila custom del productor
    function addCustomProductoRow(prefill = '') {
      const li = document.createElement('li');
      li.className = 'prod-card';
      li.dataset.tipo = 'custom';
      li.innerHTML = `
    <div class="prod-head">
      <div class="prod-name"><span class="badge-custom">Custom</span> Producto del productor</div>
      <button type="button" class="btn btn-cancelar btn-sm btn-remove">Quitar</button>
    </div>
    <div class="prod-controls">
      <span class="costos-muted">Lo aporta el productor</span>
    </div>
    <div class="prod-input" style="display:block;">
      <div class="input-group">
        <label>Nombre del producto</label>
        <div class="input-icon input-icon-name">
          <input type="text" class="prod-nombre-input" placeholder="Ej: Fungicida ABC" value="${prefill.replace(/"/g,'&quot;')}">
        </div>
      </div>
    </div>
  `;
      const btnRemove = li.querySelector('.btn-remove');
      btnRemove.addEventListener('click', () => {
        li.remove();
        recalcCostos();
      });
      customProdsList.appendChild(li);
    }

    btnAddCustomProd.addEventListener('click', () => addCustomProductoRow());

    // Carga productos por patologías seleccionadas (unión única)
    async function loadProductosPorPatologias(patologiaIds = []) {
      try {
        // Traza por cada patología
        const requests = patologiaIds.map(async (id) => {
          const data = await fetchJson(`${CTRL_URL}?action=productos_por_patologia&patologia_id=${encodeURIComponent(id)}`).catch(() => []);
          console.log(`[PRODUCTOS] Patología ${id}:`, data);
          return data;
        });

        const results = await Promise.all(requests);

        // Consolidar y de-duplicar por id de producto
        const map = new Map();
        results.flat().forEach(p => {
          if (!map.has(p.id)) {
            map.set(p.id, {
              id: p.id,
              nombre: p.nombre,
              costo_hectarea: Number(p.costo_hectarea ?? 0)
            });
          }
        });

        const productos = Array.from(map.values()).sort((a, b) => a.nombre.localeCompare(b.nombre, 'es'));

        // Traza consolidada
        console.log('[PRODUCTOS] Consolidado:', productos);
        if (console.table) console.table(productos);

        renderProductosSugeridos(productos);
        recalcCostos();
      } catch (e) {
        console.error('[PRODUCTOS] Error cargando productos sugeridos:', e);
        productosList.innerHTML = '';
        showAlert('error', 'Error cargando productos sugeridos.');
      }
    }


    // ---- Typeahead solo para Personas (productores) ----
    const initTypeahead = (input, ulList, opts) => {
      let selectedIndex = -1,
        current = [];
      const render = (items) => {
        ulList.innerHTML = '';
        items.forEach((it, i) => {
          const li = document.createElement('li');
          li.role = 'option';
          li.tabIndex = -1;
          li.textContent = it.label;
          li.dataset.value = it.value;
          li.className = 'ta-item';
          li.addEventListener('mousedown', (e) => {
            e.preventDefault();
            choose(i);
          });
          ulList.appendChild(li);
        });
        ulList.hidden = items.length === 0;
        input.setAttribute('aria-expanded', String(!ulList.hidden));
      };
      const choose = (idx) => {
        const it = current[idx];
        if (!it) return;
        input.value = it.label;
        if (opts.onChoose) opts.onChoose(it);
        ulList.hidden = true;
        input.setAttribute('aria-expanded', 'false');
      };
      const search = debounce(async () => {
        const q = input.value.trim();
        if (q.length < 2) {
          ulList.hidden = true;
          current = [];
          if (opts.onClear) opts.onClear();
          return;
        }
        try {
          const items = await opts.source(q);
          current = items.map(x => ({
            label: x.label,
            value: x.value
          }));
          render(current);
          selectedIndex = -1;
        } catch (e) {
          console.error(e);
        }
      }, 200);

      input.addEventListener('input', search);
      input.addEventListener('keydown', (e) => {
        if (ulList.hidden) return;
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          selectedIndex = Math.min(selectedIndex + 1, ulList.children.length - 1);
          ulList.children[selectedIndex]?.focus();
        }
        if (e.key === 'ArrowUp') {
          e.preventDefault();
          selectedIndex = Math.max(selectedIndex - 1, 0);
          ulList.children[selectedIndex]?.focus();
        }
        if (e.key === 'Enter') {
          e.preventDefault();
          if (selectedIndex >= 0) choose(selectedIndex);
        }
        if (e.key === 'Escape') {
          ulList.hidden = true;
          input.setAttribute('aria-expanded', 'false');
        }
      });
      document.addEventListener('click', (e) => {
        if (!ulList.contains(e.target) && e.target !== input) {
          ulList.hidden = true;
          input.setAttribute('aria-expanded', 'false');
        }
      });
    };

    // Personas (productores)
    initTypeahead(inpPersona, listPersona, {
      source: async (q) => {
        const coop = selCoop.value ? `&coop_id=${encodeURIComponent(selCoop.value)}` : '';
        const data = await fetchJson(`${CTRL_URL}?action=buscar_usuarios&q=${encodeURIComponent(q)}${coop}`);
        return data.map(u => ({
          label: u.usuario,
          value: u.id_real
        }));
      },
      onChoose: (it) => {
        hidPersona.value = it.value;
      },
      onClear: () => {
        hidPersona.value = '';
      }
    });

    // ---- Cargas dinámicas (selects) ----
    async function loadFormasPago() {
      const data = await fetchJson(`${CTRL_URL}?action=formas_pago`);
      selPago.innerHTML = `<option value="">Seleccionar</option>` + data.map(fp => `<option value="${fp.id}">${fp.nombre}</option>`).join('');
    }
    async function loadRangos() {
      const data = await fetchJson(`${CTRL_URL}?action=rangos`);
      selQuincena.innerHTML = `<option value="">Seleccionar</option>` + data.map(r => `<option value="${r.rango}">${r.label}</option>`).join('');
    }
    async function loadCooperativas() {
      const data = await fetchJson(`${CTRL_URL}?action=cooperativas`);
      selCoop.innerHTML = `<option value="">Seleccionar</option>` + data.map(u => `<option value="${u.id_real}">${u.usuario}</option>`).join('');
    }
    async function loadPatologias() {
      const data = await fetchJson(`${CTRL_URL}?action=patologias`);
      // Render de lista con checkboxes
      ulMotivoList.innerHTML = '';
      data.forEach(p => {
        const li = document.createElement('li');
        li.className = 'selectlike-item';
        li.role = 'option';
        li.dataset.id = String(p.id);

        const label = document.createElement('label');
        label.className = 'selectlike-label';
        label.style.display = 'flex';
        label.style.alignItems = 'center';
        label.style.gap = '.5rem';
        label.style.width = '100%';

        const cb = document.createElement('input');
        cb.type = 'checkbox';
        cb.value = String(p.id);
        cb.setAttribute('aria-label', p.nombre);

        const span = document.createElement('span');
        span.textContent = p.nombre;

        label.appendChild(cb);
        label.appendChild(span);
        li.appendChild(label);
        ulMotivoList.appendChild(li);
      });

      // Reset selección
      setSelectedMotivos([]);
      updateMotivoButton();
    }

    // Mostrar/Ocultar Cooperativa según forma de pago
    selPago.addEventListener('change', () => {
      const val = parseInt(selPago.value || '0', 10);
      if (val === 6) {
        grupoCooperativaShow(true);
      } else {
        grupoCooperativaShow(false);
      }
    });

    function grupoCooperativaShow(show) {
      if (show) {
        grupoCoop.hidden = false;
        selCoop.setAttribute('required', 'required');
      } else {
        grupoCoop.hidden = true;
        selCoop.removeAttribute('required');
        selCoop.value = '';
      }
    }

    document.addEventListener('motivos:change', async () => {
      const ids = getSelectedMotivos().map(n => parseInt(n, 10)).filter(n => n > 0);
      console.log('[MOTIVOS] Seleccionados:', ids);
      if (!ids.length) {
        SUGERIDOS = new Map();
        productosList.innerHTML = '';
        recalcCostos();
        return;
      }
      try {
        await loadProductosPorPatologias(ids);
      } catch (e) {
        console.error('[MOTIVOS] Error cargando productos por patologías:', e);
      }
    });


    // ===== Costos ====
    let costoBaseHa = 0;
    let monedaBase = 'Pesos';

    async function loadCostoBaseHa() {
      try {
        const data = await fetchJson(`${CTRL_URL}?action=costo_base_ha`);
        costoBaseHa = Number(data.costo || 0);
        monedaBase = data.moneda || 'Pesos';
      } catch (e) {
        costoBaseHa = 0;
        monedaBase = 'Pesos';
        console.error(e);
      }
      recalcCostos();
    }

    function num(n) {
      const v = Number(n);
      return Number.isFinite(v) ? v : 0;
    }

    function fmt(n) {
      try {
        return new Intl.NumberFormat('es-AR', {
          style: 'currency',
          currency: (monedaBase === 'USD' ? 'USD' : 'ARS'),
          minimumFractionDigits: 2
        }).format(n);
      } catch (_) {
        return '$ ' + (n.toFixed ? n.toFixed(2) : n);
      }
    }

    function recalcCostos() {
      const tbody = $('#costos-body');
      if (!tbody) return;

      const ha = Math.max(0, parseInt((inpHect.value || '0'), 10));
      const valorHa = num(costoBaseHa);
      const totalBase = valorHa * ha;

      const rows = [];
      rows.push(`<tr><th>Valor de las hectáreas</th><td class="costos-muted">${monedaBase}</td><td class="costos-right">${fmt(valorHa)}</td></tr>`);
      rows.push(`<tr><th>Cantidad de hectáreas</th><td></td><td class="costos-right">${ha}</td></tr>`);
      rows.push(`<tr><th>Total base (servicio)</th><td></td><td class="costos-right">${fmt(totalBase)}</td></tr>`);

      let productosTotal = 0;

      // Sugeridos
      productosList.querySelectorAll('.prod-card[data-tipo="sugerido"]').forEach(li => {
        const id = parseInt(li.dataset.id, 10);
        const inc = li.dataset.incluir === 'true';
        if (!inc) return;
        const p = SUGERIDOS.get(id);
        const fuente = li.dataset.fuente || '';
        const costoHaProd = (fuente === 'sve') ? num(p.costo_hectarea || 0) : 0;
        const totalProd = costoHaProd * ha;
        productosTotal += totalProd;
        const leyenda = (fuente === 'sve') ? 'Aporta SVE' : 'Aporta productor';
        rows.push(`<tr><th>Producto</th><td><span class="badge-prod">${p.nombre}</span> <span class="costos-muted">(${leyenda})</span></td><td></td></tr>`);
        rows.push(`<tr><th>Precio por hectárea del producto</th><td></td><td class="costos-right">${fmt(costoHaProd)}</td></tr>`);
        rows.push(`<tr><th>Costo total del producto</th><td></td><td class="costos-right">${fmt(totalProd)}</td></tr>`);
      });

      // Custom (del productor, sin costo)
      customProdsList.querySelectorAll('.prod-card[data-tipo="custom"]').forEach(li => {
        const name = (li.querySelector('.prod-nombre-input')?.value || '').trim() || 'Producto del productor';
        rows.push(`<tr><th>Producto</th><td><span class="badge-prod">${name}</span> <span class="costos-muted">(Aporta productor)</span></td><td></td></tr>`);
        rows.push(`<tr><th>Precio por hectárea del producto</th><td></td><td class="costos-right">${fmt(0)}</td></tr>`);
        rows.push(`<tr><th>Costo total del producto</th><td></td><td class="costos-right">${fmt(0)}</td></tr>`);
      });

      const precioFinal = totalBase + productosTotal;
      tbody.innerHTML = rows.join('');
      $('#costos-precio-final').textContent = fmt(precioFinal);
    }


    // Recalcular cuando cambia la cantidad de hectáreas
    inpHect.addEventListener('input', debounce(recalcCostos, 150));

    // Al cambiar motivos, la matriz se recompone y luego recalculamos
    document.addEventListener('motivos:change', () => {
      setTimeout(recalcCostos, 0);
    });


    // Modal con validación previa (todos los campos obligatorios)
    btnSolicitar.addEventListener('click', (e) => {
      e.preventDefault();
      const ok = validateBeforeModal();
      if (!ok) return;
      modal.classList.remove('hidden');
      modal.setAttribute('aria-hidden', 'false');
    });
    const closeModal = () => {
      modal.classList.add('hidden');
      modal.setAttribute('aria-hidden', 'true');
    };
    btnCerrarModal.addEventListener('click', (e) => {
      e.preventDefault();
      closeModal();
    });

    // Confirmar y guardar
    btnConfirmar.addEventListener('click', async (e) => {
      e.preventDefault();
      try {
        const payload = buildPayload();
        const data = await postJson(CTRL_URL, payload);
        const newId = (data && typeof data.id !== 'undefined') ? String(data.id) : '—';
        showAlert('success', `Solicitud creada. ID: ${newId}`);
        closeModal();
        form.reset();
        grupoCooperativaShow(false);
        selQuincena.value = '';
        setSelectedMotivos([]);
        recalcCostos();
      } catch (err) {
        console.log(err);
        showAlert('error', `Error: ${err.message}`);
      }
    });

    // ---------- Validador previo al modal ----------
    function markInvalid(el) {
      try {
        el.focus();
      } catch (e) {}
      el.classList.add('input-error');
      setTimeout(() => el.classList.remove('input-error'), 1500);
    }

    function getSiNoValue(sel) {
      const v = (sel?.value || '').trim().toLowerCase();
      return (v === 'si' || v === 'sí' || v === 'no') ? v : '';
    }

    // ===== Helpers Motivos (multi) =====
    function getSelectedMotivos() {
      const csv = (selMotivoHiddenIds.value || '').trim();
      if (!csv) return [];
      return csv.split(',').map(s => parseInt(s, 10)).filter(n => n > 0);
    }

    function setSelectedMotivos(arr) {
      const uniq = Array.from(new Set(arr.filter(n => Number.isInteger(n) && n > 0)));
      selMotivoHiddenIds.value = uniq.join(',');
      // Compat: el primer seleccionado para validación/payload actual
      selMotivoHidden.value = uniq.length ? String(uniq[0]) : '';
      // Marcar checkboxes en UI
      Array.from(ulMotivoList.querySelectorAll('input[type="checkbox"]')).forEach(cb => {
        cb.checked = uniq.includes(parseInt(cb.value, 10));
      });
      updateMotivoButton();
      console.log('[MOTIVOS] setSelectedMotivos ->', uniq);
      document.dispatchEvent(new CustomEvent('motivos:change', {
        detail: {
          ids: uniq
        }
      }));
    }

    function updateMotivoButton() {
      const ids = getSelectedMotivos();
      const names = [];
      ids.forEach(id => {
        const li = ulMotivoList.querySelector(`li[data-id="${id}"] span`);
        if (li) names.push(li.textContent || String(id));
      });
      if (!ids.length) {
        btnMotivoToggle.textContent = 'Seleccionar';
        btnMotivoToggle.setAttribute('aria-expanded', 'false');
        return;
      }
      const label = names.slice(0, 2).join(', ') + (names.length > 2 ? ` (+${names.length - 2})` : '');
      btnMotivoToggle.textContent = label;
    }
    // Toggle de la lista
    btnMotivoToggle.addEventListener('click', () => {
      const isHidden = ulMotivoList.hasAttribute('hidden');
      if (isHidden) {
        ulMotivoList.removeAttribute('hidden');
        btnMotivoToggle.setAttribute('aria-expanded', 'true');
      } else {
        ulMotivoList.setAttribute('hidden', '');
        btnMotivoToggle.setAttribute('aria-expanded', 'false');
      }
    });
    // Cerrar al hacer click afuera
    document.addEventListener('click', (e) => {
      if (!ulMotivoList.contains(e.target) && e.target !== btnMotivoToggle) {
        if (!ulMotivoList.hasAttribute('hidden')) {
          ulMotivoList.setAttribute('hidden', '');
          btnMotivoToggle.setAttribute('aria-expanded', 'false');
        }
      }
    });

    // Cambios de selección (robusto: change + click directo en el checkbox)
    function collectAndSetMotivos() {
      const checked = Array.from(ulMotivoList.querySelectorAll('input[type="checkbox"]:checked'))
        .map(cb => parseInt(cb.value, 10))
        .filter(n => Number.isInteger(n) && n > 0);
      console.log('[MOTIVOS] Checkboxes tildados:', checked);
      setSelectedMotivos(checked);
    }
    ulMotivoList.addEventListener('change', collectAndSetMotivos);
    ulMotivoList.addEventListener('click', (e) => {
      if (e.target && e.target.matches('input[type="checkbox"]')) collectAndSetMotivos();
    });

    function validateBeforeModal() {
      ($$('.input-error') || []).forEach(el => el.classList.remove('input-error'));

      if (!hidPersona.value) {
        showAlert('error', 'Seleccioná un productor.');
        markInvalid(inpPersona);
        return false;
      }

      const siNoFields = [{
          el: selRep,
          name: '¿Contamos con un representante?'
        },
        {
          el: selLinea,
          name: '¿Líneas de tensión?'
        },
        {
          el: selZonaRes,
          name: '¿Zona restringida?'
        },
        {
          el: selCorr,
          name: '¿Corriente eléctrica?'
        },
        {
          el: selAgua,
          name: '¿Agua potable?'
        },
        {
          el: selCuart,
          name: '¿Cuarteles libres?'
        },
        {
          el: selDespegue,
          name: '¿Área de despegue?'
        }
      ];
      for (const f of siNoFields) {
        if (!getSiNoValue(f.el)) {
          showAlert('error', `Completá: ${f.name}`);
          markInvalid(f.el);
          return false;
        }
      }

      const hectInt = parseInt(inpHect.value || '0', 10);
      if (!hectInt || hectInt <= 0) {
        showAlert('error', 'Ingresá la cantidad de hectáreas (entero mayor a 0).');
        markInvalid(inpHect);
        return false;
      }

      const fpago = parseInt(selPago.value || '0', 10);
      if (!fpago) {
        showAlert('error', 'Seleccioná la forma de pago.');
        markInvalid(selPago);
        return false;
      }

      if (fpago === 6) {
        if (!selCoop.value) {
          showAlert('error', 'Seleccioná la cooperativa para la forma de pago 6.');
          grupoCooperativaShow(true);
          markInvalid(selCoop);
          return false;
        }
      }

      const motivosSel = getSelectedMotivos();
      if (!motivosSel.length) {
        showAlert('error', 'Seleccioná al menos un motivo/patología.');
        markInvalid(btnMotivoToggle);
        return false;
      }

      if (!selQuincena.value) {
        showAlert('error', 'Seleccioná la quincena de visita.');
        markInvalid(selQuincena);
        return false;
      }

      if (!selProv.value) {
        showAlert('error', 'Seleccioná la provincia.');
        markInvalid(selProv);
        return false;
      }
      if (!(inpLoc.value || '').trim()) {
        showAlert('error', 'Ingresá la localidad.');
        markInvalid(inpLoc);
        return false;
      }
      if (!(inpCalle.value || '').trim()) {
        showAlert('error', 'Ingresá la calle.');
        markInvalid(inpCalle);
        return false;
      }
      const numero = parseInt((inpNum.value || '0'), 10);
      if (!numero || numero <= 0) {
        showAlert('error', 'Ingresá un número de puerta válido.');
        markInvalid(inpNum);
        return false;
      }
      return true;
    }

    // Inicialización
    (async function init() {
      try {
        await Promise.all([loadFormasPago(), loadRangos(), loadPatologias(), loadCooperativas(), loadCostoBaseHa()]);
        grupoCooperativaShow(false);
        recalcCostos();
      } catch (e) {
        console.error(e);
      }
    })();
  })();
</script>