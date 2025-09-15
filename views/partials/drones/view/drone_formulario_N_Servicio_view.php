<?php

declare(strict_types=1);

// Carga inicial desde servidor para evitar problemas de timing/JS
$formasPago   = [];
$patologias   = [];
$cooperativas = [];
$productosIni = [];
$primeraPatId = null;

try {
  require_once __DIR__ . '/../../../../config.php'; // ajustá si tu ruta difiere
  // Formas de pago
  $st = $pdo->query("SELECT id, nombre FROM dron_formas_pago WHERE activo='si' ORDER BY nombre");
  if ($st) {
    $formasPago = $st->fetchAll(PDO::FETCH_ASSOC);
  }

  // Patologías
  $st = $pdo->query("SELECT id, nombre FROM dron_patologias WHERE activo='si' ORDER BY nombre");
  if ($st) {
    $patologias = $st->fetchAll(PDO::FETCH_ASSOC);
  }

  // Cooperativas
  $st = $pdo->query("SELECT usuario, id_real FROM usuarios WHERE rol='cooperativa' ORDER BY usuario");
  if ($st) {
    $cooperativas = $st->fetchAll(PDO::FETCH_ASSOC);
  }

  // Productos sugeridos de la primera patología (si existe)
  $primeraPatId = $patologias[0]['id'] ?? null;
  if ($primeraPatId) {
    $sql = "SELECT s.id, s.nombre
                FROM dron_productos_stock s
                INNER JOIN dron_productos_stock_patologias sp ON sp.producto_id = s.id
                WHERE sp.patologia_id = ? AND s.activo='si'
                ORDER BY s.nombre";
    $ps = $pdo->prepare($sql);
    $ps->execute([$primeraPatId]);
    $productosIni = $ps->fetchAll(PDO::FETCH_ASSOC);
  }
} catch (Throwable $e) {
  // Silencioso en UI; si querés ver el error, loguealo en server
  $formasPago = $formasPago ?? [];
  $patologias = $patologias ?? [];
  $cooperativas = $cooperativas ?? [];
  $productosIni = $productosIni ?? [];
}
?>


<div class="content">
  <div class="card" style="background-color:#5b21b6;">
    <h3 style="color:white;">Módulo: Registro nueva solicitud de servicio de pulverización con drones</h3>
    <p style="color:white;margin:0;">Formulario limpio, accesible y listo para guardar.</p>
  </div>

    <h4>Completa el formulario para cargar una nueva solicitud de drones</h4>

    <form id="form-solicitud" class="form-modern" novalidate>
      <div class="form-grid grid-4">

        <!-- Nombre del productor (autocomplete) -->
        <div class="input-group">
          <label for="nombre">Nombre del productor</label>
          <div class="input-icon input-icon-name">
            <input type="text" id="nombre" name="nombre" placeholder="Juan Pérez" autocomplete="off" aria-autocomplete="list" aria-controls="lista-nombres" required />
            <input type="hidden" id="productor_id_real" name="productor_id_real" />
          </div>
          <ul id="lista-nombres" class="card" role="listbox" aria-label="Coincidencias" style="margin-top:.25rem; display:none;"></ul>
        </div>

        <!-- representante -->
        <div class="input-group">
          <label for="representante">¿Podremos contar con un representante en la finca? *</label>
          <div class="input-icon">
            <select id="representante" name="representante" required aria-required="true">
              <option value="">Seleccionar</option>
              <option value="si">Si</option>
              <option value="no">No</option>
            </select>
          </div>
        </div>

        <!-- linea_tension -->
        <div class="input-group">
          <label for="linea_tension">¿Hay líneas de media/alta tensión a &lt; 30m? *</label>
          <div class="input-icon">
            <select id="linea_tension" name="linea_tension" required>
              <option value="">Seleccionar</option>
              <option value="si">Si</option>
              <option value="no">No</option>
            </select>
          </div>
        </div>

        <!-- zona_restringida -->
        <div class="input-group">
          <label for="zona_restringida">¿Está a &lt; 3km de aeropuerto o zona restringida? *</label>
          <div class="input-icon">
            <select id="zona_restringida" name="zona_restringida" required>
              <option value="">Seleccionar</option>
              <option value="si">Si</option>
              <option value="no">No</option>
            </select>
          </div>
        </div>

        <!-- corriente_electrica -->
        <div class="input-group">
          <label for="corriente_electrica">¿Disponibilidad de corriente eléctrica? *</label>
          <div class="input-icon">
            <select id="corriente_electrica" name="corriente_electrica" required>
              <option value="">Seleccionar</option>
              <option value="si">Si</option>
              <option value="no">No</option>
            </select>
          </div>
        </div>

        <!-- agua_potable -->
        <div class="input-group">
          <label for="agua_potable">¿Disponibilidad de agua potable? *</label>
          <div class="input-icon">
            <select id="agua_potable" name="agua_potable" required>
              <option value="">Seleccionar</option>
              <option value="si">Si</option>
              <option value="no">No</option>
            </select>
          </div>
        </div>

        <!-- libre_obstaculos -->
        <div class="input-group">
          <label for="libre_obstaculos">¿Cuarteles libres de obstáculos? *</label>
          <div class="input-icon">
            <select id="libre_obstaculos" name="libre_obstaculos" required>
              <option value="">Seleccionar</option>
              <option value="si">Si</option>
              <option value="no">No</option>
            </select>
          </div>
        </div>

        <!-- area_despegue -->
        <div class="input-group">
          <label for="area_despegue">¿Área de despegue apropiada? *</label>
          <div class="input-icon">
            <select id="area_despegue" name="area_despegue" required>
              <option value="">Seleccionar</option>
              <option value="si">Si</option>
              <option value="no">No</option>
            </select>
          </div>
        </div>

        <!-- superficie_ha -->
        <div class="input-group">
          <label for="superficie_ha">¿Cuántas hectáreas vamos a pulverizar? *</label>
          <div class="input-icon">
            <input type="number" id="superficie_ha" name="superficie_ha" min="0" step="0.01" placeholder="20" required />
          </div>
        </div>

        <!-- forma_pago_id (campo sencillo) -->
        <div class="input-group">
          <label for="forma_pago_id">Método de pago *</label>
          <div class="input-icon">
            <select id="forma_pago_id" name="forma_pago_id" required>
              <option value="">Seleccionar</option>
              <?php foreach ($formasPago as $fp): ?>
                <option value="<?= (int)$fp['id'] ?>"><?= htmlspecialchars($fp['nombre'], ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <!-- coop_descuento_id_real (se muestra solo si forma_pago_id = 6) -->
        <div class="input-group hidden" id="wrap-cooperativa">
          <label for="coop_descuento_id_real">Seleccionar cooperativa</label>
          <div class="input-icon">
            <select id="coop_descuento_id_real" name="coop_descuento_id_real" aria-hidden="true">
              <option value="">Seleccionar</option>
              <?php foreach ($cooperativas as $c): ?>
                <option value="<?= htmlspecialchars($c['id_real'], ENT_QUOTES, 'UTF-8') ?>">
                  <?= htmlspecialchars($c['usuario'], ENT_QUOTES, 'UTF-8') ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>


        <!-- patologia_id -->
        <div class="input-group">
          <label for="patologia_id">Motivo del servicio *</label>
          <div class="input-icon">
            <select id="patologia_id" name="patologia_id" required>
              <option value="">Seleccionar</option>
              <?php foreach ($patologias as $p): ?>
                <option value="<?= (int)$p['id'] ?>" <?= ($primeraPatId && (int)$p['id'] === (int)$primeraPatId) ? 'selected' : ''; ?>>
                  <?= htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8') ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <!-- rango -->
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

        <!-- nombre_producto (matriz por producto) -->
        <div class="input-group" style="grid-column: 1 / -1;">
          <label for="productos-grid">Productos sugeridos según patología *</label>
          <div class="input-icon">
            <div id="productos-grid" class="card tabla-card" aria-live="polite">
              <div class="tabla-wrapper">
                <table class="data-table">
                  <thead>
                    <tr>
                      <th>✔</th>
                      <th>Producto</th>
                      <th>SVE</th>
                      <th>Productor</th>
                    </tr>
                  </thead>
                  <tbody id="productos-body">
                    <?php if (!empty($productosIni)): ?>
                      <?php foreach ($productosIni as $p): $pid = (int)$p['id'];
                        $chkId = "prod_$pid"; ?>
                        <tr data-producto-id="<?= $pid ?>">
                          <td><input type="checkbox" class="chk-prod" id="<?= $chkId ?>" aria-label="Seleccionar <?= htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8') ?>"></td>
                          <td style="text-align:left;"><label for="<?= $chkId ?>"><?= htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8') ?></label></td>
                          <td><input type="radio" name="fuente_<?= $pid ?>" value="sve" disabled aria-label="Aporta SVE"></td>
                          <td><input type="radio" name="fuente_<?= $pid ?>" value="productor" disabled aria-label="Aporta productor"></td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="4" style="text-align:center;">No hay productos sugeridos para la patología seleccionada.</td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
              <p id="productos-help" style="margin:.5rem 0 0 0;">Marcá el/los productos y elegí quién los aporta por fila.</p>
            </div>
          </div>
        </div>

        <!-- dir_provincia -->
        <div class="input-group">
          <label for="dir_provincia">Provincia *</label>
          <div class="input-icon">
            <input type="text" id="dir_provincia" name="dir_provincia" placeholder="Provincia" required />
          </div>
        </div>

        <!-- dir_localidad -->
        <div class="input-group">
          <label for="dir_localidad">Localidad *</label>
          <div class="input-icon">
            <input type="text" id="dir_localidad" name="dir_localidad" placeholder="Localidad" required />
          </div>
        </div>

        <!-- dir_calle -->
        <div class="input-group">
          <label for="dir_calle">Calle *</label>
          <div class="input-icon">
            <input type="text" id="dir_calle" name="dir_calle" placeholder="Calle" required />
          </div>
        </div>

        <!-- dir_numero -->
        <div class="input-group">
          <label for="dir_numero">Número *</label>
          <div class="input-icon">
            <input type="text" id="dir_numero" name="dir_numero" placeholder="Número" required />
          </div>
        </div>

        <!-- observaciones -->
        <div class="input-group" style="grid-column: 1 / -1;">
          <label for="observaciones">Observaciones</label>
          <div class="input-icon input-icon-comment">
            <textarea id="observaciones" name="observaciones" maxlength="233" rows="3" placeholder="Escribí un comentario..."></textarea>
          </div>
        </div>

      </div>

      <!-- Botones -->
      <div class="form-buttons">
        <button class="btn btn-aceptar" type="button" id="btn-previsualizar">Previsualizar</button>
        <button class="btn btn-cancelar" type="reset" id="btn-reset">Cancelar</button>
      </div>
    </form>
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
  /* ===== RESET Y BASE MOBILE-FIRST ===== */
  *,
  *::before,
  *::after {
    box-sizing: border-box;
  }

  html,
  body {
    max-width: 100%;
    overflow-x: hidden;
    /* mata la barra lateral */
  }

  img,
  video,
  canvas,
  svg {
    max-width: 100%;
    height: auto;
  }

  .content,
  .card,
  #calendar-root {
    width: 100%;
    max-width: 100%;
    overflow: visible;
  }

  /* Inputs cómodos y 100% de ancho */
  .form-modern input,
  .form-modern select,
  .form-modern textarea {
    width: 100%;
    min-height: 42px;
  }

  /* Grilla del formulario */
  .form-grid.grid-4 {
    display: grid;
    gap: 1rem;
    grid-template-columns: 1fr;
    /* 1 columna por defecto (móvil) */
  }

  @media (min-width: 720px) {
    .form-grid.grid-4 {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }

  @media (min-width: 1100px) {
    .form-grid.grid-4 {
      grid-template-columns: repeat(4, minmax(0, 1fr));
    }
  }

  /* Evita que wrappers del framework recorten */
  .input-group,
  .form-modern .input-icon {
    width: 100%;
    min-width: 0;
  }

  /* ===== UTILIDADES DE VISIBILIDAD ===== */
  .hidden {
    display: none !important;
  }

  [hidden] {
    display: none !important;
  }

  /* =========================================================
   VISIBILIDAD DE COOPERATIVA SOLO CON CSS (usa :has)
   ========================================================= */
  #wrap-cooperativa {
    display: none !important;
  }

  /* oculto por defecto */

  @supports selector(.form-grid:has(select)) {

    /* muestra el bloque si el option seleccionado del select es "6" */
    .form-grid:has(#forma_pago_id option[value="6"]:checked) #wrap-cooperativa,
    .form-grid:has(#forma_pago_id option[value="6"]:selected) #wrap-cooperativa {
      display: block !important;
    }
  }

  #wrap-cooperativa,
  #wrap-cooperativa .input-icon,
  #wrap-cooperativa select {
    width: 100%;
  }

  /* =========================================================
   MATRIZ DE PRODUCTOS — modo tabla (desktop) + tarjetas (móvil)
   ========================================================= */

  /* Contenedor con scroll horizontal suave si hace falta */
  #productos-grid .tabla-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    overscroll-behavior-x: contain;
    margin-inline: -12px;
    /* “sangría” para que el scroll no corte bordes */
    padding-inline: 12px;
  }

  /* Tabla base */
  #productos-grid table.data-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    /* celdas estables */
    min-width: 520px;
    /* asegura columnas visibles en anchos medios */
    background: #fff;
    border-radius: 12px;
  }

  #productos-grid thead th {
    text-align: center;
    font-weight: 600;
    padding: .6rem .5rem;
    white-space: nowrap;
  }

  #productos-grid tbody td {
    padding: .6rem .5rem;
    text-align: center;
    vertical-align: middle;
    word-break: break-word;
  }

  #productos-grid tbody td:nth-child(2) {
    text-align: left;
    font-weight: 600;
  }

  /* Anchos sugeridos */
  #productos-grid th:nth-child(1),
  #productos-grid td:nth-child(1) {
    width: 44px;
  }

  #productos-grid th:nth-child(3),
  #productos-grid td:nth-child(3) {
    width: 92px;
  }

  /* SVE */
  #productos-grid th:nth-child(4),
  #productos-grid td:nth-child(4) {
    width: 120px;
  }

  /* Productor */

  /* Inputs de la matriz un touch más grandes */
  #productos-grid input[type="checkbox"],
  #productos-grid input[type="radio"] {
    inline-size: 18px;
    block-size: 18px;
  }

  /* ---------- MODO TARJETAS (teléfonos) ---------- */
  @media (max-width: 520px) {

    /* Convertimos la tabla en bloques accesibles */
    #productos-grid table.data-table,
    #productos-grid thead,
    #productos-grid tbody,
    #productos-grid th,
    #productos-grid td,
    #productos-grid tr {
      display: block;
    }

    #productos-grid thead {
      position: absolute;
      left: -9999px;
      top: -9999px;
    }

    #productos-grid tbody tr {
      border: 1px solid #e5e7eb;
      border-radius: 12px;
      padding: .75rem .75rem .5rem;
      margin-bottom: .75rem;
      background: #fff;
      box-shadow: 0 1px 2px rgba(0, 0, 0, .03);
    }

    /* cada celda se vuelve una mini-fila */
    #productos-grid tbody td {
      display: grid;
      grid-template-columns: auto 1fr;
      align-items: center;
      gap: .55rem;
      text-align: left;
      padding: .25rem 0;
    }

    /* orden lógico: check, nombre, radios */
    #productos-grid tbody td:nth-child(1) {
      order: 0;
    }

    #productos-grid tbody td:nth-child(2) {
      order: 1;
    }

    #productos-grid tbody td:nth-child(3),
    #productos-grid tbody td:nth-child(4) {
      order: 2;
    }

    /* etiquetas antes de cada radio para que se entienda como en Google Forms */
    #productos-grid tbody td:nth-child(3)::before {
      content: "SVE";
      font-size: .85rem;
      color: #6b7280;
      margin-right: .35rem;
    }

    #productos-grid tbody td:nth-child(4)::before {
      content: "Productor";
      font-size: .85rem;
      color: #6b7280;
      margin-right: .35rem;
    }
  }

  /* Ajustes de tipografía/espaciado en móvil */
  @media (max-width: 640px) {
    #productos-grid tbody td {
      font-size: .95rem;
    }
  }

  /* ===== Modal responsivo ===== */
  #modal-resumen .modal-content {
    max-width: 960px;
    width: 90vw;
  }

  /* ===== Autocomplete visual ===== */
  #lista-nombres li {
    padding: .25rem .5rem;
    cursor: pointer;
  }

  #lista-nombres li[aria-selected="true"],
  #lista-nombres li:hover {
    background: #eef2ff;
  }
</style>

<script>
  (function() {
    'use strict';

    const API_URL = '../partials/drones/controller/drone_formulario_N_Servicio_controller.php';

    /* ========= Helpers ========= */
    const byId = (id) => document.getElementById(id);
    const logGroup = (title, payload) => {
      try {
        console.group(`API ▶ ${title}`);
        console.log(payload);
        console.groupEnd();
      } catch {
        console.log(`API ▶ ${title}`, payload);
      }
    };
    const onReady = (fn) => (document.readyState === 'loading' ?
      document.addEventListener('DOMContentLoaded', fn, {
        once: true
      }) :
      fn());

    const apiGet = async (action, params = {}) => {
      const qs = new URLSearchParams({
        action,
        ...params
      });
      const res = await fetch(`${API_URL}?${qs.toString()}`, {
        headers: {
          'Accept': 'application/json'
        },
        credentials: 'same-origin',
        cache: 'no-store'
      });
      let json;
      try {
        json = await res.json();
      } catch {
        json = {
          ok: false,
          error: 'Respuesta no JSON',
          status: res.status
        };
      }
      logGroup(action, json);
      return json;
    };

    const clearSelect = (sel, placeholder = 'Seleccionar') => {
      sel.innerHTML = '';
      const opt = document.createElement('option');
      opt.value = '';
      opt.textContent = placeholder;
      sel.appendChild(opt);
    };
    const fillSelect = (sel, items, mapValue = (x) => x.id, mapText = (x) => x.nombre) => {
      clearSelect(sel);
      if (Array.isArray(items)) {
        for (const it of items) {
          const o = document.createElement('option');
          o.value = String(mapValue(it) ?? '');
          o.textContent = String(mapText(it) ?? '');
          sel.appendChild(o);
        }
      }
    };
    const hasOptions = (sel) => sel && sel.options && sel.options.length > 1;

    /* ========= Matriz ========= */
    const renderProductos = (productos = []) => {
      const tbody = byId('productos-body');
      tbody.innerHTML = '';

      if (!Array.isArray(productos) || productos.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;">No hay productos sugeridos para la patología seleccionada.</td></tr>`;
        return;
      }
      const rows = productos.map(p => {
        const pid = parseInt(p.id, 10);
        const chkId = `prod_${pid}`;
        return `
          <tr data-producto-id="${pid}">
            <td><input type="checkbox" class="chk-prod" id="${chkId}" aria-label="Seleccionar ${p.nombre}"></td>
            <td style="text-align:left;"><label for="${chkId}">${p.nombre}</label></td>
            <td><input type="radio" name="fuente_${pid}" value="sve" disabled aria-label="Aporta SVE"></td>
            <td><input type="radio" name="fuente_${pid}" value="productor" disabled aria-label="Aporta productor"></td>
          </tr>`;
      }).join('');
      tbody.innerHTML = rows;

      tbody.querySelectorAll('.chk-prod').forEach(chk => {
        chk.addEventListener('change', (e) => {
          const tr = e.target.closest('tr');
          const pid = tr.getAttribute('data-producto-id');
          const radios = tr.querySelectorAll(`input[name="fuente_${pid}"]`);
          radios.forEach(r => {
            r.disabled = !chk.checked;
            if (!chk.checked) r.checked = false;
          });
        }, {
          passive: true
        });
      });
    };

    /* ========= Init ========= */
    const init = async () => {
      const selFormaPago = byId('forma_pago_id');
      const selPat = byId('patologia_id');
      const wrapCoop = byId('wrap-cooperativa');
      const selCoop = byId('coop_descuento_id_real');

      // Si el servidor ya trajo opciones, no sobreescribimos; sólo completamos si falta
      const needFP = !hasOptions(selFormaPago);
      const needPat = !hasOptions(selPat);
      const needCoop = !hasOptions(selCoop);

      const promises = [];
      if (needFP) promises.push(apiGet('formas_pago').then(r => ({
        k: 'fp',
        r
      })));
      if (needPat) promises.push(apiGet('patologias').then(r => ({
        k: 'pat',
        r
      })));
      if (needCoop) promises.push(apiGet('cooperativas').then(r => ({
        k: 'coop',
        r
      })));

      const results = await Promise.all(promises);

      for (const it of results) {
        if (it.k === 'fp') {
          if (it.r.ok && Array.isArray(it.r.data) && it.r.data.length) {
            fillSelect(selFormaPago, it.r.data, x => x.id, x => x.nombre);
          } else {
            console.warn('formas_pago vacío desde API; se mantiene lo que vino del servidor.');
          }
        }
        if (it.k === 'pat') {
          if (it.r.ok && Array.isArray(it.r.data) && it.r.data.length) {
            fillSelect(selPat, it.r.data, x => x.id, x => x.nombre);
          }
        }
        if (it.k === 'coop') {
          if (it.r.ok && Array.isArray(it.r.data) && it.r.data.length) {
            fillSelect(selCoop, it.r.data, x => x.id_real, x => x.usuario);
          }
        }
      }

      // Visibilidad de cooperativas: 3 capas (clase, atributo hidden y style inline)
      const updateCoopVisibility = () => {
        const val = (selFormaPago.value || '').trim();
        const isCoop = Number(val) === 6;

        // 1) Clase .hidden
        wrapCoop.classList.toggle('hidden', !isCoop);

        // 2) Atributo [hidden]
        wrapCoop.hidden = !isCoop;

        // 3) Inline style como último recurso ante CSS del framework
        if (isCoop) {
          wrapCoop.style.removeProperty('display');
        } else {
          wrapCoop.style.setProperty('display', 'none', 'important');
        }

        // Accesibilidad y required
        if (isCoop) {
          selCoop.required = true;
          selCoop.removeAttribute('aria-hidden');
          // Si no hay selección y hay opciones, autoseleccionar la primera útil
          if (!selCoop.value && selCoop.options.length > 1) {
            selCoop.selectedIndex = 1;
          }
        } else {
          selCoop.required = false;
          selCoop.setAttribute('aria-hidden', 'true');
          selCoop.selectedIndex = 0;
        }
      };

      // Escuchar tanto change como input (algunos navegadores o wrappers usan uno u otro)
      selFormaPago.addEventListener('change', updateCoopVisibility, {
        passive: true
      });
      selFormaPago.addEventListener('input', updateCoopVisibility, {
        passive: true
      });

      // Estado inicial
      updateCoopVisibility();


      // Matriz dinámica al cambiar patología
      const loadProductos = async (pid) => {
        if (!pid) {
          renderProductos([]);
          return;
        }
        const r = await apiGet('productos_por_patologia', {
          patologia_id: pid
        });
        renderProductos(r.ok ? r.data : []);
      };

      // Si el servidor seleccionó una patología, usamos esa
      let pid = parseInt(selPat.value || '0', 10);
      if (!pid && hasOptions(selPat)) {
        pid = parseInt(selPat.options[1].value, 10) || 0;
        if (pid) selPat.value = String(pid);
      }
      await loadProductos(pid);

      selPat.addEventListener('change', async () => {
        const nuevo = parseInt(selPat.value || '0', 10);
        await loadProductos(nuevo);
      }, {
        passive: true
      });
    };

    onReady(init);
  })();
</script>