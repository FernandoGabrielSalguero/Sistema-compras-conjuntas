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

</style>

<script>

</script>