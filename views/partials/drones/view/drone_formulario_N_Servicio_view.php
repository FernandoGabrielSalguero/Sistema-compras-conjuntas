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

        <!-- Nombre del productor -->
        <div class="input-group">
          <label for="nombre-buscador">Buscar persona</label>
          <div class="input-icon input-icon-name typeahead-wrapper">
            <input
              type="text"
              id="nombre-buscador"
              name="nombre_buscador"
              placeholder="Empezá a escribir un nombre…"
              autocomplete="off"
              aria-autocomplete="list"
              aria-expanded="false"
              aria-controls="ta-list-nombres"
              aria-activedescendant=""
              required />
            <!-- Sugerencias -->
            <ul id="ta-list-nombres" class="typeahead-list" role="listbox" hidden></ul>
          </div>
          <!-- <small class="gform-helper">Escribí y elegí una opción con Enter o clic.</small> -->
        </div>

        <!-- Representante  -->
        <div class="input-group">
          <label for="nombre">¿Contamos con un representante en la finca?</label>
          <div class="input-icon input-icon-name">
            <select id="provincia" name="provincia" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>

        <!-- Tension  -->
        <div class="input-group">
          <label for="nombre">¿Hay líneas de media y alta tensión a menos de 30km?</label>
          <div class="input-icon input-icon-name">
            <select id="provincia" name="provincia" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>

        <!-- Aeropuerto  -->
        <div class="input-group">
          <label for="nombre">¿Hay algún aeropuerto a menos de 3 km?</label>
          <div class="input-icon input-icon-name">
            <select id="provincia" name="provincia" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>


        <!-- Hay corriente electrica  -->
        <div class="input-group">
          <label for="nombre">¿Disponibilidad de corriente electrica?</label>
          <div class="input-icon input-icon-name">
            <select id="provincia" name="provincia" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>

        <!-- Hay agua potable?  -->
        <div class="input-group">
          <label for="nombre">¿Hay agua potable?</label>
          <div class="input-icon input-icon-name">
            <select id="provincia" name="provincia" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>

        <!-- Cuarteles libre de obstaculos  -->
        <div class="input-group">
          <label for="nombre">¿Los cuartiles están libres de obstaculos?</label>
          <div class="input-icon input-icon-name">
            <select id="provincia" name="provincia" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>

        <!-- Area de despegue apropiada  -->
        <div class="input-group">
          <label for="nombre">¿Hay un área de despegue apropiada?</label>
          <div class="input-icon input-icon-name">
            <select id="provincia" name="provincia" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>

        <!-- Cuantas hectareas -->
        <div class="input-group">
          <label for="nombre">¿Cuantas hectareas?</label>
          <div class="input-icon input-icon-name">
            <input type="text" id="nombre" name="nombre" placeholder="Juan Pérez" required />
          </div>
        </div>


        <!-- Metodos de pago  -->
        <div class="input-group">
          <label for="nombre">¿Como va a pagar?</label>
          <div class="input-icon input-icon-name">
            <select id="provincia" name="provincia" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>

        <!-- Cooperativas -->
        <div class="input-group">
          <label for="nombre-buscador">Selecciona una cooperativa responsable del pago</label>
          <div class="input-icon input-icon-name typeahead-wrapper">
            <input
              type="text"
              id="nombre-buscador"
              name="nombre_buscador"
              placeholder="Empezá a escribir un nombre…"
              autocomplete="off"
              aria-autocomplete="list"
              aria-expanded="false"
              aria-controls="ta-list-nombres"
              aria-activedescendant=""
              required />
            <!-- Sugerencias -->
            <ul id="ta-list-nombres" class="typeahead-list" role="listbox" hidden></ul>
          </div>
          <!-- <small class="gform-helper">Escribí y elegí una opción con Enter o clic.</small> -->
        </div>

        <!-- Patologias -->
        <div class="input-group">
          <label for="nombre-buscador">Motivo del servicio</label>
          <div class="input-icon input-icon-name typeahead-wrapper">
            <input
              type="text"
              id="nombre-buscador"
              name="nombre_buscador"
              placeholder="Empezá a escribir un nombre…"
              autocomplete="off"
              aria-autocomplete="list"
              aria-expanded="false"
              aria-controls="ta-list-nombres"
              aria-activedescendant=""
              required />
            <!-- Sugerencias -->
            <ul id="ta-list-nombres" class="typeahead-list" role="listbox" hidden></ul>
          </div>
          <!-- <small class="gform-helper">Escribí y elegí una opción con Enter o clic.</small> -->
        </div>

        <!-- Quincena de visita -->
        <div class="input-group">
          <label for="nombre">¿Quincena de visita?</label>
          <div class="input-icon input-icon-name">
            <select id="provincia" name="provincia" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>




        <!-- Provincia -->
        <div class="input-group">
          <label for="nombre">¿Cuantas hectareas?</label>
          <div class="input-icon input-icon-name">
            <input type="text" id="nombre" name="nombre" placeholder="Juan Pérez" required />
          </div>
        </div>



        <!-- Localidad -->
        <div class="input-group">
          <label for="nombre">¿Cuantas hectareas?</label>
          <div class="input-icon input-icon-name">
            <input type="text" id="nombre" name="nombre" placeholder="Juan Pérez" required />
          </div>
        </div>



        <!-- Calle -->
        <div class="input-group">
          <label for="nombre">¿Cuantas hectareas?</label>
          <div class="input-icon input-icon-name">
            <input type="text" id="nombre" name="nombre" placeholder="Juan Pérez" required />
          </div>
        </div>



        <!-- numero -->
        <div class="input-group">
          <label for="nombre">¿Cuantas hectareas?</label>
          <div class="input-icon input-icon-name">
            <input type="text" id="nombre" name="nombre" placeholder="Juan Pérez" required />
          </div>
        </div>


        <!-- Observaciones -->
        <div class="input-group">
          <label for="observaciones">Observaciones</label>
          <div class="input-icon input-icon-comment">
            <textarea id="observaciones" name="observaciones" maxlength="233" rows="3"
              placeholder="Escribí un comentario..."></textarea>
          </div>
          <small class="char-count" data-for="observaciones">Quedan 233 caracteres.</small>
        </div>

        <div class="card">
          <h2>Matriz de prueba</h2>

          <form id="form-matriz" class="gform-grid cols-1" novalidate>
            <div class="gform-question" data-required="true">
              <div class="gform-legend">
                Matriz de prueba <span class="gform-required">*</span>
              </div>
              <div class="gform-helper">
                Primero seleccioná el producto (checkbox). Solo entonces podés elegir una opción en la
                fila.
              </div>

              <table class="gform-matrix" role="table" aria-label="Matriz de prueba">
                <thead>
                  <tr>
                    <th scope="col" class="gfm-empty"></th>
                    <th scope="col">SVE</th>
                    <th scope="col">Productor</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Row 1 -->
                  <tr>
                    <th scope="row">
                      <label class="gfm-prod">
                        <input type="checkbox" class="gfm-row-toggle" name="m_sel[]"
                          value="row1" data-row="row1" />
                        <span>Producto 1</span>
                      </label>
                    </th>
                    <td>
                      <label class="gfm-radio">
                        <input type="radio" name="m_row1" value="sve" disabled />
                      </label>
                    </td>
                    <td>
                      <label class="gfm-radio">
                        <input type="radio" name="m_row1" value="productor" disabled />
                      </label>
                    </td>
                  </tr>

                  <!-- Row 2 -->
                  <tr>
                    <th scope="row">
                      <label class="gfm-prod">
                        <input type="checkbox" class="gfm-row-toggle" name="m_sel[]"
                          value="row2" data-row="row2" />
                        <span>Producto 2</span>
                      </label>
                    </th>
                    <td>
                      <label class="gfm-radio">
                        <input type="radio" name="m_row2" value="sve" disabled />
                      </label>
                    </td>
                    <td>
                      <label class="gfm-radio">
                        <input type="radio" name="m_row2" value="productor" disabled />
                      </label>
                    </td>
                  </tr>
                </tbody>
              </table>

              <div class="gform-error">Seleccioná al menos un producto y, para cada producto seleccionado,
                elegí una opción.</div>
            </div>

            <div class="gform-actions">
              <button type="button" class="gform-btn">Atrás</button>
              <button type="submit" class="gform-btn gform-primary">Enviar</button>
            </div>
          </form>
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
    <div id="resumen-detalle" class="card" style="max-height:40vh; overflow:auto;"></div>
    <div class="form-buttons">
      <button class="btn btn-aceptar" id="btn-confirmar">Confirmar y guardar</button>
      <button class="btn btn-cancelar" id="btn-cerrar-modal">Cancelar</button>
    </div>
  </div>
</div>

<style>
/* ===== Matriz (Google Forms-like) ===== */
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

/* Producto (checkbox al lado del nombre) */
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

/* Radio centrado y “material-like” */
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
</style>

<script>
// ==== Matriz: toggle por producto + validación ====
document.addEventListener('DOMContentLoaded', () => {
  const formMatriz = document.getElementById('form-matriz');
  if (!formMatriz) return;

  const toggles = formMatriz.querySelectorAll('.gfm-row-toggle');

  function setRowState(rowId, checked) {
    const radios = formMatriz.querySelectorAll(`input[name="m_${rowId}"]`);
    radios.forEach(r => {
      r.disabled = !checked;
      if (!checked) r.checked = false;
    });
  }

  // Estado inicial + listeners
  toggles.forEach(t => {
    const rowId = t.dataset.row;
    setRowState(rowId, t.checked);
    t.addEventListener('change', (e) => setRowState(rowId, e.target.checked));
  });

  // Validación al enviar
  formMatriz.addEventListener('submit', (e) => {
    const q = formMatriz.querySelector('.gform-question');
    let ok = true;

    const anyProduct = [...toggles].some(t => t.checked);
    if (!anyProduct) ok = false;

    toggles.forEach(t => {
      if (t.checked) {
        const rowId = t.dataset.row;
        const selected = formMatriz.querySelector(`input[name="m_${rowId}"]:checked`);
        if (!selected) ok = false;
      }
    });

    q.classList.toggle('is-error', !ok);
    if (!ok) {
      e.preventDefault();
      q.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  });
});
</script>