<?php // views/partials/drones/view/drone_list_view.php 
?>
<div class="content">

    <div class="card" style="background-color:#5b21b6;">
        <h3 style="color:white;">Buscar proyecto de vuelo</h3>

        <form class="form-grid grid-4" id="form-search" autocomplete="off">
            <!-- Piloto -->
            <div class="input-group">
                <label for="piloto" style="color:white;">Nombre piloto</label>
                <div class="input-icon input-icon-name">
                    <input type="text" id="piloto" name="piloto" placeholder="Piloto" />
                </div>
            </div>

            <!-- Productor -->
            <div class="input-group">
                <label for="ses_usuario" style="color:white;">Nombre productor</label>
                <div class="input-icon input-icon-name">
                    <input type="text" id="ses_usuario" name="ses_usuario" placeholder="Productor" />
                </div>
            </div>

            <!-- Estado -->
            <div class="input-group">
                <label for="estado" style="color:white;">Estado</label>
                <div class="input-icon input-icon-globe">
                    <select id="estado" name="estado">
                        <option value="">Todos</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="en_proceso">En proceso</option>
                        <option value="completado">Completado</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>
            </div>

            <!-- Fecha del servicio -->
            <div class="input-group">
                <label for="fecha_visita" style="color:white;">Fecha del servicio</label>
                <div class="input-icon input-icon-date">
                    <input type="date" id="fecha_visita" name="fecha_visita" />
                </div>
            </div>
        </form>
    </div>

    <!-- Contenedor de tarjetas -->
    <div id="cards" class="triple-tarjetas card-grid grid-4"></div>

    <!-- Alert container opcional -->
    <div class="alert-container" id="alertContainer"></div>
</div>

<style>
    /* Garantizamos que el grid quede legible si no hay items */
    #cards:empty::before {
        content: "No hay solicitudes para los filtros seleccionados.";
        display: block;
        background: #fff;
        border-radius: 14px;
        padding: 18px;
        color: #6b7280;
    }
</style>

<script>
    (function() {

        const API = '../partials/drones/controller/drone_list_controller.php';

        const $ = (s, ctx = document) => ctx.querySelector(s);
        const $$ = (s, ctx = document) => Array.from(ctx.querySelectorAll(s));

        const els = {
            piloto: $('#piloto'),
            ses_usuario: $('#ses_usuario'),
            estado: $('#estado'),
            fecha_visita: $('#fecha_visita'),
            cards: $('#cards')
        };

        function debounce(fn, t = 300) {
            let id;
            return (...args) => {
                clearTimeout(id);
                id = setTimeout(() => fn(...args), t);
            };
        }

        function prettyEstado(e) {
            switch ((e || '').toLowerCase()) {
                case 'pendiente':
                    return 'Pendiente';
                case 'en_proceso':
                    return 'En proceso';
                case 'completado':
                    return 'Completado';
                case 'cancelado':
                    return 'Cancelado';
                default:
                    return e || '';
            }
        }

        function badgeClass(e) {
            switch ((e || '').toLowerCase()) {
                case 'pendiente':
                    return 'warning';
                case 'en_proceso':
                    return 'info';
                case 'completado':
                    return 'success';
                case 'cancelado':
                    return 'danger';
                default:
                    return 'secondary';
            }
        }

        function esc(s) {
            return (s ?? '').toString()
                .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function getFilters() {
            return {
                piloto: els.piloto.value.trim(),
                ses_usuario: els.ses_usuario.value.trim(),
                estado: els.estado.value,
                fecha_visita: els.fecha_visita.value
            };
        }

        function showSpinner(show) {
            if (window.showSpinnerGlobal && window.hideSpinnerGlobal) {
                show ? window.showSpinnerGlobal() : window.hideSpinnerGlobal();
            }
        }

        async function load() {
            const params = new URLSearchParams({
                action: 'list_solicitudes',
                ...getFilters()
            });
            try {
                showSpinner(true);
                const res = await fetch(`${API}?${params.toString()}`, {
                    cache: 'no-store'
                });
                const json = await res.json();
                if (!json.ok) throw new Error(json.error || 'Error desconocido');
                renderCards(json.data.items || []);
            } catch (err) {
                console.error(err);
                els.cards.innerHTML = '<div class="card">Ocurrió un error cargando las solicitudes.</div>';
            } finally {
                showSpinner(false);
            }
        }

        function renderCards(items) {
            els.cards.innerHTML = '';
            items.forEach(it => {
                const card = document.createElement('div');
                card.className = 'product-card';
                card.innerHTML = `
        <div class="product-header">
          <h4>${esc(it.ses_usuario || '—')}</h4>
          <p>${esc(it.productor_id_real || '—')}</p>
        </div>
        <div class="product-body">
          <div class="user-info">
            <div>
              <strong>${esc(it.piloto || 'Sin piloto asignado aún')}</strong>
              <div class="role">Fecha de visita: ${esc(it.fecha_visita || '')} hora ${esc(it.hora_visita || '')}</div>
            </div>
          </div>
          <p class="description">Observaciones del productor: ${esc(it.observaciones || '')}</p>
          <hr />
          <div class="product-footer">
            <div class="metric">
              <span class="badge ${badgeClass(it.estado)}">${prettyEstado(it.estado)}</span>
            </div>
            <div class="metric">
              ${it.motivo_cancelacion ? `<span>${esc(it.motivo_cancelacion)}</span>` : ``}
            </div>
            <button class="btn-view" data-id="${it.id}">Ver detalle</button>
          </div>
        </div>
      `;
                els.cards.appendChild(card);
            });

            // Eventos "Ver detalle"
            els.cards.querySelectorAll('.btn-view').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const id = btn.dataset.id;
                    try {
                        const res = await fetch(`${API}?action=get_solicitud&id=${encodeURIComponent(id)}`);
                        const json = await res.json();
                        if (json.ok) {
                            console.log('Detalle solicitud:', json.data); // 👈 toda la info en consola
                        } else {
                            console.error(json.error || 'No se pudo obtener el detalle');
                        }
                    } catch (e) {
                        console.error(e);
                    }
                });
            });
        }

        // Filtro en vivo
        const debouncedLoad = debounce(load, 300);
        els.piloto.addEventListener('input', debouncedLoad);
        els.ses_usuario.addEventListener('input', debouncedLoad);
        els.estado.addEventListener('change', debouncedLoad);
        els.fecha_visita.addEventListener('change', debouncedLoad);

        // Carga inicial
        load();
    })();
</script>