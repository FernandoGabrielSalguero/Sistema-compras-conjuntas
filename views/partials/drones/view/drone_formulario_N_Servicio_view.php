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
    <label for="form_nuevo_servicio_persona">Persona / Productor</label>
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

  <!-- Aeropuerto cercano -->
  <div class="input-group">
    <label for="form_nuevo_servicio_aeropuerto">¿Hay algún aeropuerto a menos de 3 km?</label>
    <div class="input-icon input-icon-airport">
      <select id="form_nuevo_servicio_aeropuerto" name="form_nuevo_servicio_aeropuerto" required>
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
             placeholder="Ej: 12.5" step="0.01" min="0" required />
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
  <div class="input-group">
    <label for="form_nuevo_servicio_cooperativa">Cooperativa responsable del pago</label>
    <div class="input-icon input-icon-coop typeahead-wrapper">
      <input
        type="text"
        id="form_nuevo_servicio_cooperativa"
        name="form_nuevo_servicio_cooperativa"
        placeholder="Buscar cooperativa…"
        autocomplete="off"
        class="js-typeahead"
        data-ta="coops"
        aria-autocomplete="list"
        aria-expanded="false"
        aria-controls="ta-list-coops"
        aria-activedescendant=""
        required />
      <ul id="ta-list-coops" class="typeahead-list" role="listbox" hidden></ul>
    </div>
  </div>

  <!-- Motivo (typeahead) -->
  <div class="input-group">
    <label for="form_nuevo_servicio_motivo">Motivo del servicio</label>
    <div class="input-icon input-icon-motivo typeahead-wrapper">
      <input
        type="text"
        id="form_nuevo_servicio_motivo"
        name="form_nuevo_servicio_motivo"
        placeholder="Buscar patología o motivo…"
        autocomplete="off"
        class="js-typeahead"
        data-ta="motivos"
        aria-autocomplete="list"
        aria-expanded="false"
        aria-controls="ta-list-motivos"
        aria-activedescendant=""
        required />
      <ul id="ta-list-motivos" class="typeahead-list" role="listbox" hidden></ul>
    </div>
  </div>

  <!-- Quincena -->
  <div class="input-group">
    <label for="form_nuevo_servicio_quincena">¿Quincena de visita?</label>
    <div class="input-icon input-icon-date">
      <select id="form_nuevo_servicio_quincena" name="form_nuevo_servicio_quincena" required>
        <option value="">Seleccionar</option>
        <option>Primera quincena de Octubre</option>
        <option>Segunda quincena de Octubre</option>
        <option>Primera quincena de Noviembre</option>
        <option>Segunda quincena de Noviembre</option>
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
        <option>Córdoba</option>
        <option>Santa Fe</option>
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
    <small class="char-count" data-for="form_nuevo_servicio_observaciones">Quedan 233 caracteres.</small>
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
    <div id="resumen-detalle" class="card" style="max-height:40vh; overflow:auto;"></div>
    <div class="form-buttons">
      <button class="btn btn-aceptar" id="btn-confirmar">Confirmar y guardar</button>
      <button class="btn btn-cancelar" id="btn-cerrar-modal">Cancelar</button>
    </div>
  </div>
</div>

<style>
  /* Matriz: columnas proporcionadas y sin overflow */
.gform-matrix {
  width: 100%;
  border: 1px solid #e9d7f7;
  border-radius: 12px;
  overflow: hidden;
  border-collapse: separate;
  border-spacing: 0;
  background: #fff;
  table-layout: fixed;             /* reparte el ancho y evita saltos */
}

/* cabeceras */
.gform-matrix thead th {
  background: #faf5ff;
  color: #111827;
  font-weight: 700;
  text-align: center;
  padding: 12px 16px;
  font-size: .95rem;
  border-bottom: 1px solid #efe7fb;
}
.gform-matrix thead th.gfm-empty { background:#fff; border-bottom-color:transparent; }

/* proporciones: 50% nombre + 25% + 25% */
.gform-matrix tbody th[scope="row"] { width: 50%; }
.gform-matrix thead th:not(.gfm-empty) { width: 25%; }

/* nombre de producto puede saltar de línea (¡reemplaza tu nowrap!) */
.gform-matrix tbody th[scope="row"] {
  text-align: left;
  font-weight: 600;
  color: #374151;
  padding: 14px 16px;
  white-space: normal;             /* <— importante: antes tenías nowrap */
  word-break: break-word;
}
.gform-matrix th, .gform-matrix td { overflow-wrap: anywhere; }

.gform-matrix td {
  text-align: center;
  padding: 12px 16px;
  border-bottom: 1px solid #f3f4f6;
}
.gform-matrix tbody tr:nth-child(even) { background: #fafafa; }
.gform-matrix tbody tr:last-child td,
.gform-matrix tbody tr:last-child th { border-bottom: 0; }

/* Responsive sin scroll horizontal */
@media (max-width: 560px) {
  .gform-matrix thead { display: none; }
  .gform-matrix, .gform-matrix tbody, .gform-matrix tr { display: block; width: 100%; }
  .gform-matrix tr {
    border: 1px solid #efe7fb;
    border-radius: 12px;
    margin: 10px 0;
    background: #fff;
  }
  .gform-matrix tbody th[scope="row"] {
    display: block;
    width: 100%;
    background: #faf5ff;
    border-bottom: 1px solid #efe7fb;
    border-radius: 12px 12px 0 0;
  }
  .gform-matrix td {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 0;
  }
  .gform-matrix td::before {
    content: attr(data-col);        /* “SVE” / “Productor” */
    font-weight: 600;
    color: #6b21a8;
    margin-right: 12px;
  }
  .gfm-radio { justify-content: flex-end; }
}

/* aseguramos que ocupe 4 columnas del grid */
.full-span { grid-column: 1 / -1; }
</style>

<script>

</script>