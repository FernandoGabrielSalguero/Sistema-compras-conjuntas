<?php

?>
<script defer src="https://www.fernandosalguero.com/cdn/assets/javascript/framework.js"></script>
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
              <option value="">Cargando...</option>
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
/**
 * Consola de diagnóstico inicial solicitada:
 * - formas_pago
 * - patologias
 * - cooperativas
 * - productos_por_patologia (usa la patología seleccionada o la primera disponible)
 *
 * Supuesto: el controlador está accesible en "controller/drone_formulario_N_Servicio_controller.php"
 * Ajustá API_URL si tu ruta es otra.
 */
(function () {
  'use strict';

  const API_URL = 'controller/drone_formulario_N_Servicio_controller.php';

  const logGroup = (title, payload) => {
    try {
      console.group(`API ▶ ${title}`);
      console.log(payload);
      console.groupEnd();
    } catch (e) {
      // Si la consola no soporta group en algún navegador legacy
      console.log(`API ▶ ${title}`, payload);
    }
  };

  const apiGet = async (action, params = {}) => {
    const qs = new URLSearchParams({ action, ...params });
    const res = await fetch(`${API_URL}?${qs.toString()}`, {
      headers: { 'Accept': 'application/json' },
      credentials: 'same-origin',
      cache: 'no-store'
    });
    let json;
    try {
      json = await res.json();
    } catch (e) {
      json = { ok: false, error: 'Respuesta no JSON', status: res.status };
    }
    logGroup(action, json);
    return json;
  };

  const initConsoleDiagnostics = async () => {
    // 1) formas_pago
    const fp = apiGet('formas_pago');

    // 2) patologias (usaremos el primer id disponible para disparar productos_por_patologia)
    const patologiasPromise = apiGet('patologias');

    // 3) cooperativas
    const coop = apiGet('cooperativas');

    // 4) productos_por_patologia: al cargar patologías, usamos la primera; si no hay, probamos con el valor del select (si existe)
    try {
      const patologias = await patologiasPromise;
      let patologiaId = 0;

      // Intentamos con el select si ya existe un valor válido
      const sel = document.getElementById('patologia_id');
      if (sel && sel.value && !isNaN(parseInt(sel.value, 10))) {
        patologiaId = parseInt(sel.value, 10);
      }

      // Si no había selección previa, tomamos el primer id de la API
      if ((!patologiaId || patologiaId <= 0) && patologias && patologias.ok && Array.isArray(patologias.data) && patologias.data.length) {
        patologiaId = parseInt(patologias.data[0].id, 10) || 0;
      }

      // Si conseguimos un id válido, disparamos productos_por_patologia
      if (patologiaId > 0) {
        await apiGet('productos_por_patologia', { patologia_id: patologiaId });
      } else {
        // Último recurso: log de advertencia y consulta con id=1 (puede no existir)
        console.warn('No se pudo determinar patologia_id a partir del select ni de la API. Se probará con patologia_id=1');
        await apiGet('productos_por_patologia', { patologia_id: 1 });
      }
    } catch (e) {
      console.error('Error en diagnóstico de productos_por_patologia:', e);
    }

    // Listener: cuando el usuario cambie la patología, volver a loggear productos_por_patologia
    const patSel = document.getElementById('patologia_id');
    if (patSel) {
      patSel.addEventListener('change', async () => {
        const id = parseInt(patSel.value || '0', 10);
        if (id > 0) {
          await apiGet('productos_por_patologia', { patologia_id: id });
        }
      }, { passive: true });
    }
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initConsoleDiagnostics, { once: true });
  } else {
    initConsoleDiagnostics();
  }
})();
</script>
