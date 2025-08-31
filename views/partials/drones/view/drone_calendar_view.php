<?php // views/partials/drones/view/drone_calendar_view.php ?>
<div class="content">
  <div class="card" style="background-color:#5b21b6;">
    <h3 style="color:white;">Módulo: Calendario</h3>
    <p style="color:white;margin:0;">Plantilla mínima lista para empezar.</p>
  </div>

  <div id="calendar-root" class="card">
    <h4>Esta es la vista del calendario, próximamente va a estar disponible para su consumo.</h4>
    <div id="calendar-health" style="margin-top:8px;color:#64748b;">Verificando conexión…</div>
  </div>
</div>

<script>
(function () {
  // Scoped: no contamina el global
  const API = '../partials/drones/controller/drone_calendar_controller.php';

  // Chequeo opcional de wiring (podés quitarlo si no lo necesitás)
  const el = document.getElementById('calendar-health');
  if (!el) return;

  fetch(API + '?t=' + Date.now(), { cache: 'no-store' })
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
