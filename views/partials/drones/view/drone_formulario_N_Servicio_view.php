<?php // views/partials/drones/view/drone_calendar_view.php 
?>
<div class="content">
  <div class="card" style="background-color:#5b21b6;">
    <h3 style="color:white;">Módulo: Registro nueva solicitud de servicio de pulverización con drones</h3>
    <p style="color:white;margin:0;">Plantilla mínima lista para empezar.</p>
  </div>

  <div id="calendar-root" class="card">
    <h4>Completa el formulario para cargar una nueva solicitud de drones</h4>
    <form class="form-modern">
      <div class="form-grid grid-4">

        <!-- Nombre completo -->
        <div class="input-group">
          <label for="nombre">Nombre completo</label>
          <div class="input-icon input-icon-name">
            <input type="text" id="nombre" name="nombre" placeholder="Juan Pérez" required />
          </div>
        </div>

        <!-- Correo electrónico -->
        <div class="input-group">
          <label for="email">Correo electrónico</label>
          <div class="input-icon input-icon-email">
            <input id="email" name="email" placeholder="usuario@correo.com" />
          </div>
        </div>

        <!-- Fecha de nacimiento -->
        <div class="input-group">
          <label for="fecha">Fecha de nacimiento</label>
          <div class="input-icon input-icon-date">
            <input id="fecha" name="fecha" />
          </div>
        </div>

        <!-- Teléfono -->
        <div class="input-group">
          <label for="telefono">Teléfono</label>
          <div class="input-icon input-icon-phone">
            <input id="telefono" name="telefono" />
          </div>
        </div>

        <!-- DNI -->
        <div class="input-group">
          <label for="dni">DNI</label>
          <div class="input-icon input-icon-dni">
            <input id="dni" name="dni" />
          </div>
        </div>

        <!-- Edad -->
        <div class="input-group">
          <label for="edad">Edad</label>
          <div class="input-icon input-icon-age">
            <input id="edad" name="edad" />
          </div>
        </div>

        <!-- CUIT -->
        <div class="input-group">
          <label for="cuit">CUIT</label>
          <div class="input-icon input-icon-cuit">
            <input id="cuit" name="cuit" />
          </div>
        </div>

        <!-- Provincia -->
        <div class="input-group">
          <label for="provincia">Provincia</label>
          <div class="input-icon input-icon-globe">
            <select id="provincia" name="provincia" required>
              <option value="">Seleccionar</option>
              <option>Buenos Aires</option>
              <option>Córdoba</option>
              <option>Santa Fe</option>
            </select>
          </div>
        </div>

        <!-- Localidad -->
        <div class="input-group">
          <label for="localidad">Localidad</label>
          <div class="input-icon input-icon-city">
            <input type="text" id="localidad" name="localidad" required />
          </div>
        </div>

        <!-- Código Postal -->
        <div class="input-group">
          <label for="cp">Código Postal</label>
          <div class="input-icon input-icon-cp">
            <input type="text" id="cp" name="cp" />
          </div>
        </div>

        <!-- Dirección -->
        <div class="input-group">
          <label for="direccion">Dirección</label>
          <div class="input-icon input-icon-address">
            <input type="text" id="direccion" name="direccion" required />
          </div>
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

      <!-- Botones -->
      <div class="form-buttons">
        <button class="btn btn-aceptar" type="submit">Enviar</button>
        <button class="btn btn-cancelar" type="reset">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script>
  (function() {
    // Scoped: no contamina el global
    const API = '../partials/drones/controller/drone_formulario_N_Servicio_controller.php';

    // Chequeo opcional de wiring (podés quitarlo si no lo necesitás)
    const el = document.getElementById('registro-health');
    if (!el) return;

    fetch(API + '?t=' + Date.now(), {
        cache: 'no-store'
      })
      .then(r => r.json())
      .then(json => {
        if (json && json.ok) {
          el.innerHTML = '<strong>Controlador y modelo conectados correctamente</strong> ✅';
        } else {
          el.innerHTML = '<strong style="color:#b91c1c;">No se pudo verificar la conexión</strong> ❌';
        }
      })
      .catch(e => {
        el.innerHTML = '<strong style="color:#b91c1c;">Error:</strong> ' + (e?.message || e);
      });
  })();
</script>