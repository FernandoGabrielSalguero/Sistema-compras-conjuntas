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
          <label for="form_nuevo_servicio_lineas_tension">¿Hay líneas de media/alta tensión a menos de 30 metros?</label>
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

        <!-- ===== Productos disponibles (chips) ===== -->
        <div class="card full-span costos-full" id="card-productos" aria-live="polite" hidden>
          <h2>Productos disponibles para tratar patología</h2>
          <div id="productos-chips" class="chips-grid" role="group" aria-label="Productos sugeridos"></div>

          <!-- Chip “Otro” + input -->
          <div class="chips-custom">
            <label class="chip">
              <input type="checkbox" id="chip-custom" />
              <span class="chip-box">
                <span class="chip-name">Otro</span>
              </span>
            </label>
            <div id="custom-wrapper" class="custom-input" hidden>
              <label for="custom-producto" class="sr-only">Nombre de producto</label>
              <input type="text" id="custom-producto" placeholder="Nombre del producto..." />
            </div>
          </div>
        </div>

        <!-- ===== Tarjeta: Costo del servicio ===== -->
        <div class="card full-span costos-full" id="card-costos" aria-live="polite">
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

      <!-- Correos a enviar en el modal -->
      <div id="correos-a-enviar" class="card" style="background:#f9fafb;margin-top:.5rem;">
        <h4 style="margin:.5rem 0 .25rem 0;">Correos a enviar</h4>
        <div><strong>Productor:</strong> —</div>
        <div><strong>Cooperativa:</strong> —</div>
      </div>
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

  /* Forzar que la tarjeta de costos ocupe todo el ancho */
  #card-costos.costos-full {
    grid-column: 1 / -1 !important;
    width: 100% !important;
    flex: 0 0 100% !important;
    display: block;
    background-color: aliceblue;
  }

  /* ===== Productos (chips) ===== */
  .chips-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 6px;
  }

  .chip {
    flex: 0 1 auto;
  }

  .chip input[type="checkbox"] {
    position: absolute;
    opacity: 0;
    pointer-events: none;
  }

  .chip-box {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .5rem;
    padding: 8px 10px;
    border: 1px solid #e5e7eb;
    border-radius: 999px;
    background: #fff;
    cursor: pointer;
    user-select: none;
    transition: box-shadow .15s, border-color .15s, background .15s;
  }

  .chip-name {
    font-weight: 600;
    color: #111827;
  }

  .chip-cost {
    color: #6b7280;
    font-size: .9rem;
  }

  /* Estado seleccionado: VERDE */
  .chip input[type="checkbox"]:checked+.chip-box {
    background: #ecfdf5;
    /* verde muy claro */
    border-color: #10b981;
    /* emerald-500 */
    box-shadow: 0 0 0 2px rgba(16, 185, 129, .20) inset;
  }

  .chip input[type="checkbox"]:checked+.chip-box .chip-name {
    color: #065f46;
  }

  /* emerald-800 */
  .chip input[type="checkbox"]:checked+.chip-box .chip-cost {
    color: #047857;
  }

  /* emerald-700 */

  /* Accesibilidad: focus ring en verde */
  .chip input[type="checkbox"]:focus+.chip-box {
    outline: 2px solid #10b981;
    outline-offset: 2px;
  }

  /* Hover en estado no seleccionado */
  .chip-box:hover {
    border-color: #d1d5db;
  }


  .chips-custom {
    margin-top: 12px;
  }

  .custom-input {
    margin-top: 8px;
  }

  .custom-input input {
    width: 100%;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 8px 10px;
    outline: none;
  }

  .custom-input input:focus {
    border-color: var(--primary-color, #6d28d9);
    box-shadow: 0 0 0 3px rgba(109, 40, 217, .1);
  }

  /* accesibilidad invisible */
  .sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
  }

  #card-productos {
    background-color: aliceblue;
    grid-column: 1 / -1 !important;
    width: 100% !important;
    flex: 0 0 100% !important;
  }
</style>


<script>
  (function() {
    const CTRL_URL = '../partials/drones/controller/drone_formulario_N_Servicio_controller.php';

    // ===== Helpers =====
    const $ = (sel, ctx = document) => ctx.querySelector(sel);
    const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));
    const debounce = (fn, ms = 250) => {
      let t;
      return (...a) => {
        clearTimeout(t);
        t = setTimeout(() => fn(...a), ms);
      };
    };

    // ===== Correos (productor/cooperativa) =====
    async function getCorreoByIdReal(idReal) {
      if (!idReal) return null;
      try {
        const data = await fetchJson(`${CTRL_URL}?action=correo_por_id_real&id_real=${encodeURIComponent(idReal)}`);
        // El controller retorna { ok:true, data:{ correo: string|null } }
        const correo = (data && typeof data === 'object') ? data.correo : null;
        return (correo && String(correo).trim() !== '') ? String(correo).trim() : null;
      } catch (e) {
        console.error('[CORREO] Error obteniendo correo de', idReal, e);
        return null;
      }
    }

        async function refreshCorreos() {
      const productorId = (hidPersona.value || '').trim();
      const coopId = (selCoop.value || '').trim();

      const [correoProd, correoCoop] = await Promise.all([
        getCorreoByIdReal(productorId),
        getCorreoByIdReal(coopId)
      ]);

      // Logs requeridos
      console.log('Correo productor:', correoProd ?? 'null');
      console.log('Correo cooperativa:', correoCoop ?? 'null');

      // Renderizar en el modal
      if (modalCorreos) {
        const valProd = correoProd ?? 'null';
        const valCoop = correoCoop ?? 'null';
        modalCorreos.innerHTML = `
          <h4 style="margin:.5rem 0 .25rem 0;">Correos a enviar</h4>
          <div><strong>Productor:</strong> ${valProd}</div>
          <div><strong>Cooperativa:</strong> ${valCoop}</div>
        `;
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

    // ===== Refs UI =====
    const form = $('#form-solicitud');
    const btnSolicitar = $('#btn-solicitar');
    const modal = $('#modal-resumen');
    const btnConfirmar = $('#btn-confirmar');
    const btnCerrarModal = $('#btn-cerrar-modal');

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

    const selMotivoHidden = $('#form_nuevo_servicio_motivo');
    const selMotivoHiddenIds = $('#form_nuevo_servicio_motivo_ids');
    const btnMotivoToggle = $('#form_nuevo_servicio_motivo_toggle');
    const ulMotivoList = $('#form_nuevo_servicio_motivo_list');
    const selQuincena = $('#form_nuevo_servicio_quincena');

    const selProv = $('#form_nuevo_servicio_provincia');
    const inpLoc = $('#form_nuevo_servicio_localidad');
    const inpCalle = $('#form_nuevo_servicio_calle');
    const inpNum = $('#form_nuevo_servicio_numero');
    const inpObs = $('#form_nuevo_servicio_observaciones');
    const modalCorreos = $('#correos-a-enviar');

    // ===== Estado =====
    let costoBaseHa = 0;
    let monedaBase = 'Pesos';

    // ===== Render productos sugeridos =====
    function fmtMon(n, curr) {
      try {
        return new Intl.NumberFormat('es-AR', {
          style: 'currency',
          currency: curr,
          minimumFractionDigits: 2
        }).format(n);
      } catch {
        return '$ ' + Number(n || 0).toFixed(2);
      }
    }

    // ===== Typeahead personas =====
    (function initTypeahead() {
      const render = (items) => {
        listPersona.innerHTML = '';
        items.forEach((it, i) => {
          const li = document.createElement('li');
          li.role = 'option';
          li.tabIndex = -1;
          li.className = 'ta-item';
          li.textContent = it.label;
          li.dataset.value = it.value;
          li.addEventListener('mousedown', (e) => {
            e.preventDefault();
            choose(i);
          });
          listPersona.appendChild(li);
        });
        listPersona.hidden = items.length === 0;
        inpPersona.setAttribute('aria-expanded', String(!listPersona.hidden));
      };
      let current = [];
      const choose = (idx) => {
        const it = current[idx];
        if (!it) return;
        inpPersona.value = it.label;
        hidPersona.value = it.value;
        listPersona.hidden = true;
        inpPersona.setAttribute('aria-expanded', 'false');
        refreshCorreos();
      };
      const search = debounce(async () => {
        const q = inpPersona.value.trim();
        if (q.length < 2) {
          listPersona.hidden = true;
          current = [];
          hidPersona.value = '';
          return;
        }
        try {
          const coop = selCoop.value ? `&coop_id=${encodeURIComponent(selCoop.value)}` : '';
          const data = await fetchJson(`${CTRL_URL}?action=buscar_usuarios&q=${encodeURIComponent(q)}${coop}`);
          current = data.map(u => ({
            label: u.usuario,
            value: u.id_real
          }));
          render(current);
        } catch (e) {
          console.error(e);
        }
      }, 200);
      inpPersona.addEventListener('input', search);
      inpPersona.addEventListener('keydown', (e) => {
        if (listPersona.hidden) return;
        if (e.key === 'Escape') {
          listPersona.hidden = true;
          inpPersona.setAttribute('aria-expanded', 'false');
        }
      });
      document.addEventListener('click', (e) => {
        if (!listPersona.contains(e.target) && e.target !== inpPersona) {
          listPersona.hidden = true;
          inpPersona.setAttribute('aria-expanded', 'false');
        }
      });
    })();

    // ===== Cargas select =====
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
      ulMotivoList.innerHTML = '';
      data.forEach(p => {
        const li = document.createElement('li');
        li.className = 'selectlike-item';
        li.role = 'option';
        li.dataset.id = String(p.id);
        li.innerHTML = `<label class="selectlike-label" style="display:flex;align-items:center;gap:.5rem;width:100%">
        <input type="checkbox" value="${p.id}" aria-label="${p.nombre}"><span>${p.nombre}</span>
      </label>`;
        ulMotivoList.appendChild(li);
      });
      setSelectedMotivos([]);
      updateMotivoButton();
    }

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
    selPago.addEventListener('change', () => grupoCooperativaShow(parseInt(selPago.value || '0', 10) === 6));
    // Actualiza correos cuando cambia la cooperativa seleccionada
    selCoop.addEventListener('change', refreshCorreos);

    // ===== Productos por patología (chips) =====
    const ProductosState = {
      seleccionados: new Set(), // ids seleccionados
      customChecked: false,
      customNombre: '',
      catalog: new Map() // id -> { id, nombre, costo_hectarea }
    };

    async function loadProductosChips(patologiaIds = []) {
      const card = document.getElementById('card-productos');
      const grid = document.getElementById('productos-chips');
      const chipCustom = document.getElementById('chip-custom');
      const customWrap = document.getElementById('custom-wrapper');
      const customInp = document.getElementById('custom-producto');

      // reset visual/estado
      grid.innerHTML = '';
      ProductosState.seleccionados.clear();
      ProductosState.catalog = new Map();

      if (!Array.isArray(patologiaIds) || patologiaIds.length === 0) {
        if (card) card.hidden = true;
        if (chipCustom) chipCustom.checked = false;
        if (customWrap) customWrap.hidden = true;
        if (customInp) customInp.value = '';
        ProductosState.customChecked = false;
        ProductosState.customNombre = '';
        recalcCostos();
        return;
      }

      // fetch y consolidado
      const results = await Promise.all(
        patologiaIds.map(async (id) => {
          try {
            const data = await fetchJson(`${CTRL_URL}?action=productos_por_patologia&patologia_id=${encodeURIComponent(id)}`);
            return Array.isArray(data) ? data : [];
          } catch {
            return [];
          }
        })
      );

      const dict = new Map();
      results.flat().forEach(p => {
        const pid = Number(p.id);
        if (!dict.has(pid)) {
          const item = {
            id: pid,
            nombre: String(p.nombre ?? ''),
            detalle: String(p.detalle ?? ''),
            costo_hectarea: Number(p.costo_hectarea ?? 0)
          };
          dict.set(pid, item);
        }
      });

      const productos = Array.from(dict.values())
        .sort((a, b) => a.nombre.localeCompare(b.nombre, 'es'));

      // guardar catálogo
      productos.forEach(p => ProductosState.catalog.set(p.id, p));

      // Render chips
      const frag = document.createDocumentFragment();
      productos.forEach(p => {
        const label = document.createElement('label');
        label.className = 'chip';
        label.innerHTML = `
          <input type="checkbox" value="${p.id}">
          <span class="chip-box">
            <span class="chip-name">${p.nombre}</span>
            <span class="chip-cost">${fmtMon(p.costo_hectarea, (monedaBase==='USD'?'USD':'ARS'))}/ha</span>
          </span>
        `;
        frag.appendChild(label);
      });
      grid.appendChild(frag);

      // Delegación: selección/deselección de chips → recálculo
      grid.onchange = (e) => {
        const cb = e.target;
        if (cb && cb.type === 'checkbox' && cb.value) {
          const id = parseInt(cb.value, 10);
          if (cb.checked) ProductosState.seleccionados.add(id);
          else ProductosState.seleccionados.delete(id);
          recalcCostos();
        }
      };

      // Custom “Otro”
      chipCustom.onchange = () => {
        ProductosState.customChecked = chipCustom.checked;
        customWrap.hidden = !chipCustom.checked;
        if (!chipCustom.checked) {
          ProductosState.customNombre = '';
          customInp.value = '';
        }
        recalcCostos();
      };
      customInp.oninput = () => {
        ProductosState.customNombre = customInp.value.trim();
      };

      // Mostrar tarjeta
      if (card) card.hidden = productos.length === 0;

      console.log('[PRODUCTOS][ARRAY]', productos);
      recalcCostos();
    }

    // ===== Costos =====
    const num = (n) => Number.isFinite(Number(n)) ? Number(n) : 0;
    const fmt = (n) => fmtMon(n, (monedaBase === 'USD' ? 'USD' : 'ARS'));

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

      // ===== Productos SVE seleccionados =====
      let productosTotal = 0;
      if (ProductosState && ProductosState.seleccionados && ProductosState.catalog) {
        Array.from(ProductosState.seleccionados).forEach(id => {
          const p = ProductosState.catalog.get(id);
          if (!p) return;
          const costoHaProd = num(p.costo_hectarea || 0);
          const totalProd = costoHaProd * ha;
          productosTotal += totalProd;

          rows.push(`<tr><th>Producto</th><td><span class="badge-prod">${p.nombre}</span> <span class="costos-muted">(Aporta SVE)</span></td><td></td></tr>`);
          rows.push(`<tr><th>Precio por hectárea del producto</th><td></td><td class="costos-right">${fmt(costoHaProd)}</td></tr>`);
          rows.push(`<tr><th>Costo total del producto</th><td></td><td class="costos-right">${fmt(totalProd)}</td></tr>`);
        });
      }

      // ===== Producto “Otro” (custom, costo $0/ha) =====
      if (ProductosState?.customChecked) {
        const name = (ProductosState.customNombre || 'Producto del productor').trim();
        rows.push(`<tr><th>Producto</th><td><span class="badge-prod">${name}</span> <span class="costos-muted">(Aporta productor)</span></td><td></td></tr>`);
        rows.push(`<tr><th>Precio por hectárea del producto</th><td></td><td class="costos-right">${fmt(0)}</td></tr>`);
        rows.push(`<tr><th>Costo total del producto</th><td></td><td class="costos-right">${fmt(0)}</td></tr>`);
      }

      const precioFinal = totalBase + productosTotal;
      tbody.innerHTML = rows.join('');
      $('#costos-precio-final').textContent = fmt(precioFinal);
    }



    // Hectáreas -> recálculo
    inpHect.addEventListener('input', debounce(recalcCostos, 150));

    // ===== UI motivos (único set) =====
    function getSelectedMotivos() {
      const csv = (selMotivoHiddenIds.value || '').trim();
      if (!csv) return [];
      return csv.split(',').map(s => parseInt(s, 10)).filter(n => n > 0);
    }

    async function setSelectedMotivos(arr) {
      const uniq = Array.from(new Set(arr.filter(n => Number.isInteger(n) && n > 0)));
      selMotivoHiddenIds.value = uniq.join(',');
      selMotivoHidden.value = uniq.length ? String(uniq[0]) : '';
      ulMotivoList.querySelectorAll('input[type="checkbox"]')
        .forEach(cb => cb.checked = uniq.includes(parseInt(cb.value, 10)));
      updateMotivoButton();
      console.log('[MOTIVOS] setSelectedMotivos ->', uniq);

      // Cargar y mostrar productos como chips
      await loadProductosChips(uniq);

      // Recalcular costos base
      recalcCostos();

      // Cerrar dropdown si está abierto
      if (!ulMotivoList.hasAttribute('hidden')) {
        ulMotivoList.setAttribute('hidden', '');
        btnMotivoToggle.setAttribute('aria-expanded', 'false');
      }
    }

    function updateMotivoButton() {
      const ids = getSelectedMotivos();
      const names = [];
      ids.forEach(id => {
        const span = ulMotivoList.querySelector(`li[data-id="${id}"] span`);
        if (span) names.push(span.textContent || String(id));
      });
      btnMotivoToggle.textContent = ids.length ? (names.slice(0, 2).join(', ') + (names.length > 2 ? ` (+${names.length-2})` : '')) : 'Seleccionar';
      btnMotivoToggle.setAttribute('aria-expanded', ids.length ? 'true' : 'false');
    }
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
    document.addEventListener('click', (e) => {
      if (!ulMotivoList.contains(e.target) && e.target !== btnMotivoToggle) {
        if (!ulMotivoList.hasAttribute('hidden')) {
          ulMotivoList.setAttribute('hidden', '');
          btnMotivoToggle.setAttribute('aria-expanded', 'false');
        }
      }
    });

    function collectAndSetMotivos() {
      const checked = Array.from(ulMotivoList.querySelectorAll('input[type="checkbox"]:checked'))
        .map(cb => parseInt(cb.value, 10))
        .filter(n => Number.isInteger(n) && n > 0);
      console.log('[MOTIVOS] Checkboxes tildados:', checked);
      setSelectedMotivos(checked);
    }

    ulMotivoList.addEventListener('change', collectAndSetMotivos); // solo una vía → evita duplicados

    // ===== Validación previa al modal =====
    function markInvalid(el) {
      try {
        el.focus();
      } catch {}
      el.classList.add('input-error');
      setTimeout(() => el.classList.remove('input-error'), 1500);
    }

    function getSiNoValue(sel) {
      const v = (sel?.value || '').trim().toLowerCase();
      return (v === 'si' || v === 'sí' || v === 'no') ? v : '';
    }

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
      if (fpago === 6 && !selCoop.value) {
        showAlert('error', 'Seleccioná la cooperativa para la forma de pago 6.');
        grupoCooperativaShow(true);
        markInvalid(selCoop);
        return false;
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

    // ===== Confirmación y POST =====
    btnSolicitar.addEventListener('click', (e) => {
      e.preventDefault();
      if (!validateBeforeModal()) return;
      // Actualizar correos antes de abrir el modal
      refreshCorreos();
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

    btnConfirmar.addEventListener('click', async (e) => {
      e.preventDefault();
      console.log(buildPayload())
      try {
        const payload = buildPayload(); // se asume función existente en tu base
        const data = await postJson(CTRL_URL, payload);
        const newId = (data && typeof data.id !== 'undefined') ? String(data.id) : '—';
                showAlert('success', `Solicitud creada. ID: ${newId}`);
        closeModal();
        form.reset();
        grupoCooperativaShow(false);
        selQuincena.value = '';
        setSelectedMotivos([]);
        recalcCostos();
        // Reset visual del bloque de correos en el modal
        if (modalCorreos) {
          modalCorreos.innerHTML = `
            <h4 style="margin:.5rem 0 .25rem 0;">Correos a enviar</h4>
            <div><strong>Productor:</strong> —</div>
            <div><strong>Cooperativa:</strong> —</div>
          `;
        }

      } catch (err) {
        console.log(err);
        showAlert('error', `Error: ${err.message}`);
      }
    });

        // === Build payload para POST (con chips de productos) ===
    function buildPayload() {
      const motivos = getSelectedMotivos();
      const patologiaId = motivos.length ? motivos[0] : null;

      // Armar ítems de productos
      const items = [];
      // Productos aportados por SVE (chips tildados)
      Array.from(ProductosState.seleccionados).forEach(id => {
        items.push({ producto_id: id, fuente: 'sve' });
      });
      // Producto "Otro" (aporta productor)
      if (ProductosState.customChecked && (ProductosState.customNombre || '').trim()) {
        items.push({
          producto_id: 0,
          fuente: 'productor',
          nombre_producto_custom: ProductosState.customNombre.trim()
        });
      }

      return {
        productor_id_real: hidPersona.value || null,
        representante:     getSiNoValue(selRep),
        linea_tension:     getSiNoValue(selLinea),
        zona_restringida:  getSiNoValue(selZonaRes),
        corriente_electrica: getSiNoValue(selCorr),
        agua_potable:      getSiNoValue(selAgua),
        libre_obstaculos:  getSiNoValue(selCuart),
        area_despegue:     getSiNoValue(selDespegue),

        superficie_ha: parseFloat(inpHect.value || '0'),
        forma_pago_id:  parseInt(selPago.value || '0', 10),
        // Enviamos el id_real; el controller ya lo mapea al campo que use tu DB
        coop_descuento_id_real: selCoop.value || null,

        patologia_id: patologiaId,
        rango:        selQuincena.value || '',

        dir_provincia: selProv.value || '',
        dir_localidad: inpLoc.value || '',
        dir_calle:     inpCalle.value || '',
        dir_numero:    inpNum.value || '',
        observaciones: inpObs.value || '',

        items // << clave: acá viajan los productos
      };
    }


    // ===== Costo base/Inicialización =====
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

    (async function init() {
      await Promise.all([loadFormasPago(), loadRangos(), loadPatologias(), loadCooperativas(), loadCostoBaseHa()]);
      grupoCooperativaShow(false);
      await loadProductosChips([]);
      recalcCostos();
      // Inicializa visualización de correos (puede mostrar null/null hasta seleccionar)
      await refreshCorreos();
    })();

  })();

</script>