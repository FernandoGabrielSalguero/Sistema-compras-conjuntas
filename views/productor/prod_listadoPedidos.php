<?php

declare(strict_types=1);
ini_set('display_errors', '0');
error_reporting(E_ALL);

session_start();

require_once '../../middleware/authMiddleware.php';
checkAccess('productor');
$nombre  = $_SESSION['nombre']  ?? 'Sin nombre';
$idReal  = $_SESSION['id_real'] ?? null;

$sesion_payload = [
    'id_real' => $idReal,
    'nombre'  => $nombre,
    'usuario' => $_SESSION['usuario'] ?? null,
    'rol'     => $_SESSION['rol'] ?? null,
];
?>
<script id="session-data" type="application/json">
    <?= json_encode($sesion_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
</script>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SVE - Mis pedidos</title>

    <!-- Material Icons (si ya lo usás en el proyecto) -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />

    <!-- CDN proyecto -->
    <link rel="stylesheet" href="https://framework.impulsagroup.com/assets/css/framework.css">
    <script src="https://framework.impulsagroup.com/assets/javascript/framework.js" defer></script>

    <style>
        .main {
            margin-left: 0;
        }

        .header-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 1rem;
        }

        .listado {
            display: grid;
            gap: .75rem;
        }

        .pedido-card {
            border: 1px solid #eee;
            border-radius: 14px;
            background: #fff;
            padding: .85rem .9rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .04);
        }

        .pedido-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .75rem;
        }

        .pedido-title {
            font-weight: 700;
            font-size: 1rem;
            margin: 0;
        }

        .pedido-meta {
            display: grid;
            grid-template-columns: 1fr;
            gap: .35rem;
            margin-top: .5rem;
        }

        .row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: .75rem;
        }

        .label {
            color: #475569;
            font-size: .92rem;
        }

        .value {
            font-weight: 600;
            font-variant-numeric: tabular-nums;
            text-align: right;
            white-space: nowrap;
        }

        .estado-badge {
            padding: .15rem .5rem;
            border-radius: 999px;
            font-size: .78rem;
            line-height: 1;
        }

        .estado-ingresada {
            background: #e6f4ff;
            color: #0b72b9;
        }

        .estado-procesando {
            background: #fff7e6;
            color: #b6720b;
        }

        .estado-aprobada_coop {
            background: #f0f9ff;
            color: #075985;
        }

        .estado-completada {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .estado-cancelada {
            background: #ffe8e8;
            color: #b71c1c;
        }

        .card-actions {
            display: flex;
            gap: .5rem;
            margin-top: .75rem;
            justify-content: flex-end;
        }

        .btn-cancelar-soft {
            background: #fff;
            color: #b71c1c;
            border: 1px solid #ffd1d1;
        }

        @media (min-width:680px) {
            .pedido-meta {
                grid-template-columns: 1fr 1fr;
            }
        }

        /* ==== Modal cancelación (mobile-first) ==== */
        .modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .55);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            z-index: 10000;
        }

        .modal.is-open {
            display: flex;
        }

        .modal-content {
            background: #fff;
            border-radius: 10px;
            max-width: 1100px;
            /* <-- Cambiá este valor para definir el ancho deseado */
            width: 90%;
            /* <-- Ajusta el porcentaje de ancho */
            max-height: 85vh;
            /* <-- Ahora no ocupa el 100% del alto */
            overflow-y: auto;
            padding: 20px;
        }

        .modal-header,
        .modal-actions {
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            border-bottom: 1px solid #eee;
        }

        .modal-actions {
            border-top: 1px solid #eee;
            border-bottom: 0;
            justify-content: flex-end;
        }

        .modal-title {
            font-size: 1.05rem;
            font-weight: 700;
            margin: 0;
        }

        .modal-body {
            padding: 1rem 1.25rem;
            color: #475569;
        }

        .modal-close {
            border: 0;
            background: transparent;
            cursor: pointer;
        }

        /* ===== Estilos Registro Fitosanitario (modal) ===== */
        #registroPrint {
            background: #fff;
            padding: 18px 20px;
        }

        .rf-row {
            display: grid;
            gap: 12px;
            margin-bottom: 12px
        }

        .rf-grid-2 {
            grid-template-columns: 1fr 1fr
        }

        .rf-card {
            border: 1px solid #eee;
            border-radius: 12px;
            padding: 14px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .05)
        }

        .rf-muted {
            color: #475569
        }

        .rf-title {
            font-weight: 700;
            margin: 0 0 6px 0;
            text-align: center
        }

        .rf-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px
        }

        .rf-table th,
        .rf-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e9ecef;
            text-align: left;
            font-size: .92rem;
        }

        .rf-media {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px
        }

        .rf-media img {
            width: 100%;
            height: 140px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid #eee
        }

        .rf-signs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-top: 10px
        }

        .rf-sign {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px
        }

        .rf-sign img {
            width: 180px;
            height: 90px;
            object-fit: contain;
            border: 1px dashed #bbb;
            background: #fff
        }

        .rf-actions {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
            margin-top: 10px
        }

        @media (max-width:720px) {
            .rf-grid-2 {
                grid-template-columns: 1fr
            }

            .rf-media {
                grid-template-columns: repeat(2, 1fr)
            }
        }

        #modal-fito-json .hidden {
        display: none !important
    }

    #modal-fito-json table td,
    #modal-fito-json table th {
        font-size: .95rem
    }

    /* Mostrar menú solo a SVE */
    #modal-fito-json.role-no-sve .menu-flotante {
        display: none !important;
    }


    /* Centramos el contenedor y damos padding para respirar en viewport pequeños */
    #modal-fito-json {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
    }

    /* Caja del modal: tamaño máximo y scroll vertical */
    #modal-fito-json .modal-content {
        max-width: 1024px;
        /* respeta tu ancho actual */
        max-height: 82vh;
        /* ACHICADO: no ocupa toda la altura */
        overflow: auto;
        /* scroll interno */
        scrollbar-gutter: stable both-edges;
    }

    /* Menú hamburguesa flotante en esquina superior derecha */
    #modal-fito-json .menu-flotante {
        position: sticky;
        top: 8px;
        margin-left: auto;
        display: flex;
        justify-content: flex-end;
        z-index: 3;
    }

    /* Dropdown del menú */
    #modal-fito-json .menu-dropdown {
        position: absolute;
        right: 16px;
        top: 56px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .12);
        padding: 8px;
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-width: 160px;
    }

    #modal-fito-json .menu-dropdown.hidden {
        display: none !important;
    }


    /* Scrollbar amigable (opcional) */
    #modal-fito-json .modal-content::-webkit-scrollbar {
        width: 8px;
    }

    #modal-fito-json .modal-content::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 8px;
    }

    /* Un poco menos de margen vertical entre cards internas */
    #modal-fito-json .card {
        margin-top: 10px;
    }

    /* En móviles permitimos un poco más de alto útil */
    @media (max-width: 768px) {
        #modal-fito-json .modal-content {
            max-height: 88vh;
        }
    }

    /* Impresión se mantiene como antes (si ya lo añadiste en la vista principal, no hace falta duplicar) */
    @media print {
        body * {
            visibility: hidden;
        }

        #modal-fito-json,
        #modal-fito-json * {
            visibility: visible;
        }

        #modal-fito-json .form-buttons,
        #modal-fito-json .menu-flotante {
            display: none !important;
        }


        #modal-fito-json .modal-content {
            box-shadow: none !important;
            border: none !important;
        }
    }

    /* Tamaño más grande para fotos (mejor impresión) */
    #fito-galeria img {
        height: 220px !important;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        object-fit: cover;
        width: 100%;
    }
    </style>
</head>

<body>
    <div class="layout">
        <div class="main">
            <header class="navbar">
                <h4>Listado de pedidos de pulverización con dron</h4>
            </header>

            <section class="content">
                <!-- Intro -->
                <div class="card">
                    <p>Señor productor, aquí puede ver sus solicitudes y, si fuera necesario, cancelarlas antes de ser atendidas.</p>
                </div>

                <!-- Header resumen / volver -->
                <div class="card header-card">
                    <div>
                        <h4><?= htmlspecialchars($nombre) ?></h4>
                        <p>¿Queres ir al atras?</p>
                    </div>
                    <a class="btn btn-info" href="prod_dashboard.php">Apreta acá</a>
                </div>

                <!-- Listado -->
                <div class="card">
                    <h4>Mis pedidos</h4>
                    <div id="listado" class="listado" aria-live="polite"></div>
                    <div id="paginacion" class="form-buttons" style="justify-content:center; margin-top: .5rem;"></div>
                </div>

                <!-- Modal de confirmación de cancelación -->
                <div id="modalCancel" class="modal" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="modalCancelTitle">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 id="modalCancelTitle" class="modal-title">¿Cancelar la solicitud?</h3>
                            <button type="button" class="modal-close" aria-label="Cerrar" onclick="closeCancelModal()">
                                <span class="material-icons">close</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>Vas a cancelar la solicitud <strong id="modalCancelIdText">#</strong>. Esta acción actualizará el estado a <b>cancelada</b> y registrará el motivo <i>“Cancelada por productor”. Esta acción no se puede revertir.</i>.</p>

                        </div>
                        <div class="modal-actions">
                            <button class="btn btn-cancelar" onclick="closeCancelModal()">Volver</button>
                            <button id="btnModalConfirm" class="btn btn-aceptar">Sí, cancelar</button>
                        </div>
                    </div>
                </div>

                <!-- Modal de Registro Fitosanitario -->
                <div id="modalRegistro" class="modal" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="modalRegistroTitle">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 id="modalRegistroTitle" class="modal-title">Registro Fitosanitario</h3>
                            <button type="button" class="modal-close" aria-label="Cerrar" onclick="closeRegistroModal()">
                                <span class="material-icons">close</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <!-- Contenedor imprimible -->
                            <div id="registroPrint">
                                <p class="gform-helper">Cargando…</p>
                            </div>
                        </div>
                        <div class="modal-actions rf-actions">
                            <button id="btnPdf" class="btn btn-info">Descargar PDF</button>
                            <button class="btn btn-aceptar" onclick="closeRegistroModal()">Aceptar</button>
                            <button class="btn btn-cancelar" onclick="closeRegistroModal()">Cancelar</button>
                        </div>
                    </div>
                </div>

            </section>
        </div>
    </div>

    <!-- Spinner global (si lo tenés en tu proyecto) -->
    <script src="../../views/partials/spinner-global.js"></script>
    <!-- Exportar a PDF -->
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <script>
        (() => {
            const API = '../../controllers/prod_listadoPedidosController.php';
            const $ = (s, ctx = document) => ctx.querySelector(s);
            const $$ = (s, ctx = document) => Array.from(ctx.querySelectorAll(s));

            const ses = (() => {
                try {
                    return JSON.parse($('#session-data')?.textContent || '{}')
                } catch {
                    return {}
                }
            })();
            const listado = $('#listado');
            const pag = $('#paginacion');
            const PAGE_SIZE = 8;
            let page = 1;

            const fmtNum = (n) => new Intl.NumberFormat('es-AR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(Number(n || 0));
            const fmtFecha = (s) => s ? new Date(s).toLocaleDateString('es-AR') : '—';

            async function apiGet(params) {
                const url = `${API}?${new URLSearchParams(params).toString()}`;
                const res = await fetch(url, {
                    credentials: 'same-origin'
                });
                const json = await res.json().catch(() => ({
                    ok: false,
                    error: 'JSON inválido'
                }));
                if (!json.ok) throw new Error(json.error || 'Error de red');
                return json.data;
            }
            async function apiPost(body) {
                const res = await fetch(API, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(body)
                });
                const json = await res.json().catch(() => ({
                    ok: false,
                    error: 'JSON inválido'
                }));
                if (!json.ok) throw new Error(json.error || 'Error de red');
                return json;
            }

            function renderItems(items, total, pageNow) {
                listado.innerHTML = items.map(it => {
                    const estadoClass = `estado-${it.estado}`;
                    const puedeCancelar = (['ingresada', 'aprobada_coop'].includes(it.estado));
                    const estadoNorm = String(it.estado ?? '').trim().toLowerCase();
                    const puedeVerRegistro = (estadoNorm === 'completada');


                    return `
        <article class="pedido-card" aria-label="Solicitud #${it.id}">
          <div class="pedido-head">
            <h5 class="pedido-title">Solicitud #${it.id}</h5>
            <span class="estado-badge ${estadoClass}">${it.estado}</span>
          </div>
          <div class="pedido-meta">
            <div class="row"><span class="label">Hectáreas</span><span class="value">${fmtNum(it.superficie_ha)}</span></div>
            <div class="row"><span class="label">Fecha de visita</span><span class="value">${fmtFecha(it.fecha_visita)}</span></div>
            <div class="row"><span class="label">Horario de visita</span><span class="value">${it.hora_visita || '—'}</span></div>
            <div class="row"><span class="label">Patologías</span><span class="value" style="white-space:normal; text-align:right;">${it.patologias || '—'}</span></div>
            <div class="row"><span class="label">Costo del servicio</span><span class="value">${fmtNum(it.costo_total)} ${it.moneda||''}</span></div>
          </div>
          <div class="card-actions">
            <button
              class="btn btn-cancelar btn-cancelar-soft"
              data-id="${it.id}"
              aria-label="Cancelar solicitud ${it.id}"
              ${puedeCancelar ? '' : 'disabled'}
              title="${puedeCancelar ? 'Cancelar solicitud' : 'Solo se puede cancelar en estado INGRESADA o APROBADA_COOP'}"
            >Cancelar</button>
            ${puedeVerRegistro ? `
            <button
              class="btn btn-info btn-registro"
              data-id="${it.id}"
              aria-label="Ver registro fitosanitario ${it.id}"
              title="Registro Fitosanitario"
            >Registro Fitosanitario</button>` : ``}
          </div>
        </article>
      `;
                }).join('') || `<div class="gform-helper">No hay pedidos todavía.</div>`;


                // Pagination (simple)
                const pages = Math.max(1, Math.ceil(total / PAGE_SIZE));
                pag.innerHTML = pages > 1 ? `
      <div class="form-grid grid-3">
        <button class="btn" ${pageNow<=1?'disabled':''} data-nav="prev">Anterior</button>
        <span class="gform-helper" style="align-self:center;">Página ${pageNow} de ${pages}</span>
        <button class="btn" ${pageNow>=pages?'disabled':''} data-nav="next">Siguiente</button>
      </div>` : '';
            }

            async function load() {
                try {
                    window.showSpinner?.();
                    const data = await apiGet({
                        action: 'list',
                        page: String(page),
                        size: String(PAGE_SIZE)
                    });
                    renderItems(data.items, data.total, data.page);
                } catch (e) {
                    window.showToast?.('error', e.message || 'No se pudo cargar el listado.');
                } finally {
                    window.hideSpinner?.();
                }
            }

            // ====== Modal de cancelación ======
            const modalCancel = document.getElementById('modalCancel');
            const modalCancelIdText = document.getElementById('modalCancelIdText');
            const btnModalConfirm = document.getElementById('btnModalConfirm');
            let __pendingCancelId = null;

            function openCancelModal(id) {
                if (!modalCancel) {
                    window.showToast?.('error', 'No se pudo abrir el modal.');
                    return;
                }
                __pendingCancelId = Number(id);
                const idLabel = modalCancelIdText || modalCancel.querySelector('#modalCancelIdText');
                if (idLabel) idLabel.textContent = `#${__pendingCancelId}`;
                modalCancel.classList.add('is-open');
                modalCancel.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
                setTimeout(() => btnModalConfirm?.focus(), 0);
            }


            function closeCancelModal() {
                __pendingCancelId = null;
                modalCancel.classList.remove('is-open');
                modalCancel.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            }
            window.closeCancelModal = closeCancelModal;

            modalCancel?.addEventListener('click', (e) => {
                if (e.target === modalCancel) closeCancelModal();
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && modalCancel?.classList.contains('is-open')) closeCancelModal();
            });

            btnModalConfirm?.addEventListener('click', async () => {
                if (!__pendingCancelId) return closeCancelModal();
                const id = __pendingCancelId;

                try {
                    btnModalConfirm.disabled = true;
                    btnModalConfirm.textContent = 'Cancelando…';
                    window.showSpinner?.();

                    await apiPost({
                        action: 'cancel',
                        id
                    });

                    // Actualización inmediata en la tarjeta
                    const card = listado.querySelector(`.pedido-card .btn-cancelar[data-id="${id}"]`)?.closest('.pedido-card');
                    if (card) {
                        const badge = card.querySelector('.estado-badge');
                        badge.textContent = 'cancelada';
                        badge.className = 'estado-badge estado-cancelada';
                        const btn = card.querySelector('.btn-cancelar');
                        if (btn) btn.remove();
                    }

                    // Aviso y refresco suave del listado
                    window.showAlert?.('success', '¡Solicitud cancelada correctamente!');
                    closeCancelModal();
                    await load(); // sincroniza con servidor sin recargar página
                } catch (e) {
                    window.showToast?.('error', e.message || 'No se pudo cancelar.');
                } finally {
                    btnModalConfirm.disabled = false;
                    btnModalConfirm.textContent = 'Sí, cancelar';
                    window.hideSpinner?.();
                }
            });

            // Delegación: abrir modal desde cada tarjeta (solo si está pendiente)
            listado.addEventListener('click', (ev) => {
                const btn = ev.target.closest('button.btn-cancelar');
                if (!btn) return;
                if (btn.hasAttribute('disabled')) return; // evita abrir modal si está deshabilitado


                const card = btn.closest('.pedido-card');
                const badge = card?.querySelector('.estado-badge');
                const estado = badge?.textContent?.trim()?.toLowerCase() || '';
                const cancelables = ['ingresada', 'aprobada_coop'];
                const esCancelable = cancelables.includes(estado);

                if (!esCancelable) {
                    window.showToast?.('error', 'Solo se pueden cancelar solicitudes en estado INGRESADA o APROBADA_COOP.');
                    return;
                }
                openCancelModal(btn.dataset.id);
            });

            // ====== Modal de Registro Fitosanitario ======
            const modalRegistro = document.getElementById('modalRegistro');
            const registroBody = document.getElementById('registroBody');
            let __invokerBtnRegistro = null;

            function openRegistroModal(id) {
                if (!modalRegistro) {
                    window.showToast?.('error', 'No se pudo abrir el modal.');
                    return;
                }
                modalRegistro.classList.add('is-open');
                modalRegistro.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
                cargarRegistro(id);
            }

            function closeRegistroModal() {
                modalRegistro.classList.remove('is-open');
                modalRegistro.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
                if (__invokerBtnRegistro) {
                    __invokerBtnRegistro.focus();
                    __invokerBtnRegistro = null;
                }
            }
            window.closeRegistroModal = closeRegistroModal;

            async function cargarRegistro(id) {
                try {
                    const cont = document.getElementById('registroPrint');
                    cont.innerHTML = '<p class="gform-helper">Cargando…</p>';

                    const resp = await apiGet({
    action: 'detail',
    id: String(id)
});

const s = resp.solicitud || {};
const r = resp.reporte || {};
const prods = Array.isArray(resp.productos) ? resp.productos : [];
const media = resp.media || {
    foto: [],
    firma_cliente: [],
    firma_piloto: []
};

// === DEBUG: imprimir en consola lo que se muestra en el modal ===
const registroFitoDebug = {
    id_solicitud: s.id ?? id,
    solicitud: s,
    reporte: r,
    productos: prods,
    media: media
};
try {
    console.log('[RegistroFitosanitario] payload:', JSON.stringify(registroFitoDebug, null, 2));
} catch (e) {
    console.log('[RegistroFitosanitario] payload (obj):', registroFitoDebug);
}


                    const f = (x) => (x ?? '—');
                    const fmtDT = (x) => x ? new Date(x).toLocaleString('es-AR') : '—';
                    const addr = [s.dir_calle, s.dir_numero, s.dir_localidad, s.dir_provincia].filter(Boolean).join(' ');

                    const normalizaRuta = (p) => {
                        if (!p) return '';
                        // si ya viene absoluta (/uploads/...), la dejamos; si es relativa, anteponemos "../../"
                        const clean = String(p).replace(/^\.?\/*/, '');
                        return p.startsWith('/') ? p : `../../${clean}`;
                    };

                    // Galería de fotos
                    const fotosHtml = (media.foto || [])
                        .map(src => `<img src="${normalizaRuta(src)}" alt="foto">`)
                        .join('');

                    // Firmas (si no existen, mostramos marco vacío)
                    const firmaCliente = (media.firma_cliente && media.firma_cliente[0]) ?
                        `<img src="${normalizaRuta(media.firma_cliente[0])}" alt="firma cliente">` :
                        `<div style="width:180px;height:90px;border:1px dashed #bbb;background:#fff;"></div>`;

                    const firmaPiloto = (media.firma_piloto && media.firma_piloto[0]) ?
                        `<img src="${normalizaRuta(media.firma_piloto[0])}" alt="firma piloto">` :
                        `<div style="width:180px;height:90px;border:1px dashed #bbb;background:#fff;"></div>`;

                    const prodsHtml = prods.map(p => `
                        <tr>
                            <td>${f(p.nombre_comercial)}</td>
                            <td>${f(p.principio_activo)}</td>
                            <td>${f(p.dosis_ml_ha)}</td>
                            <td>${f(p.cant_usada)}</td>
                            <td>${f(p.fecha_vencimiento)}</td>
                        </tr>`).join('');

                    // ==== Formato estilo “fito-” (estructura y estilos inline, como tu ejemplo) ====
cont.innerHTML = `
<div id="fito-formato" style="background:#fff; border-radius:14px; padding:16px;">
  <!-- Encabezado -->
  <div class="header" style="display:flex; align-items:center; gap:16px; border-bottom:1px solid #e5e7eb; padding-bottom:12px; margin-bottom:12px;">
    <img id="fito-logo" src="/assets/png/logo_con_color_original.png" alt="Logo" style="height:56px; width:auto;">
    <div style="flex:1;">
      <div style="font-weight:600;">Registro Aplicación Drone:</div>
      <div>Ruta50Km1036,SanMartín</div>
      <div>BodegaToro–Mdz.Arg</div>
      <div>Teléfonodecontacto:261-2070518</div>
    </div>
    <div style="text-align:right;">
      <div><strong>N°: ${f(s.id)}</strong></div>
      <div>Fecha: ${f(s.fecha_visita)}</div>
    </div>
  </div>

  <!-- Datos principales -->
  <div class="grid-2" style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
    <div class="card" style="padding:12px; border:1px solid #e5e7eb; border-radius:12px;">
      <div><strong>Cliente:</strong> ${f(r.nom_cliente)}</div>
      <div><strong>Representante:</strong> ${f(r.nom_encargado)}</div>
      <div><strong>Nombre finca:</strong> ${f(r.nombre_finca)}</div>
    </div>
    <div class="card" style="padding:12px; border:1px solid #e5e7eb; border-radius:12px;">
      <div><strong>Cultivo pulverizado:</strong> ${f(r.cultivo_pulverizado)}</div>
      <div><strong>Superficie pulverizada (ha):</strong> ${f(r.sup_pulverizada)}</div>
      <div><strong>Operador Drone:</strong> ${f(r.nom_piloto)}</div>
    </div>
  </div>

  <!-- Condiciones (solo si hay datos) -->
  <div class="card" style="margin-top:12px; padding:12px; border:1px solid #e5e7eb; border-radius:12px; ${(r.hora_ingreso||r.hora_egreso||r.temperatura||r.humedad_relativa||r.vel_viento||r.vol_aplicado)?'':'display:none;'}">
    <div style="font-weight:600; margin-bottom:8px;">Condiciones meteorológicas al momento del vuelo</div>
    <div class="grid-4" style="display:grid; grid-template-columns: repeat(4,1fr); gap:8px;">
      <div>Hora Ingreso: <span>${f(r.hora_ingreso)}</span></div>
      <div>Hora Salida: <span>${f(r.hora_egreso)}</span></div>
      <div>Temperatura (°C): <span>${f(r.temperatura)}</span></div>
      <div>Humedad Relativa (%): <span>${f(r.humedad_relativa)}</span></div>
      <div>Vel. Viento (m/s): <span>${f(r.vel_viento)}</span></div>
      <div>Volumen aplicado (l/ha): <span>${f(r.vol_aplicado)}</span></div>
    </div>
  </div>

  <!-- Tabla de productos -->
  <div class="card" style="margin-top:12px; padding:12px; border:1px solid #e5e7eb; border-radius:12px;">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
      <div style="font-weight:600;">Productos utilizados</div>
    </div>
    <div style="overflow:auto;">
      <table style="width:100%; border-collapse:collapse;">
        <thead>
          <tr>
            <th style="border-bottom:1px solid #e5e7eb; text-align:left; padding:8px;">Nombre Comercial</th>
            <th style="border-bottom:1px solid #e5e7eb; text-align:left; padding:8px;">Principio Activo</th>
            <th style="border-bottom:1px solid #e5e7eb; text-align:left; padding:8px;">Dosis (ml/gr/ha)</th>
            <th style="border-bottom:1px solid #e5e7eb; text-align:left; padding:8px;">Cant. Producto Usado</th>
            <th style="border-bottom:1px solid #e5e7eb; text-align:left; padding:8px;">Fecha de Vencimiento</th>
          </tr>
        </thead>
        <tbody>
          ${prodsHtml}
        </tbody>
      </table>
    </div>
  </div>

  <!-- Galería -->
  <div class="card" style="margin-top:12px; padding:12px; border:1px solid #e5e7eb; border-radius:12px;">
    <div style="font-weight:600; margin-bottom:8px;">Registro fotográfico y firmas</div>
    <div id="fito-galeria" class="grid" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap:12px;">${fotosHtml}</div>
    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-top:12px;">
      <div style="text-align:center;">
        ${firmaPiloto}
        <div>Firma Prestador de Servicio</div>
      </div>
      <div style="text-align:center;">
        ${firmaCliente}
        <div>Firma Representante del cliente</div>
      </div>
    </div>
  </div>
</div>
`;

                    // Event export PDF
                    const btnPdf = document.getElementById('btnPdf');
                    btnPdf.onclick = async () => {
                        try {
                            const {
                                jsPDF
                            } = window.jspdf;
                            const node = document.getElementById('registroPrint');

                            const canvas = await html2canvas(node, {
                                scale: 2,
                                useCORS: true
                            });
                            const imgData = canvas.toDataURL('image/png');

                            const pdf = new jsPDF('p', 'pt', 'a4');
                            const pageWidth = pdf.internal.pageSize.getWidth();
                            const pageHeight = pdf.internal.pageSize.getHeight();

                            const imgWidth = pageWidth - 40; // 20pt margen izq/der
                            const imgHeight = canvas.height * imgWidth / canvas.width;

                            let position = 20;
                            let remaining = imgHeight;

                            let y = 20;
                            let h = imgHeight;

                            if (h <= pageHeight - 40) {
                                pdf.addImage(imgData, 'PNG', 20, y, imgWidth, h);
                            } else {
                                // Particionado vertical simple
                                let sY = 0;
                                const onePageHeightPx = canvas.height * ((pageHeight - 40) / imgHeight);

                                while (remaining > 0) {
                                    const pageCanvas = document.createElement('canvas');
                                    pageCanvas.width = canvas.width;
                                    pageCanvas.height = Math.min(onePageHeightPx, remaining);
                                    const ctx = pageCanvas.getContext('2d');
                                    ctx.drawImage(canvas, 0, sY, canvas.width, pageCanvas.height, 0, 0, canvas.width, pageCanvas.height);

                                    const pageImg = pageCanvas.toDataURL('image/png');
                                    const pageImgHeight = (pageCanvas.height / canvas.width) * imgWidth;

                                    pdf.addImage(pageImg, 'PNG', 20, 20, imgWidth, pageImgHeight);

                                    remaining -= pageCanvas.height;
                                    sY += pageCanvas.height;

                                    if (remaining > 0) pdf.addPage();
                                }
                            }

                            pdf.save(`registro_fitosanitario_${f(s.id)}.pdf`);
                        } catch (err) {
                            console.error(err);
                            window.showToast?.('error', 'No se pudo exportar el PDF.');
                        }
                    };
                } catch (e) {
                    const cont = document.getElementById('registroPrint');
                    cont.innerHTML = `<p class="gform-helper">No se pudo cargar el registro: ${e.message || 'Error'}</p>`;
                }
            }

            // Delegación: abrir modal de registro
            listado.addEventListener('click', (ev) => {
                const btn = ev.target.closest('button.btn-registro');
                if (!btn) return;
                __invokerBtnRegistro = btn;
                const card = btn.closest('.pedido-card');
                const badge = card?.querySelector('.estado-badge');
                const estado = badge?.textContent?.trim()?.toLowerCase() || '';
                if (estado !== 'completada') {
                    // No mostramos toast para evitar el error del framework
                    console.warn('Registro solo disponible para solicitudes completadas.');
                    return;
                }
                openRegistroModal(btn.dataset.id);
            });

            // Cerrar modal de registro con click de fondo y Escape
            modalRegistro?.addEventListener('click', (e) => {
                if (e.target === modalRegistro) closeRegistroModal();
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && modalRegistro?.classList.contains('is-open')) closeRegistroModal();
            });


            // Paginación
            pag.addEventListener('click', (ev) => {
                const b = ev.target.closest('button[data-nav]');
                if (!b) return;
                page += (b.dataset.nav === 'next' ? 1 : -1);
                if (page < 1) page = 1;
                load();
            });

            // Init
            load();
        })();
    </script>
</body>

</html>