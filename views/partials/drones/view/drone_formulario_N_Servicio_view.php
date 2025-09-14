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

        <!-- Nombre del productor -->
        <div class="input-group">
          <label for="nombre">Nombre del productor</label>
          <div class="input-icon input-icon-name">
            <input type="text" id="nombre" name="nombre" placeholder="Juan Pérez" autocomplete="off" required />
          </div>
        </div>

        <!-- Representante en finca -->
        <div class="input-group">
          <label for="representante">¿Podremos contar con un representante en la finca? *</label>
          <div class="input-icon">
            <select id="representante" name="representante" required aria-required="true">
              <option value="">Seleccionar</option>
              <option value="si">Sí</option>
              <option value="no">No</option>
            </select>
          </div>
        </div>

        <!-- Línea de tensión -->
        <div class="input-group">
          <label for="linea_tension">¿Hay líneas de media/alta tensión a &lt; 30m? *</label>
          <div class="input-icon">
            <select id="linea_tension" name="linea_tension" required>
              <option value="">Seleccionar</option>
              <option value="si">Sí</option>
              <option value="no">No</option>
            </select>
          </div>
        </div>

        <!-- Zona restringida -->
        <div class="input-group">
          <label for="zona_restringida">¿Está a &lt; 3km de aeropuerto o zona restringida? *</label>
          <div class="input-icon">
            <select id="zona_restringida" name="zona_restringida" required>
              <option value="">Seleccionar</option>
              <option value="si">Sí</option>
              <option value="no">No</option>
            </select>
          </div>
        </div>

        <!-- Corriente eléctrica -->
        <div class="input-group">
          <label for="corriente_electrica">¿Disponibilidad de corriente eléctrica? *</label>
          <div class="input-icon">
            <select id="corriente_electrica" name="corriente_electrica" required>
              <option value="">Seleccionar</option>
              <option value="si">Sí</option>
              <option value="no">No</option>
            </select>
          </div>
        </div>

        <!-- Agua potable -->
        <div class="input-group">
          <label for="agua_potable">¿Disponibilidad de agua potable? *</label>
          <div class="input-icon">
            <select id="agua_potable" name="agua_potable" required>
              <option value="">Seleccionar</option>
              <option value="si">Sí</option>
              <option value="no">No</option>
            </select>
          </div>
        </div>

        <!-- Libres de obstáculos -->
        <div class="input-group">
          <label for="libre_obstaculos">¿Cuarteles libres de obstáculos? *</label>
          <div class="input-icon">
            <select id="libre_obstaculos" name="libre_obstaculos" required>
              <option value="">Seleccionar</option>
              <option value="si">Sí</option>
              <option value="no">No</option>
            </select>
          </div>
        </div>

        <!-- Área de despegue -->
        <div class="input-group">
          <label for="area_despegue">¿Área de despegue apropiada? *</label>
          <div class="input-icon">
            <select id="area_despegue" name="area_despegue" required>
              <option value="">Seleccionar</option>
              <option value="si">Sí</option>
              <option value="no">No</option>
            </select>
          </div>
        </div>

        <!-- Superficie -->
        <div class="input-group">
          <label for="superficie_ha">¿Cuántas hectáreas vamos a pulverizar? *</label>
          <div class="input-icon">
            <input type="number" id="superficie_ha" name="superficie_ha" min="0" step="0.01" placeholder="20" required />
          </div>
        </div>

        <!-- Método de pago (estático por ahora) -->
        <div class="input-group">
          <label for="forma_pago_id">Método de pago *</label>
          <div class="input-icon">
            <select id="forma_pago_id" name="forma_pago_id" required>
              <option value="">Seleccionar</option>
              <!-- Opciones estáticas de ejemplo; reemplazar por valores reales cuando se conecte lógica -->
              <option value="4">E-cheq</option>
              <option value="5">Transferencia Bancaria</option>
              <option value="6">Descuento por cooperativa</option>
            </select>
          </div>
        </div>

        <!-- Motivo del servicio (estático por ahora) -->
        <div class="input-group">
          <label for="patologia_id">Motivo del servicio *</label>
          <div class="input-icon">
            <select id="patologia_id" name="patologia_id" required>
              <option value="">Seleccionar</option>
              <!-- Opciones estáticas de ejemplo -->
              <option value="1">Fertilización foliar</option>
              <option value="2">Lobesia</option>
              <option value="3">Oídio</option>
            </select>
          </div>
        </div>

        <!-- Rango temporal -->
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

        <!-- Dirección: Provincia -->
        <div class="input-group">
          <label for="dir_provincia">Provincia *</label>
          <div class="input-icon">
            <input type="text" id="dir_provincia" name="dir_provincia" placeholder="Provincia" required />
          </div>
        </div>

        <!-- Dirección: Localidad -->
        <div class="input-group">
          <label for="dir_localidad">Localidad *</label>
          <div class="input-icon">
            <input type="text" id="dir_localidad" name="dir_localidad" placeholder="Localidad" required />
          </div>
        </div>

        <!-- Dirección: Calle -->
        <div class="input-group">
          <label for="dir_calle">Calle *</label>
          <div class="input-icon">
            <input type="text" id="dir_calle" name="dir_calle" placeholder="Calle" required />
          </div>
        </div>

        <!-- Dirección: Número -->
        <div class="input-group">
          <label for="dir_numero">Número *</label>
          <div class="input-icon">
            <input type="text" id="dir_numero" name="dir_numero" placeholder="Número" required />
          </div>
        </div>

        <!-- Observaciones -->
        <div class="input-group" style="grid-column: 1 / -1;">
          <label for="observaciones">Observaciones</label>
          <div class="input-icon input-icon-comment">
            <textarea id="observaciones" name="observaciones" maxlength="233" rows="3" placeholder="Escribí un comentario..."></textarea>
          </div>
        </div>

      </div>

      <!-- Botones (sin acciones JS) -->
      <div class="form-buttons">
        <button class="btn btn-aceptar" type="submit">Enviar</button>
        <button class="btn btn-cancelar" type="reset">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<style>
  /* ====== Responsive & UX mínimos (mobile-first) ====== */

  .form-grid.grid-4 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1rem;
  }

  .form-modern input,
  .form-modern select,
  .form-modern textarea {
    min-height: 42px;
  }

  /* Accesibilidad visual en foco */
  .form-modern input:focus,
  .form-modern select:focus,
  .form-modern textarea:focus {
    outline: 2px solid #5b21b6;
    outline-offset: 1px;
  }

  /* Evitar FOUC en tarjetas/tablas si se agregan luego */
  .card { transition: box-shadow .2s ease, transform .2s ease; }
  .card:hover { transform: translateY(-1px); }

  @media (max-width: 640px) {
    html, body { overflow-x: hidden; }
  }
</style>
