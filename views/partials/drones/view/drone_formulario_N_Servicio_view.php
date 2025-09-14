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

        <!-- representante -->
        <div class="input-group">
          <label for="representante">¿A LA HORA DE TOMAR EL SERVICIO PODREMOS CONTAR CON UN REPRESENTATE DE LA PROPIEDAD EN LA FINCA? *</label>
          <div class="input-icon">
            <select id="representante" name="representante" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>

        <!-- linea_tension -->
        <div class="input-group">
          <label for="linea_tension">¿EL/ LOS CUARTELES A PULVERIZAR CUENTAN CON ALGUNA LINEA DE MEDIA O ALTA TENSION A MENOS DE 30 METROS? *</label>
          <div class="input-icon">
            <select id="linea_tension" name="linea_tension" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>

        <!-- zona_restringida -->
        <div class="input-group">
          <label for="zona_restringida">¿EL/LOS CUARTELES A PULVERIZAR SE ENCUENTRA A MENOS DE 3 KM DE UN AEROPUERTO O ZONA DE VUELO RESTRINGIDA? *</label>
          <div class="input-icon">
            <select id="zona_restringida" name="zona_restringida" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>

        <!-- corriente_electrica -->
        <div class="input-group">
          <label for="corriente_electrica">¿CUENTA CON DISPONIBILIDAD DE CORRIENTE ELÉCTRICA?*</label>
          <div class="input-icon">
            <select id="corriente_electrica" name="corriente_electrica" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>

        <!-- agua_potable -->
        <div class="input-group">
          <label for="agua_potable">¿EN LA PROPIEDAD HAY DISPONIBILIDAD DE AGUA POTABLE? *</label>
          <div class="input-icon">
            <select id="agua_potable" name="agua_potable" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>

        <!-- libre_obstaculos -->
        <div class="input-group">
          <label for="libre_obstaculos">¿ EL/LOS CUARTELES A PULVERIZAR ESTAN LIBRES DE OBSTÁCULOS? *</label>
          <div class="input-icon">
            <select id="libre_obstaculos" name="libre_obstaculos" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>

        <!-- area_despegue -->
        <div class="input-group">
          <label for="area_despegue">¿EL/ LOS CUARTELES A PULVERIZAR CUENTAN CON UN ÁREA DE DESPEGUE APROPIADA? *</label>
          <div class="input-icon">
            <select id="area_despegue" name="area_despegue" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>

        <!-- superficie_ha -->
        <div class="input-group">
          <label for="superficie_ha">¿CUATAS HECTAREAS VAMOS A PULVERIZAR</label>
          <div class="input-icon">
            <input type="number" id="superficie_ha" name="superficie_ha" placeholder="20" required />
          </div>
        </div>

        <!-- forma_pago_id -->
        <div class="input-group">
          <label for="forma_pago_id">MÉTODO DE PAGO *</label>
          <div class="input-icon input-icon-globe">
            <select id="forma_pago_id" name="forma_pago_id" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>

        <!-- coop_descuento_id_real -->
        <div class="input-group">
          <label for="coop_descuento_id_real">Selecciona una cooperativa</label>
          <div class="input-icon input-icon-globe">
            <select id="coop_descuento_id_real" name="coop_descuento_id_real" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>div>

        <!-- patologia_id -->
        <div class="input-group">
          <label for="patologia_id">INDICAR EL MOTIVO POR EL QUE DESEA CONTRATAR EL SERVICIO*</label>
          <div class="input-icon">
            <select id="patologia_id" name="patologia_id" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>

        <!-- rango -->
        <div class="input-group">
          <label for="rango">INDICAR EN QUE MOMENTO DESEA CONTRATAR EL SERVICIO*</label>
          <div class="input-icon">
            <select id="rango" name="rango" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>

        <!-- nombre_producto -->
        <div class="input-group">
          <label for="nombre_producto">En el caso de necesitar productos fitosanitarios para realizar la pulverización indicar los que sean necesarios. *</label>
          <div class="input-icon">
            <select id="nombre_producto" name="nombre_producto" required>
              <option value="">Seleccionar</option>
              <option>Si</option>
              <option>No</option>
            </select>
          </div>
        </div>

        <!-- dir_provincia -->
        <div class="input-group">
          <label for="dir_provincia">Provincia</label>
          <div class="input-icon">
            <input type="text" id="dir_provincia" name="dir_provincia" placeholder="Juan Pérez" required />
          </div>
        </div>

        <!-- dir_localidad -->
        <div class="input-group">
          <label for="dir_localidad">Localidad</label>
          <div class="input-icon">
            <input type="text" id="dir_localidad" name="dir_localidad" placeholder="Juan Pérez" required />
          </div>
        </div>

        <!-- dir_calle -->
        <div class="input-group">
          <label for="dir_calle">Calle</label>
          <div class="input-icon">
            <input type="text" id="dir_calle" name="dir_calle" placeholder="Juan Pérez" required />
          </div>
        </div>
      </div>

      <!-- dir_numero -->
      <div class="input-group">
        <label for="dir_numero">Número</label>
        <div class="input-icon">
          <input type="text" id="dir_numero" name="dir_numero" placeholder="Juan Pérez" required />
        </div>
      </div>
  </div>

  <!-- observaciones -->
  <div class="input-group">
    <label for="observaciones">Observaciones</label>
    <div class="input-icon input-icon-comment">
      <textarea id="observaciones" name="observaciones" maxlength="233" rows="3"
        placeholder="Escribí un comentario..."></textarea>
    </div>
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