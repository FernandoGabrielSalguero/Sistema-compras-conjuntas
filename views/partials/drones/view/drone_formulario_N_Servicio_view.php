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
          <label for="form_nuevo_servicio_lineas_tension">¿Hay líneas de media/alta tensión a menos de 30 km?</label>
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


        <!-- Motivo (typeahead) -->
        <div class="input-group">
          <label for="form_nuevo_servicio_motivo">Motivo del servicio</label>
          <div class="input-icon input-icon-motivo">
            <select id="form_nuevo_servicio_motivo" name="form_nuevo_servicio_motivo" required>
              <option value="">Seleccionar</option>
              <!-- Opciones dinámicas: value = id, label = nombre -->
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
        <div class="input-group">
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
              <option>Mendoza</option>
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

        <!-- Observaciones (full width) -->
        <div class="input-group full-span">
          <label for="form_nuevo_servicio_observaciones">Observaciones</label>
          <div class="input-icon input-icon-comment">
            <textarea id="form_nuevo_servicio_observaciones" name="form_nuevo_servicio_observaciones"
              maxlength="233" rows="3" placeholder="Escribí un comentario..."></textarea>
          </div>
        </div>

        <!-- Matriz (full width, parte del mismo formulario) -->
        <fieldset id="form_nuevo_servicio_matriz"
          class="gform-grid cols-1 full-span"
          aria-labelledby="legend-matriz">
          <div class="gform-question" data-required="true">
            <div id="legend-matriz" class="gform-legend">
              Elegí los productos <span class="gform-required">*</span>
            </div>
            <div class="gform-helper">
              Primero seleccioná el producto. Solo entonces podés elegir una opción en la fila.
            </div>

            <div class="gform-matrix-scroll" role="region"
              aria-label="Desplazate horizontalmente para ver todas las columnas">
              <table class="gform-matrix" role="table" aria-label="Matriz de productos">
                <thead>
                  <tr>
                    <th scope="col" class="gfm-empty"></th>
                    <th scope="col">SVE</th>
                    <th scope="col">Productor</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <th scope="row">
                      <label class="gfm-prod">
                        <input type="checkbox" class="gfm-row-toggle" name="m_sel[]" value="row1" data-row="row1" />
                        <span>Producto 1</span>
                      </label>
                    </th>
                    <td data-col="SVE">
                      <label class="gfm-radio"><input type="radio" name="m_row1" value="sve" disabled /></label>
                    </td>
                    <td data-col="Productor">
                      <label class="gfm-radio"><input type="radio" name="m_row1" value="productor" disabled /></label>
                    </td>
                  </tr>

                  <tr>
                    <th scope="row">
                      <label class="gfm-prod">
                        <input type="checkbox" class="gfm-row-toggle" name="m_sel[]" value="row2" data-row="row2" />
                        <span>Producto 2</span>
                      </label>
                    </th>
                    <td data-col="SVE">
                      <label class="gfm-radio"><input type="radio" name="m_row2" value="sve" disabled /></label>
                    </td>
                    <td data-col="Productor">
                      <label class="gfm-radio"><input type="radio" name="m_row2" value="productor" disabled /></label>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>


            <div class="gform-error">
              Seleccioná al menos un producto y, para cada producto seleccionado, elegí una opción.
            </div>
          </div>
        </fieldset>



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

  /* ===== Matriz (Google Forms-like) ===== */
  .gform-matrix-scroll {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    margin: .5rem 0;
    padding-bottom: .25rem;
  }

  .gform-matrix {
    min-width: 640px;
  }

  @supports (position: sticky) {
    .gform-matrix thead th {
      position: sticky;
      top: 0;
      z-index: 1;
    }

    .gform-matrix thead th.gfm-empty,
    .gform-matrix tbody th[scope="row"] {
      position: sticky;
      left: 0;
      z-index: 2;
      background: #fff;
    }

    .gform-matrix tbody tr:nth-child(even) th[scope="row"] {
      background: #fafafa;
    }
  }

  .full-span {
    grid-column: 1 / -1;
  }

  .gform-matrix {
    width: 100%;
    border: 1px solid #e9d7f7;
    border-radius: 12px;
    overflow: hidden;
    border-collapse: separate;
    border-spacing: 0;
    background: #fff;
  }

  .gform-matrix thead th {
    background: #faf5ff;
    color: #111827;
    font-weight: 700;
    text-align: center;
    padding: 12px 16px;
    font-size: .95rem;
    border-bottom: 1px solid #efe7fb;
  }

  .gform-matrix thead .gfm-empty {
    background: #fff;
    border-bottom-color: transparent;
  }

  .gform-matrix tbody th[scope="row"] {
    text-align: left;
    font-weight: 600;
    color: #374151;
    padding: 14px 16px;
    white-space: nowrap;
  }

  .gform-matrix td {
    text-align: center;
    padding: 10px 16px;
    border-bottom: 1px solid #f3f4f6;
  }

  .gform-matrix tbody tr:nth-child(even) {
    background: #fafafa;
  }

  .gform-matrix tbody tr:last-child td,
  .gform-matrix tbody tr:last-child th {
    border-bottom: 0;
  }

  .gfm-prod {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    cursor: pointer;
  }

  .gfm-prod input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: var(--primary-color);
  }

  .gfm-radio {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    min-height: 32px;
    cursor: pointer;
  }

  .gfm-radio input {
    appearance: none;
    -webkit-appearance: none;
    width: 18px;
    height: 18px;
    border: 2px solid #9ca3af;
    border-radius: 50%;
    display: inline-block;
    position: relative;
    outline: none;
    background: #fff;
    transition: border-color .15s ease, box-shadow .15s ease, opacity .15s ease;
  }

  .gfm-radio input[disabled] {
    opacity: .45;
    cursor: not-allowed;
  }

  .gfm-radio input:hover:not([disabled]) {
    box-shadow: 0 0 0 4px rgba(91, 33, 182, .08);
  }

  .gfm-radio input:checked {
    border-color: var(--primary-color);
  }

  .gfm-radio input:checked::after {
    content: "";
    position: absolute;
    top: 50%;
    left: 50%;
    width: 8px;
    height: 8px;
    background: var(--primary-color);
    border-radius: 50%;
    transform: translate(-50%, -50%);
  }

  .input-error {
    outline: 2px solid #ef4444;
    border-radius: 6px;
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
      const j = await r.json();
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

    const selMotivo = $('#form_nuevo_servicio_motivo');
    const selQuincena = $('#form_nuevo_servicio_quincena');

    const selProv = $('#form_nuevo_servicio_provincia');
    const inpLoc = $('#form_nuevo_servicio_localidad');
    const inpCalle = $('#form_nuevo_servicio_calle');
    const inpNum = $('#form_nuevo_servicio_numero');
    const inpObs = $('#form_nuevo_servicio_observaciones');

    // Matriz
    const matrizFS = $('#form_nuevo_servicio_matriz');
    const matrizTable = matrizFS.querySelector('table.gform-matrix');
    const matrizBody = matrizTable.querySelector('tbody');

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
        const data = await fetchJson(`${CTRL_URL}?action=buscar_usuarios&q=${encodeURIComponent(q)}`);
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
      selMotivo.innerHTML = `<option value="">Seleccionar</option>` + data.map(p => `<option value="${p.id}">${p.nombre}</option>`).join('');
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

    // Patología → cargar productos de matriz
    selMotivo.addEventListener('change', async () => {
      const pid = parseInt(selMotivo.value || '0', 10);
      if (!pid) {
        matrizBody.innerHTML = '';
        return;
      }
      await loadProductosPorPatologia(pid);
    });

    // Matriz dinámica
    async function loadProductosPorPatologia(patologiaId) {
      try {
        const data = await fetchJson(`${CTRL_URL}?action=productos_por_patologia&patologia_id=${patologiaId}`);
        matrizBody.innerHTML = data.map((p) => {
          const rowId = `row_${p.id}`;
          return `
          <tr>
            <th scope="row">
              <label class="gfm-prod">
                <input type="checkbox" class="gfm-row-toggle" name="m_sel[]" value="${rowId}" data-row="${rowId}" data-producto-id="${p.id}" />
                <span>${p.nombre}</span>
              </label>
            </th>
            <td data-col="SVE">
              <label class="gfm-radio"><input type="radio" name="m_${rowId}" value="sve" disabled /></label>
            </td>
            <td data-col="Productor">
              <label class="gfm-radio"><input type="radio" name="m_${rowId}" value="productor" disabled /></label>
            </td>
          </tr>`;
        }).join('');
        $$('.gfm-row-toggle', matrizBody).forEach(cb => {
          cb.addEventListener('change', () => {
            const name = `m_${cb.dataset.row}`;
            const radios = $$(`input[type="radio"][name="${name}"]`, matrizBody);
            if (cb.checked) {
              radios.forEach(r => {
                r.disabled = false;
              });
            } else {
              radios.forEach(r => {
                r.checked = false;
                r.disabled = true;
              });
            }
          });
        });
      } catch (e) {
        console.error(e);
        matrizBody.innerHTML = '';
      }
    }

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
        matrizBody.innerHTML = '';
        grupoCooperativaShow(false);
        selQuincena.value = '';
      } catch (err) {
        console.log(err);
        showAlert('error', `Error: ${err.message}`);
      }
    });

    function buildPayload() {
      if (!hidPersona.value) throw new Error('Seleccioná un productor.');

      const rep = normalizaSiNo(selRep.value);
      const linea = normalizaSiNo(selLinea.value);
      const zRestr = normalizaSiNo(selZonaRes.value);
      const corr = normalizaSiNo(selCorr.value);
      const agua = normalizaSiNo(selAgua.value);
      const cuart = normalizaSiNo(selCuart.value);
      const despegue = normalizaSiNo(selDespegue.value);
      const fpago = parseInt(selPago.value || '0', 10);
      const hectInt = parseInt(inpHect.value || '0', 10);
      const provincia = selProv.value.trim();
      const localidad = inpLoc.value.trim();
      const calle = inpCalle.value.trim();
      const numero = String(inpNum.value || '').trim();
      const rango = selQuincena.value;
      const patologiaId = parseInt(selMotivo.value || '0', 10);
      const coopIdReal = selCoop.value || null;

      if ([rep, linea, zRestr, corr, agua, cuart, despegue].some(v => !v)) throw new Error('Completá los campos de Sí/No.');
      if (!hectInt || hectInt < 0) throw new Error('Ingresá la cantidad de hectáreas (entero).');
      if (!fpago) throw new Error('Seleccioná la forma de pago.');
      if (!rango) throw new Error('Seleccioná la quincena.');
      if (!patologiaId) throw new Error('Seleccioná un motivo/patología.');
      if (!provincia || !localidad || !calle || !numero) throw new Error('Completá la dirección.');
      if (fpago === 6 && !coopIdReal) throw new Error('Seleccioná la cooperativa para la forma de pago 6.');

      const items = [];
      $$('.gfm-row-toggle:checked', matrizBody).forEach(cb => {
        const name = `m_${cb.dataset.row}`;
        const choice = $(`input[type="radio"][name="${name}"]:checked`, matrizBody);
        if (!choice) throw new Error('Indicá quién aporta cada producto seleccionado.');
        items.push({
          producto_id: parseInt(cb.dataset.productoId, 10),
          fuente: choice.value
        });
      });

      return {
        productor_id_real: hidPersona.value,
        representante: rep,
        linea_tension: linea,
        zona_restringida: zRestr,
        corriente_electrica: corr,
        agua_potable: agua,
        libre_obstaculos: cuart,
        area_despegue: despegue,
        superficie_ha: Number(hectInt.toFixed(2)),
        forma_pago_id: fpago,
        coop_descuento_id_real: coopIdReal,
        patologia_id: patologiaId,
        rango: rango,
        items: items,
        dir_provincia: provincia,
        dir_localidad: localidad,
        dir_calle: calle,
        dir_numero: numero,
        observaciones: inpObs.value || null
      };
    }

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

    function validateBeforeModal() {
      // Quitar clases previas
      ($$('.input-error') || []).forEach(el => el.classList.remove('input-error'));

      // Productor (hidden lleno)
      if (!hidPersona.value) {
        showAlert('error', 'Seleccioná un productor.');
        markInvalid(inpPersona);
        return false;
      }

      // Sí/No obligatorios
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

      // Hectáreas entero > 0
      const hectInt = parseInt(inpHect.value || '0', 10);
      if (!hectInt || hectInt <= 0) {
        showAlert('error', 'Ingresá la cantidad de hectáreas (entero mayor a 0).');
        markInvalid(inpHect);
        return false;
      }

      // Forma de pago
      const fpago = parseInt(selPago.value || '0', 10);
      if (!fpago) {
        showAlert('error', 'Seleccioná la forma de pago.');
        markInvalid(selPago);
        return false;
      }

      // Cooperativa requerida solo si forma de pago id=6
      if (fpago === 6) {
        if (!selCoop.value) {
          showAlert('error', 'Seleccioná la cooperativa para la forma de pago 6.');
          grupoCooperativaShow(true); // asegurar visible
          markInvalid(selCoop);
          return false;
        }
      }

      // Motivo / Patología (select)
      const patologiaId = parseInt(selMotivo.value || '0', 10);
      if (!patologiaId) {
        showAlert('error', 'Seleccioná un motivo/patología.');
        markInvalid(selMotivo);
        return false;
      }

      // Quincena (rango)
      if (!selQuincena.value) {
        showAlert('error', 'Seleccioná la quincena de visita.');
        markInvalid(selQuincena);
        return false;
      }

      // Dirección completa
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

      // Matriz: NO obligamos seleccionar productos; si selecciona alguno,
      // se validará la fuente en buildPayload() (ya implementado).
      return true;
    }


    // Inicialización
    (async function init() {
      try {
        await Promise.all([loadFormasPago(), loadRangos(), loadPatologias(), loadCooperativas()]);
        grupoCooperativaShow(false);
      } catch (e) {
        console.error(e);
      }
    })();
  })();
</script>