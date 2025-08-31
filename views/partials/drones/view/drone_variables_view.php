<?php // views/partials/drones/view/drone_variables_view.php ?>
<div class="content">
  <div class="card" style="background-color:#5b21b6;">
    <h3 style="color:white;">Módulo: Variables</h3>
    <p style="color:white;margin:0;">Plantilla mínima lista para empezar.</p>
  </div>

  <div id="variables-root" class="card">
    <h4>Vista de variables (WIP)</h4>
    <div id="variables-health" style="margin-top:8px;color:#64748b;">Verificando conexión…</div>
  </div>
</div>

<script>
(function () {
  const API = '../partials/drones/controller/drone_variables_controller.php';

  const el = document.getElementById('variables-health');
  if (!el) return;

  fetch(API + '?t=' + Date.now(), { cache: 'no-store' })
    .then(r => r.json())
    .then(json => {
      el.innerHTML = (json && json.ok)
        ? '<strong>Controlador y modelo conectados correctamente</strong> ✅'
        : '<strong style="color:#b91c1c;">No se pudo verificar la conexión</strong> ❌';
    })
    .catch(e => {
      el.innerHTML = '<strong style="color:#b91c1c;">Error:</strong> ' + (e?.message || e);
    });
})();
</script>
