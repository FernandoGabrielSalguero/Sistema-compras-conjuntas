<?php

declare(strict_types=1);
?>
<!-- Links CDN propio -->
<link rel="preload" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css" as="style" onload="this.rel='stylesheet'">
<script defer src="https://www.fernandosalguero.com/cdn/assets/javascript/framework.js"></script>

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

            <h3 style="color: #5b21b6;">Datos de Geolocalización</h3>
            <div class="grid-4">
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
                <label for="pv_usuario">Productor</label>
                <div class="input-icon input-icon-user">
                  <input id="pv_usuario" readonly>
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

          <div class="protocolo-bloque">
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
            <button type="button" id="btn-modificar" class="btn btn-primary" aria-label="Habilitar edición de protocolo">
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
    background: #f8fafc;
  }

  /* Textareas readonly SIN fondo: el contenedor ya estiliza */
  textarea[readonly] {
    background: transparent !important;
    width: 100%;
    min-height: 64px;
    resize: none;
    overflow: hidden;
    white-space: pre-wrap;
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

  /* (Opcional) Alinear números en Orden mezcla */
  .data-table td:nth-child(4),
  .data-table th:nth-child(4) {
    text-align: center;
  }

  /* Ajustes solo para exportación (usados en el clon) */
  @media print {
    .protocolo-header {
      min-height: 80px !important;
    }
  }

  /* ===== Listado de Servicios: mostrar 10 filas con scroll vertical ===== */
  aside .tabla-wrapper {
    /* Altura calculada: alto del header + 10 filas */
    --headH: 44px;
    /* ajustable si tu header es más alto/bajo */
    --rowH: 40px;
    /* alto promedio de una fila (ajustable) */
    max-height: calc(var(--headH) + (var(--rowH) * 10));
    overflow-y: auto;
    /* scroll vertical solo en el listado */
    border: 1px solid #e5e7eb;
    /* opcional: delimita visualmente el área scrolleable */
    border-radius: 6px;
    /* opcional */
  }

  /* Header pegado arriba mientras se hace scroll */
  aside .data-table thead th {
    position: sticky;
    top: 0;
    background: #fff;
    /* asegura contraste sobre el contenido scrolleado */
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
</style>

<script>
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

    // Bind acciones
    btnDescargar?.addEventListener('click', descargarComoPDFUnaPagina);
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
        <td>${row.id}</td>
        <td>${row.productor_nombre || ''}</td>
        <td><span class="badge ${badgeClass(row.estado)}">${row.estado}</span></td>
        <td>${row.fecha_visita || ''}</td>
      `;
        tbodyServicios.appendChild(tr);
      });
    }

    // Funcion para descargar en PDF de una sola página 

    async function descargarComoPDFUnaPagina() {
      try {
        if (!sectionEl) {
          showAlert('error', 'No se encontró la sección a exportar.');
          return;
        }

        // helper opcional si más adelante querés reutilizar
        const A4_PX_WIDTH = 794; // 210mm a ~96dpi

        const canvas = await html2canvas(sectionEl, {
          backgroundColor: '#ffffff',
          scale: 2,
          useCORS: true,
          scrollX: 0,
          scrollY: -window.scrollY,

          // ✅ Fuerza layout de escritorio para evitar que la grilla “salte”
          // en pantallas chicas y que se corten tablas.
          windowWidth: 1400,
          windowHeight: Math.max(
            document.body.scrollHeight,
            sectionEl.scrollHeight,
            1800
          ),

          onclone: (clonedDoc) => {
            // Mostrar contenido
            const cont = clonedDoc.querySelector('#protocolo-contenido');
            if (cont) cont.hidden = false;

            // ===== Forzar lienzo A4 (ancho) =====
            // 210mm ≈ 794px @96dpi. Forzamos ese ancho para que TODO
            // el contenido se adapte al ancho de página sin cortar.
            const A4_PX = 794;

            const content = clonedDoc.querySelector('.content');
            if (content) {
              content.style.padding = '0';
              content.style.margin = '0 auto';
              content.style.width = A4_PX + 'px';
              content.style.maxWidth = A4_PX + 'px';
              content.style.boxSizing = 'border-box';
              content.style.overflow = 'visible';
            }

            const card = clonedDoc.querySelector('.protocolo-card');
            if (card) {
              card.style.boxShadow = 'none';
              card.style.border = 'none';
              card.style.borderRadius = '0';
              card.style.margin = '0';
              card.style.padding = '12px';
              card.style.width = '100%';
              card.style.maxWidth = A4_PX + 'px';
              card.style.boxSizing = 'border-box';
              card.style.overflow = 'visible';
            }

            const hdr = clonedDoc.querySelector('.protocolo-header');
            if (hdr) hdr.style.minHeight = '72px';

            // Ocultar acciones (botón Descargar) sólo en el PDF
            const footer = clonedDoc.querySelector('.protocolo-footer');
            if (footer) footer.style.display = 'none';
            const btn = clonedDoc.getElementById('btn-descargar');
            if (btn) btn.style.display = 'none';

            // Reemplazar textareas por bloques (sin fondo)
            function textareaToBlock(id) {
              const ta = clonedDoc.getElementById(id);
              if (!ta) return;
              const div = clonedDoc.createElement('div');
              div.setAttribute('data-export-from', id);
              div.style.whiteSpace = 'pre-wrap';
              div.style.lineHeight = '1.35';
              div.style.minHeight = '96px';
              div.style.width = '100%';
              div.style.background = 'transparent';
              div.textContent = ta.value || '';
              ta.parentNode.replaceChild(div, ta);
            }
            textareaToBlock('pp_obs');
            textareaToBlock('pp_obs_agua');

            // Reglas de exportación: desktop + tablas al 100% sin overflow
            const style = clonedDoc.createElement('style');
            style.textContent = `
              .protocol-grid { grid-template-columns: 1fr 2fr !important; }
              .grid-2 { grid-template-columns: 1fr 1fr !important; }
              .grid-3 { grid-template-columns: repeat(3, 1fr) !important; }
              .grid-4 { grid-template-columns: repeat(4, 1fr) !important; }
              .grid-2, .grid-3, .grid-4 { align-items: start; }
              .tabla-wrapper { overflow: visible !important; }
              table { table-layout: fixed !important; width: 100% !important; border-collapse: collapse !important; }
              th, td { word-break: break-word !important; }
              img { max-width: 100% !important; height: auto !important; }
            `;
            clonedDoc.head.appendChild(style);

            // Asegurar presencia de 'pp_hectareas'
            clonedDoc.getElementById('pp_hectareas');
          }


        });

        // Imagen del canvas
        const imgData = canvas.toDataURL('image/jpeg', 0.98);

        // Crear PDF A4 y escalar para que entre en 1 página (mantener aspecto)
        const {
          jsPDF
        } = window.jspdf;
        const pdf = new jsPDF({
          orientation: 'portrait',
          unit: 'mm',
          format: 'a4'
        });

        const pageW = pdf.internal.pageSize.getWidth();
        const pageH = pdf.internal.pageSize.getHeight();

        const margin = 4; // mm (más pequeño para aprovechar la hoja)
        const maxW = pageW - margin * 2;
        const maxH = pageH - margin * 2;

        // px -> mm (A4 width enforce: llenamos SIEMPRE el ancho imprimible)
        const px2mm = 0.264583;
        const imgWmm = canvas.width * px2mm;
        const imgHmm = canvas.height * px2mm;

        // Escalar POR ANCHO para que el contenido ocupe 100% del ancho útil
        const ratio = maxW / imgWmm;
        const w = maxW;
        const h = imgHmm * ratio;


        // Centrado horizontal y alineado ARRIBA con pequeño margen
        const x = (pageW - w) / 2;
        const y = margin;

        // Si entra en una página, normal:
        if (h <= maxH) {
          pdf.addImage(imgData, 'JPEG', x, y, w, h, '', 'FAST');
        } else {
          // Slicing vertical del canvas en varias páginas
          const pageCanvas = document.createElement('canvas');
          const pageCtx = pageCanvas.getContext('2d');

          // Escala px->mm usada para ajustar el alto de cada porción
          const scale = w / (canvas.width * 0.264583); // (mm renderizados / mm reales del canvas)
          const pagePixelHeight = Math.floor((maxH / scale) / 0.264583); // px por página visibles

          let sY = 0;
          while (sY < canvas.height) {
            const sliceH = Math.min(pagePixelHeight, canvas.height - sY);
            pageCanvas.width = canvas.width;
            pageCanvas.height = sliceH;

            pageCtx.clearRect(0, 0, pageCanvas.width, pageCanvas.height);
            pageCtx.drawImage(canvas, 0, sY, canvas.width, sliceH, 0, 0, canvas.width, sliceH);

            const sliceData = pageCanvas.toDataURL('image/jpeg', 0.98);
            const sliceHmm = sliceH * 0.264583; // px -> mm
            const sliceHmmScaled = sliceHmm * scale; // mm en el PDF con la misma escala

            // Nueva página salvo en la primera iteración
            if (sY > 0) pdf.addPage();

            pdf.addImage(sliceData, 'JPEG', x, y, w, sliceHmmScaled, '', 'FAST');
            sY += sliceH;
          }
        }

        // Tomamos productor (usuario) y fecha de la visita desde inputs
        const productor = (document.getElementById('pv_usuario')?.value || 'productor').trim();
        const fechaVisita = (document.getElementById('pv_fecha')?.value || 'fecha').trim();

        // Sanitizar para que no tenga caracteres inválidos en el nombre
        function slugify(txt) {
          return txt.normalize("NFD").replace(/[\u0300-\u036f]/g, "") // sin tildes
            .replace(/[^a-zA-Z0-9_-]/g, "_"); // solo seguro
        }

        const nombreProd = slugify(productor);
        const fecha = slugify(fechaVisita);

        // Armar nombre final
        const filename = `protocolo_${nombreProd}_${fecha}.pdf`;

        pdf.save(filename);
        showAlert('success', 'PDF generado correctamente.');
      } catch (err) {
        const msg = (err && err.message) ? err.message : String(err);
        showAlert('error', 'No se pudo generar el PDF: ' + msg);
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
          healthEl.textContent = 'Estas viendo el protocolo de la solicitud número ' + id;
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
      setVal('pv_provincia', d.dir_provincia || '');
      setVal('pv_localidad', d.dir_localidad || '');
      setVal('pv_calle', d.dir_calle || '');
      setVal('pv_numero', d.dir_numero || '');
      setVal('pv_lat', d.ubicacion_lat || '');
      setVal('pv_lng', d.ubicacion_lng || '');
      setVal('pv_usuario', d.ses_usuario || '');

      // Guardar el id de la solicitud en data.solicitud_id para el flujo de edición
      if (!data.solicitud_id && d && typeof d === 'object') {
        data.solicitud_id = Number(data.id || 0) || null;
      }

      // Habilitar/Deshabilitar botón Maps acorde a coords
      updateMapsButton(d.ubicacion_lat, d.ubicacion_lng);

      // Items + receta (ordenado por prioridad: orden_mezcla ASC) y con soporte de edición
      const tbody = document.getElementById('tabla-items');
      /** @type {Array<{receta_id:number|null, producto:string, principio:string, dosis_val:string|number, unidad:string, orden:number|string, notas:string}>} */
      const filas = [];

      if (Array.isArray(data.items) && data.items.length) {
        data.items.forEach((it) => {
          const producto = it.nombre_producto || '';
          if (it.receta && it.receta.length) {
            it.receta.forEach((rc) => {
              const ordenVal = (rc.orden_mezcla === null || rc.orden_mezcla === undefined) ? 9999 : Number(rc.orden_mezcla);
              filas.push({
                receta_id: Number(rc.id) || null,
                producto,
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
            <td>${f.producto}</td>
            <td>${f.principio}</td>
            <td>
              <div class="input-icon input-icon-lab">
                <input 
                  type="number" step="0.001" inputmode="decimal"
                  class="edit-receta" data-field="dosis" data-receta-id="${f.receta_id ?? ''}"
                  value="${String(f.dosis_val ?? '').replace(/"/g,'&quot;')}" 
                  readonly
                />
              </div>
              <small class="muted">${f.unidad || ''}</small>
            </td>
            <td style="text-align:center;">
              <input 
                type="number" step="1" class="edit-receta" data-field="orden_mezcla" data-receta-id="${f.receta_id ?? ''}"
                value="${(f.orden===9999 || Number.isNaN(Number(f.orden))) ? '' : f.orden}" 
                readonly
                style="max-width:90px;text-align:center;"
              />
            </td>
            <td>
              <textarea rows="1" class="edit-receta" data-field="notas" data-receta-id="${f.receta_id ?? ''}" readonly>${(f.notas||'')}</textarea>
            </td>
          </tr>
        `).join('');
      }


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

    function setEditMode(on) {
      editMode = !!on;
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
</script>