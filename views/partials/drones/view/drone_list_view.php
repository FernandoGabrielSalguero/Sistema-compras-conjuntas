<?php
include __DIR__ . '/drone_drawerListado_view.php';
?>
<link rel="preload" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript>
    <link rel="stylesheet" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css">
</noscript>
<script defer src="https://www.fernandosalguero.com/cdn/assets/javascript/framework.js"></script>

<div class="content">
    <!-- Filtros mínimos -->
    <div class="card" style="background-color:#5b21b6;">
        <h3 style="color:white;">Buscar proyecto de vuelo</h3>
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
        }


        // Filtros en vivo
        const debouncedLoad = debounce(load, 300);
        els.piloto.addEventListener('input', debouncedLoad);
        els.ses_usuario.addEventListener('input', debouncedLoad);
        els.estado_filtro.addEventListener('change', debouncedLoad);
        els.fecha_visita.addEventListener('change', debouncedLoad);

        load(); // arranque
    })();
</script>