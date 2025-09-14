<?php
// Archivo: formulario_solicitud_drones.php (solo HTML + CSS, sin JS ni CDN)
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Solicitud de pulverización con drones</title>
  <style>
    :root{
      --bg:#f6f7fb;
      --card:#ffffff;
      --text:#1f2937;
      --muted:#6b7280;
      --brand:#5b21b6;
      --brand-contrast:#ffffff;
      --border:#e5e7eb;
      --radius:14px;
      --shadow:0 6px 20px rgba(17,24,39,.07);
      --focus:2px solid rgba(91,33,182,.35);
    }

    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0;
      font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,"Noto Sans",sans-serif;
      color:var(--text);
      background:linear-gradient(180deg, #fafbff 0%, #f3f4f8 100%);
    }

    .wrap{max-width:1100px;margin:auto;padding:24px}
    .header{
      background:var(--brand);
      color:var(--brand-contrast);
      border-radius:var(--radius);
      padding:18px 20px;
      box-shadow:var(--shadow);
    }
    .header h1{margin:0 0 6px 0;font-size:20px;font-weight:700}
    .header p{margin:0;font-size:14px;opacity:.9}

    .card{
      margin-top:16px;
      background:var(--card);
      border:1px solid var(--border);
      border-radius:var(--radius);
      box-shadow:var(--shadow);
      padding:20px;
    }

    .card h2{
      margin:0 0 12px 0;
      font-size:18px;
      font-weight:600;
      color:#111827;
    }

    /* grid del formulario */
    .grid{
      display:grid;
      gap:14px;
      grid-template-columns:repeat(12,1fr);
    }
    /* columnas por defecto (desktop) */
    .col-6{grid-column:span 6}
    .col-4{grid-column:span 4}
    .col-3{grid-column:span 3}
    .col-12{grid-column:span 12}

    /* inputs */
    .field label{
      display:block;
      font-size:13px;
      color:var(--muted);
      margin:0 0 6px 2px;
    }
    .control{
      width:100%;
      border:1px solid var(--border);
      border-radius:10px;
      padding:11px 12px;
      font-size:15px;
      background:#fff;
      transition:border-color .15s, box-shadow .15s;
    }
    .control:focus{
      outline:none;
      box-shadow:0 0 0 var(--focus);
      border-color:#c7b7f1;
    }
    textarea.control{min-height:92px;resize:vertical}

    /* botones */
    .actions{
      display:flex;
      gap:10px;
      justify-content:flex-end;
      margin-top:8px;
    }
    .btn{
      appearance:none; border:0; cursor:pointer;
      padding:11px 16px; border-radius:10px; font-weight:600; font-size:14px;
    }
    .btn-primary{background:var(--brand);color:#fff}
    .btn-secondary{background:#eef2ff;color:#4338ca}

    /* responsive */
    @media (max-width:960px){
      .col-6{grid-column:span 6}
      .col-4{grid-column:span 6}
      .col-3{grid-column:span 6}
    }
    @media (max-width:640px){
      .wrap{padding:16px}
      .grid{gap:12px}
      .col-6,.col-4,.col-3,.col-12{grid-column:span 12}
      .btn{width:100%}
      .actions{flex-direction:column}
    }
  </style>
</head>
<body>
  <main class="wrap">
    <section class="header">
      <h1>Módulo: Registro nueva solicitud de servicio de pulverización con drones</h1>
      <p>Formulario limpio, accesible y listo para guardar.</p>
    </section>

    <section class="card">
      <h2>Completa el formulario</h2>

      <form method="post" action="#">
        <div class="grid">

          <!-- Nombre del productor -->
          <div class="field col-6">
            <label for="nombre">Nombre del productor *</label>
            <input class="control" type="text" id="nombre" name="nombre" placeholder="Juan Pérez" required>
          </div>

          <!-- Representante -->
          <div class="field col-6">
            <label for="representante">¿Habrá representante en la finca? *</label>
            <select class="control" id="representante" name="representante" required>
              <option value="">Seleccionar</option>
              <option value="si">Sí</option>
              <option value="no">No</option>
            </select>
          </div>

          <!-- Riesgos / entorno -->
          <div class="field col-4">
            <label for="linea_tension">¿Líneas de media/alta tensión a &lt; 30m? *</label>
            <select class="control" id="linea_tension" name="linea_tension" required>
              <option value="">Seleccionar</option>
              <option value="si">Sí</option>
              <option value="no">No</option>
            </select>
          </div>

          <div class="field col-4">
            <label for="zona_restringida">¿A &lt; 3km de aeropuerto o zona restringida? *</label>
            <select class="control" id="zona_restringida" name="zona_restringida" required>
              <option value="">Seleccionar</option>
              <option value="si">Sí</option>
              <option value="no">No</option>
            </select>
          </div>

          <div class="field col-4">
            <label for="area_despegue">¿Área de despegue apropiada? *</label>
            <select class="control" id="area_despegue" name="area_despegue" required>
              <option value="">Seleccionar</option>
              <option value="si">Sí</option>
              <option value="no">No</option>
            </select>
          </div>

          <!-- Servicios disponibles -->
          <div class="field col-4">
            <label for="corriente_electrica">¿Disponibilidad de corriente eléctrica? *</label>
            <select class="control" id="corriente_electrica" name="corriente_electrica" required>
              <option value="">Seleccionar</option>
              <option value="si">Sí</option>
              <option value="no">No</option>
            </select>
          </div>

          <div class="field col-4">
            <label for="agua_potable">¿Disponibilidad de agua potable? *</label>
            <select class="control" id="agua_potable" name="agua_potable" required>
              <option value="">Seleccionar</option>
              <option value="si">Sí</option>
              <option value="no">No</option>
            </select>
          </div>

          <div class="field col-4">
            <label for="libre_obstaculos">¿Cuarteles libres de obstáculos? *</label>
            <select class="control" id="libre_obstaculos" name="libre_obstaculos" required>
              <option value="">Seleccionar</option>
              <option value="si">Sí</option>
              <option value="no">No</option>
            </select>
          </div>

          <!-- Superficie -->
          <div class="field col-3">
            <label for="superficie_ha">Superficie a pulverizar (ha) *</label>
            <input class="control" type="number" id="superficie_ha" name="superficie_ha" min="0" step="0.01" placeholder="20" required>
          </div>

          <!-- Método de pago (estático simple) -->
          <div class="field col-3">
            <label for="forma_pago_id">Método de pago *</label>
            <select class="control" id="forma_pago_id" name="forma_pago_id" required>
              <option value="">Seleccionar</option>
              <option value="4">E-cheq</option>
              <option value="5">Transferencia bancaria</option>
              <option value="6">Descuento por cooperativa</option>
            </select>
          </div>

          <!-- Motivo del servicio (estático simple) -->
          <div class="field col-3">
            <label for="patologia_id">Motivo del servicio *</label>
            <select class="control" id="patologia_id" name="patologia_id" required>
              <option value="">Seleccionar</option>
              <option value="1">Fertilización foliar</option>
              <option value="2">Lobesia</option>
              <option value="3">Oídio</option>
            </select>
          </div>

          <!-- Momento deseado -->
          <div class="field col-3">
            <label for="rango">Momento deseado *</label>
            <select class="control" id="rango" name="rango" required>
              <option value="">Seleccionar</option>
              <option value="octubre_q1">1ª quincena de octubre</option>
              <option value="octubre_q2">2ª quincena de octubre</option>
              <option value="noviembre_q1">1ª quincena de noviembre</option>
              <option value="noviembre_q2">2ª quincena de noviembre</option>
              <option value="diciembre_q1">1ª quincena de diciembre</option>
              <option value="diciembre_q2">2ª quincena de diciembre</option>
              <option value="enero_q1">1ª quincena de enero</option>
              <option value="enero_q2">2ª quincena de enero</option>
              <option value="febrero_q1">1ª quincena de febrero</option>
              <option value="febrero_q2">2ª quincena de febrero</option>
            </select>
          </div>

          <!-- Dirección -->
          <div class="field col-6">
            <label for="dir_provincia">Provincia *</label>
            <input class="control" type="text" id="dir_provincia" name="dir_provincia" placeholder="Provincia" required>
          </div>

          <div class="field col-6">
            <label for="dir_localidad">Localidad *</label>
            <input class="control" type="text" id="dir_localidad" name="dir_localidad" placeholder="Localidad" required>
          </div>

          <div class="field col-9">
            <label for="dir_calle">Calle *</label>
            <input class="control" type="text" id="dir_calle" name="dir_calle" placeholder="Calle" required>
          </div>

          <div class="field col-3">
            <label for="dir_numero">Número *</label>
            <input class="control" type="text" id="dir_numero" name="dir_numero" placeholder="Número" required>
          </div>

          <!-- Observaciones -->
          <div class="field col-12">
            <label for="observaciones">Observaciones</label>
            <textarea class="control" id="observaciones" name="observaciones" maxlength="233" placeholder="Escribí un comentario..."></textarea>
          </div>
        </div>

        <div class="actions">
          <button class="btn btn-secondary" type="reset">Cancelar</button>
          <button class="btn btn-primary" type="submit">Enviar</button>
        </div>
      </form>
    </section>
  </main>
</body>
</html>
