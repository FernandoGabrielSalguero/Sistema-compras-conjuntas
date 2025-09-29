<?php
include __DIR__ . '/drone_drawerListado_view.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$isSVE = isset($_SESSION['rol']) && strtolower((string)$_SESSION['rol']) === 'sve';
?>

<link rel="preload" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript>
    <link rel="stylesheet" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css">
</noscript>
<script defer src="https://www.fernandosalguero.com/cdn/assets/javascript/framework.js"></script>

<!-- Descarga de consolidado -->
<script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

<div class="content">

    <div class="card" style="background-color:#5b21b6;">
        <div class="product-header">
            <h3 style="color:white;">Buscar proyecto de vuelo</h3>
            <?php if ($isSVE): ?>
                <button type="button" id="btn-export-excel" class="btn-icon" title="Descargar Excel" aria-label="Descargar Excel">
                    <span class="material-symbols-outlined" style="color: white;" aria-hidden="true">download</span>
                </button>
            <?php endif; ?>
        </div>
        <form class="form-grid grid-4" id="form-search" autocomplete="off" aria-describedby="help-busqueda">

            <p id="help-busqueda" class="sr-only">Usá los campos para filtrar por piloto, productor, estado y fecha.</p>

            <div class="input-group">
                <label for="piloto" style="color:white;">Nombre piloto</label>
                <div class="input-icon input-icon-name">
                    <input type="text" id="piloto" name="piloto" placeholder="Piloto" />
                </div>
            </div>

            <div class="input-group">
                <label for="ses_usuario" style="color:white;">Nombre productor</label>
                <div class="input-icon input-icon-name">
                    <input type="text" id="ses_usuario" name="ses_usuario" placeholder="Productor" />
                </div>
            </div>

            <div class="input-group">
                <label for="estado_filtro" style="color:white;">Estado</label>
                <div class="input-icon input-icon-globe">
                    <select id="estado_filtro" name="estado_filtro">
                        <option value="">Todos</option>
                        <option value="ingresada">Ingresada</option>
                        <option value="procesando">Procesando</option>
                        <option value="aprobada_coop">Aprobada coop</option>
                        <option value="cancelada">Cancelada</option>
                        <option value="completada">Completada</option>
                    </select>
                </div>
            </div>

            <div class="input-group">
                <label for="fecha_visita" style="color:white;">Fecha del servicio</label>
                <div class="input-icon input-icon-date">
                    <input type="date" id="fecha_visita" name="fecha_visita" />
                </div>
            </div>
        </form>
    </div>

    <!-- Contenedor tarjetas -->
    <div id="cards" class="triple-tarjetas card-grid grid-4" role="region" aria-live="polite" aria-busy="false"></div>

    <!-- Modal confirmar eliminación -->
    <div id="modal-delete" class="modal hidden" role="dialog" aria-modal="true" aria-labelledby="modal-delete-title" aria-describedby="modal-delete-desc">
        <div class="modal-content">
            <h3 id="modal-delete-title">Eliminar solicitud</h3>
            <p id="modal-delete-desc">¿Confirmás que querés eliminar esta solicitud? Esta acción no se puede deshacer.</p>
            <div class="form-buttons">
                <button type="button" class="btn btn-cancelar" id="btn-cancel-delete">Cancelar</button>
                <button type="button" class="btn btn-aceptar" id="btn-confirm-delete">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<style>
    #cards:empty::before {
        content: "No hay solicitudes para los filtros seleccionados.";
        display: block;
        background: #fff;
        border-radius: 14px;
        padding: 18px;
        color: #6b7280;
    }

    /* Botón icono en el header */
    .product-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
    }

    .btn-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 999px;
        border: none;
        background: #fff;
        box-shadow: 0 1px 2px rgba(0, 0, 0, .08);
        cursor: pointer;
        transition: transform .12s ease, box-shadow .12s ease;
    }

    .btn-icon:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(0, 0, 0, .12);
    }

    .btn-icon svg {
        width: 20px;
        height: 20px;
    }

    .btn-icon.btn-delete {
        background: #FEE2E2;
    }

    .btn-icon.btn-delete svg {
        fill: #B91C1C;
    }

    .product-card .badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 999px;
        font-size: .8rem
    }


    .badge.warning {
        background: #FEF3C7;
        color: #92400E
    }

    .badge.info {
        background: #DBEAFE;
        color: #1E40AF
    }

    .badge.primary {
        background: #E0E7FF;
        color: #3730A3
    }

    .badge.success {
        background: #DCFCE7;
        color: #166534
    }

    .badge.danger {
        background: #FEE2E2;
        color: #B91C1C
    }

    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }

    #cards {
        min-height: 80px;
    }
</style>

<script>
    const DRONE_API = '../partials/drones/controller/drone_list_controller.php';

    (function() {
        if (window.__SVE_DRONE_LIST_INIT__) return;
        window.__SVE_DRONE_LIST_INIT__ = true;

        const $ = (s, ctx = document) => ctx.querySelector(s);

        const els = {
            piloto: $('#piloto'),
            ses_usuario: $('#ses_usuario'),
            estado_filtro: $('#estado_filtro'),
            fecha_visita: $('#fecha_visita'),
            cards: $('#cards')
        };

        // helpers
        function debounce(fn, t = 300) {
            let id;
            return (...a) => {
                clearTimeout(id);
                id = setTimeout(() => fn(...a), t);
            };
        }

        function esc(s) {
            return (s ?? '').toString().replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }
        const fmtNum = (v) => {
            if (v === null || v === undefined || v === '') return '';
            const n = Number(v);
            if (!Number.isFinite(n)) return '';
            return Number.isInteger(n) ? String(n) : String(n).replace('.', ',');
        };

        const getFilters = () => ({
            piloto: els.piloto.value.trim(),
            ses_usuario: els.ses_usuario.value.trim(),
            estado: els.estado_filtro.value,
            fecha_visita: els.fecha_visita.value
        });

        // Estado -> UI
        function prettyEstado(e) {
            switch ((e || '').toLowerCase()) {
                case 'ingresada':
                    return 'Ingresada';
                case 'procesando':
                    return 'Procesando';
                case 'aprobada_coop':
                    return 'Aprobada coop';
                case 'cancelada':
                    return 'Cancelada';
                case 'completada':
                    return 'Completada';
                default:
                    return e || '';
            }
        }

        function badgeClass(e) {
            switch ((e || '').toLowerCase()) {
                case 'ingresada':
                    return 'warning';
                case 'procesando':
                    return 'info';
                case 'aprobada_coop':
                    return 'primary';
                case 'completada':
                    return 'success';
                case 'cancelada':
                    return 'danger';
                default:
                    return 'secondary';
            }
        }

        // Listado
        let currentListAbort = null;
        async function load() {
            const params = new URLSearchParams({
                action: 'list_solicitudes',
                ...getFilters()
            });
            els.cards.setAttribute('aria-busy', 'true');
            try {
                if (currentListAbort) currentListAbort.abort();
                currentListAbort = new AbortController();
                const res = await fetch(`${DRONE_API}?${params.toString()}`, {
                    cache: 'no-store',
                    signal: currentListAbort.signal
                });
                const json = await res.json();
                if (!json.ok) throw new Error(json.error || 'Error');
                renderCards(json.data.items || []);
            } catch (e) {
                if (e.name === 'AbortError') return;
                console.error(e);
                els.cards.innerHTML = '<div class="card">Ocurrió un error cargando las solicitudes.</div>';
            } finally {
                els.cards.setAttribute('aria-busy', 'false');
            }
        }

        function renderCards(items) {
            els.cards.innerHTML = '';
            items.forEach(it => {
                const card = document.createElement('div');
                card.className = 'product-card';
                card.innerHTML = `
            <div class="product-header">
                <h4>${esc(it.productor_nombre || it.ses_usuario || 'Sin dato')}</h4>
                <p>Pedido número: ${esc(it.id ?? '')}</p>
                                <button type="button" class="btn-icon btn-delete" title="Eliminar solicitud" aria-label="Eliminar solicitud ${esc(it.id ?? '')}" data-id="${esc(it.id ?? '')}">
                    <!-- Trash SVG, sin dependencias -->
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path d="M9 3h6a1 1 0 0 1 1 1v1h4v2h-1v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7H3V5h4V4a1 1 0 0 1 1-1Zm1 2v0h4V5h-4Zm-3 4v11h10V9H7Zm3 2h2v7H10v-7Zm4 0h2v7h-2v-7Z"/>
                    </svg>
                </button>
            </div>
            <div class="product-body">
                <div class="user-info">
                    <div>
                        <strong>${esc(it.piloto || 'Sin piloto asignado')}</strong>
                        <div class="role">
                            Fecha visita: ${esc(it.fecha_visita || '')}
                            ${it.hora_visita ? `(${esc(it.hora_visita)})` : ''}
                        </div>
                    </div>
                </div>

                <div class="mini-block">
                    <div class="mini-title">Observaciones productor</div>
                    <p class="description">${esc(it.observaciones || '—')}</p>
                </div>

                <div class="mini-block">
                    <div class="mini-title">Costo servicio</div>
                    <p class="price">$${fmtNum(it.costo_total ?? 0)}</p>
                </div>

                <hr />

                <div class="product-footer">
                    <div class="metric">
                        <span class="badge ${badgeClass(it.estado)}">${prettyEstado(it.estado)}</span>
                        ${it.motivo_cancelacion ? `<span class="motivo-cancel">${esc(it.motivo_cancelacion)}</span>` : ''}
                    </div>
                    <button type="button" class="btn btn-info btn-detalle" data-id="${esc(it.id ?? '')}" aria-label="Ver detalle de pedido ${esc(it.id ?? '')}">
                        Detalle
                    </button>
                </div>
            </div>
        `;
                els.cards.appendChild(card);
            });

            // bind botones Detalle
            // bind botones Detalle
            els.cards.querySelectorAll('.btn-detalle').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.getAttribute('data-id');
                    if (!id) return;
                    if (window.DroneDrawerListado && typeof window.DroneDrawerListado.open === 'function') {
                        window.DroneDrawerListado.open({
                            id: Number(id)
                        });
                    } else {
                        console.error('DroneDrawerListado no está disponible');
                    }
                });
            });

            // bind botón eliminar (abre modal)
            els.cards.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.getAttribute('data-id');
                    if (!id) return;
                    openDeleteModal(Number(id), btn.closest('.product-card'));
                });
            });
        }


        // ----- Modal eliminar -----
        const modal = document.getElementById('modal-delete');
        const btnCancelDelete = document.getElementById('btn-cancel-delete');
        const btnConfirmDelete = document.getElementById('btn-confirm-delete');
        let deleteCtx = {
            id: null,
            cardEl: null
        };

        function openDeleteModal(id, cardEl) {
            deleteCtx.id = id;
            deleteCtx.cardEl = cardEl;
            modal.classList.remove('hidden');
            btnConfirmDelete.focus();
        }

        function closeDeleteModal() {
            modal.classList.add('hidden');
            deleteCtx = {
                id: null,
                cardEl: null
            };
        }

        btnCancelDelete.addEventListener('click', closeDeleteModal);
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeDeleteModal();
        });

        btnConfirmDelete.addEventListener('click', async () => {
            const id = deleteCtx.id;
            if (!id) return;
            try {
                const res = await fetch(`${DRONE_API}?action=delete_solicitud`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id
                    })
                });
                const json = await res.json();
                if (!json.ok) throw new Error(json.error || 'No se pudo eliminar');

                // Remover tarjeta del DOM sin layout shift brusco
                if (deleteCtx.cardEl && deleteCtx.cardEl.parentNode) {
                    deleteCtx.cardEl.style.transition = 'opacity .18s ease, transform .18s ease';
                    deleteCtx.cardEl.style.opacity = '0';
                    deleteCtx.cardEl.style.transform = 'scale(.98)';
                    setTimeout(() => deleteCtx.cardEl.remove(), 180);
                }
                showAlert('success', '¡Solicitud eliminada!');
            } catch (e) {
                showAlert('error', 'No se pudo eliminar la solicitud.');
            } finally {
                closeDeleteModal();
            }
        });


        // Filtros en vivo
        const debouncedLoad = debounce(load, 300);
        els.piloto.addEventListener('input', debouncedLoad);
        els.ses_usuario.addEventListener('input', debouncedLoad);
        els.estado_filtro.addEventListener('change', debouncedLoad);
        els.fecha_visita.addEventListener('change', debouncedLoad);

        load(); // arranque
    })();
</script>

<script>
    /* ===== Export Excel (encapsulado) ===== */
    (function() {
        if (window.__SVE_DRONE_EXPORT_INIT__) return;
        window.__SVE_DRONE_EXPORT_INIT__ = true;

        const DRONE_API = '../partials/drones/controller/drone_list_controller.php';
        const btn = document.getElementById('btn-export-excel');
        if (!btn) return; // si no es SVE no existe el botón

        const $ = (s, ctx = document) => ctx.querySelector(s);
        const els = {
            piloto: $('#piloto'),
            ses_usuario: $('#ses_usuario'),
            estado_filtro: $('#estado_filtro'),
            fecha_visita: $('#fecha_visita')
        };

        const getFilters = () => ({
            piloto: els.piloto ? els.piloto.value.trim() : '',
            ses_usuario: els.ses_usuario ? els.ses_usuario.value.trim() : '',
            estado: els.estado_filtro ? els.estado_filtro.value : '',
            fecha_visita: els.fecha_visita ? els.fecha_visita.value : ''
        });

        function showMsg(type, msg) {
            if (typeof window.showAlert === 'function') {
                window.showAlert(type, msg);
            } else {
                alert(msg);
            }
        }

        async function fetchRows() {
            const res = await fetch(`${DRONE_API}?action=export_solicitudes`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    filtros: getFilters()
                })
            });
            const json = await res.json();
            if (!json.ok) {
                const err = json.error || 'Error al exportar';
                throw new Error(err);
            }
            return json.data.items || [];
        }

        function mapHeaders(rows) {
            const headerMap = {
                id: 'ID',
                productor_id_real: 'Productor ID',
                ses_usuario: 'Productor (sesión)',
                productor_nombre: 'Productor (nombre)',
                piloto: 'Piloto',
                piloto_id: 'Piloto ID',
                fecha_visita: 'Fecha visita',
                hora_visita_desde: 'Hora desde',
                hora_visita_hasta: 'Hora hasta',
                observaciones: 'Observaciones',
                estado: 'Estado',
                motivo_cancelacion: 'Motivo cancelación',
                coop_descuento_nombre: 'Desc. Coop',
                moneda: 'Moneda',
                costo_base_por_ha: 'Costo base/ha',
                base_ha: 'Base ha',
                base_total: 'Base total',
                productos_total: 'Productos total',
                total: 'Total',
                created_at: 'Creado',
                updated_at: 'Actualizado'
            };
            return rows.map(r => {
                const o = {};
                Object.keys(headerMap).forEach(k => {
                    o[headerMap[k]] = (r[k] ?? '');
                });
                return o;
            });
        }

        async function exportExcel() {
            try {
                btn.disabled = true;
                const rows = await fetchRows();
                if (!rows.length) {
                    showMsg('info', 'No hay datos para exportar.');
                    return;
                }
                const data = mapHeaders(rows);
                const ws = XLSX.utils.json_to_sheet(data);
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, 'Solicitudes');
                const now = new Date();
                const pad = n => String(n).padStart(2, '0');
                const fname = `solicitudes_drones_${now.getFullYear()}${pad(now.getMonth()+1)}${pad(now.getDate())}_${pad(now.getHours())}${pad(now.getMinutes())}.xlsx`;
                XLSX.writeFile(wb, fname);
            } catch (e) {
                console.error(e);
                showMsg('error', e.message || 'Error al exportar');
            } finally {
                btn.disabled = false;
            }
        }

        btn.addEventListener('click', exportExcel);
    })();
</script>