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
    <link rel="stylesheet" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css">
    <script defer src="https://www.fernandosalguero.com/cdn/assets/javascript/framework.js"></script>

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
            width: min(560px, 100%);
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 24px 60px rgba(0, 0, 0, .25);
            overflow: hidden;
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

            </section>
        </div>
    </div>

    <!-- Spinner global (si lo tenés en tu proyecto) -->
    <script src="../../views/partials/spinner-global.js"></script>

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
                    const puedeCancelar = (['procesando', 'aprobada_coop'].includes(it.estado));

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
            ${puedeCancelar ? `<button class="btn btn-cancelar btn-cancelar-soft" data-id="${it.id}" aria-label="Cancelar solicitud ${it.id}">Cancelar</button>` : ''}
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

                const card = btn.closest('.pedido-card');
                const badge = card?.querySelector('.estado-badge');
                const estado = badge?.textContent?.trim()?.toLowerCase() || '';
                const cancelables = ['procesando', 'aprobada_coop'];
                const esCancelable = cancelables.includes(estado);

                if (!esCancelable) {
                    window.showToast?.('error', 'Solo se pueden cancelar solicitudes en estado PROCESANDO o APROBADA_COOP.');
                    return;
                }
                openCancelModal(btn.dataset.id);
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