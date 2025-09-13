<?php

?>
<div class="content">
  <div class="card" style="background-color:#5b21b6;">
    <h3 style="color:white;">Solicitud de pulverización con dron</h3>
    <p style="color:white;margin:0;">Complete el siguiente formulario para registrar su solicitud.</p>
  </div>

  <!-- Formulario -->
  <form id="form-dron" class="gform-grid cols-2">

    <!-- Seleccionar un productor -->
    <div class="gform-question" data-required="true" id="q_metodo_pago">
      <label class="gform-label" for="metodo_pago">Productor<span class="gform-required">*</span></label>
      <div class="gform-helper">Elege un productor</div>
      <select class="gform-input" id="metodo_pago" name="metodo_pago" aria-required="true">
        <option value="">Cargando productores</option>
      </select>
      <div id="productor" class="gform-helper" style="margin-top:.35rem;"></div>
    </div>

    <!-- representante -->
    <div class="gform-question" role="group" aria-labelledby="q_representante_label" id="q_representante">
      <div id="q_representante_label" class="gform-legend">
        ¿A LA HORA DE TOMAR EL SERVICIO PODREMOS CONTAR CON UN REPRESENTANTE DE LA PROPIEDAD EN LA FINCA? <span class="gform-required">*</span>
      </div>
      <div class="gform-helper">
        Debe recibir al piloto, indicar cuarteles, asistir y firmar registro fitosanitario.
      </div>
      <div class="gform-options">
        <label class="gform-option"><input type="radio" id="representante_si" name="representante" value="si"><span>SI</span></label>
        <label class="gform-option"><input type="radio" id="representante_no" name="representante" value="no"><span>NO</span></label>
      </div>
      <div class="gform-error">Esta pregunta es obligatoria.</div>
    </div>

    <!-- línea tensión -->
    <div class="gform-question" role="group" aria-labelledby="q_linea_tension_label" id="q_linea_tension">
      <div id="q_linea_tension_label" class="gform-legend">
        ¿LOS CUARTELES TIENEN LÍNEA DE MEDIA/ALTA TENSIÓN A &lt; 30 m? <span class="gform-required">*</span>
      </div>
      <div class="gform-options">
        <label class="gform-option"><input type="radio" id="linea_tension_si" name="linea_tension" value="si"><span>SI</span></label>
        <label class="gform-option"><input type="radio" id="linea_tension_no" name="linea_tension" value="no"><span>NO</span></label>
      </div>
      <div class="gform-error">Esta pregunta es obligatoria.</div>
    </div>

    <!-- zona restringida -->
    <div class="gform-question" role="group" aria-labelledby="q_zona_restringida_label" id="q_zona_restringida">
      <div id="q_zona_restringida_label" class="gform-legend">
        ¿SE ENCUENTRA A &lt; 3 KM DE AEROPUERTO O ZONA RESTRINGIDA? <span class="gform-required">*</span>
      </div>
      <div class="gform-options">
        <label class="gform-option"><input type="radio" id="zona_restringida_si" name="zona_restringida" value="si"><span>SI</span></label>
        <label class="gform-option"><input type="radio" id="zona_restringida_no" name="zona_restringida" value="no"><span>NO</span></label>
      </div>
      <div class="gform-error">Esta pregunta es obligatoria.</div>
    </div>

    <!-- corriente eléctrica -->
    <div class="gform-question" role="group" aria-labelledby="q_corriente_electrica_label" id="q_corriente_electrica">
      <div id="q_corriente_electrica_label" class="gform-legend">
        ¿CUENTA CON CORRIENTE ELÉCTRICA? <span class="gform-required">*</span>
      </div>
      <div class="gform-helper">Requerido para carga de baterías (toma de 35A).</div>
      <div class="gform-options">
        <label class="gform-option"><input type="radio" id="corriente_electrica_si" name="corriente_electrica" value="si"><span>SI</span></label>
        <label class="gform-option"><input type="radio" id="corriente_electrica_no" name="corriente_electrica" value="no"><span>NO</span></label>
      </div>
      <div class="gform-error">Esta pregunta es obligatoria.</div>
    </div>

    <!-- agua potable -->
    <div class="gform-question" role="group" aria-labelledby="q_agua_potable_label" id="q_agua_potable">
      <div id="q_agua_potable_label" class="gform-legend">
        ¿HAY DISPONIBILIDAD DE AGUA POTABLE? <span class="gform-required">*</span>
      </div>
      <div class="gform-helper">Para preparación de caldos y limpieza del dron.</div>
      <div class="gform-options">
        <label class="gform-option"><input type="radio" id="agua_potable_si" name="agua_potable" value="si"><span>SI</span></label>
        <label class="gform-option"><input type="radio" id="agua_potable_no" name="agua_potable" value="no"><span>NO</span></label>
      </div>
      <div class="gform-error">Esta pregunta es obligatoria.</div>
    </div>

    <!-- obstáculos -->
    <div class="gform-question" role="group" aria-labelledby="q_obstaculos_label" id="q_obstaculos">
      <div id="q_obstaculos_label" class="gform-legend">
        ¿LOS CUARTELES ESTÁN LIBRES DE OBSTÁCULOS? <span class="gform-required">*</span>
      </div>
      <div class="gform-helper">Árboles internos, cables/alambres que superen la altura del cultivo, etc.</div>
      <div class="gform-options">
        <label class="gform-option"><input type="radio" id="libre_obstaculos_si" name="libre_obstaculos" value="si"><span>SI</span></label>
        <label class="gform-option"><input type="radio" id="libre_obstaculos_no" name="libre_obstaculos" value="no"><span>NO</span></label>
      </div>
      <div class="gform-error">Esta pregunta es obligatoria.</div>
    </div>

    <!-- área despegue -->
    <div class="gform-question" role="group" aria-labelledby="q_area_despegue_label" id="q_area_despegue">
      <div id="q_area_despegue_label" class="gform-legend">
        ¿CUENTAN CON ÁREA DE DESPEGUE APROPIADA (5×5 m)? <span class="gform-required">*</span>
      </div>
      <div class="gform-helper">Callejón despejado y libre de obstáculos.</div>
      <div class="gform-options">
        <label class="gform-option"><input type="radio" id="area_despegue_si" name="area_despegue" value="si"><span>SI</span></label>
        <label class="gform-option"><input type="radio" id="area_despegue_no" name="area_despegue" value="no"><span>NO</span></label>
      </div>
      <div class="gform-error">Esta pregunta es obligatoria.</div>
    </div>

    <!-- hectáreas -->
    <div class="gform-question" data-required="true" id="q_superficie">
      <label class="gform-label" for="superficie_ha">SUPERFICIE (ha) <span class="gform-required">*</span></label>
      <div class="gform-helper">Ingrese sólo el número de hectáreas a pulverizar.</div>
      <input class="gform-input" id="superficie_ha" name="superficie_ha" type="number" inputmode="decimal" min="0.01" step="0.01" placeholder="Ej.: 3.5" />
      <div class="gform-error">Debe ser un número &gt; 0.</div>
    </div>

    <!-- método de pago -->
    <div class="gform-question" data-required="true" id="q_metodo_pago">
      <label class="gform-label" for="metodo_pago">MÉTODO DE PAGO <span class="gform-required">*</span></label>
      <div class="gform-helper">Elegí una opción disponible.</div>
      <select class="gform-input" id="metodo_pago" name="metodo_pago" aria-required="true">
        <option value="">Cargando métodos…</option>
      </select>
      <div id="metodo_pago_desc" class="gform-helper" style="margin-top:.35rem;"></div>

      <div id="wrap_coop_cuota" class="gform-field" style="margin-top:.6rem; display:none;">
        <label class="gform-label" for="coop_descuento_id_real">Seleccioná la cooperativa</label>
        <select class="gform-input" id="coop_descuento_id_real" name="coop_descuento_id_real">
          <option value="">Cargando cooperativas…</option>
        </select>
        <div class="gform-helper">Sólo cooperativas habilitadas.</div>
        <div class="gform-error" id="coop_descuento_error" style="display:none;">Debés seleccionar una cooperativa.</div>
      </div>
    </div>

    <!-- motivo/patologías -->
    <div class="gform-question" role="group" aria-labelledby="q_motivo_label" id="q_motivo">
      <div id="q_motivo_label" class="gform-legend">INDICAR EL MOTIVO <span class="gform-required">*</span></div>
      <div class="gform-options" id="motivo_dynamic">
        <div class="gform-helper">Cargando patologías…</div>
      </div>
      <div class="gform-error">Seleccioná al menos una opción.</div>
    </div>

    <!-- rango fecha -->
    <div class="gform-question" role="group" aria-labelledby="q_rango_label" id="q_rango">
      <div id="q_rango_label" class="gform-legend">MOMENTO DESEADO <span class="gform-required">*</span></div>
      <div class="gform-options">
        <label class="gform-option"><input type="radio" name="rango_fecha" value="octubre_q1"><span>1ª quincena Octubre</span></label>
        <label class="gform-option"><input type="radio" name="rango_fecha" value="octubre_q2"><span>2ª quincena Octubre</span></label>
        <label class="gform-option"><input type="radio" name="rango_fecha" value="noviembre_q1"><span>1ª quincena Noviembre</span></label>
        <label class="gform-option"><input type="radio" name="rango_fecha" value="noviembre_q2"><span>2ª quincena Noviembre</span></label>
        <label class="gform-option"><input type="radio" name="rango_fecha" value="diciembre_q1"><span>1ª quincena Diciembre</span></label>
        <label class="gform-option"><input type="radio" name="rango_fecha" value="diciembre_q2"><span>2ª quincena Diciembre</span></label>
        <label class="gform-option"><input type="radio" name="rango_fecha" value="enero_q1"><span>1ª quincena Enero</span></label>
        <label class="gform-option"><input type="radio" name="rango_fecha" value="enero_q2"><span>2ª quincena Enero</span></label>
        <label class="gform-option"><input type="radio" name="rango_fecha" value="febrero_q1"><span>1ª quincena Febrero</span></label>
        <label class="gform-option"><input type="radio" name="rango_fecha" value="febrero_q2"><span>2ª quincena Febrero</span></label>
      </div>
      <div class="gform-error">Seleccioná una opción.</div>
    </div>

    <!-- productos dinámicos -->
    <div class="gform-question" role="group" aria-labelledby="q_productos_label" id="q_productos">
      <div id="q_productos_label" class="gform-legend">
        Si necesitás productos fitosanitarios indicá los necesarios. <span class="gform-required">*</span>
      </div>
      <div class="gform-options gopts-with-complement" id="productos_dynamic">
        <div class="gform-helper">Seleccioná patologías arriba para ver productos.</div>
      </div>
      <div class="gform-error">Seleccioná al menos una opción.</div>
    </div>

    <!-- dirección -->
    <div class="gform-question span-2" role="group" aria-labelledby="q_direccion_label" id="q_direccion">
      <div id="q_direccion_label" class="gform-legend">DIRECCIÓN DE LA FINCA</div>
      <div class="gform-helper">Si no capturás coordenadas, completá estos datos.</div>
      <div class="gform-grid cols-4">
        <div class="gform-field">
          <label class="gform-label" for="dir_provincia">Provincia</label>
          <input class="gform-input" id="dir_provincia" name="dir_provincia" type="text" placeholder="Provincia">
        </div>
        <div class="gform-field">
          <label class="gform-label" for="dir_localidad">Localidad</label>
          <input class="gform-input" id="dir_localidad" name="dir_localidad" type="text" placeholder="Localidad">
        </div>
        <div class="gform-field">
          <label class="gform-label" for="dir_calle">Calle</label>
          <input class="gform-input" id="dir_calle" name="dir_calle" type="text" placeholder="Calle">
        </div>
        <div class="gform-field">
          <label class="gform-label" for="dir_numero">Numeración</label>
          <input class="gform-input" id="dir_numero" name="dir_numero" type="text" inputmode="numeric" placeholder="Nº">
        </div>
      </div>
    </div>

    <!-- resumen costos -->
    <div id="resumen-costos-inline" class="card" style="margin-top:1rem;">
      <h4 style="margin:0 0 .5rem 0;">Resumen de costos</h4>
      <div class="gform-grid cols-2">
        <div>
          <div class="gform-helper">Servicio base</div>
          <div id="rc_base" style="font-weight:700;">—</div>
        </div>
        <div>
          <div class="gform-helper">Productos SVE</div>
          <div id="rc_prod" style="font-weight:700;">—</div>
        </div>
        <div class="span-2" style="border-top:1px solid #eee; padding-top:.5rem; margin-top:.35rem;">
          <div class="gform-helper">Total estimado</div>
          <div id="rc_total" style="font-weight:800; font-size:1.1rem;">—</div>
        </div>
      </div>
    </div>

    <!-- acciones -->
    <div class="gform-actions span-1">
      <button type="submit" id="btn_solicitar" class="gform-btn gform-primary">Solicitar el servicio</button>
    </div>
  </form>

  <!-- Toasts -->
  <div id="toast-container"></div>
  <div id="toast-container-boton"></div>
</div>

<!-- Modal de confirmación -->
<div id="modalConfirmacion" class="modal" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="modalTitle">
  <div class="modal-content">
    <div class="modal-header">
      <h3 id="modalTitle" class="modal-title">¿Confirmar pedido?</h3>
      <button type="button" class="modal-close" aria-label="Cerrar" onclick="cerrarModal()"><span class="material-icons">close</span></button>
    </div>
    <div class="modal-body">
      <p class="muted">Estás por enviar el pedido. Revisá el detalle:</p>

    </div>
    <div class="modal-actions">
      <button type="button" class="btn btn-cancelar" onclick="cerrarModal()">Cancelar</button>
      <button type="button" id="btnConfirmarModal" class="btn btn-aceptar" onclick="confirmarEnvio()">Sí, enviar</button>
    </div>
  </div>
</div>

<!-- CDN -->
<link rel="stylesheet" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
<script src="https://www.fernandosalguero.com/cdn/assets/javascript/framework.js" defer></script>

<!-- Estilos específicos (inline para esta vista) -->
<style>
  .main {
    margin-left: 0;
  }

  .header-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 2rem 1.5rem;
  }

  .modal {
    position: fixed;
    inset: 0;
    display: none;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, .55);
    padding: 1rem;
    z-index: 10000;
  }

  .modal.is-open {
    display: flex;
  }

  .modal-content {
    width: min(720px, 100%);
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 24px 60px rgba(0, 0, 0, .25);
    overflow: hidden;
  }

  .modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #eee;
  }

  .modal-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 700;
  }

  .modal-close {
    border: 0;
    background: transparent;
    cursor: pointer;
    font-size: 0;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .modal-close .material-icons {
    font-size: 22px;
    color: #666;
  }

  .modal-body {
    padding: 1rem 1.25rem;
    max-height: 65vh;
    overflow: auto;
    overflow-x: hidden;
  }

  .modal-actions {
    padding: 1rem 1.25rem;
    display: flex;
    gap: .75rem;
    justify-content: flex-end;
    border-top: 1px solid #eee;
  }

  .modal-summary dl {
    display: grid;
    grid-template-columns: 1fr;
    gap: .5rem 1rem;
    margin: 1rem 0 0;
  }

  .modal-summary dt {
    font-weight: 600;
    color: #333;
  }

  .modal-summary dd {
    margin: 0;
    color: #111;
  }

  @media(min-width:640px) {
    .modal-summary dl {
      grid-template-columns: 1.2fr 1fr;
    }
  }

  .modal-summary .note {
    white-space: pre-wrap;
    border: 1px solid #eee;
    border-radius: 10px;
    padding: .75rem;
    background: #fafafa;
  }

  .costos-block {
    margin-top: 1rem;
  }

  .costos-title {
    font-size: 1.05rem;
    font-weight: 700;
    margin: 0 0 .5rem 0;
  }

  .costos-list {
    list-style: none;
    margin: 0;
    padding: 0;
    border: 1px solid #eee;
    border-radius: 12px;
    overflow: hidden;
    background: #fff;
  }

  .costos-item {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: .75rem;
    padding: .75rem .9rem;
    line-height: 1.2;
  }

  .costos-item+.costos-item {
    border-top: 1px solid #f2f2f2;
  }

  .costos-label {
    font-weight: 500;
    color: #333;
    flex: 1 1 auto;
    min-width: 0;
  }

  .costos-help {
    display: block;
    font-size: .85rem;
    color: #666;
    margin-top: .15rem;
    word-break: break-word;
  }

  .costos-amount {
    flex: 0 0 auto;
    font-variant-numeric: tabular-nums;
    text-align: right;
    white-space: nowrap;
  }

  .costos-total {
    font-weight: 800;
  }

  .costos-total .costos-amount {
    font-size: 1.05rem;
  }

  .gform-question .gform-error {
    display: none;
    color: #dc2626;
    font-size: .9rem;
    margin-top: .5rem
  }

  .gform-question.has-error .gform-error {
    display: block
  }

  .gform-question.has-error .gform-legend,
  .gform-question.has-error .gform-label {
    color: #dc2626
  }

  .gform-question.has-error .gform-options,
  .gform-question.has-error .gform-input,
  .gform-question.has-error textarea {
    outline: 2px solid #dc2626;
    outline-offset: 2px
  }
</style>

<script>

</script>