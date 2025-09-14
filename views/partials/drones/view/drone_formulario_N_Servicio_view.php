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

        <!-- Nombre del productor -->
        <div class="input-group">
          <label for="nombre">Nombre del productor</label>
          <div class="input-icon input-icon-name">
            <input type="text" id="nombre" name="nombre" placeholder="Juan Pérez" required />
          </div>
        </div>

        <!-- Provincia -->
        <div class="input-group">
          <label for="provincia">¿A LA HORA DE TOMAR EL SERVICIO PODREMOS CONTAR CON UN REPRESENTATE DE LA PROPIEDAD EN LA FINCA? *</label>
          <div class="input-icon input-icon-globe">
            <select id="provincia" name="provincia" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>

        <!-- Provincia -->
        <div class="input-group">
          <label for="provincia">¿EL/ LOS CUARTELES A PULVERIZAR CUENTAN CON ALGUNA LINEA DE MEDIA O ALTA TENSION A MENOS DE 30 METROS? *</label>
          <div class="input-icon input-icon-globe">
            <select id="provincia" name="provincia" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>

        <!-- Provincia -->
        <div class="input-group">
          <label for="provincia">¿EL/LOS CUARTELES A PULVERIZAR SE ENCUENTRA A MENOS DE 3 KM DE UN AEROPUERTO O ZONA DE VUELO RESTRINGIDA? *</label>
          <div class="input-icon input-icon-globe">
            <select id="provincia" name="provincia" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>
        <!-- Provincia -->
        <div class="input-group">
          <label for="provincia">¿CUENTA CON DISPONIBILIDAD DE CORRIENTE ELÉCTRICA?*</label>
          <div class="input-icon input-icon-globe">
            <select id="provincia" name="provincia" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>

        <!-- Provincia -->
        <div class="input-group">
          <label for="provincia">¿EN LA PROPIEDAD HAY DISPONIBILIDAD DE AGUA POTABLE? *</label>
          <div class="input-icon input-icon-globe">
            <select id="provincia" name="provincia" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>

        <!-- Provincia -->
        <div class="input-group">
          <label for="provincia">¿ EL/LOS CUARTELES A PULVERIZAR ESTAN LIBRES DE OBSTÁCULOS? *</label>
          <div class="input-icon input-icon-globe">
            <select id="provincia" name="provincia" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>

        <!-- Provincia -->
        <div class="input-group">
          <label for="provincia">¿EL/ LOS CUARTELES A PULVERIZAR CUENTAN CON UN ÁREA DE DESPEGUE APROPIADA? *</label>
          <div class="input-icon input-icon-globe">
            <select id="provincia" name="provincia" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>

        <!-- Provincia -->
        <div class="input-group">
          <label for="provincia">SUPERFICIE (en hectáreas) PARA LAS QUE DESEA CONTRATAR EL SERVICIO*</label>
          <div class="input-icon input-icon-globe">
            <select id="provincia" name="provincia" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>

        <!-- Provincia -->
        <div class="input-group">
          <label for="provincia">MÉTODO DE PAGO *</label>
          <div class="input-icon input-icon-globe">
            <select id="provincia" name="provincia" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>

        <!-- Provincia -->
        <div class="input-group">
          <label for="provincia">INDICAR EL MOTIVO POR EL QUE DESEA CONTRATAR EL SERVICIO*</label>
          <div class="input-icon input-icon-globe">
            <select id="provincia" name="provincia" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>

        <!-- Provincia -->
        <div class="input-group">
          <label for="provincia">INDICAR EN QUE MOMENTO DESEA CONTRATAR EL SERVICIO*</label>
          <div class="input-icon input-icon-globe">
            <select id="provincia" name="provincia" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>

        <!-- Provincia -->
        <div class="input-group">
          <label for="provincia">En el caso de necesitar productos fitosanitarios para realizar la pulverización indicar los que sean necesarios. *</label>
          <div class="input-icon input-icon-globe">
            <select id="provincia" name="provincia" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
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


        <!-- Dirección -->
        <div class="input-group">
          <label for="direccion">Altura</label>
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