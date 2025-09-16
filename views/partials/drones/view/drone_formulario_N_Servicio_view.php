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
      required
    />
    <!-- Sugerencias -->
    <ul id="ta-list-nombres" class="typeahead-list" role="listbox" hidden></ul>
  </div>
  <small class="gform-helper">Escribí y elegí una opción con Enter o clic.</small>
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