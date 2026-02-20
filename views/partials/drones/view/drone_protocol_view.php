<?php

declare(strict_types=1);
?>
<!-- Links CDN propio -->
<link rel="preload" href="https://framework.impulsagroup.com/assets/css/framework.css" as="style" onload="this.rel='stylesheet'">
<script defer src="https://framework.impulsagroup.com/assets/javascript/framework.js"></script>

<!-- Exportar a PDF-->
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<!-- Íconos de Material Design -->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

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
            <button type="button" id="btn-refrescar" class="btn btn-aceptar">Borrar filtro</button>
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
              <tr>
                <td colspan="4">Cargando…</td>
              </tr>
            </tbody>
          </table>
        </div>
      </aside>

      <!-- Columna derecha: 66% (Protocolo lectura) -->
      <section id="protocolo-section" class="card protocolo-card" aria-labelledby="protocolo-title">
        <div class="protocolo-header">
          <img src="../../../../assets/png/logo_con_color_original.png" alt="Logo" class="protocolo-logo" />
          <h1 id="protocolo-title" style="margin-left:56px; color: #5b21b6;">PROTOCOLO SERVICIO DE DRONE</h1>
        </div>
        <div id="protocol-health" class="muted" aria-live="polite" style="margin-top:-6px;">Verificando conexión…</div>

        <div id="protocolo-contenido" class="protocolo" hidden>
          <div class="protocolo-bloque">

            <h3 style="color: #5b21b6;">Fecha de la visita</h3>
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

            <h3 style="color: #5b21b6;">Dirección</h3>
            <div class="grid-4">
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

            <h3 id="geo-title" style="color: #5b21b6;">Datos de Geolocalización</h3>
            <div id="bloque-geo" class="grid-4">
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
                <label for="btn-maps">Abrir en Google Maps</label>
                <button type="button" id="btn-maps" class="btn btn-info" aria-label="Abrir ubicación en Google Maps" disabled aria-disabled="true">
                  Maps
                </button>
              </div>
            </div>

          </div>

          <div class="protocolo-bloque">
            <h3 style="color: #5b21b6;">Productos a utilizar</h3>
            <div class="tabla-wrapper">
              <table class="data-table" aria-label="Productos y receta">
                <thead>
                  <tr>
                    <th>Producto</th>
                    <th>Principio activo</th>
                    <th>Dosis</th>
                    <th>Orden mezcla</th>
                    <th>Notas</th>
                  </tr>
                </thead>
                <tbody id="tabla-items">
                  <tr>
                    <td colspan="5">Sin datos</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="protocolo-bloque" id="bloque-parametros">
            <h3 style="color: #5b21b6;">Parametros de vuelo</h3>
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
                <label for="pp_hectareas">Hectareas a pulverizar</label>
                <div class="input-icon input-icon-hashtag">
                  <input id="pp_hectareas" readonly>
                </div>
              </div>
            </div>

            <div class="input-group">
              <label for="pp_obs">Observaciones</label>
              <div class="input-icon input-icon-note">
                <textarea id="pp_obs" rows="2" readonly></textarea>
              </div>
            </div>

            <div class="input-group">
              <label for="pp_obs_agua">Observaciones de agua</label>
              <div class="input-icon input-icon-note">
                <textarea id="pp_obs_agua" rows="2" readonly></textarea>
              </div>
            </div>

          </div>
        </div>

        <footer class="protocolo-footer" role="contentinfo" aria-label="Acciones del protocolo">
          <div class="form-grid grid-2" style="justify-content:end; gap:.5rem;">
            <button type="button" id="btn-modificar" class="btn btn-aceptar" aria-label="Habilitar edición de protocolo">
              Modificar
            </button>
            <button type="button" id="btn-descargar" class="btn btn-info" aria-label="Descargar protocolo como imagen">
              Descargar
            </button>
          </div>
        </footer>
      </section>
    </div>
  </div>
</div>

<style>
  /* CSS mínimo para layout 33% / 66% sin romper CDN */
  .protocol-grid {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 12px;
  }

  @media (max-width: 1024px) {
    .protocol-grid {
      grid-template-columns: 1fr
    }
  }

  .muted {
    color: #64748b
  }

  .protocolo-bloque {
    margin-top: 8px
  }

  .grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px
  }

  .grid-3 {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px
  }

  .grid-4 {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
  }

  @media (max-width: 768px) {

    .grid-2,
    .grid-3,
    .grid-4 {
      grid-template-columns: 1fr
    }
  }

  /* Inputs readonly con fondo suave */
  input[readonly],
  select[readonly] {
    background: transparent;
    box-shadow: none;
  }

  /* Textareas readonly SIN fondo: el contenedor ya estiliza */
  textarea[readonly] {
    background: transparent !important;
    box-shadow: none;
    width: 100%;
    min-height: 64px;
    resize: none;
    overflow: hidden;
    white-space: pre-wrap;
    word-break: break-word;
    /* fuerza salto en palabras largas */
    line-height: 1.35;
  }

  /* Evitar FOUC del contenido dinámico */
  #protocolo-contenido[hidden] {
    display: none !important
  }

  tbody tr.is-active {
    outline: 2px solid #5b21b6
  }

  /* Encabezado con logo en protocolo (centrado real + logo mayor sin header alto) */
  .protocolo-card {
    position: relative;
  }

  .protocolo-header {
    /* ✅ Ajustá altura/espaciado acá si querés más/menos “aire” */
    min-height: 112px;
    /* <-- AJUSTABLE */
    margin-bottom: 8px;
    /* <-- AJUSTABLE */
    display: grid;
    grid-template-columns: auto 1fr auto;
    /* logo | título centrado | spacer */
    align-items: center;
    position: relative;
    gap: 12px;
  }

  .protocolo-header::after {
    content: "";
  }

  /* spacer para centrar el h3 de verdad */
  .protocolo-logo {
    width: 180px;
    /* <-- AJUSTABLE (tamaño del logo) */
    max-width: 28vw;
    height: auto;
    border-radius: 4px;
    position: relative;
    /* deja de ser absolute para no tapar el título */
  }

  .protocolo-header #protocolo-title {
    margin: 0;
    text-align: center;
    font-weight: 600;
    line-height: 1.25;
  }

  .protocolo-footer {
    margin-top: 12px;
    padding-top: 8px;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: flex-end;
  }

  /* Evitar scroll horizontal accidental en la página principal */
  .content,
  .protocolo-card {
    overflow-x: hidden;
    max-width: 100%;
  }

  /* Ajustes solo para exportación (usados en el clon) */
  @media print {
    .protocolo-header {
      min-height: 80px !important;
    }
  }

  /* ===== Listado de Servicios: mostrar 10 filas con scroll vertical ===== */
  aside .tabla-wrapper {
    --headH: 44px;
    --rowH: 40px;
    max-height: calc(var(--headH) + (var(--rowH) * 10));
    overflow-y: auto;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
  }

  /* Header pegado arriba mientras se hace scroll */
  aside .data-table thead th {
    position: sticky;
    top: 0;
    background: #fff;
    z-index: 2;
  }

  /* Ajuste suave de altura de filas para que ~40px sea realista con el framework */
  #tabla-servicios tr>td,
  #tabla-servicios tr>th {
    padding-top: .45rem;
    padding-bottom: .45rem;
  }

  /* Mejora opcional del alto mínimo en pantalla (sin afectar diseño) */
  #pp_obs[readonly],
  #pp_obs_agua[readonly] {
    min-height: 80px;
  }

  /* Export-only (cuando html2canvas clona el DOM) – asegurar desktop layout */
  @media print {
    .protocol-grid {
      grid-template-columns: 1fr 2fr !important;
    }

    .grid-2 {
      grid-template-columns: 1fr 1fr !important;
    }

    .grid-3 {
      grid-template-columns: repeat(3, 1fr) !important;
    }

    .grid-4 {
      grid-template-columns: repeat(4, 1fr) !important;
    }
  }

  /* === Ajustes visuales base === */
  .data-table {
    table-layout: auto;
    /* contenido define ancho */
    border-collapse: separate;
  }

  .data-table th {
    padding-top: .45rem;
    padding-bottom: .45rem;
  }

  .data-table td {
    word-break: break-word;
    white-space: normal;
  }

  /* Evitar “subcampo” gris: el fondo lo da el contenedor input-icon */
  .input-icon {
    background: #f8fafc;
    border-radius: 8px;
  }
  .input-icon input[readonly],
  .input-icon textarea[readonly] {
    background: transparent !important;
  }

  /* ===== Tabla Servicios (columna por contenido, centrada) ===== */
  aside .data-table th,
  aside .data-table td {
    text-align: center;
    vertical-align: middle;
    padding-left: .25rem;
    padding-right: .25rem;
    /* ↓ menos padding = menos “aire” innecesario */
  }

  aside .data-table .badge {
    display: inline-block;
    white-space: nowrap;
  }

  /* ===== Tabla Productos a utilizar ===== */
  table[aria-label="Productos y receta"] th,
  table[aria-label="Productos y receta"] td {
    text-align: center;
    vertical-align: middle;
    padding-left: .25rem;
    padding-right: .25rem;
  }

  table[aria-label="Productos y receta"] .input-icon {
    width: 100%;
    max-width: 100%;
    margin: 0 auto;
  }

  table[aria-label="Productos y receta"] input,
  table[aria-label="Productos y receta"] textarea {
    text-align: center;
  }

  /* Quitar bordes por defecto en Dosis (3), Orden mezcla (4) y Notas (5) */
  table[aria-label="Productos y receta"] tbody td:nth-child(3) .input-icon,
  table[aria-label="Productos y receta"] tbody td:nth-child(4) .input-icon,
  table[aria-label="Productos y receta"] tbody td:nth-child(5) .input-icon {
    border: none !important;
    box-shadow: none !important;
    background: transparent !important;
  }

  table[aria-label="Productos y receta"] tbody td:nth-child(3) .edit-receta,
  table[aria-label="Productos y receta"] tbody td:nth-child(4) .edit-receta,
  table[aria-label="Productos y receta"] tbody td:nth-child(5) .edit-receta {
    border: none !important;
    box-shadow: none !important;
    outline: none !important;
    background: transparent !important;
  }

  /* Al entrar en modo edición, volver a mostrar bordes en Dosis/Orden/Notas */
  #protocolo-section.editing table[aria-label="Productos y receta"] tbody td:nth-child(3) .input-icon,
  #protocolo-section.editing table[aria-label="Productos y receta"] tbody td:nth-child(4) .input-icon,
  #protocolo-section.editing table[aria-label="Productos y receta"] tbody td:nth-child(5) .input-icon {
    border: 1px solid #d1d5db !important;
    background: #fff !important;
    box-shadow: inset 0 0 0 1px rgba(0, 0, 0, .02) !important;
    border-radius: 8px !important;
  }

  #protocolo-section.editing table[aria-label="Productos y receta"] tbody td:nth-child(3) .edit-receta,
  #protocolo-section.editing table[aria-label="Productos y receta"] tbody td:nth-child(4) .edit-receta,
  #protocolo-section.editing table[aria-label="Productos y receta"] tbody td:nth-child(5) .edit-receta {
    border: 0 !important;
    /* el borde visual lo da .input-icon */
    background: transparent !important;
    outline: none !important;
  }

  /* Unificar estilo de inputs editables (sin fondo “gris azulado”) */
  .edit-receta {
    background: transparent !important;
    box-shadow: none;
  }

  /* Etiqueta de fuente debajo del nombre del producto */
  .fuente-prod {
    font-weight: 700;
    color: #5b21b6;
    margin-top: 2px;
    line-height: 1.2;
  }

  /* ocultar bloque de geolocalización cuando no hay datos */
  #bloque-geo[hidden],
  #geo-title[hidden] {
    display: none !important;
  }

  /* pequeño ajuste general de padding para alinear encabezados con celdas */
  .data-table th {
    padding-top: .45rem;
    padding-bottom: .45rem;
  }
</style>

<script>
  const TABLE_COL_MAXPX_SERV = 30; // ← ancho máx. por columna en "Servicios"
  const TABLE_COL_MAXPX_PROD = 60; // ← ancho máx. por columna en "Productos a utilizar"
  (function() {


    const API = '../partials/drones/controller/drone_protocol_controller.php';
    const $ = (sel) => document.querySelector(sel);

    const healthEl = $('#protocol-health');
    const tbodyServicios = $('#tabla-servicios');
    const contenido = $('#protocolo-contenido');
    const sectionEl = $('#protocolo-section');
    const btnDescargar = $('#btn-descargar');
    const btnModificar = $('#btn-modificar');
    const btnMaps = $('#btn-maps');

    const inputs = {
      nombre: $('#filtro_nombre'),
      estado: $('#filtro_estado')
    };

    // Escapar HTML simple para insertar texto seguro
    function escapeHtml(s) {
      const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      };
      return String(s ?? '').replace(/[&<>"']/g, ch => map[ch]);
    }

    // Formato de dosis: si decimales son 0, devolver entero; si no, conservar
    function formatDosis(v) {
      if (v === null || v === undefined || v === '') return '';
      const n = Number(v);
      if (!Number.isFinite(n)) return String(v);
      // mantener hasta 3 decimales reales; si es entero, sin decimales
      const isInteger = Math.abs(n - Math.trunc(n)) < 1e-9;
      return isInteger ? String(Math.trunc(n)) : String(+n.toFixed(3)).replace(/\.?0+$/, '');
    }

    function loadScriptOnce(src) {
      return new Promise((resolve, reject) => {
        if (document.querySelector(`script[data-src="${src}"]`)) {
          resolve();
          return;
        }
        const s = document.createElement('script');
        s.src = src;
        s.async = true;
        s.dataset.src = src;
        s.onload = () => resolve();
        s.onerror = () => reject(new Error('No se pudo cargar: ' + src));
        document.head.appendChild(s);
      });
    }

    // Bind acciones
    btnDescargar?.addEventListener('click', descargarComoPDFExacto);
    btnModificar?.addEventListener('click', onClickModificarGuardar);


    // Healthcheck inicial
    fetch(API + '?t=' + Date.now(), {
        cache: 'no-store'
      })
      .then(r => r.json())
      .then(json => {
        if (json && json.ok) {
          healthEl.textContent = 'Selecciona un servicio en el panel izquierdo';
          cargarServicios();
        } else {
          healthEl.innerHTML = '<strong style="color:#b91c1c;">No se pudo verificar la conexión</strong> ❌';
        }
      })
      .catch(e => {
        healthEl.innerHTML = '<strong style="color:#b91c1c;">Error:</strong> ' + (e && e.message ? e.message : e);
      });

    // Eventos filtros
    $('#filtros-form').addEventListener('submit', function(e) {
      e.preventDefault();
      cargarServicios();
    });
    $('#btn-limpiar')?.addEventListener('click', function() {
      inputs.nombre.value = '';
      inputs.estado.value = '';
      cargarServicios();
    });
    $('#btn-refrescar')?.addEventListener('click', cargarServicios);

    // Debounce en texto
    let to;
    inputs.nombre.addEventListener('input', function() {
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
      fetch(API + '?' + params.toString(), {
          cache: 'no-store'
        })
        .then(r => r.json())
        .then(json => {
          if (!json.ok) {
            showAlert('error', json.error || 'No se pudo obtener el listado');
            return;
          }
          renderServicios(json.data);
        })
        .catch(err => showAlert('error', err && err.message ? err.message : String(err)));
    }

    function renderServicios(items) {
      if (!items || !items.length) {
        tbodyServicios.innerHTML = '<tr><td colspan="4">Sin resultados</td></tr>';
        // intentar ajustar igualmente la estructura vacía
        const table = tbodyServicios.closest('table');
        if (table) autoFitTableByContent(table);
        return;
      }
      tbodyServicios.innerHTML = '';
      items.forEach((row) => {
        const tr = document.createElement('tr');
        tr.tabIndex = 0;
        tr.setAttribute('role', 'button');
        tr.setAttribute('aria-label', 'Ver protocolo de ' + (row.productor_nombre || 'sin nombre'));
        tr.addEventListener('click', () => seleccionar(row.id, tr));
        tr.addEventListener('keydown', (e) => {
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            seleccionar(row.id, tr);
          }
        });

        tr.innerHTML = `
        <td>${row.id ?? ''}</td>
        <td>${row.productor_nombre || ''}</td>
        <td><span class="badge ${badgeClass(row.estado)}">${row.estado || ''}</span></td>
        <td>${row.fecha_visita || ''}</td>
      `;
        tbodyServicios.appendChild(tr);
      });

      // Ajuste de columnas según contenido real (tabla Servicios)
      const table = tbodyServicios.closest('table');
      if (table) autoFitTableByContent(table, {
        maxPx: TABLE_COL_MAXPX_SERV
      });

    }

    // ===== Descarga en PDF (copia exacta de lo visible) =====
    async function descargarComoPDFExacto() {
      try {
        if (!sectionEl) {
          showAlert('error', 'No se encontró la sección a exportar.');
          return;
        }
        if (contenido.hidden) {
          showAlert('warning', 'Selecciona un protocolo para descargar.');
          return;
        }

        const SCALE = Math.max(2, window.devicePixelRatio || 1);

        // Captura EXACTA del DOM visible, respetando estilos/colores
        const canvas = await html2canvas(sectionEl, {
          backgroundColor: '#ffffff',
          scale: SCALE,
          useCORS: true,
          scrollX: 0,
          scrollY: -window.scrollY,
          onclone: (doc) => {
            // Asegurar que el contenido esté visible; ocultar el footer con botones
            const cont = doc.getElementById('protocolo-contenido');
            if (cont) cont.hidden = false;
            const footer = doc.querySelector('.protocolo-footer');
            if (footer) footer.style.display = 'none';

            // --- Fix: Textareas e inputs se pintan como texto envuelto en el CLON ---
            const style = doc.createElement('style');
            style.textContent = `
          .export-input{
            display:block; padding:5px 7px; border:1px solid #d1d5db; border-radius:8px;
            background:#f8fafc; line-height:1.3; font-size:12px; white-space:pre-wrap; word-break:break-word;
          }
          .export-area{
            display:block; padding:7px 8px; border:1px solid #d1d5db; border-radius:8px;
            background:transparent; line-height:1.3; font-size:12px; white-space:pre-wrap; word-break:break-word; min-height:56px;
          }
          #protocolo-contenido{ font-size:12px; }
          #protocolo-contenido h3{ font-size:14px; margin:6px 0; }
          #protocolo-contenido .input-group label{ font-size:12px; }
          #protocolo-contenido .data-table th,
          #protocolo-contenido .data-table td{ padding-top:.3rem; padding-bottom:.3rem; }
        `;
            doc.head.appendChild(style);

            const scope = doc.getElementById('protocolo-section') || doc;

            // Reemplazar <textarea> por bloques de texto con saltos reales
            scope.querySelectorAll('textarea').forEach((ta) => {
              const div = doc.createElement('div');
              div.className = 'export-area';
              div.textContent = ta.value || ta.textContent || '';
              div.style.width = '100%';
              ta.parentNode.replaceChild(div, ta);
            });

            // Reemplazar <input> por bloques de texto (mantiene apariencia y evita rendering plano)
            scope.querySelectorAll('input').forEach((inp) => {
              const div = doc.createElement('div');
              div.className = 'export-input';
              div.textContent = (inp.value ?? inp.getAttribute('value') ?? '').toString();
              div.style.width = '100%';
              inp.parentNode.replaceChild(div, inp);
            });
            // --- Fin Fix ---
          }
        });

        let jspdfNS = window.jspdf;
        if (!jspdfNS || !jspdfNS.jsPDF) {
          try {
            await loadScriptOnce('https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js');
          } catch (e1) {
            try {
              await loadScriptOnce('https://unpkg.com/jspdf@2.5.1/dist/jspdf.umd.min.js');
            } catch (e2) {
              // fallthrough
            }
          }
          jspdfNS = window.jspdf;
        }
        if (!jspdfNS || !jspdfNS.jsPDF) {
          showAlert('error', 'No se pudo generar el PDF: la librería jsPDF no está disponible. Verificá la carga del CDN o CSP.');
          return;
        }
        const { jsPDF } = jspdfNS;
        // Crear PDF con tamaño EXACTO al canvas (unidad en px para evitar reflow)
        const pdf = new jsPDF({
          orientation: canvas.width >= canvas.height ? 'landscape' : 'portrait',
          unit: 'px',
          format: [canvas.width, canvas.height]
        });

        // Insertar la imagen ocupando toda la página
        const imgData = canvas.toDataURL('image/jpeg', 0.98);
        pdf.addImage(imgData, 'JPEG', 0, 0, canvas.width, canvas.height, '', 'FAST');

        // Nombre de archivo: productor + fecha visita
        const productorSafe = (currentProductorName || '').trim() || 'productor';
        const fechaSafe = (currentFechaVisita || document.getElementById('pv_fecha')?.value || 'fecha').trim();
        const slug = (t) => String(t)
          .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
          .replace(/[^a-zA-Z0-9_-]/g, '_');

        pdf.save(`protocolo_${slug(productorSafe)}_${slug(fechaSafe)}.pdf`);
        showAlert('success', 'PDF descargado correctamente.');
      } catch (err) {
        showAlert('error', 'No se pudo generar el PDF: ' + (err?.message || String(err)));
      }
    }

    function updateMapsButton(lat, lng) {
      if (!btnMaps) return;
      const hasCoords = !!lat && !!lng;
      btnMaps.disabled = !hasCoords;
      btnMaps.setAttribute('aria-disabled', hasCoords ? 'false' : 'true');
      btnMaps.onclick = null;
      if (hasCoords) {
        btnMaps.onclick = () => {
          const url = `https://www.google.com/maps?q=${encodeURIComponent(String(lat))},${encodeURIComponent(String(lng))}`;
          window.open(url, '_blank', 'noopener');
        };
      }
    }

    function badgeClass(est) {
      switch (est) {
        case 'completada':
          return 'success';
        case 'procesando':
          return 'info';
        case 'aprobada_coop':
          return 'primary';
        case 'cancelada':
          return 'danger';
        default:
          return 'warning';
      }
    }

    function seleccionar(id, tr) {
      [...tbodyServicios.querySelectorAll('tr')].forEach(x => x.classList.remove('is-active'));
      tr.classList.add('is-active');
      cargarDetalle(id);
    }

    function cargarDetalle(id) {
      healthEl.textContent = 'Cargando protocolo #' + id + '…';
      const params = new URLSearchParams({
        action: 'detail',
        id: String(id)
      });
      fetch(API + '?' + params.toString(), {
          cache: 'no-store'
        })
        .then(r => r.json())
        .then(json => {
          if (!json.ok) {
            showAlert('error', json.error || 'No se pudo obtener el protocolo');
            return;
          }
          pintarDetalle(json.data);
          const prod = json.data?.solicitud?.ses_usuario || '';
          healthEl.textContent = 'Estas viendo el protocolo de la solicitud número: ' + id + (prod ? ' — Corresponde al productor: ' + prod : '');
        })
        .catch(err => showAlert('error', err && err.message ? err.message : String(err)));
    }

    function setVal(id, v) {
      const el = document.getElementById(id);
      if (!el) return;
      el.value = (v ?? '');
      // Auto-ajuste de altura si es textarea
      if (el.tagName === 'TEXTAREA') {
        el.style.height = 'auto';
        el.style.height = el.scrollHeight + 'px';
      }
    }

    function pintarDetalle(data) {
      contenido.hidden = false;
      currentSolicitudId = data?.solicitud_id || data?.solicitud?.id || null;
      // al cargar detalle, aseguramos modo lectura
      setEditMode(false);

      // drones_solicitud (solo lectura)
      const d = data.solicitud;
      setVal('pv_fecha', d.fecha_visita || '');
      setVal('pv_rango', (d.hora_visita_desde || '') + (d.hora_visita_hasta ? ' - ' + d.hora_visita_hasta : ''));

      // Guardar productor/fecha para usar en el nombre del archivo
      currentProductorName = (d.ses_usuario || d.productor_nombre || '').toString();
      currentFechaVisita = (d.fecha_visita || document.getElementById('pv_fecha')?.value || '').toString();

      setVal('pv_provincia', d.dir_provincia || '');
      setVal('pv_localidad', d.dir_localidad || '');
      setVal('pv_calle', d.dir_calle || '');
      setVal('pv_numero', d.dir_numero || '');
      setVal('pv_lat', d.ubicacion_lat || '');
      setVal('pv_lng', d.ubicacion_lng || '');

      // Guardar el id de la solicitud en data.solicitud_id para el flujo de edición
      if (!data.solicitud_id && d && typeof d === 'object') {
        data.solicitud_id = Number(data.id || 0) || null;
      }

      // Habilitar/Deshabilitar botón Maps acorde a coords
      updateMapsButton(d.ubicacion_lat, d.ubicacion_lng);
      toggleGeo(d.ubicacion_lat, d.ubicacion_lng);

      function toggleGeo(lat, lng) {
        const geo = document.getElementById('bloque-geo');
        const geoTitle = document.getElementById('geo-title');
        const has = !!lat && !!lng;
        if (geo) geo.hidden = !has;
        if (geoTitle) geoTitle.hidden = !has;
      }


      // Items + receta (ordenado por prioridad: orden_mezcla ASC) y con soporte de edición
      const tbody = document.getElementById('tabla-items');
      /** @type {Array<{receta_id:number|null, producto:string, principio:string, dosis_val:string|number, unidad:string, orden:number|string, notas:string}>} */
      const filas = [];

      if (Array.isArray(data.items) && data.items.length) {
        data.items.forEach((it) => {
          const producto = it.nombre_producto || '';
          const fuente = it.fuente || '';
          if (it.receta && it.receta.length) {
            it.receta.forEach((rc) => {
              const ordenVal = (rc.orden_mezcla === null || rc.orden_mezcla === undefined) ? 9999 : Number(rc.orden_mezcla);
              filas.push({
                receta_id: Number(rc.id) || null,
                producto,
                fuente,
                principio: rc.principio_activo || '',
                dosis_val: (rc.dosis ?? ''),
                unidad: (rc.unidad || ''),
                orden: isNaN(ordenVal) ? 9999 : ordenVal,
                notas: rc.notas || ''
              });
            });
          } else {
            filas.push({
              producto,
              fuente,
              principio: '',
              dosis: '',
              orden: 9999,
              notas: 'Sin receta cargada'
            });
          }
        });
      }


      // ordenar por orden_mezcla asc (prioridad)
      filas.sort((a, b) => (a.orden - b.orden));

      if (!filas.length) {
        tbody.innerHTML = '<tr><td colspan="5">Sin datos</td></tr>';
      } else {
        tbody.innerHTML = filas.map((f, idx) => `
          <tr data-row="${idx}">
            <td>
              <div>${f.producto || ''}</div>
              ${f.fuente ? `<div class="fuente-prod">${escapeHtml(f.fuente)}</div>` : ''}
            </td>
            <td>${f.principio || ''}</td>
            <td>
              <div class="input-icon input-icon-lab">
                <input 
                  type="number" step="0.001" inputmode="decimal"
                  class="edit-receta" data-field="dosis" data-receta-id="${f.receta_id ?? ''}"
                  value="${formatDosis(f.dosis_val).replace(/"/g,'&quot;')}" 
                  readonly
                />
              </div>
              <small class="muted">${f.unidad || ''}</small>
            </td>
            <td>
              <div class="input-icon input-icon-hashtag center">
                <input 
                  type="number" step="1"
                  class="edit-receta" data-field="orden_mezcla" data-receta-id="${f.receta_id ?? ''}"
                  value="${(f.orden===9999 || Number.isNaN(Number(f.orden))) ? '' : f.orden}" 
                  readonly
                />
              </div>
            </td>
            <td>
              <div class="input-icon input-icon-note">
                <textarea rows="1" class="edit-receta" data-field="notas" data-receta-id="${f.receta_id ?? ''}" readonly>${(f.notas||'')}</textarea>
              </div>
            </td>

          </tr>
        `).join('');
      }

      // Ajuste de columnas de la tabla de productos según contenido
      const tablaProductos = document.querySelector('#tabla-items')?.closest('table');
      if (tablaProductos) autoFitTableByContent(tablaProductos, {
        minEmptyPx: 56,
        paddingPx: 14,
        maxPx: TABLE_COL_MAXPX_PROD
      });

      // parámetros
      const p = data.parametros || {};
      setVal('pp_volumen', p.volumen_ha ?? '');
      setVal('pp_velocidad', p.velocidad_vuelo ?? '');
      setVal('pp_alto', p.alto_vuelo ?? '');
      setVal('pp_ancho', p.ancho_pasada ?? '');
      setVal('pp_gota', p.tamano_gota ?? '');
      // nuevo: hectáreas viene desde la solicitud (d)
      setVal('pp_hectareas', (data.solicitud?.superficie_ha ?? ''));
      setVal('pp_obs', p.observaciones || '');
      // nuevo: observaciones de agua (parametros)
      setVal('pp_obs_agua', p.observaciones_agua || '');
    }

    // ===== Edición =====
    let editMode = false;
    let currentSolicitudId = null;
    // Datos para nombre de archivo (productor y fecha)
    let currentProductorName = '';
    let currentFechaVisita = '';

    function setEditMode(on) {
      editMode = !!on;

      // Toggle clase de edición en el contenedor para que el CSS muestre/oculte bordes
      if (sectionEl) {
        if (editMode) sectionEl.classList.add('editing');
        else sectionEl.classList.remove('editing');
      }

      const recInputs = document.querySelectorAll('.edit-receta');
      recInputs.forEach(el => {
        if (editMode) {
          el.removeAttribute('readonly');
        } else {
          el.setAttribute('readonly', 'readonly');
        }
      });

      // Habilitar/Deshabilitar campos de parámetros
      const paramIds = ['pp_volumen', 'pp_velocidad', 'pp_alto', 'pp_ancho', 'pp_gota', 'pp_hectareas', 'pp_obs'];
      paramIds.forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        if (editMode) el.removeAttribute('readonly');
        else el.setAttribute('readonly', 'readonly');
      });

      // UX: ajustar altura de textareas al habilitar/deshabilitar
      document.querySelectorAll('textarea').forEach(ta => {
        ta.style.height = 'auto';
        ta.style.height = ta.scrollHeight + 'px';
      });

      // Botón
      if (btnModificar) btnModificar.textContent = editMode ? 'Guardar' : 'Modificar';
    }


    function onClickModificarGuardar() {
      if (!currentSolicitudId) {
        showAlert('warning', 'Seleccioná un servicio antes de modificar.');
        return;
      }
      if (!editMode) {
        setEditMode(true);
        showAlert('info', 'Edición habilitada. Modificá y luego presioná Guardar.');
        return;
      }
      // Guardar
      guardarCambios()
        .then(() => {
          setEditMode(false);
          // recargar para normalizar datos
          cargarDetalle(currentSolicitudId);
        })
        .catch(err => {
          showAlert('error', err?.message || String(err));
        });
    }

    async function guardarCambios() {
      // 1) Recetas
      const recetaRows = Array.from(document.querySelectorAll('.edit-receta'));
      /** @type {{receta_id:number, dosis:string, orden_mezcla:string|number, notas:string}[]} */
      const updatesMap = new Map();
      recetaRows.forEach(el => {
        const id = Number(el.getAttribute('data-receta-id') || '0');
        const field = el.getAttribute('data-field');
        if (!id || !field) return;
        const o = updatesMap.get(id) || {
          receta_id: id,
          dosis: null,
          orden_mezcla: null,
          notas: null
        };
        if (field === 'dosis') o.dosis = el.value === '' ? null : el.value;
        if (field === 'orden_mezcla') o.orden_mezcla = el.value === '' ? null : Number(el.value);
        if (field === 'notas') o.notas = el.value === '' ? null : el.value;
        updatesMap.set(id, o);
      });
      const recetasPayload = Array.from(updatesMap.values());

      // 2) Parámetros
      const p = {
        volumen_ha: valOrNull('pp_volumen'),
        velocidad_vuelo: valOrNull('pp_velocidad'),
        alto_vuelo: valOrNull('pp_alto'),
        ancho_pasada: valOrNull('pp_ancho'),
        tamano_gota: valOrNull('pp_gota'),
        observaciones: valOrNull('pp_obs'),
        superficie_ha: valOrNull('pp_hectareas')
      };

      // Llamados
      if (recetasPayload.length) {
        const r1 = await fetch(API + '?action=update_recetas', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            recetas: recetasPayload
          })
        }).then(r => r.json());
        if (!r1?.ok) throw new Error(r1?.error || 'No se pudieron actualizar las recetas');
      }

      const r2 = await fetch(API + '?action=update_parametros', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          solicitud_id: currentSolicitudId,
          parametros: p
        })
      }).then(r => r.json());
      if (!r2?.ok) throw new Error(r2?.error || 'No se pudieron actualizar los parámetros');

      showAlert('success', 'Cambios guardados correctamente.');
    }

    function valOrNull(id) {
      const el = document.getElementById(id);
      if (!el) return null;
      const v = (el.value ?? '').toString().trim();
      return v === '' ? null : v;
    }

  })();

  /**
   * Ajusta anchos de columnas de una tabla según el contenido visible.
   * - Si TODA la columna está vacía => usa un mínimo (minEmptyPx).
   * - Si tiene texto => fija ancho al máximo contenido observado + padding.
   * No requiere <colgroup>; aplica width en px a <th> y <td>.
   * @param {HTMLTableElement} tableEl
   * @param {{minEmptyPx?:number, paddingPx?:number, maxPx?:number}} opts
   */
  function autoFitTableByContent(tableEl, opts = {}) {
    if (!tableEl) return;
    const minEmptyPx = Number.isFinite(opts.minEmptyPx) ? opts.minEmptyPx : 56;
    const paddingPx = Number.isFinite(opts.paddingPx) ? opts.paddingPx : 16;
    const maxPx = Number.isFinite(opts.maxPx) ? opts.maxPx : 560;

    const thead = tableEl.tHead;
    const tbody = tableEl.tBodies && tableEl.tBodies[0];
    if (!thead || !tbody) return;

    const ths = Array.from(thead.rows[0]?.cells || []);
    const rows = Array.from(tbody.rows || []);
    const colCount = ths.length;

    // Helper para medir texto con mismo font del TH
    const measurer = (function() {
      const canvas = document.createElement('canvas');
      const ctx = canvas.getContext('2d');

      function getFont(el) {
        const cs = window.getComputedStyle(el);
        return `${cs.fontStyle} ${cs.fontVariant} ${cs.fontWeight} ${cs.fontSize}/${cs.lineHeight} ${cs.fontFamily}`;
      }
      return function measure(text, refEl) {
        const t = (text ?? '').toString();
        if (!ctx) return t.length * 8; // fallback tosco
        ctx.font = getFont(refEl || tableEl);
        const m = ctx.measureText(t);
        // sumar algo de holgura
        return Math.ceil(m.width);
      };
    })();

    for (let c = 0; c < colCount; c++) {
      const hdr = ths[c];
      let maxWidth = measurer(hdr?.innerText || '', hdr);
      let allEmpty = true;

      rows.forEach(tr => {
        const td = tr.cells[c];
        if (!td) return;

        // tomar valor visible prioritario (inputs/textareas), si no, texto
        let val = '';
        const input = td.querySelector('input');
        const ta = td.querySelector('textarea');
        if (input) val = input.value ?? input.getAttribute('value') ?? '';
        else if (ta) val = ta.value ?? ta.textContent ?? '';
        else val = td.innerText ?? '';

        const trimmed = (val || '').toString().trim();
        if (trimmed !== '') allEmpty = false;

        // medir texto (limitando por maxPx posteriormente)
        const w = measurer(trimmed, td);
        if (w > maxWidth) maxWidth = w;
      });

      const finalWidth = Math.min(allEmpty ? minEmptyPx : (maxWidth + paddingPx), maxPx);

      // aplicar ancho a header y celdas
      if (hdr) {
        hdr.style.width = finalWidth + 'px';
        hdr.style.maxWidth = finalWidth + 'px';
      }
      rows.forEach(tr => {
        const td = tr.cells[c];
        if (!td) return;
        td.style.width = finalWidth + 'px';
        td.style.maxWidth = finalWidth + 'px';
      });
    }
  }
</script>
