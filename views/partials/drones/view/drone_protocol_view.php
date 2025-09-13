<?php
declare(strict_types=1);
?>
<link rel="preload" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css" as="style" onload="this.rel='stylesheet'">
<script defer src="https://www.fernandosalguero.com/cdn/assets/javascript/framework.js"></script>

<div class="content">
  <!-- Encabezado con filtros -->
  <div class="card" style="background-color:#5b21b6;">
    <h3 style="color:white;">Buscar protocolos</h3>

    <form id="filtros-form" class="filtros" aria-label="Filtros de búsqueda" style="margin-top:10px;">
      <div class="grid-3">
        <div class="input-group">
          <label for="filtro_nombre" style="color:#fff;">Nombre del productor</label>
          <div class="input-icon input-icon-search">
            <input type="text" id="filtro_nombre" name="nombre" placeholder="Buscar por nombre" autocomplete="off" />
          </div>
        </div>
        <div class="input-group">
          <label for="filtro_estado" style="color:#fff;">Estado</label>
          <div class="input-icon input-icon-filter">
            <select id="filtro_estado" name="estado" aria-label="Filtrar por estado">
              <option value="">Todos</option>
              <option value="ingresada">Ingresada</option>
              <option value="procesando">Procesando</option>
              <option value="aprobada_coop">Aprobada coop</option>
              <option value="cancelada">Cancelada</option>
              <option value="completada">Completada</option>
            </select>
          </div>
        </div>
        <div class="input-group" style="align-self:end;">
          <div class="form-grid grid-3" style="gap:.5rem;">
            <button type="submit" class="btn btn-info">Aplicar</button>
            <button type="button" id="btn-limpiar" class="btn btn-cancelar">Limpiar</button>
            <button type="button" id="btn-refrescar" class="btn btn-aceptar">Refrescar</button>
          </div>
        </div>
      </div>
    </form>
  </div>

  <!-- Layout 33% / 66% -->
  <div class="card" style="padding:0;border:none;background:transparent;box-shadow:none !important;">
    <div class="protocol-grid">
      <!-- Columna izquierda: 33% (solo listado) -->
      <aside class="card" aria-labelledby="listado-title">
        <h3 id="listado-title">Servicios</h3>
        <div class="tabla-wrapper" style="margin-top:10px;">
          <table class="data-table" aria-describedby="listado-title">
            <thead>
              <tr>
                <th>#</th>
                <th>Productor</th>
                <th>Estado</th>
                <th>Fecha</th>
              </tr>
            </thead>
            <tbody id="tabla-servicios">
              <tr><td colspan="4">Cargando…</td></tr>
            </tbody>
          </table>
        </div>
      </aside>

      <!-- Columna derecha: 66% (Protocolo lectura) -->
      <section class="card protocolo-card" aria-labelledby="protocolo-title">
        <div class="protocolo-header">
          <img src="../../../../assets/png/logo_con_color_original.png" alt="Logo" class="protocolo-logo" />
          <h3 id="protocolo-title" style="margin-left:56px;">Protocolo</h3>
        </div>
        <div id="protocol-health" class="muted" aria-live="polite" style="margin-top:-6px;">Verificando conexión…</div>

        <div id="protocolo-contenido" class="protocolo" hidden>
          <div class="protocolo-bloque">
            <h4>Datos de visita</h4>
            <div class="grid-2">
              <div class="input-group">
                <label for="pv_fecha">Fecha visita</label>
                <div class="input-icon input-icon-calendar">
                  <input id="pv_fecha" readonly>
                </div>
              </div>
              <div class="input-group">
                <label for="pv_rango">Horario</label>
                <div class="input-icon input-icon-clock">
                  <input id="pv_rango" readonly>
                </div>
              </div>
            </div>
            <div class="grid-2">
              <div class="input-group">
                <label for="pv_provincia">Provincia</label>
                <div class="input-icon input-icon-location">
                  <input id="pv_provincia" readonly>
                </div>
              </div>
              <div class="input-group">
                <label for="pv_localidad">Localidad</label>
                <div class="input-icon input-icon-location">
                  <input id="pv_localidad" readonly>
                </div>
              </div>
            </div>
            <div class="grid-2">
              <div class="input-group">
                <label for="pv_calle">Calle</label>
                <div class="input-icon input-icon-home">
                  <input id="pv_calle" readonly>
                </div>
              </div>
              <div class="input-group">
                <label for="pv_numero">Número</label>
                <div class="input-icon input-icon-hashtag">
                  <input id="pv_numero" readonly>
                </div>
              </div>
            </div>
            <div class="grid-3">
              <div class="input-group">
                <label for="pv_lat">Lat</label>
                <div class="input-icon input-icon-gps">
                  <input id="pv_lat" readonly>
                </div>
              </div>
              <div class="input-group">
                <label for="pv_lng">Lng</label>
                <div class="input-icon input-icon-compass">
                  <input id="pv_lng" readonly>
                </div>
              </div>
              <div class="input-group">
                <label for="pv_usuario">Usuario</label>
                <div class="input-icon input-icon-user">
                  <input id="pv_usuario" readonly>
                </div>
              </div>
            </div>
            <div class="grid-2">
              <div class="input-group">
                <label for="pv_estado">Estado</label>
                <div class="input-icon input-icon-flag">
                  <input id="pv_estado" readonly>
                </div>
              </div>
              <div class="input-group">
                <label for="pv_motivo">Motivo cancelación</label>
                <div class="input-icon input-icon-warning">
                  <input id="pv_motivo" readonly>
                </div>
              </div>
            </div>
          </div>

          <div class="protocolo-bloque">
            <h4>Productos y Receta</h4>
            <div class="tabla-wrapper">
              <table class="data-table" aria-label="Productos y receta">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Producto</th>
                    <th>Principio activo</th>
                    <th>Dosis</th>
                    <th>Orden mezcla</th>
                    <th>Notas</th>
                  </tr>
                </thead>
                <tbody id="tabla-items">
                  <tr><td colspan="6">Sin datos</td></tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="protocolo-bloque">
            <h4>Parámetros de vuelo</h4>
            <div class="grid-3">
              <div class="input-group">
                <label for="pp_volumen">Volumen/ha</label>
                <div class="input-icon input-icon-droplet">
                  <input id="pp_volumen" readonly>
                </div>
              </div>
              <div class="input-group">
                <label for="pp_velocidad">Velocidad vuelo</label>
                <div class="input-icon input-icon-speed">
                  <input id="pp_velocidad" readonly>
                </div>
              </div>
              <div class="input-group">
                <label for="pp_alto">Alto vuelo</label>
                <div class="input-icon input-icon-arrow-up">
                  <input id="pp_alto" readonly>
                </div>
              </div>
            </div>
            <div class="grid-3">
              <div class="input-group">
                <label for="pp_ancho">Ancho pasada</label>
                <div class="input-icon input-icon-arrows">
                  <input id="pp_ancho" readonly>
                </div>
              </div>
              <div class="input-group">
                <label for="pp_gota">Tamaño de gota</label>
                <div class="input-icon input-icon-droplet">
                  <input id="pp_gota" readonly>
                </div>
              </div>
              <div class="input-group">
                <label for="pp_obs">Observaciones</label>
                <div class="input-icon input-icon-note">
                  <input id="pp_obs" readonly>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</div>

<style>
  /* CSS mínimo para layout 33% / 66% sin romper CDN */
  .protocol-grid{display:grid;grid-template-columns:1fr 2fr;gap:12px;}
  @media (max-width: 1024px){.protocol-grid{grid-template-columns:1fr}}
  .muted{color:#64748b}
  .protocolo-bloque{margin-top:8px}
  .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:8px}
  .grid-3{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}
  @media (max-width: 768px){.grid-2,.grid-3{grid-template-columns:1fr}}
  [readonly]{background:#f8fafc}
  /* Evitar FOUC del contenido dinámico */
  #protocolo-contenido[hidden]{display:none!important}
  tbody tr.is-active{outline:2px solid #5b21b6}

  /* Encabezado con logo en protocolo */
  .protocolo-card{position:relative;}
  .protocolo-header{position:relative;min-height:148px;margin-bottom:6px;}
  .protocolo-logo{position:absolute;left:0;top:0;width:144px;height:auto;border-radius:4px;}
</style>

<script>
(function () {
  const API = '../partials/drones/controller/drone_protocol_controller.php';
  const $ = (sel) => document.querySelector(sel);

  const healthEl = $('#protocol-health');
  const tbodyServicios = $('#tabla-servicios');
  const contenido = $('#protocolo-contenido');

  const inputs = {
    nombre: $('#filtro_nombre'),
    estado: $('#filtro_estado')
  };

  // Healthcheck inicial
  fetch(API + '?t=' + Date.now(), { cache: 'no-store' })
    .then(r => r.json())
    .then(json => {
      if (json && json.ok) {
        healthEl.textContent = 'Controlador y modelo conectados correctamente ✅';
        cargarServicios();
      } else {
        healthEl.innerHTML = '<strong style="color:#b91c1c;">No se pudo verificar la conexión</strong> ❌';
      }
    })
    .catch(e => {
      healthEl.innerHTML = '<strong style="color:#b91c1c;">Error:</strong> ' + (e && e.message ? e.message : e);
    });

  // Eventos filtros
  $('#filtros-form').addEventListener('submit', function (e) {
    e.preventDefault();
    cargarServicios();
  });
  $('#btn-limpiar')?.addEventListener('click', function(){
    inputs.nombre.value = '';
    inputs.estado.value = '';
    cargarServicios();
  });
  $('#btn-refrescar')?.addEventListener('click', cargarServicios);

  // Debounce en texto
  let to;
  inputs.nombre.addEventListener('input', function(){
    clearTimeout(to);
    to = setTimeout(cargarServicios, 350);
  });

  function cargarServicios() {
    tbodyServicios.innerHTML = '<tr><td colspan="4">Cargando…</td></tr>';
    const params = new URLSearchParams({
      action: 'list',
      nombre: inputs.nombre.value || '',
      estado: inputs.estado.value || ''
    });
    fetch(API + '?' + params.toString(), { cache: 'no-store' })
      .then(r => r.json())
      .then(json => {
        if (!json.ok) { showAlert('error', json.error || 'No se pudo obtener el listado'); return; }
        renderServicios(json.data);
      })
      .catch(err => showAlert('error', err && err.message ? err.message : String(err)));
  }

  function renderServicios(items) {
    if (!items || !items.length) {
      tbodyServicios.innerHTML = '<tr><td colspan="4">Sin resultados</td></tr>';
      return;
    }
    tbodyServicios.innerHTML = '';
    items.forEach((row) => {
      const tr = document.createElement('tr');
      tr.tabIndex = 0;
      tr.setAttribute('role','button');
      tr.setAttribute('aria-label', 'Ver protocolo de ' + (row.productor_nombre || 'sin nombre'));
      tr.addEventListener('click', () => seleccionar(row.id, tr));
      tr.addEventListener('keydown', (e)=>{ if(e.key==='Enter' || e.key===' '){ e.preventDefault(); seleccionar(row.id, tr);} });

      tr.innerHTML = `
        <td>${row.id}</td>
        <td>${row.productor_nombre || ''}</td>
        <td><span class="badge ${badgeClass(row.estado)}">${row.estado}</span></td>
        <td>${row.fecha_visita || ''}</td>
      `;
      tbodyServicios.appendChild(tr);
    });
  }

  function badgeClass(est){
    switch(est){
      case 'completada': return 'success';
      case 'procesando': return 'info';
      case 'aprobada_coop': return 'primary';
      case 'cancelada': return 'danger';
      default: return 'warning';
    }
  }

  function seleccionar(id, tr) {
    [...tbodyServicios.querySelectorAll('tr')].forEach(x=>x.classList.remove('is-active'));
    tr.classList.add('is-active');
    cargarDetalle(id);
  }

  function cargarDetalle(id) {
    healthEl.textContent = 'Cargando protocolo #' + id + '…';
    const params = new URLSearchParams({ action: 'detail', id: String(id) });
    fetch(API + '?' + params.toString(), { cache: 'no-store' })
      .then(r => r.json())
      .then(json => {
        if (!json.ok) { showAlert('error', json.error || 'No se pudo obtener el protocolo'); return; }
        pintarDetalle(json.data);
        healthEl.textContent = 'Protocolo cargado';
      })
      .catch(err => showAlert('error', err && err.message ? err.message : String(err)));
  }

  function setVal(id, v){ const el = document.getElementById(id); if (el) el.value = v ?? ''; }

  function pintarDetalle(data) {
    contenido.hidden = false;

    // drones_solicitud (solo lectura)
    const d = data.solicitud;
    setVal('pv_fecha', d.fecha_visita || '');
    setVal('pv_rango', (d.hora_visita_desde || '') + (d.hora_visita_hasta ? ' - ' + d.hora_visita_hasta : ''));
    setVal('pv_provincia', d.dir_provincia || '');
    setVal('pv_localidad', d.dir_localidad || '');
    setVal('pv_calle', d.dir_calle || '');
    setVal('pv_numero', d.dir_numero || '');
    setVal('pv_lat', d.ubicacion_lat || '');
    setVal('pv_lng', d.ubicacion_lng || '');
    setVal('pv_usuario', d.ses_usuario || '');
    setVal('pv_estado', d.estado || '');
    setVal('pv_motivo', d.motivo_cancelacion || '');

    // Items + receta
    const tbody = document.getElementById('tabla-items');
    const rows = [];
    if (Array.isArray(data.items) && data.items.length) {
      data.items.forEach((it, idx) => {
        if (it.receta && it.receta.length) {
          it.receta.forEach((rc, j) => {
            rows.push(`
              <tr>
                <td>${idx + 1}${it.receta.length > 1 ? '.'+(j+1) : ''}</td>
                <td>${j===0 ? (it.nombre_producto || '') : ''}</td>
                <td>${rc.principio_activo || ''}</td>
                <td>${rc.dosis ?? ''} ${rc.unidad || ''}</td>
                <td>${rc.orden_mezcla ?? ''}</td>
                <td>${rc.notas || ''}</td>
              </tr>
            `);
          });
        } else {
          rows.push(`
            <tr>
              <td>${idx + 1}</td>
              <td>${it.nombre_producto || ''}</td>
              <td colspan="4" class="muted">Sin receta cargada</td>
            </tr>
          `);
        }
      });
    }
    tbody.innerHTML = rows.length ? rows.join('') : '<tr><td colspan="6">Sin datos</td></tr>';

    // parámetros
    const p = data.parametros || {};
    setVal('pp_volumen', p.volumen_ha ?? '');
    setVal('pp_velocidad', p.velocidad_vuelo ?? '');
    setVal('pp_alto', p.alto_vuelo ?? '');
    setVal('pp_ancho', p.ancho_pasada ?? '');
    setVal('pp_gota', p.tamano_gota ?? '');
    setVal('pp_obs', p.observaciones || '');
  }
})();
</script>
