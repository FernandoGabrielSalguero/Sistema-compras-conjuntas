<?php
// Mostrar errores en pantalla (útil en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión y configurar parámetros de seguridad
require_once '../../middleware/authMiddleware.php';
checkAccess('sve');

// Datos del usuario en sesión
$nombre = $_SESSION['nombre'] ?? 'Sin nombre';
$correo = $_SESSION['correo'] ?? 'Sin correo';
$cuit = $_SESSION['cuit'] ?? 'Sin CUIT';
$telefono = $_SESSION['telefono'] ?? 'Sin teléfono';
$observaciones = $_SESSION['observaciones'] ?? 'Sin observaciones';

//Cargamos los operativos cerrados
$cierre_info = $_SESSION['cierre_info'] ?? null;
unset($_SESSION['cierre_info']); // Limpiamos para evitar residuos
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SVE</title>

    <!-- descargar imagen -->
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>


    <!-- Íconos de Material Design -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- Framework Success desde CDN -->
    <link rel="stylesheet" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css">
    <script src="https://www.fernandosalguero.com/cdn/assets/javascript/framework.js" defer></script>

    <style>
        /* ===== Píldoras de estado (colores de referencia) =====
     pendiente:  #f59e0b (naranja)
     en_proceso: #5b21b6 (violeta brand)
     completado: #10b981 (verde)
     cancelado:  #ef4444 (rojo)
  */
        .status-pill {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 9999px;
            font-weight: 600;
            font-size: .85rem;
            line-height: 1;
            white-space: nowrap;
        }

        .status-pendiente {
            background: #f59e0b;
            color: #fff;
        }

        .status-en_proceso {
            background: #5b21b6;
            color: #fff;
        }

        .status-completado {
            background: #10b981;
            color: #fff;
        }

        .status-cancelado {
            background: #ef4444;
            color: #fff;
        }

        #ModalEditarServicio {
            position: fixed;
            inset: 0;
            display: none;
            /* JS -> flex */
            align-items: center;
            justify-content: center;
            /* centrado */
            padding: 24px;
            background: rgba(0, 0, 0, .35);
            z-index: 10001;
        }


        /* Evita barras dentro de cada tarjeta en la grid */
        #ModalEditarServicio .modal-grid>.card {
            min-width: 0;
        }

        /* Por si aparecen textos largos en el valor derecho */
        #ModalEditarServicio .kv span:last-child {
            white-space: normal;
            word-break: break-word;
        }

        /* MODAL ancho XXL (override del framework) */
        #ModalEditarServicio .modal-content {
            width: min(1600px, calc(100vw - 48px)) !important;
            max-width: none !important;
            max-height: 90vh;
            overflow: auto;
            background: #f3f4f6;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, .2);
            border: 1px solid rgba(0, 0, 0, .06);
            margin: 0;
        }

        @media (min-width: 1280px) {
            #ModalEditarServicio .modal-content {
                min-width: 1180px;
            }

            /* 3 columnas cómodas en desktop */
        }

        /* === Grilla interna del modal (3 columnas) === */
        #ModalEditarServicio .modal-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        /* Responsivo */
        @media (max-width: 1280px) {
            #ModalEditarServicio .modal-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 720px) {
            #ModalEditarServicio .modal-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Evita scrolls dentro de cada tarjeta de la grid */
        #ModalEditarServicio .modal-grid>.card {
            min-width: 0;
        }

        /* Valores largos alineados y sin cortar */
        #ModalEditarServicio .kv span:last-child {
            text-align: right;
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        /* Header pegajoso */
        #ModalEditarServicio .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            position: sticky;
            top: 0;
            background: #fff;
            padding: 4px 0 12px;
            margin: 0 0 12px;
            z-index: 1;
        }

        /* Inputs dentro del modal: usar layout de tu CDN */
        #ModalEditarServicio .form-modern .input-group input,
        #ModalEditarServicio .form-modern .input-group select,
        #ModalEditarServicio .form-modern .input-group textarea {
            width: 100%
        }

        /* Pares clave/valor prolijos */
        .kv {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 3px 0
        }

        .kv span:first-child {
            opacity: .75;
            font-weight: 600
        }

        .kv span:last-child {
            text-align: right
        }

        /* Tablas del detalle */
        #ModalEditarServicio table {
            width: 100%
        }

        /* === Helpers de columnas dentro del modal === */
        #ModalEditarServicio .col-span-2 {
            grid-column: span 2;
            /* en la grilla de 3 columnas ocupa dos */
        }

        /* En pantallas donde el modal usa 2 o 1 columna, que ocupe el ancho completo */
        @media (max-width:1280px) {
            #ModalEditarServicio .col-span-2 {
                grid-column: 1 / -1;
            }
        }

        /* (opcional) wrapper para scroll horizontal si la tabla crece */
        #ModalEditarServicio .table-wrap {
            overflow: auto;
        }

        /* Estilo de tabla similar al framework, scopeado al modal */
        #ModalEditarServicio .table {
            width: 100%;
            border-collapse: collapse;
        }

        #ModalEditarServicio .table thead th {
            text-align: left;
            font-weight: 700;
            padding: 12px 16px;
            border-bottom: 2px solid rgba(0, 0, 0, .08);
            white-space: nowrap;
        }

        #ModalEditarServicio .table tbody td {
            padding: 12px 16px;
            border-bottom: 1px solid rgba(0, 0, 0, .06);
        }

        #ModalEditarServicio .table tbody tr:last-child td {
            border-bottom: 0;
        }

        /* --- pantalla --- */
        @media screen {
            .only-print {
                display: none !important;
            }
        }

        /* --- impresión --- */
        @media print {
            @page {
                size: A4;
                /* A4 vertical */
                margin: 16mm 14mm;
                /* márgenes cómodos */
            }

            html,
            body {
                background: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* Mostrar solo #printArea al imprimir */
            body * {
                visibility: hidden !important;
            }

            #printArea,
            #printArea * {
                visibility: visible !important;
            }

            #printArea {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            /* Cards “limpias” para impresión */
            .print-card {
                border: 1px solid #e5e7eb;
                border-radius: 10px;
                padding: 12px 14px;
                margin: 8px 0;
                background: #fff;
            }

            /* Cada .print-page fuerza salto de página */
            .print-page {
                page-break-after: always;
            }

            /* Portada */
            .print-cover {
                display: grid;
                grid-template-rows: auto 1fr;
                gap: 12px;
            }

            .cover-title {
                font-size: 22pt;
                font-weight: 800;
                margin: 6px 0 2px;
            }

            .cover-subtitle {
                font-size: 12pt;
                color: #555;
            }

            .cover-brand {
                font-size: 11pt;
                font-weight: 700;
                color: #5b21b6;
                letter-spacing: .5px;
                text-transform: uppercase;
            }

            .cover-meta {
                margin-top: 18px;
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 8px 16px;
                font-size: 11pt;
            }

            .kvp {
                display: flex;
                gap: 8px;
            }

            .kvp b {
                min-width: 140px;
            }
        }
    </style>

</head>

<body>

    <!-- 🔲 CONTENEDOR PRINCIPAL -->
    <div class="layout">

        <!-- 🧭 SIDEBAR -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <span class="material-icons logo-icon">dashboard</span>
                <span class="logo-text">SVE</span>
            </div>

            <nav class="sidebar-menu">
                <ul>
                    <li onclick="location.href='sve_dashboard.php'">
                        <span class="material-icons" style="color: #5b21b6;">home</span><span class="link-text">Inicio</span>
                    </li>
                    <li onclick="location.href='sve_consolidado.php'">
                        <span class="material-icons" style="color: #5b21b6;">analytics</span><span class="link-text">Consolidado</span>
                    </li>
                    <li onclick="location.href='sve_altausuarios.php'">
                        <span class="material-icons" style="color: #5b21b6;">person</span><span class="link-text">Alta usuarios</span>
                    </li>
                    <li onclick="location.href='sve_asociarProductores.php'">
                        <span class="material-icons" style="color: #5b21b6;">link</span><span class="link-text">Asociaciones</span>
                    </li>
                    <li onclick="location.href='sve_cargaMasiva.php'">
                        <span class="material-icons" style="color: #5b21b6;">upload_file</span><span class="link-text">Carga masiva</span>
                    </li>
                    <li onclick="location.href='sve_operativos.php'">
                        <span class="material-icons" style="color: #5b21b6;">assignment</span><span class="link-text">Operativos</span>
                    </li>
                    <li onclick="location.href='sve_mercadodigital.php'">
                        <span class="material-icons" style="color: #5b21b6;">shopping_cart</span><span class="link-text">Mercado Digital</span>
                    </li>
                    <li onclick="location.href='sve_listadoPedidos.php'">
                        <span class="material-icons" style="color: #5b21b6;">assignment_turned_in</span><span class="link-text">Listado Pedidos</span>
                    </li>
                    <li onclick="location.href='sve_productos.php'">
                        <span class="material-icons" style="color: #5b21b6;">inventory</span><span class="link-text">Productos</span>
                    </li>
                    <li onclick="location.href='sve_pulverizacionDrone.php'">
                        <span class="material-symbols-outlined" style="color:#5b21b6;">drone</span>
                        <span class="link-text">Drones</span>
                    </li>
                    <li onclick="location.href='sve_publicaciones.php'">
                        <span class="material-icons" style="color: #5b21b6;">menu_book</span><span class="link-text">Biblioteca Virtual</span>
                    </li>
                    <li onclick="location.href='../../../logout.php'">
                        <span class="material-icons" style="color: red;">logout</span><span class="link-text">Salir</span>
                    </li>
                </ul>
            </nav>

            <div class="sidebar-footer">
                <button class="btn-icon" onclick="toggleSidebar()">
                    <span class="material-icons" id="collapseIcon">chevron_left</span>
                </button>
            </div>
        </aside>

        <!-- 🧱 MAIN -->
        <div class="main">

            <!-- 🟪 NAVBAR -->
            <header class="navbar">
                <button class="btn-icon" onclick="toggleSidebar()">
                    <span class="material-icons">menu</span>
                </button>
                <div class="navbar-title">Inicio</div>
            </header>

            <!-- 📦 CONTENIDO -->
            <section class="content">

                <!-- Bienvenida -->
                <div class="card">
                    <h2>Hola</h2>
                    <p>Te presentamos el gestor de proyectos de vuelo. Armar todos los protocolos y los registros fitosanitarios desde esta página</p>
                </div>

                <!-- Filtros -->
                <div class="card">
                    <h2>Busca el servicio</h2>
                    <form class="form-modern">
                        <div class="form-grid grid-3">

                            <!-- Filtro por nombre del productor -->
                            <div class="input-group">
                                <label for="nombre">Nombre completo</label>
                                <div class="input-icon input-icon-name">
                                    <input type="text" id="nombre" name="nombre" placeholder="Juan Pérez" />
                                </div>
                            </div>

                            <!-- Filtro por fecha -->
                            <div class="input-group">
                                <label for="fecha">Fecha</label>
                                <div class="input-icon input-icon-date">
                                    <input id="fecha" name="fecha" />
                                </div>
                            </div>

                            <!-- Filtro por estado -->
                            <div class="input-group">
                                <label for="provincia">Estado</label>
                                <div class="input-icon input-icon-globe">
                                    <select id="provincia" name="provincia">
                                        <option value="">Seleccionar</option>
                                        <option>Pendiente</option>
                                        <option>En proceso</option>
                                        <option>Completado</option>
                                        <option>Cancelado</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Listado de proyectos -->
                <div class="card">
                    <h2>Listado de proyectos</h2>
                    <div class="card-grid grid-3" id="proyectosContainer" style="max-height:400px; overflow:auto;">
                        <!-- JS rellena -->
                    </div>
                </div>


                <!-- contenedor del toastify -->
                <div id="toast-container"></div>
                <div id="toast-container-boton"></div>
                <!-- Spinner Global -->
                <script src="../../views/partials/spinner-global.js"></script>

            </section>

        </div>
    </div>

    <!-- Modal -->
    <div id="ModalEditarServicio" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Detalle del servicio</h3>
                <button class="btn-icon" id="modalCloseBtn"><span class="material-icons">close</span></button>
            </div>
            <div id="modalBody"></div>
            <div class="modal-actions" style="gap:8px;">
                <button class="btn btn-aceptar" id="btnImprimir">Imprimir / PDF</button>
                <button class="btn btn-info" id="btnDescargar">Descargar protocolo</button>
                <button class="btn btn-aceptar" id="btnActualizar">Actualizar pedido</button>
            </div>
        </div>
    </div>

    <!-- Espacio para scripts adicionales -->
    <script>
        (() => {

            let lastDetalle = null;

            document.getElementById('btnDescargar')?.addEventListener('click', async () => {
  const node = document.querySelector('#ModalEditarServicio .modal-content');
  if (!node) return;

  // tarjetas seleccionadas
  const checks = document.querySelectorAll('#exportSelector input[type=checkbox][data-block]');
  let selected = new Set([...checks].filter(i => i.checked).map(i => i.dataset.block));
  if (selected.size === 0) {
    selected = new Set(
      [...document.querySelectorAll('.modal-grid .card[data-block]')].map(el => el.getAttribute('data-block'))
    );
  }

  try {
    const canvas = await html2canvas(node, {
      scale: 2,
      useCORS: true,
      backgroundColor: '#fff',
      scrollX: 0,
      scrollY: 0,
      onclone: (doc) => {
        // 1) expandir modal para incluir TODO el contenido
        const modal = doc.querySelector('#ModalEditarServicio .modal-content');
        if (modal) {
          modal.style.maxHeight = 'none';
          modal.style.height = 'auto';
          modal.style.overflow = 'visible';
          modal.style.background = '#fff';
        }
        const grid = doc.querySelector('#ModalEditarServicio .modal-grid');
        if (grid) grid.style.overflow = 'visible';

        // 2) eliminar UI que no queremos en la imagen
        doc.querySelectorAll('[data-noprint], .modal-actions').forEach(el => el.remove());

        // 3) eliminar tarjetas NO seleccionadas (deja solo las elegidas)
        doc.querySelectorAll('.modal-grid .card[data-block]').forEach(el => {
          const block = el.getAttribute('data-block');
          if (!selected.has(block)) el.remove();
        });
      }
    });

    const link = document.createElement('a');
    link.download = `protocolo_${currentSolicitudId || 'servicio'}.png`;
    link.href = canvas.toDataURL('image/png');
    link.click();
  } catch (e) {
    toastError('No se pudo generar la imagen');
  }
});




            let currentSolicitudId = null;
            const $ = (sel, ctx = document) => ctx.querySelector(sel);
            const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];
            const debounce = (fn, ms = 350) => {
                let t;
                return (...a) => {
                    clearTimeout(t);
                    t = setTimeout(() => fn(...a), ms);
                }
            };

            // ✅ Path correcto (dos niveles arriba desde views/sve/)
            const CONTROLLER_URL = '../../controllers/sve_pulverizacionDroneController.php';

            // Refs UI
            const inputNombre = document.getElementById('nombre');
            const inputFecha = document.getElementById('fecha');
            const selEstado = document.getElementById('provincia'); // es "estado"
            const grid = document.getElementById('proyectosContainer');

            // Forzar type="date" si faltó en el HTML
            if (inputFecha && inputFecha.type !== 'date') {
                inputFecha.setAttribute('type', 'date');
            }

            // ✅ Sin paginación
            const state = {
                q: '',
                fecha: '',
                estado: ''
            };

            const toEnumEstado = (label) => !label ? '' : label.trim().toLowerCase().replace(/\s+/g, '_');

            // ✅ Sin page/limit en la query
            async function fetchListado() {
                const params = new URLSearchParams({
                    action: 'list_solicitudes',
                    q: state.q || '',
                    fecha: state.fecha || '',
                    estado: state.estado || ''
                });
                const url = `${CONTROLLER_URL}?${params.toString()}`;
                const res = await fetch(url, {
                    credentials: 'same-origin'
                });
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                const json = await res.json();
                if (!json.ok) throw new Error(json.error || 'Error desconocido');
                return json.data; // { items: [...] }
            }

            function renderCards(data) {
                grid.innerHTML = '';
                const items = data.items || [];
                if (!items.length) {
                    grid.innerHTML = `<div class="empty-state" style="grid-column:1/-1; text-align:center;">
        <span class="material-icons" style="font-size:42px;opacity:.6;">inbox</span>
        <p style="opacity:.8;">No hay servicios para los filtros aplicados.</p>
      </div>`;
                    return;
                }
                const frag = document.createDocumentFragment();
                for (const it of items) {
                    const card = document.createElement('div');
                    card.className = 'user-card';
                    card.innerHTML = `
  <h3 class="user-name" title="${it.ses_nombre || ''}">${escapeHtml(it.ses_usuario || it.ses_nombre || '—')}</h3>
  <div class="user-info">
    <span class="material-icons icon-email">flag</span>
    <span class="user-email">${badgeEstado(it.estado)}</span>
  </div>
  ${it.fecha_visita ? `
    <div class="user-info">
      <span class="material-icons icon-email">event</span>
      <span class="user-email">${formatFecha(it.fecha_visita)}</span>
    </div>` : ``}
  <button class="btn btn-info btn-ver" data-id="${it.id}">Ver</button>
`;
                    frag.appendChild(card);
                }
                grid.appendChild(frag);
                $$('.btn-ver', grid).forEach(btn => btn.addEventListener('click', () => openModal(parseInt(btn.dataset.id, 10))));
            }

            // ------ Modal / detalle (igual que te pasé antes) ------
            const modal = document.getElementById('ModalEditarServicio');
            const modalBody = document.getElementById('modalBody');
            const modalCloseBtn = document.getElementById('modalCloseBtn');
            modalCloseBtn?.addEventListener('click', closeModal);

            function openModal(id) {
                currentSolicitudId = id;
                loadDetalle(id).catch(err => toastError(err.message));
            }

            function closeModal() {
                modal.style.display = 'none';
                document.body.classList.remove('modal-open');
            }

            async function loadDetalle(id) {
  const url = `${CONTROLLER_URL}?action=get_solicitud&id=${id}`;
  const res = await fetch(url, { credentials: 'same-origin' });
  if (!res.ok) throw new Error(`HTTP ${res.status}`);
  const json = await res.json();
  if (!json.ok) throw new Error(json.error || 'Error desconocido');

  const { solicitud: s, motivos, productos, rangos } = json.data;

  // ✅ guardar para impresión
  lastDetalle = { s, motivos, productos, rangos };

  // 1) inyectar HTML del detalle
  modalBody.innerHTML = buildDetalleHTML(s, motivos, productos, rangos);

  // 2) bind de eventos internos
  initDetalleEvents();

  // 3) handler "Agregar producto"
  const btnAdd = document.getElementById('btnAddProducto');
  btnAdd?.addEventListener('click', async () => {
    const tipo = document.getElementById('prod_tipo').value;
    const fuente = document.getElementById('prod_fuente').value;
    const marca = document.getElementById('prod_marca').value.trim();
    try {
      const fd = new URLSearchParams({
        action: 'add_producto',
        solicitud_id: String(currentSolicitudId),
        tipo, fuente, marca
      });
      const r = await fetch(CONTROLLER_URL, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: fd.toString()
      });
      const j = await r.json();
      if (!j.ok) throw new Error(j.error || 'No se pudo agregar');
      await loadDetalle(currentSolicitudId); // refresca
    } catch (e) {
      toastError(e.message);
    }
  });

  // 4) abrir modal
  modal.style.display = 'flex';
  document.body.classList.add('modal-open');

  function initDetalleEvents() {
    const sel = document.getElementById('estado_select');
    const wrap = document.getElementById('wrap_motivo_cancel');
    sel?.addEventListener('change', () => {
      wrap.style.display = sel.value === 'cancelado' ? 'block' : 'none';
    });
  }
}


            function buildDetalleHTML(s, motivos, productos, rangos) {

                const mapsLink = (s) => {
                    const hasCoords = s.ubicacion_lat && s.ubicacion_lng;
                    if (hasCoords) {
                        const q = `${s.ubicacion_lat},${s.ubicacion_lng}`;
                        return {
                            url: `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(q)}`,
                            label: 'Ver en Google Maps'
                        };
                    }
                    const addr = [s.dir_calle, s.dir_numero, s.dir_localidad, s.dir_provincia]
                        .filter(Boolean).join(' ');
                    if (addr) {
                        return {
                            url: `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(addr)}`,
                            label: 'Ver en Google Maps'
                        };
                    }
                    return null;
                };


                const siNo = v => v === 'si' ? 'Sí' : (v === 'no' ? 'No' : '—');
                const fmt = v => (v ?? '—');
                const fecha = v => formatFecha(v);

                const fuenteLabel = v => {
                    if (!v) return '—';
                    const val = ('' + v).toLowerCase();
                    if (val === 'yo') return 'productor';
                    return val; // 'sve' u otros
                };

                const motivosHtml = (motivos || []).map(m => `
    <li><strong>${m.motivo}</strong>${m.otros_text ? ` — ${escapeHtml(m.otros_text)}`:''}</li>
  `).join('') || '<li>—</li>';

                const productosHtml = (productos || []).map(p => `
  <tr>
    <td>${fmt(p.tipo)}</td>
    <td>${fuenteLabel(p.fuente)}</td>
    <td>${escapeHtml(p.marca||'—')}</td>
  </tr>
`).join('') || '<tr><td colspan="3">—</td></tr>';

                const rangosHtml = (rangos || []).map(r => `<span class="chip">${r.rango}</span>`).join(' ') || '—';

                return `
    <div class="modal-grid">
      <!-- Fila 1 -->
<div class="card" data-block="generales">
  <h4>Datos generales del servicio</h4>
  <div class="kv"><span>ID</span><span>${s.id}</span></div>

  <!-- Estado editable -->
  <div class="input-group" data-block="generales">
    <label>Estado</label>
    <div class="input-icon input-icon-globe">
      <select id="estado_select">
        <option value="pendiente"   ${s.estado==='pendiente'?'selected':''}>Pendiente</option>
        <option value="en_proceso"  ${s.estado==='en_proceso'?'selected':''}>En proceso</option>
        <option value="completado"  ${s.estado==='completado'?'selected':''}>Completado</option>
        <option value="cancelado"   ${s.estado==='cancelado'?'selected':''}>Cancelado</option>
      </select>
    </div>
  </div>

  <!-- Motivo cancelación (aparece solo si estado = cancelado) -->
  <div class="input-group" id="wrap_motivo_cancel" style="display:${s.estado==='cancelado'?'block':'none'};">
    <label>Motivo de cancelación</label>
    <div class="input-icon input-icon-name">
      <input type="text" id="plan_motivo_cancelacion" value="${escapeAttr(s.motivo_cancelacion || '')}" />
    </div>
  </div>

  <div class="kv"><span>Superficie (ha)</span><span>${fmt(s.superficie_ha)}</span></div>
  <div class="kv"><span>Fecha servicio</span><span>${fecha(s.fecha_servicio || s.created_at)}</span></div>
  <div class="kv"><span>Creado</span><span>${fecha(s.created_at)}</span></div>
  <div class="kv"><span>Actualizado</span><span>${fecha(s.updated_at)}</span></div>
  <div class="kv"><span>Rango de fecha preferido</span><span>${rangosHtml}</span></div>
  <h4>Motivos por el cual solicita el servicio</h4>
  <div class="kv"><span>Patologia</span><span>${motivosHtml}</span></div>
</div>


<div class="card" data-block="ubicacion">
  <h4>Ubicación de la finca</h4>
  <div class="kv"><span>Provincia</span><span>${fmt(s.dir_provincia)}</span></div>
  <div class="kv"><span>Localidad</span><span>${fmt(s.dir_localidad)}</span></div>
  <div class="kv"><span>Calle / Nº</span><span>${fmt(s.dir_calle)} ${fmt(s.dir_numero)}</span></div>
  <div class="kv"><span>En finca</span><span>${siNo(s.en_finca)}</span></div>
  <div class="kv"><span>Lat / Lng</span><span>${fmt(s.ubicacion_lat)} / ${fmt(s.ubicacion_lng)}</span></div>
  <div class="kv"><span>Precisión</span><span>${fmt(s.ubicacion_acc)}</span></div>
  <div class="kv"><span>Fecha GPS</span><span>${fecha(s.ubicacion_ts)}</span></div>

  ${(() => {
      const m = mapsLink(s);
      return m ? `
        <div style="margin-top:10px;">
          <a class="btn btn-info" href="${m.url}" target="_blank" rel="noopener">
            <span class="material-icons" style="font-size:18px;vertical-align:-3px;">map</span>
            <span style="margin-left:6px;">${m.label}</span>
          </a>
        </div>
      ` : '';
    })()}
</div>

      <div class="card" data-block="productor">
        <h4>Datos del productor</h4>
        <div class="kv"><span>Usuario</span><span>${fmt(s.ses_usuario)}</span></div>
        <div class="kv"><span>Rol</span><span>${fmt(s.ses_rol)}</span></div>
        <div class="kv"><span>Nombre</span><span>${fmt(s.ses_nombre)}</span></div>
        <div class="kv"><span>Correo</span><span>${fmt(s.ses_correo)}</span></div>
        <div class="kv"><span>Teléfono</span><span>${fmt(s.ses_telefono)}</span></div>
        <div class="kv"><span>Dirección</span><span>${fmt(s.ses_direccion)}</span></div>
        <div class="kv"><span>CUIT</span><span>${fmt(s.ses_cuit)}</span></div>
        <div class="kv"><span>Última actividad</span><span>${fecha(s.ses_last_activity_ts)}</span></div>
      </div>

      <!-- Fila 2 -->
      <div class="card" data-block="infraestructura">
        <h4>Infraestructura de la finca</h4>
        <div class="kv"><span>Línea de tensión</span><span>${siNo(s.linea_tension)}</span></div>
        <div class="kv"><span>Zona restringida</span><span>${siNo(s.zona_restringida)}</span></div>
        <div class="kv"><span>Corriente eléctrica</span><span>${siNo(s.corriente_electrica)}</span></div>
        <div class="kv"><span>Agua potable</span><span>${siNo(s.agua_potable)}</span></div>
        <div class="kv"><span>Libre de obstáculos</span><span>${siNo(s.libre_obstaculos)}</span></div>
        <div class="kv"><span>Área de despegue</span><span>${siNo(s.area_despegue)}</span></div>
        <div class="kv"><span>Representante en finca</span><span>${siNo(s.representante)}</span></div>
      </div>

      <!-- Fila 3 -->
      <div class="card col-span-2" data-block="productos_tabla">
        <h4>Productos a utilizar</h4>
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>Tipo</th>
                <th>Fuente</th>
                <th>Marca</th>
              </tr>
            </thead>
            <tbody>${productosHtml}</tbody>
          </table>
        </div>
      </div>

            <!-- Fila 3 -->
<div class="card col-span-2">
  <h4>Agregar un nuevo producto</h4>

  <div class="form-modern" style="margin-top:12px;">
    <div class="form-grid grid-4">
      <div class="input-group">
        <label>Tipo</label>
        <div class="input-icon input-icon-globe">
          <select id="prod_tipo">
            <option value="lobesia">lobesia</option>
            <option value="peronospora">peronospora</option>
            <option value="oidio">oidio</option>
            <option value="podredumbre">podredumbre</option>
          </select>
        </div>
      </div>

      <div class="input-group">
        <label>Fuente</label>
        <div class="input-icon input-icon-globe">
          <select id="prod_fuente">
            <option value="sve">SVE</option>
            <option value="yo">Productor</option>
          </select>
        </div>
      </div>

      <div class="input-group">
        <label>Marca</label>
        <div class="input-icon input-icon-name">
          <input type="text" id="prod_marca" placeholder="Marca" />
        </div>
      </div>

      <div class="input-group" style="align-self:end;">
        <button class="btn btn-aceptar" id="btnAddProducto" type="button">Agregar</button>
      </div>
    </div>
  </div>
</div>

      <!-- Nueva: Parámetros de vuelo -->
<div class="card" data-block="parametros">
  <h4>Parámetros de vuelo</h4>
  <div class="form-modern">
    <div class="form-grid grid-2">
      <div class="input-group">
        <label>Volumen/ha</label>
        <div class="input-icon input-icon-number">
          <input type="number" step="0.01" id="volumen_ha" value="${escapeAttr(s.volumen_ha ?? '')}" placeholder="L/ha" />
        </div>
      </div>
      <div class="input-group">
        <label>Velocidad de vuelo</label>
        <div class="input-icon input-icon-number">
          <input type="number" step="0.01" id="velocidad_vuelo" value="${escapeAttr(s.velocidad_vuelo ?? '')}" placeholder="m/s o km/h" />
        </div>
      </div>
      <div class="input-group">
        <label>Alto de vuelo</label>
        <div class="input-icon input-icon-number">
          <input type="number" step="0.01" id="alto_vuelo" value="${escapeAttr(s.alto_vuelo ?? '')}" placeholder="m" />
        </div>
      </div>
      <div class="input-group">
        <label>Tamaño de gota</label>
        <div class="input-icon input-icon-name">
          <input type="text" id="tamano_gota" value="${escapeAttr(s.tamano_gota ?? '')}" placeholder="μm o descripción" />
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Nueva: Observaciones para el piloto -->
<div class="card" style="grid-column:1/-1;" data-block="observaciones">
  <h4>Indicaciones para el piloto</h4>
  <div class="form-modern">
    <div class="input-group">
      <label>Indicaciones</label>
      <textarea id="obs_piloto" rows="3" placeholder="Notas para el piloto...">${escapeHtml(s.obs_piloto ?? '')}</textarea>
    </div>
  </div>
</div>

      <!-- Fila 4 (ancho completo) -->
      <div class="card" style="grid-column:1/-1;" data-block="planificacion">
        <h4>Planificación</h4>
        <div class="form-modern">
          <div class="form-grid grid-2">
            <div class="input-group">
              <label>Responsable</label>
              <div class="input-icon input-icon-name">
                <input type="text" id="plan_responsable" value="${escapeAttr(s.responsable || '')}" />
              </div>
            </div>
            <div class="input-group">
              <label>Piloto</label>
              <div class="input-icon input-icon-name">
                <input type="text" id="plan_piloto" value="${escapeAttr(s.piloto || '')}" />
              </div>
            </div>
            <div class="input-group">
              <label>Fecha de visita</label>
              <div class="input-icon input-icon-date">
                <input type="date" id="plan_fecha_visita" value="${toDateValue(s.fecha_visita)}" />
              </div>
            </div>
            <div class="input-group">
              <label>Hora de visita</label>
              <div class="input-icon input-icon-date">
                <input type="time" id="plan_hora_visita" value="${toTimeValue(s.hora_visita)}" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    
    <!-- Selector de tarjetas para imprimir -->
    <div class="card" id="exportSelector" style="grid-column:1/-1;" data-noprint>
  <h4>¿Qué incluir al descargar?</h4>
  <div class="form-modern">
    <div class="form-grid grid-4">
      <label><input type="checkbox" data-block="generales" checked> Datos generales</label>
      <label><input type="checkbox" data-block="ubicacion" checked> Ubicación</label>
      <label><input type="checkbox" data-block="productor" checked> Datos del productor</label>
      <label><input type="checkbox" data-block="infraestructura" checked> Infraestructura</label>
      <label><input type="checkbox" data-block="productos_tabla" checked> Productos (tabla)</label>
      <label><input type="checkbox" data-block="parametros" checked> Parámetros de vuelo</label>
      <label><input type="checkbox" data-block="observaciones" checked> Indicaciones</label>
      <label><input type="checkbox" data-block="planificacion" checked> Planificación</label>
    </div>
    <small style="opacity:.7">Este panel no se incluye en la imagen.</small>
  </div>
</div>
  `;
            }

            function formatFecha(v) {
                if (!v) return '—';
                const d = new Date(v);
                if (isNaN(d)) return ('' + v).slice(0, 10);
                return d.toISOString().slice(0, 10);
            }

            function toDateValue(v) {
                return v ? formatFecha(v) : '';
            }

            function toTimeValue(v) {
                if (!v) return '';
                const s = ('' + v).slice(0, 5);
                return /^\d{2}:\d{2}$/.test(s) ? s : '';
            }

            function escapeHtml(s) {
                return (s ?? '').toString().replace(/[&<>"']/g, m => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                } [m]));
            }

            function escapeAttr(s) {
                return escapeHtml(s);
            }

            function badgeEstado(est) {
                const key = (est || '').toLowerCase();
                const mapLabel = {
                    'pendiente': 'Pendiente',
                    'en_proceso': 'En proceso',
                    'completado': 'Completado',
                    'cancelado': 'Cancelado'
                };
                const mapClass = {
                    'pendiente': 'status-pill status-pendiente',
                    'en_proceso': 'status-pill status-en_proceso',
                    'completado': 'status-pill status-completado',
                    'cancelado': 'status-pill status-cancelado'
                };
                const label = mapLabel[key] || (est || '—');
                const cls = mapClass[key] || 'status-pill';
                return `<span class="${cls}">${label}</span>`;
            }

            function toastError(msg) {
                try {
                    window.Toastify?.show({
                        text: msg,
                        className: 'toast-error'
                    });
                } catch (e) {
                    console.error(msg);
                }
            }

            // Filtros en vivo
            inputNombre?.addEventListener('input', debounce(() => {
                state.q = inputNombre.value.trim();
                refresh();
            }, 300));
            inputFecha?.addEventListener('change', () => {
                state.fecha = inputFecha.value || '';
                refresh();
            });
            selEstado?.addEventListener('change', () => {
                state.estado = toEnumEstado(selEstado.value || '');
                refresh();
            });

            async function refresh() {
                try {
                    const data = await fetchListado();
                    renderCards(data);
                } catch (err) {
                    toastError('No se pudo cargar el listado: ' + err.message);
                }
            }

            // Primera carga
            refresh();

            document.getElementById('btnActualizar')?.addEventListener('click', saveSolicitud);

            async function saveSolicitud() {
                try {
                    const estado = document.getElementById('estado_select')?.value || '';
                    const motivo = (estado === 'cancelado') ?
                        (document.getElementById('plan_motivo_cancelacion')?.value || '') :
                        ''; // <- limpiar

                    const fd = new URLSearchParams({
                        action: 'update_solicitud',
                        id: String(currentSolicitudId),
                        estado: estado,
                        motivo_cancelacion: motivo,
                        responsable: document.getElementById('plan_responsable')?.value || '',
                        piloto: document.getElementById('plan_piloto')?.value || '',
                        fecha_visita: document.getElementById('plan_fecha_visita')?.value || '',
                        hora_visita: document.getElementById('plan_hora_visita')?.value || '',
                        volumen_ha: document.getElementById('volumen_ha')?.value || '',
                        velocidad_vuelo: document.getElementById('velocidad_vuelo')?.value || '',
                        alto_vuelo: document.getElementById('alto_vuelo')?.value || '',
                        tamano_gota: document.getElementById('tamano_gota')?.value || '',
                        obs_piloto: document.getElementById('obs_piloto')?.value || ''
                    });

                    const r = await fetch(CONTROLLER_URL, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: fd.toString()
                    });
                    const j = await r.json();
                    if (!j.ok) throw new Error(j.error || 'No se pudo actualizar');

                    // refrescar listado para reflejar fecha_visita/estado
                    await refresh();
                    // recargar detalle para ver cambios
                    await loadDetalle(currentSolicitudId);
                } catch (e) {
                    toastError(e.message);
                }
            }
        })();


        /* Guarda el último detalle cargado para reusarlo en la impresión */
        let lastDetalle = null;

        /* Hook en loadDetalle: guardamos datos para imprimir */
        async function loadDetalle(id) {
            const url = `${CONTROLLER_URL}?action=get_solicitud&id=${id}`;
            const res = await fetch(url, {
                credentials: 'same-origin'
            });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const json = await res.json();
            if (!json.ok) throw new Error(json.error || 'Error desconocido');

            const {
                solicitud: s,
                motivos,
                productos,
                rangos
            } = json.data;

            // guardamos para impresión
            lastDetalle = {
                s,
                motivos,
                productos,
                rangos
            };

            // ... lo que ya tenías:
            modalBody.innerHTML = buildDetalleHTML(s, motivos, productos, rangos);
            // bind de eventos, etc...
            // ...
        }

        /* Botón imprimir */
        document.getElementById('btnImprimir')?.addEventListener('click', () => {
            if (!lastDetalle) return toastError('Cargá primero un servicio.');
            const area = document.getElementById('printArea');
            area.innerHTML = buildPrintHTML(lastDetalle.s, lastDetalle.motivos, lastDetalle.productos, lastDetalle.rangos);
            // Disparamos el diálogo del navegador: Imprimir / Guardar como PDF
            window.print();
            // Limpieza tras imprimir (por si el usuario cierra sin imprimir)
            setTimeout(() => {
                area.innerHTML = '';
            }, 500);
        });

        /* === Construye el HTML SOLO PARA IMPRESIÓN (con portada) === */
        function buildPrintHTML(s, motivos, productos, rangos) {
            const fmt = v => (v ?? '—');
            const siNo = v => v === 'si' ? 'Sí' : (v === 'no' ? 'No' : '—');
            const fecha = v => formatFecha(v);
            const fuenteLabel = v => !v ? '—' : (('' + v).toLowerCase() === 'yo' ? 'productor' : v);

            const motivosHtml = (motivos || []).map(m => `
    <li><strong>${m.motivo}</strong>${m.otros_text ? ` — ${escapeHtml(m.otros_text)}` : ''}</li>
  `).join('') || '<li>—</li>';

            const productosHtml = (productos || []).map(p => `
    <tr><td>${fmt(p.tipo)}</td><td>${fuenteLabel(p.fuente)}</td><td>${escapeHtml(p.marca||'—')}</td></tr>
  `).join('') || '<tr><td colspan="3">—</td></tr>';

            const rangosHtml = (rangos || []).map(r => `<span>${r.rango}</span>`).join(', ') || '—';

            /* --- PORTADA --- */
            const cover = `
  <section class="print-page print-cover">
    <div>
      <div class="cover-brand">SVE</div>
      <div class="cover-title">Protocolo de pulverización con drone</div>
      <div class="cover-subtitle">Documento técnico para impresión / archivo</div>
    </div>

    <div class="cover-meta">
      <div class="kvp"><b>Nº de protocolo</b><span>${s.id}</span></div>
      <div class="kvp"><b>Estado</b><span>${(s.estado||'').replace('_',' ')}</span></div>
      <div class="kvp"><b>Productor</b><span>${escapeHtml(s.ses_nombre || s.ses_usuario || '—')}</span></div>
      <div class="kvp"><b>Fecha servicio</b><span>${fecha(s.fecha_servicio || s.created_at)}</span></div>
      <div class="kvp"><b>Superficie (ha)</b><span>${fmt(s.superficie_ha)}</span></div>
      <div class="kvp"><b>Generado</b><span>${fecha(new Date())}</span></div>
    </div>
  </section>`;

            /* --- PÁGINA 2: Datos generales + Ubicación + Productor --- */
            const page2 = `
  <section class="print-page">
    <div class="print-card">
      <h3>Datos generales</h3>
      <div class="kvp"><b>Rango preferido</b><span>${rangosHtml}</span></div>
      <div class="kvp"><b>Creado</b><span>${fecha(s.created_at)}</span></div>
      <div class="kvp"><b>Actualizado</b><span>${fecha(s.updated_at)}</span></div>
      <div><b>Motivos:</b><ul>${motivosHtml}</ul></div>
    </div>

    <div class="print-card">
      <h3>Ubicación de la finca</h3>
      <div class="kvp"><b>Provincia</b><span>${fmt(s.dir_provincia)}</span></div>
      <div class="kvp"><b>Localidad</b><span>${fmt(s.dir_localidad)}</span></div>
      <div class="kvp"><b>Dirección</b><span>${fmt(s.dir_calle)} ${fmt(s.dir_numero)}</span></div>
      <div class="kvp"><b>En finca</b><span>${siNo(s.en_finca)}</span></div>
      <div class="kvp"><b>Lat/Lng</b><span>${fmt(s.ubicacion_lat)} / ${fmt(s.ubicacion_lng)}</span></div>
      <div class="kvp"><b>Precisión</b><span>${fmt(s.ubicacion_acc)}</span></div>
      <div class="kvp"><b>Fecha GPS</b><span>${fecha(s.ubicacion_ts)}</span></div>
    </div>

    <div class="print-card">
      <h3>Datos del productor</h3>
      <div class="kvp"><b>Usuario</b><span>${fmt(s.ses_usuario)}</span></div>
      <div class="kvp"><b>Rol</b><span>${fmt(s.ses_rol)}</span></div>
      <div class="kvp"><b>Nombre</b><span>${fmt(s.ses_nombre)}</span></div>
      <div class="kvp"><b>Correo</b><span>${fmt(s.ses_correo)}</span></div>
      <div class="kvp"><b>Teléfono</b><span>${fmt(s.ses_telefono)}</span></div>
      <div class="kvp"><b>Dirección</b><span>${fmt(s.ses_direccion)}</span></div>
      <div class="kvp"><b>CUIT</b><span>${fmt(s.ses_cuit)}</span></div>
      <div class="kvp"><b>Últ. actividad</b><span>${fecha(s.ses_last_activity_ts)}</span></div>
    </div>
  </section>`;

            /* --- PÁGINA 3: Infraestructura + Productos --- */
            const page3 = `
  <section class="print-page">
    <div class="print-card">
      <h3>Infraestructura</h3>
      <div class="kvp"><b>Línea de tensión</b><span>${siNo(s.linea_tension)}</span></div>
      <div class="kvp"><b>Zona restringida</b><span>${siNo(s.zona_restringida)}</span></div>
      <div class="kvp"><b>Corriente eléctrica</b><span>${siNo(s.corriente_electrica)}</span></div>
      <div class="kvp"><b>Agua potable</b><span>${siNo(s.agua_potable)}</span></div>
      <div class="kvp"><b>Libre de obstáculos</b><span>${siNo(s.libre_obstaculos)}</span></div>
      <div class="kvp"><b>Área de despegue</b><span>${siNo(s.area_despegue)}</span></div>
      <div class="kvp"><b>Representante en finca</b><span>${siNo(s.representante)}</span></div>
    </div>

    <div class="print-card">
      <h3>Productos a utilizar</h3>
      <table style="width:100%; border-collapse:collapse;">
        <thead>
          <tr>
            <th style="text-align:left; border-bottom:1px solid #ddd; padding:6px 4px;">Tipo</th>
            <th style="text-align:left; border-bottom:1px solid #ddd; padding:6px 4px;">Fuente</th>
            <th style="text-align:left; border-bottom:1px solid #ddd; padding:6px 4px;">Marca</th>
          </tr>
        </thead>
        <tbody>
          ${productosHtml}
        </tbody>
      </table>
    </div>
  </section>`;

            /* --- PÁGINA 4: Parámetros + Observaciones + Planificación --- */
            const page4 = `
  <section class="print-page">
    <div class="print-card">
      <h3>Parámetros de vuelo</h3>
      <div class="kvp"><b>Volumen/ha</b><span>${fmt(s.volumen_ha)}</span></div>
      <div class="kvp"><b>Velocidad</b><span>${fmt(s.velocidad_vuelo)}</span></div>
      <div class="kvp"><b>Altura</b><span>${fmt(s.alto_vuelo)}</span></div>
      <div class="kvp"><b>Tamaño de gota</b><span>${fmt(s.tamano_gota)}</span></div>
    </div>

    <div class="print-card">
      <h3>Indicaciones para el piloto</h3>
      <div>${escapeHtml(s.obs_piloto || '—')}</div>
    </div>

    <div class="print-card">
      <h3>Planificación</h3>
      <div class="kvp"><b>Responsable</b><span>${fmt(s.responsable)}</span></div>
      <div class="kvp"><b>Piloto</b><span>${fmt(s.piloto)}</span></div>
      <div class="kvp"><b>Fecha de visita</b><span>${fecha(s.fecha_visita)}</span></div>
      <div class="kvp"><b>Hora de visita</b><span>${fmt(s.hora_visita)}</span></div>
      ${s.motivo_cancelacion ? `<div class="kvp"><b>Motivo cancelación</b><span>${escapeHtml(s.motivo_cancelacion)}</span></div>` : ''}
    </div>
  </section>`;

            return cover + page2 + page3 + page4;
        }
    </script>

    <!-- Contenedor exclusivo para impresión -->
    <div id="printArea" class="only-print"></div>
</body>

</html>