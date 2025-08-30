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


    <!-- Drawer lateral de detalle/edición -->
    <!-- Drawer lateral de detalle/edición -->
    <div id="drawer" class="sv-drawer hidden" aria-hidden="true">
        <div class="sv-drawer__overlay" data-close></div>

        <aside class="sv-drawer__panel" role="dialog" aria-modal="true" aria-labelledby="drawer-title">
            <div class="sv-drawer__header">
                <h3 id="drawer-title">Solicitud <span id="drawer-id"></span></h3>
                <button class="sv-drawer__close" id="drawer-close" aria-label="Cerrar">×</button>
            </div>

            <!-- BODY scrollable -->
            <div class="sv-drawer__body">
                <!-- Envolvemos el formulario en una card para que tome el mismo estilo de inputs del resto del sitio -->
                <div class="card">
                    <form id="detalle-form" class="form-grid grid-2" autocomplete="off">
                        <!-- Identificación -->
                        <div class="input-group input-icon">
                            <label>ID (interno)</label>
                            <input type="text" name="id" id="f-id" readonly />
                        </div>
                        <div class="input-group">
                            <label>Id real Productor</label>
                            <input type="text" name="productor_id_real" id="f-productor_id_real" readonly />
                        </div>

                        <div class="input-group">
                            <label>Productor</label>
                            <input type="text" name="ses_usuario" id="f-ses_usuario" readonly />
                        </div>
                        <div class="input-group">
                            <label>Piloto</label>
                            <input type="text" name="piloto" id="f-piloto" />
                        </div>

                        <div class="input-group">
                            <label>Fecha visita</label>
                            <input type="date" name="fecha_visita" id="f-fecha_visita" />
                        </div>
                        <div class="input-group">
                            <label>Hora visita</label>
                            <input type="time" name="hora_visita" id="f-hora_visita" />
                        </div>

                        <div class="input-group">
                            <label>Estado</label>
                            <select name="estado" id="f-estado">
                                <option value="pendiente">Pendiente</option>
                                <option value="en_proceso">En proceso</option>
                                <option value="completado">Completado</option>
                                <option value="cancelado">Cancelado</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Motivo cancelación</label>
                            <input type="text" name="motivo_cancelacion" id="f-motivo_cancelacion" />
                        </div>

                        <div class="input-group" style="grid-column:1/-1;">
                            <label>Observaciones del productor</label>
                            <textarea name="observaciones" id="f-observaciones" rows="3"></textarea>
                        </div>
                        <div class="input-group" style="grid-column:1/-1;">
                            <label>Observaciones del piloto</label>
                            <textarea name="obs_piloto" id="f-obs_piloto" rows="3"></textarea>
                        </div>

                        <!-- Dirección / ubicación -->
                        <div class="input-group"><label>Provincia</label><input name="dir_provincia" id="f-dir_provincia" /></div>
                        <div class="input-group"><label>Localidad</label><input name="dir_localidad" id="f-dir_localidad" /></div>
                        <div class="input-group"><label>Calle</label><input name="dir_calle" id="f-dir_calle" /></div>
                        <div class="input-group"><label>Número</label><input name="dir_numero" id="f-dir_numero" /></div>

                        <div class="input-group">
                            <label>En finca</label>
                            <select name="en_finca" id="f-en_finca">
                                <option value="si">si</option>
                                <option value="no">no</option>
                            </select>
                        </div>
                        <div class="input-group"><label>Lat</label><input name="ubicacion_lat" id="f-ubicacion_lat" /></div>
                        <div class="input-group"><label>Lng</label><input name="ubicacion_lng" id="f-ubicacion_lng" /></div>

                        <!-- Parámetros de vuelo -->
                        <div class="input-group"><label>Volumen (ha)</label><input name="volumen_ha" id="f-volumen_ha" /></div>
                        <div class="input-group"><label>Velocidad (m/s)</label><input name="velocidad_vuelo" id="f-velocidad_vuelo" /></div>
                        <div class="input-group"><label>Altura (m)</label><input name="alto_vuelo" id="f-alto_vuelo" /></div>
                        <div class="input-group"><label>Tamaño gota</label><input name="tamano_gota" id="f-tamano_gota" /></div>

                        <!-- Seguridad -->
                        <div class="input-group">
                            <label>Línea de tensión</label>
                            <select name="linea_tension" id="f-linea_tension">
                                <option>si</option>
                                <option>no</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Zona restringida</label>
                            <select name="zona_restringida" id="f-zona_restringida">
                                <option>si</option>
                                <option>no</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Corriente eléctrica</label>
                            <select name="corriente_electrica" id="f-corriente_electrica">
                                <option>si</option>
                                <option>no</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Agua potable</label>
                            <select name="agua_potable" id="f-agua_potable">
                                <option>si</option>
                                <option>no</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Libre de obstáculos</label>
                            <select name="libre_obstaculos" id="f-libre_obstaculos">
                                <option>si</option>
                                <option>no</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Área despegue</label>
                            <select name="area_despegue" id="f-area_despegue">
                                <option>si</option>
                                <option>no</option>
                            </select>
                        </div>

                        <!-- Listas hijas (solo visual en v1) -->
                        <div class="input-group" style="grid-column:1/-1;">
                            <label>Motivos</label>
                            <div id="f-motivos" class="pill-list"></div>
                        </div>
                        <div class="input-group" style="grid-column:1/-1;">
                            <label>Productos</label>
                            <div id="f-productos" class="table-mini"></div>
                        </div>
                        <div class="input-group" style="grid-column:1/-1;">
                            <label>Rangos</label>
                            <div id="f-rangos" class="pill-list"></div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="sv-drawer__footer">
                <button class="btn" id="drawer-cancel" type="button">Cerrar</button>
                <button class="btn primary" id="drawer-save" type="submit" form="detalle-form">Guardar cambios</button>
            </div>
        </aside>
    </div>


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

    /* Drawer lateral */
    /* Drawer lateral */
    .sv-drawer.hidden {
        display: none
    }

    .sv-drawer {
        position: fixed;
        inset: 0;
        z-index: 60
    }

    /* overlay sin animación por defecto (se anima con las clases opening/closing) */
    .sv-drawer__overlay {
        position: absolute;
        inset: 0;
        background: #0006;
        opacity: 0
    }

    /* panel */
    .sv-drawer__panel {
        position: absolute;
        top: 0;
        right: 0;
        height: 100%;
        width: min(760px, 100%);
        background: #fff;
        box-shadow: -6px 0 24px #00000022;
        display: flex;
        flex-direction: column;
        border-top-left-radius: 16px;
        border-bottom-left-radius: 16px;
    }

    .sv-drawer__header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
        border-bottom: 1px solid #eee
    }

    .sv-drawer__footer {
        padding: 12px 20px;
        border-top: 1px solid #eee;
        display: flex;
        gap: 12px;
        justify-content: flex-end
    }

    .sv-drawer__close {
        font-size: 24px;
        line-height: 1;
        border: none;
        background: transparent;
        cursor: pointer
    }

    /* Cuerpo scrollable (mantiene header y footer fijos) */
    .sv-drawer__body {
        flex: 1;
        overflow: auto;
        -webkit-overflow-scrolling: touch;
        padding: 16px 20px;
    }

    /* La card del body es sólo contenedor visual para heredar estilos de inputs del framework */
    .sv-drawer__body .card {
        box-shadow: none;
    }

    /* Animaciones */
    @keyframes slideInRight {
        from {
            transform: translateX(100%)
        }

        to {
            transform: translateX(0)
        }
    }

    @keyframes slideOutRight {
        from {
            transform: translateX(0)
        }

        to {
            transform: translateX(100%)
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0
        }

        to {
            opacity: 1
        }
    }

    @keyframes fadeOut {
        from {
            opacity: 1
        }

        to {
            opacity: 0
        }
    }

    /* Estados animados */
    .sv-drawer.opening .sv-drawer__panel {
        animation: slideInRight .28s cubic-bezier(.22, .61, .36, 1) both;
    }

    .sv-drawer.closing .sv-drawer__panel {
        animation: slideOutRight .22s ease both;
    }

    .sv-drawer.opening .sv-drawer__overlay {
        animation: fadeIn .25s ease both;
    }

    .sv-drawer.closing .sv-drawer__overlay {
        animation: fadeOut .20s ease both;
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
          <p>Pedido número: ${esc(it.id ?? '')}</p>
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
                            openDrawer(json.data);
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
        window.refreshSolicitudes = load;
    })();

    // --- Constantes de enums para pretty print ---
    const ENUM_LABEL = {
        estado: {
            pendiente: 'Pendiente',
            en_proceso: 'En proceso',
            completado: 'Completado',
            cancelado: 'Cancelado'
        }
    };

    // --- Drawer refs y helpers ---
    const drawer = document.getElementById('drawer');
    const drawerPanel = drawer.querySelector('.sv-drawer__panel');
    const drawerOverlay = drawer.querySelector('.sv-drawer__overlay');
    const drawerClose = document.getElementById('drawer-close');
    const drawerCancel = document.getElementById('drawer-cancel');
    const drawerId = document.getElementById('drawer-id');
    const formDetalle = document.getElementById('detalle-form');
    let currentDetalle = null;

    function openDrawer(data) {
        currentDetalle = data;
        drawer.classList.remove('hidden', 'closing');
        drawer.classList.add('opening');
        fillForm(data);

        const onEnd = (e) => {
            if (e.target !== drawer.querySelector('.sv-drawer__panel')) return;
            drawer.classList.remove('opening');
            drawer.removeEventListener('animationend', onEnd, true);
        };
        drawer.addEventListener('animationend', onEnd, true);
    }

    function closeDrawer() {
        drawer.classList.add('closing');
        const onEnd = (e) => {
            if (e.target !== drawer.querySelector('.sv-drawer__panel')) return;
            drawer.classList.remove('closing');
            drawer.classList.add('hidden');
            drawer.removeEventListener('animationend', onEnd, true);
            currentDetalle = null;
        };
        drawer.addEventListener('animationend', onEnd, true);
    }


    drawerOverlay.addEventListener('click', closeDrawer);
    drawerClose.addEventListener('click', closeDrawer);
    drawerCancel.addEventListener('click', closeDrawer);

    // --- Rellenar formulario con el detalle ---
    function fillForm({
        solicitud,
        motivos,
        productos,
        rangos
    }) {
        drawerId.textContent = `#${solicitud.id}`;

        // Campos simples
        const map = {
            'f-id': 'id',
            'f-productor_id_real': 'productor_id_real',
            'f-ses_usuario': 'ses_usuario',
            'f-piloto': 'piloto',
            'f-fecha_visita': 'fecha_visita',
            'f-hora_visita': 'hora_visita',
            'f-estado': 'estado',
            'f-motivo_cancelacion': 'motivo_cancelacion',
            'f-observaciones': 'observaciones',
            'f-obs_piloto': 'obs_piloto',
            'f-dir_provincia': 'dir_provincia',
            'f-dir_localidad': 'dir_localidad',
            'f-dir_calle': 'dir_calle',
            'f-dir_numero': 'dir_numero',
            'f-en_finca': 'en_finca',
            'f-ubicacion_lat': 'ubicacion_lat',
            'f-ubicacion_lng': 'ubicacion_lng',
            'f-volumen_ha': 'volumen_ha',
            'f-velocidad_vuelo': 'velocidad_vuelo',
            'f-alto_vuelo': 'alto_vuelo',
            'f-tamano_gota': 'tamano_gota',
            'f-linea_tension': 'linea_tension',
            'f-zona_restringida': 'zona_restringida',
            'f-corriente_electrica': 'corriente_electrica',
            'f-agua_potable': 'agua_potable',
            'f-libre_obstaculos': 'libre_obstaculos',
            'f-area_despegue': 'area_despegue',
        };
        Object.entries(map).forEach(([id, key]) => {
            const el = document.getElementById(id);
            if (!el) return;
            el.value = solicitud[key] ?? '';
        });

        // Motivos (chips)
        const contMotivos = document.getElementById('f-motivos');
        contMotivos.innerHTML = (motivos || []).map(m => `<span class="pill">${esc(m.motivo)}${m.otros_text?`: ${esc(m.otros_text)}`:''}</span>`).join('');

        // Productos (tabla mini)
        const contProd = document.getElementById('f-productos');
        if ((productos || []).length) {
            contProd.innerHTML = `
      <table>
        <thead><tr><th>Tipo</th><th>Fuente</th><th>Marca</th></tr></thead>
        <tbody>${productos.map(p=>`<tr><td>${esc(p.tipo)}</td><td>${esc(p.fuente)}</td><td>${esc(p.marca||'')}</td></tr>`).join('')}</tbody>
      </table>`;
        } else contProd.innerHTML = '<em>Sin productos</em>';

        // Rangos (chips)
        const contRangos = document.getElementById('f-rangos');
        contRangos.innerHTML = (rangos || []).map(r => `<span class="pill">${esc(r.rango)}</span>`).join('');
    }

    // Serializar form => objeto plano
    function formToJSON(form) {
        const fd = new FormData(form);
        const obj = {};
        fd.forEach((v, k) => {
            obj[k] = v;
        });
        // Aseguramos enums si están vacíos
        ['linea_tension', 'zona_restringida', 'corriente_electrica', 'agua_potable', 'libre_obstaculos', 'area_despegue', 'en_finca']
        .forEach(k => {
            if (k in obj && obj[k] === '') obj[k] = null;
        });
        return obj;
    }

    // Guardar cambios (solo tabla principal v1)
    formDetalle.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!currentDetalle) return;
        const payload = formToJSON(formDetalle);
        payload.id = Number(payload.id || currentDetalle.solicitud.id);
        try {
            showSpinner(true);
            const res = await fetch(API, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'update_solicitud',
                    data: payload
                })
            });
            const json = await res.json();
            if (!json.ok) throw new Error(json.error || 'No se pudo guardar');
            closeDrawer();
            // Refrescamos listado para ver cambios
            if (typeof window.refreshSolicitudes === 'function') {
                window.refreshSolicitudes();
            }
        } catch (err) {
            console.error(err);
            alert('No se pudo guardar los cambios.');
        } finally {
            showSpinner(false);
        }
    });
</script>