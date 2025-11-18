<?php
// Mostrar errores en pantalla (útil en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión y configurar parámetros de seguridad
require_once '../../middleware/authMiddleware.php';
checkAccess('ingeniero');

// Datos del usuario en sesión
$nombre = $_SESSION['nombre'] ?? 'Sin nombre';
$correo = $_SESSION['correo'] ?? 'Sin correo';
$cuit = $_SESSION['cuit'] ?? 'Sin CUIT';
$rol = $_SESSION['rol'] ?? 'Sin ROL';
$id_real = $_SESSION['id_real'] ?? 'Sin ROL';
$telefono = $_SESSION['telefono'] ?? 'Sin teléfono';
$observaciones = $_SESSION['observaciones'] ?? 'Sin observaciones';

//Cargamos los operativos cerrados
$cierre_info = $_SESSION['cierre_info'] ?? null;
unset($_SESSION['cierre_info']);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SVE</title>

    <!-- Íconos de Material Design -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- Framework Success desde CDN -->
    <link rel="stylesheet" href="https://framework.impulsagroup.com/assets/css/framework.css">
    <script src="https://framework.impulsagroup.com/assets/javascript/framework.js" defer></script>

    <!-- Exportar a PDF -->
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <style>
        /* Estilos tarjetas */
        .user-card {
            border: 2px solid #5b21b6;
            border-radius: 12px;
            padding: 1rem;
            transition: border 0.3s ease;
        }

        .user-card.completo {
            border-color: green;
        }

        .user-card.incompleto {
            border-color: red;
        }

        .oculto {
            display: none !important;
        }

        .tab-panel {
            display: none;
        }

        .tab-panel.active {
            display: block;
        }

        .tabs .tab-buttons {
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .tab-button.active {
            border-bottom: 2px solid #5b21b6;
        }

        .js-ready .tab-panel {
            display: none;
        }

        .js-ready .tab-panel.active {
            display: block;
        }

        /* Título pequeño de sección (similar a “APPS”) */
        .sidebar-section-title {
            margin: 12px 16px 6px;
            font-size: .72rem;
            font-weight: 600;
            letter-spacing: .08em;
            text-transform: uppercase;
            opacity: .7;
        }

        /* Lista simple de subitems */
        .submenu-root {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .submenu-root a {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .4rem 1.5rem;
            text-decoration: none;
        }

        .table-actions-hide .data-table .btn-icon:not(.btn-open-registro) {
            display: none !important;
        }

        /* Estilos para el volcado JSON en el modal */
        pre.json-dump {
            max-height: 70vh;
            overflow: auto;
            background: #0b1020;
            color: #d8f3ff;
            padding: 12px;
            border-radius: 8px;
            font: 12px/1.4 ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            margin: 0;
        }

        /* ===== Registro Fitosanitario – estilos visuales tipo tarjetas ===== */
        .rf-header {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 1rem;
            align-items: start;
        }

        .rf-logo {
            height: 48px;
        }

        .rf-meta {
            text-align: right;
        }

        .rf-pill {
            background: #f7f8fb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: .9rem 1rem;
        }

        .rf-title {
            font-size: 1rem;
            font-weight: 600;
            color: #4b5563;
            margin: 0 0 .4rem;
        }

        .rf-table {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
        }

        .rf-table table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .rf-table thead th {
            background: #f7f8fb;
            font-weight: 600;
            text-align: left;
            padding: .7rem .8rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .rf-table tbody td {
            padding: .65rem .8rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .rf-grid-fotos {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: .8rem;
        }

        .rf-foto {
            width: 100%;
            height: 160px;
            object-fit: cover;
            border-radius: 12px;
        }

        .rf-firma {
            display: block;
            height: 90px;
            margin: 10px auto;
        }

        .rf-caption {
            text-align: center;
            opacity: .7;
            margin-top: .25rem;
        }

        .rf-section {
            margin-top: 1rem;
        }

        .modal .form-buttons {
            display: flex;
            gap: .5rem;
            justify-content: flex-end;
        }

        /* Altura y scroll del modal */
        #modal .modal-content {
            height: 80vh;
            /* ocupar 80% del alto de la pantalla */
            max-height: 80vh;
            /* prevenir excedentes por estilos del framework */
            overflow-y: auto;
            /* scroll vertical si hace falta */
            overscroll-behavior: contain;
            scrollbar-gutter: stable both-edges;
            /* evita saltos al aparecer el scroll */
        }

        /* Opcional: impedir scroll horizontal por contenidos anchos */
        #modal .modal-content,
        #modal-detalle .modal-content {
            overflow-x: auto;
            overflow-y: auto;
            max-height: 80vh;
            /* ocupa 80% del alto visible */
            overscroll-behavior: contain;
            scrollbar-gutter: stable both-edges;
            /* evita saltos */
        }

        /* El contenido del registro crece dentro del modal; sólo el modal tiene scroll */
        #registro-container {
            overflow-x: visible;
            overflow-y: visible;
            max-height: none;
            overscroll-behavior: contain;
            scrollbar-gutter: stable both-edges;
        }

        .rf-row {
            display: grid;
            gap: 1rem;
            align-items: center;
        }

        .rf-row.cols-3 {
            grid-template-columns: 160px 1fr 220px;
        }

        /* logo | tarjeta | meta */
        .rf-row.cols-2 {
            grid-template-columns: 1fr 1fr;
        }

        /* cliente | cultivo  */
        .rf-app {
            text-align: center;
        }

        /* tarjeta aplicación centrada */
        @media (max-width: 900px) {
            .rf-row.cols-3 {
                grid-template-columns: 120px 1fr 180px;
            }
        }

        @media (max-width: 720px) {

            .rf-row.cols-3,
            .rf-row.cols-2 {
                grid-template-columns: 1fr;
            }
        }

        /* ===== Tarjetas de solicitudes (lista) ===== */
        .sol-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: .9rem;
            background: #fff;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
            display: grid;
            grid-template-columns: 1fr auto;
            gap: .5rem .75rem;
            margin: 10px;
        }

        .sol-card .titulo {
            font-weight: 600;
        }

        .sol-card .numero {
            font-size: .85rem;
            opacity: .8;
        }

        .sol-card .observaciones {
            font-size: .9rem;
            color: #374151;
        }

        .sol-card .precio {
            font-weight: 600;
        }

        .sol-card .acciones {
            display: flex;
            flex-direction: column;
            gap: .5rem;
            align-items: flex-end;
            justify-content: center;
        }

        #cards-solicitudes {
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }

        @media (min-width:1024px) {
            #cards-solicitudes {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .badge.estado {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .15rem .5rem;
            border-radius: 999px;
            font-size: .75rem;
            background: #5b21b6;
        }

        /* Ocultar acciones en pedidos completados */
        .sol-card.estado-completada .btn-ver,
        .sol-card.estado-completada .btn-editar,
        .sol-card.estado-completada .btn-eliminar {
            display: none !important;
        }

        /* ====== Resumen Pedido (modal detalle) ====== */
        .res-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: .9rem;
            background: #fff;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
            margin-bottom: .8rem;
        }

        .res-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            margin-bottom: .4rem;
        }

        .res-title {
            font-weight: 600;
            font-size: 1rem;
            color: #374151;
        }

        .res-badge {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .15rem .55rem;
            border-radius: 999px;
            font-size: .75rem;
            border: 1px solid #e5e7eb;
            background: #f7f8fb;
        }

        .res-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .6rem .8rem;
        }

        @media (max-width: 720px) {
            .res-grid {
                grid-template-columns: 1fr;
            }
        }

        .res-kv {
            display: grid;
            grid-template-columns: 160px 1fr;
            gap: .4rem;
        }

        .res-kv .k {
            color: #6b7280;
            font-size: .85rem;
        }

        .res-kv .v {
            font-weight: 500;
        }

        .res-table {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
        }

        .res-table table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .res-table thead th {
            background: #f7f8fb;
            font-weight: 600;
            text-align: left;
            padding: .55rem .65rem;
            border-bottom: 1px solid #e5e7eb;
            font-size: .9rem;
        }

        .res-table tbody td {
            padding: .5rem .65rem;
            border-bottom: 1px solid #f1f5f9;
            font-size: .9rem;
        }

        .res-media {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: .6rem;
        }

        .res-media img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 10px;
        }

        /* Evitar cortes en tablas dentro de los modales: forzar ancho mínimo y permitir scroll */
        .res-table table,
        .rf-table table,
        .data-table {
            min-width: 760px;
            /* ajustable si necesitás más/menos */
        }

        /* Opcional: mejora de usabilidad del scroll horizontal */
        #modal .modal-content,
        #modal-detalle .modal-content {
            scrollbar-gutter: stable both-edges;
            overscroll-behavior: contain;
        }

        /* Ajuste para el modal Detalle (scroll interno completo) */
        #modal-detalle .modal-content {
            display: flex;
            flex-direction: column;
            height: 80vh;
            max-height: 80vh;
            overflow-y: auto;
            overflow-x: hidden;
        }

        #modal-detalle form {
            flex: 1;
            overflow-y: auto;
            padding-bottom: 1rem;
        }
    </style>

</head>

<body>

    <!-- 🔲 CONTENEDOR PRINCIPAL -->
    <div class="layout">

        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <span class="material-icons logo-icon">dashboard</span>
                <span class="logo-text">SVE</span>
            </div>
            <nav class="sidebar-menu">

                <!-- Título de sección -->
                <div class="sidebar-section-title">Menú</div>

                <!-- Grupo superior -->
                <ul>
                    <li onclick="location.href='ing_dashboard.php'">
                        <span class="material-icons" style="color:#5b21b6;">home</span>
                        <span class="link-text">Inicio</span>
                    </li>
                </ul>

                <!-- Título de sección -->
                <div class="sidebar-section-title">Drones</div>

                <!-- Lista directa de páginas de Drones (sin acordeón) -->
                <ul class="submenu-root">
                    <li>
                        <a href="ing_servicios.php">
                            <span class="material-symbols-outlined">add</span>
                            <span class="link-text">Solicitar Servicio</span>
                        </a>
                    </li>

                    <li>
                        <a href="ing_pulverizacion.php">
                            <span class="material-symbols-outlined">drone</span>
                            <span class="link-text">Servicios Solicitados</span>
                        </a>
                    </li>

                    <!-- Agregá más ítems aquí cuando existan nuevas hojas de Drones -->
                </ul>

                <!-- Resto de opciones -->
                <ul>
                    <li onclick="location.href='../../../logout.php'">
                        <span class="material-icons" style="color:red;">logout</span>
                        <span class="link-text">Salir</span>
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
            <section class="content table-actions-hide">

                <!-- Filtros -->
                <div class="card">
                    <h2>Solicitudes de pulverización</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 items-end">
                        <div class="input-group md:col-span-2">
                            <label for="filtro-productor">Buscar solicitudes de pulverización con drone por el nombre del productor</label>
                            <div class="input-icon input-icon-name">
                                <input type="text" id="filtro-productor" name="filtro-productor" placeholder="Escribí para filtrar…" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Listado con tarjetas -->
                <div class="card tabla-card">
                    <h2>Listado</h2>
                    <div id="cards-solicitudes" class="grid grid-cols-1 lg:grid-cols-4 gap-3"></div>
                </div>

                <!-- Modal -->
                <div id="modal" class="modal hidden">
                    <div class="modal-content" style="max-width:980px">
                        <div class="flex items-center justify-between mb-2">
                            <h3>Registro Fitosanitario</h3>
                            <button class="btn-icon" onclick="closeModal()"><span class="material-icons">close</span></button>
                        </div>

                        <!-- CONTENIDO PRINT/PDF -->
                        <div id="registro-container"></div>

                        <div class="form-buttons">
                            <button id="btn-descargar" class="btn btn-info">Descargar</button>
                            <button class="btn btn-cancelar" onclick="closeModal()">Cerrar</button>
                        </div>
                    </div>
                </div>

                <!-- Modal Detalle/Edición (limpio con iframe) -->
                <div id="modal-detalle" class="modal hidden">
                    <div class="modal-content" style="max-width:980px">
                        <div class="flex items-center justify-between mb-2">
                            <h3 id="md-title">Editar pedido</h3>
                            <button class="btn-icon" onclick="closeDetalle()"><span class="material-icons">close</span></button>
                        </div>

                        <iframe id="md-iframe" src="" style="width:100%;height:70vh;border:0" loading="lazy" referrerpolicy="no-referrer"></iframe>

                        <div class="form-buttons">
                            <button class="btn btn-cancelar" onclick="closeDetalle()">Cerrar</button>
                        </div>
                    </div>
                </div>

                <!-- Modal Confirmación Eliminar -->
                <div id="modal-confirm" class="modal hidden">
                    <div class="modal-content" style="max-width:520px">
                        <h3 class="mb-2">Confirmar eliminación</h3>
                        <p>Esta acción marcará el pedido como <b>cancelado</b>. ¿Desea continuar?</p>
                        <input type="hidden" id="confirm-id">
                        <div class="form-buttons">
                            <button id="btn-confirm-ok" class="btn btn-aceptar">Eliminar</button>
                            <button class="btn btn-cancelar" onclick="closeConfirm()">Cancelar</button>
                        </div>
                    </div>
                </div>


                <!-- contenedor del toastify -->
                <div id="toast-container"></div>
                <div id="toast-container-boton"></div>
                <!-- Spinner Global -->
                <script src="../../views/partials/spinner-global.js"></script>
                <!-- Debug de sesión (solo campos no sensibles) -->
                <script>
                    (function() {
                        try {
                            const sessionData = <?= json_encode([
                                                    'nombre'         => $nombre,
                                                    'correo'         => $correo,
                                                    'cuit'           => $cuit,
                                                    'rol'            => $rol,
                                                    'telefono'       => $telefono,
                                                    'observaciones'  => $observaciones,
                                                    'id_real'        => $_SESSION['id_real'] ?? null,
                                                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

                            Object.defineProperty(window, '__SVE_SESSION__', {
                                value: Object.freeze(sessionData),
                                writable: false,
                                configurable: false,
                                enumerable: true
                            });
                            console.info('[SVE] Sesión cargada:', sessionData);
                        } catch (err) {
                            console.error('[SVE] Error al exponer la sesión:', err);
                        }
                    })();
                </script>
            </section>
        </div>
    </div>

    <script>
        (function() {
            const API = "../../controllers/ing_pulverizacionController.php";
            const $cards = document.getElementById('cards-solicitudes');
            const $q = document.getElementById('filtro-productor');
            const $btnFiltrar = null;
            const $btnLimpiar = null;
            // Mapa global de solicitudes por id para poder precompletar el formulario de edición
            window.__SVE_SOL_MAP = new Map();

            // Normaliza estados con posibles espacios/mayúsculas
            function normEstado(s) {
                return String(s ?? '').trim().toLowerCase();
            }

            function safeSrc(src) {
                try {
                    if (!src) return '';
                    const u = new URL(src, window.location.origin);
                    // Permitir relativas y mismo host (y opcionalmente tu CDN)
                    const allowed = [window.location.origin, "https://www.framework.impulsagroup.com"];
                    if (allowed.includes(u.origin)) return u.pathname + u.search + u.hash;
                    return '';
                } catch {
                    // Soportar rutas relativas simples
                    if (String(src).startsWith('/')) return src;
                    return '';
                }
            }

            function esc(v) {
                const s = String(v ?? '');
                return s
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            async function openModal(id, estado) {
                const el = document.getElementById('modal');
                const cont = document.getElementById('registro-container');

                // console.log('[SVE][Pulv][openModal] selección:', {id, estado});

                cont.innerHTML = '<div class="skeleton h-8 w-full mb-2"></div>';
                el.classList.remove('hidden');

                if (normEstado(estado) !== 'completada') {
                    console.warn('[SVE][Pulv] Solicitud no completada, no se carga el registro.');
                    cont.innerHTML = '<div class="alert">Registro disponible solo cuando la solicitud está COMPLETADA.</div>';
                    return;
                }

                try {
                    const url = `${API}?action=registro&id=${id}`;
                    // console.log('[SVE][Pulv] Fetch registro:', url);
                    const res = await fetch(url, {
                        credentials: 'same-origin'
                    });
                    // console.log('[SVE][Pulv] HTTP status:', res.status);

                    const raw = await res.json();
                    const payload = (raw && typeof raw === 'object' && 'ok' in raw) ? (raw.ok ? raw.data : null) : raw;

                    // console.log('[RegistroFitosanitario] payload:', payload);

                    if (!payload) {
                        cont.innerHTML = `<div class="alert alert-error">${(raw && raw.error) ? raw.error : 'Error al obtener el registro'}</div>`;
                        return;
                    }

                    const hasAltShape = !!payload.solicitud || !!payload.reporte || !!payload.media;
                    const d = (function normalize() {
                        if (!hasAltShape) {
                            return {
                                numero: payload.numero || payload.solicitud_id || id,
                                solicitud_id: payload.solicitud_id || id,
                                fecha_visita: payload.fecha_visita || '',
                                productor_nombre: payload.productor_nombre || '—',
                                piloto_nombre: payload.piloto_nombre || '—',
                                representante: payload.representante || '—',
                                nombre_finca: payload.nombre_finca || '—',
                                cultivo: payload.cultivo || '—',
                                superficie: payload.superficie || '—',
                                hora_ingreso: payload.hora_ingreso || '—',
                                hora_egreso: payload.hora_egreso || '—',
                                temperatura: payload.temperatura || '—',
                                humedad: payload.humedad || '—',
                                vel_viento: payload.vel_viento || '—',
                                vol_aplicado: payload.vol_aplicado || '—',
                                lavado_dron_miner: payload.lavado_dron_miner || '—',
                                triple_lavado_envases: payload.triple_lavado_envases || '—',
                                productos: (payload.productos || []).map(p => ({
                                    nombre: p.nombre || '',
                                    principio: p.principio || '',
                                    dosis: p.dosis || '',
                                    unidad: p.unidad || '',
                                    cant_usada: p.cant_usada || '',
                                    vto: p.vto || ''
                                })),
                                fotos: payload.fotos || [],
                                firma_cliente: payload.firma_cliente || null,
                                firma_prestador: payload.firma_prestador || null
                            };
                        }

                        const s = payload.solicitud || {};
                        const r = payload.reporte || {};
                        const prods = Array.isArray(payload.productos) ? payload.productos : [];
                        const media = payload.media || {};
                        const fotos = Array.isArray(media.foto) ? media.foto : [];
                        const firmaCliente = Array.isArray(media.firma_cliente) ? (media.firma_cliente[0] || null) : null;
                        const firmaPiloto = Array.isArray(media.firma_piloto) ? (media.firma_piloto[0] || null) : null;

                        return {
                            numero: payload.id_solicitud || s.id || id,
                            solicitud_id: s.id || id,
                            fecha_visita: s.fecha_visita || r.fecha_visita || '',
                            productor_nombre: r.nom_cliente || '—',
                            piloto_nombre: r.nom_piloto || '—',
                            representante: r.nom_encargado || '—',
                            nombre_finca: r.nombre_finca || '—',
                            cultivo: r.cultivo_pulverizado || '—',
                            superficie: r.sup_pulverizada || s.superficie_ha || '—',
                            hora_ingreso: r.hora_ingreso || '—',
                            hora_egreso: r.hora_egreso || '—',
                            temperatura: r.temperatura || '—',
                            humedad: r.humedad_relativa || '—',
                            vel_viento: r.vel_viento || '—',
                            vol_aplicado: r.vol_aplicado || '—',
                            lavado_dron_miner: r.lavado_dron_miner || '—',
                            triple_lavado_envases: r.triple_lavado_envases || '—',
                            productos: prods.map(p => ({
                                nombre: p.nombre_comercial || '',
                                principio: p.principio_activo || '',
                                dosis: (p.dosis_ml_ha ?? ''),
                                unidad: 'ml/ha',
                                cant_usada: (p.cant_usada ?? ''),
                                vto: (p.fecha_vencimiento ?? '')
                            })),
                            fotos,
                            firma_cliente: firmaCliente,
                            firma_prestador: firmaPiloto
                        };

                    })();

                    // console.log('[RegistroFitosanitario] normalizado:', d);

                    cont.innerHTML = `
  <div class="card" style="box-shadow:none;border:0">
    <!-- Fila 1: logo | tarjeta aplicación | N° + fecha -->
    <div class="rf-row cols-3">
      <!-- Logo -->
      <div>
        <img class="rf-logo" src="${safeSrc('../../../assets/png/logo_con_color_original.png')}" alt="SVE">
      </div>

      <!-- Tarjeta de aplicación (centrada) -->
      <div class="rf-pill rf-app">
        <div class="rf-title">Registro Aplicación Drone:</div>
        <div>Ruta50Km1036, SanMartín<br>BodegaToro–Mdz.Arg<br>Teléfo:2612070518</div>
      </div>

      <!-- N° y Fecha -->
      <div class="rf-meta rf-pill">
        <div><strong>N°:</strong> ${esc(d.numero)}</div>
        <div><strong>Fecha:</strong> ${esc(d.fecha_visita)}</div>
      </div>
    </div>

    <!-- Fila 2: Cliente | Cultivo -->
    <div class="rf-row cols-2 rf-section">
      <!-- Datos del cliente -->
      <div class="rf-pill">
        <div><strong>Cliente:</strong> ${esc(d.productor_nombre)}</div>
        <div><strong>Representante:</strong> ${esc(d.representante)}</div>
        <div><strong>Nombre finca:</strong> ${esc(d.nombre_finca)}</div>
      </div>

      <!-- Datos del cultivo -->
      <div class="rf-pill">
        <div><strong>Cultivo pulverizado:</strong> ${esc(d.cultivo)}</div>
        <div><strong>Superficie pulverizada (ha):</strong> ${esc(d.superficie || '—')}</div>
        <div><strong>Operador Drone:</strong> ${esc(d.piloto_nombre)}</div>
      </div>
    </div>

    <div class="rf-pill rf-section">
      <div class="rf-title">Condiciones meteorológicas al momento del vuelo</div>
      <div class="grid grid-cols-3 gap-2">
        <div><strong>Hora Ingreso:</strong> ${esc(d.hora_ingreso || '—')}</div>
        <div><strong>Hora Salida:</strong> ${esc(d.hora_egreso || '—')}</div>
        <div><strong>Temperatura (°C):</strong> ${esc(d.temperatura || '—')}</div>
        <div><strong>Humedad Relativa (%):</strong> ${esc(d.humedad || '—')}</div>
        <div><strong>Vel. Viento (m/s):</strong> ${esc(d.vel_viento || '—')}</div>
        <div><strong>Volumen aplicado (l/ha):</strong> ${esc(d.vol_aplicado || '—')}</div>
        <div><strong>Lavado dron (agua mineralizada):</strong> ${esc(d.lavado_dron_miner || '—')}</div>
        <div><strong>Triple lavado de envases:</strong> ${esc(d.triple_lavado_envases || '—')}</div>
      </div>
    </div>

    <div class="rf-section">
      <div class="rf-title" style="margin-bottom:.4rem;">Productos utilizados</div>
      <div class="rf-table">
        <table class="data-table">
          <thead>
            <tr>
              <th>Nombre Comercial</th>
              <th>Principio Activo</th>
              <th>Dosis (ml/gr/ha)</th>
              <th>Cant. Producto Usado</th>
              <th>Fecha de Vencimiento</th>
            </tr>
          </thead>
          <tbody>
            ${(d.productos||[]).map(p => `
              <tr>
                <td>${esc(p.nombre || '—')}</td>
                <td>${esc(p.principio || '—')}</td>
                <td>${esc(p.dosis || '—')} ${esc(p.unidad ?? '')}</td>
                <td>${esc(p.cant_usada || '—')}</td>
                <td>${esc(p.vto || '—')}</td>
              </tr>`).join('')}
          </tbody>
        </table>
      </div>
    </div>

    <div class="rf-section">
      <div class="rf-title" style="text-align:center;">Registro fotográfico y firmas</div>
      <div class="rf-grid-fotos" style="margin-bottom:.6rem;">
        ${(d.fotos||[]).map(src => {
          const s = safeSrc(src);
          return s ? `<img class="rf-foto" src="${s}" alt="foto">` : '';
        }).join('')}
      </div>
      <div>
        ${d.firma_prestador && safeSrc(d.firma_prestador) ? `<img class="rf-firma" src="${safeSrc(d.firma_prestador)}" alt="firma prestador">` : ''}
        <div class="rf-caption">Firma Prestador de Servicio</div>
      </div>
    </div>
  </div>
`;

                    const btns = document.querySelector('.modal .form-buttons');
                    if (btns) {
                        btns.innerHTML = `
    <button id="btn-descargar" class="btn btn-info">Descargar PDF</button>
    <button class="btn btn-aceptar" onclick="closeModal()">Aceptar</button>
    <button class="btn btn-cancelar" onclick="closeModal()">Cancelar</button>
  `;
                    }


                    document.getElementById('btn-descargar').onclick = async function() {
                        const {
                            jsPDF
                        } = window.jspdf;
                        const node = document.getElementById('registro-container');

                        const canvas = await html2canvas(node, {
                            scale: 2,
                            useCORS: true,
                            backgroundColor: '#ffffff',
                            allowTaint: false
                        });

                        const imgData = canvas.toDataURL('image/png');
                        const pdf = new jsPDF({
                            orientation: 'p',
                            unit: 'pt',
                            format: 'a4'
                        });
                        const pageWidth = pdf.internal.pageSize.getWidth();
                        const pageHeight = pdf.internal.pageSize.getHeight();
                        const ratio = Math.min(pageWidth / canvas.width, pageHeight / canvas.height);
                        const w = canvas.width * ratio;
                        const h = canvas.height * ratio;
                        const x = (pageWidth - w) / 2;
                        const y = 20;

                        pdf.setFillColor(255, 255, 255);
                        pdf.rect(0, 0, pageWidth, pageHeight, 'F');

                        pdf.addImage(imgData, 'PNG', x, y, w, h);
                        pdf.save(`registro_${id}.pdf`);
                    };
                } catch (e) {
                    console.error('[SVE][Pulv] Excepción al cargar registro:', e);
                    cont.innerHTML = `<div class="alert alert-error">Error inesperado al cargar el registro.</div>`;
                }
            }

            window.openModal = openModal;

            // ===== Nuevo flujo de edición: abre editor externo en iframe (con prefill COMPLETO) =====
            function openEditar(id) {
                const modal = document.getElementById('modal-detalle');
                const iframe = document.getElementById('md-iframe');
                const numId = Number(id);

                // Abrimos el editor con el ID en querystring (permite que el propio editor haga fetch si quiere)
                // cache-buster para evitar que el navegador use una versión vieja del iframe
                iframe.src = `ing_new_pulverizacion_edit.php?id=${numId}&_=${Date.now()}`;
                document.getElementById('md-title').textContent = 'Editar pedido';
                modal.classList.remove('hidden');

                // Helper: envía prefills al iframe si está disponible
                const sendToIframe = (payload) => {
                    if (!iframe || !iframe.contentWindow) return;
                    try {
                        // Prefill mínimo (productor) para que el typeahead quede seteado inmediato
                        const prodPayload = {
                            id_real: payload?.productor_id_real ?? payload?.productorIdReal ?? payload?.id_real ?? '',
                            nombre: payload?.productor_nombre ?? payload?.productorNombre ?? payload?.usuario ?? ''
                        };
                        if (prodPayload.id_real && prodPayload.nombre) {
                            iframe.contentWindow.postMessage({
                                type: 'sve:modal_prefill',
                                payload: prodPayload
                            }, '*');
                        }

                        // Prefill COMPLETO (todos los campos, patologías, productos, etc.)
                        iframe.contentWindow.postMessage({
                            type: 'sve:prefill_solicitud',
                            payload
                        }, '*');
                    } catch (e) {
                        console.warn('[SVE][Pulv] Error enviando prefill al iframe:', e);
                    }
                };

                // Al cargar el iframe, el propio editor se autogestiona (fetch detalle por id y prefill)
                iframe.addEventListener('load', () => {
                    // No hacemos nada aquí.
                }, {
                    once: true
                });

            }

            // Escucha de eventos del iframe para refrescar listado al guardar
            window.addEventListener('message', (ev) => {
                try {
                    if (ev && ev.data && ev.data.type === 'sve:solicitud_actualizada') {
                        // Cierra el modal de edición y recarga las tarjetas
                        closeDetalle();
                        cargar();
                    }
                } catch (e) {
                    console.warn('[SVE][Pulv] postMessage no procesado:', e);
                }
            });


            window.closeDetalle = function() {
                const modal = document.getElementById('modal-detalle');
                const iframe = document.getElementById('md-iframe');
                // Limpieza del src para liberar recursos del iframe al cerrar
                if (iframe) iframe.src = '';
                modal.classList.add('hidden');
            };

            window.closeModal = function() {
                document.getElementById('modal').classList.add('hidden');
            };

            (function() {
                const modalEl = document.getElementById('modal');

                // Click en el fondo (fuera de .modal-content)
                modalEl.addEventListener('click', (ev) => {
                    // Si el usuario clickea exactamente el overlay (no un hijo), cerramos
                    if (ev.target === modalEl && !modalEl.classList.contains('hidden')) {
                        closeModal();
                    }
                });

                // Tecla Escape
                document.addEventListener('keydown', (ev) => {
                    if (ev.key === 'Escape' && !modalEl.classList.contains('hidden')) {
                        closeModal();
                    }
                });
            })();

            function badgeEstado(estado) {
                const cls = {
                    ingresada: 'badge warning',
                    procesando: 'badge info',
                    aprobada_coop: 'badge info',
                    visita_realizada: 'badge info',
                    completada: 'badge success',
                    cancelada: 'badge danger'
                } [normEstado(estado)] || 'badge';
                return `<span class="${cls}">${estado || '—'}</span>`;
            }

            function fmtMoney(v) {
                try {
                    return Number(v || 0).toLocaleString('es-AR', {
                        style: 'currency',
                        currency: 'ARS'
                    });
                } catch (e) {
                    return '$0';
                }
            }

            function openConfirm(id) {
                document.getElementById('confirm-id').value = String(id);
                document.getElementById('modal-confirm').classList.remove('hidden');
            }

            function closeConfirm() {
                document.getElementById('modal-confirm').classList.add('hidden');
            }
            window.closeConfirm = closeConfirm;

            document.getElementById('btn-confirm-ok').addEventListener('click', async (e) => {
                e.preventDefault();
                const id = Number(document.getElementById('confirm-id').value || 0);
                closeConfirm();
                if (id > 0) await eliminarSolicitud(id);
            });


            function row(r) {
                const estado = normEstado(r.estado);
                const precio = fmtMoney(r.costo_total);
                const badge = `<span class="badge estado">${r.estado || '—'}</span>`;
                const estadoClass = estado === 'completada' ? 'estado-completada' : '';
                return `
    <div class="sol-card ${estadoClass}">
      <div>
        <div class="titulo">${esc(r.productor_nombre || r.productor_id_real)}</div>
        <div class="numero">Pedido número: ${esc(r.id)}</div>
        <div class="mt-2"><span class="link">Observaciones</span></div>
        <div class="observaciones">${esc(r.observaciones || '—')}</div>
        <div class="mt-2 precio">${precio}</div>
        <div class="mt-1">${badge}</div>
      </div>
      <div class="acciones">
        <button class="btn btn-aceptar btn-editar" data-id="${r.id}">Ver y Modificar</button>
        <button class="btn btn-cancelar btn-eliminar" data-id="${r.id}">Eliminar</button>
        ${estado === 'completada' ? `
          <button class="btn btn-info btn-open-registro" data-id="${r.id}" data-estado="${(r.estado||'').replace(/"/g, '&quot;')}">Registro</button>
        ` : ``}
      </div>
    </div>`;
            }


            // Delegación para acciones de tarjeta
            $cards.addEventListener('click', (ev) => {
                const btnReg = ev.target.closest('.btn-open-registro');
                if (btnReg) {
                    ev.preventDefault();
                    const id = Number(btnReg.dataset.id || 0);
                    const estado = btnReg.dataset.estado || '';
                    if (id > 0) openModal(id, estado);
                    return;
                }
                const btnVer = ev.target.closest('.btn-ver');
                if (btnVer) {
                    ev.preventDefault();
                    openDetalle(Number(btnVer.dataset.id), false);
                    return;
                }
                const btnEd = ev.target.closest('.btn-editar');
                if (btnEd) {
                    ev.preventDefault();
                    openEditar(Number(btnEd.dataset.id));
                    return;
                }
                const btnDel = ev.target.closest('.btn-eliminar');
                if (btnDel) {
                    ev.preventDefault();
                    openConfirm(Number(btnDel.dataset.id));
                }
            });

            // Cerrar modal de confirmación con fondo o ESC
            (function() {
                const m = document.getElementById('modal-confirm');
                if (!m) return;
                m.addEventListener('click', (ev) => {
                    if (ev.target === m) closeConfirm();
                });
                document.addEventListener('keydown', (ev) => {
                    if (ev.key === 'Escape' && !m.classList.contains('hidden')) closeConfirm();
                });
            })();

            async function cargar(page = 1, size = 20) {
                $cards.innerHTML = `<div class="skeleton h-10 w-full"></div>`;
                const params = new URLSearchParams({
                    action: 'list_ingeniero',
                    page: String(page),
                    size: String(size),
                    q: $q.value.trim()
                });
                const url = `${API}?${params.toString()}`;

                // console.log('[SVE][Pulv] Fetch listado:', url);

                try {
                    const res = await fetch(url, {
                        credentials: 'same-origin'
                    });
                    // console.log('[SVE][Pulv] listado status:', res.status);
                    const j = await res.json();
                    // console.log('[SVE][Pulv] listado payload:', j);

                    if (!j.ok) {
                        $cards.innerHTML = `<div class="alert alert-error">${j.error||'Error'}</div>`;
                        return;
                    }
                    const rows = j.data.items || [];
                    if (!rows.length) {
                        $cards.innerHTML = `<div class="alert">Sin resultados</div>`;
                        return;
                    }
                    // Guardar items en un mapa global (id -> objeto solicitud) para prefill
                    try {
                        window.__SVE_SOL_MAP = new Map((rows || []).map(r => [Number(r.id), r]));
                    } catch (e) {
                        console.warn('[SVE][Pulv] No se pudo construir el mapa de solicitudes:', e);
                    }
                    $cards.innerHTML = rows.map(row).join('');
                } catch (e) {
                    console.error('[SVE][Pulv] Error listado:', e);
                    $cards.innerHTML = `<div class="alert alert-error">Error cargando listado</div>`;
                }
            }

            // Debounce simple para búsqueda en vivo
            function debounce(fn, ms = 250) {
                let t;
                return (...a) => {
                    clearTimeout(t);
                    t = setTimeout(() => fn(...a), ms);
                };
            }
            const onType = debounce(() => cargar(), 250);
            $q.addEventListener('input', onType);

            document.addEventListener('DOMContentLoaded', () => {
                cargar();
            });

            async function eliminarSolicitud(id) {
                // solo usa el modal de confirmación; aquí NO se pide confirmación de nuevo
                const res = await fetch(API, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'delete',
                        id
                    })
                });
                const j = await res.json();
                if (!j.ok) {
                    // mostrar error sin alert nativo
                    const msg = (j.error || 'No se pudo eliminar');
                    const host = document.getElementById('cards-solicitudes');
                    if (host) {
                        host.insertAdjacentHTML('afterbegin', `<div class="alert alert-error">${esc(msg)}</div>`);
                    } else {
                        console.error(msg);
                    }
                    return;
                }
                cargar();
            }

            window.closeDetalle = function() {
                document.getElementById('modal-detalle').classList.add('hidden');
            };

            document.addEventListener('DOMContentLoaded', () => {
                cargar();
            });
        })();
    </script>


    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('modal-edit-solicitud');
            const iframe = document.getElementById('iframe-edit-solicitud');

            document.querySelectorAll('.btn-editar').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.dataset.id;
                    if (!id) return alert('ID de solicitud no encontrado');
                    iframe.src = `ing_new_pulverizacion_edit.php?id=${id}`;
                    modal.classList.add('show');
                    modal.style.display = 'block';
                });
            });
        });
    </script>


    <!-- Mantener defer; si el tutorial manipula tabs, no debe sobreescribir el estado -->
    <script src="../partials/tutorials/cooperativas/pulverizacion.js?v=<?= time() ?>" defer></script>

</body>

</html>