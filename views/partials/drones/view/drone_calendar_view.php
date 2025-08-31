<?php

?>
<!-- Framework visual del proyecto -->
<link rel="stylesheet" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css">
<script src="https://www.fernandosalguero.com/cdn/assets/javascript/framework.js" defer></script>

<!-- Íconos (opcional) -->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

<div class="content">
    <div class="card" style="background-color:#5b21b6;">
        <h3 style="color:white;">Módulo: Calendario</h3>
        <p style="color:white;margin:0;">Proximamente va a estar activa esta funcionalidad.</p>
    </div>

    <div id="feature-root" class="card-grid grid-4">
        <div class="card" id="healthcard">
            Verificando conexión con el controlador y el modelo…
        </div>
    </div>
</div>

<script>
    const API = '../partials/drones/controller/drone_calendar_controller.php';

    (async function checkWiring() {
        const card = document.getElementById('healthcard');
        try {
            const res = await fetch(API + '?t=' + Date.now(), {
                cache: 'no-store'
            });
            const json = await res.json();

            if (json && json.ok) {
                card.innerHTML = `
          <strong>Controlador y modelo conectados correctamente</strong> ✅
          <div style="margin-top:6px;font-size:.9rem;color:#64748b;">
            Clase: <code>${json.checks?.modelClass || '-'}</code> · PDO: <code>${json.checks?.pdo ? 'OK' : 'NO'}</code>
          </div>
        `;
            } else {
                card.innerHTML = `
          <strong style="color:#b91c1c;">No se pudo verificar la conexión</strong> ❌
          <pre style="white-space:pre-wrap;margin-top:8px;">${(json && (json.message || json.error)) || 'Respuesta inválida'}</pre>
        `;
            }
        } catch (e) {
            card.innerHTML = `
        <strong style="color:#b91c1c;">Error contactando al controlador</strong> ❌
        <pre style="white-space:pre-wrap;margin-top:8px;">${e.message}</pre>
      `;
        }
    })();
</script>