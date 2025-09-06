<?php
// VISTA LIMPIA: lista + drawer sin lógica de edición/guardar
?>

<!-- Framework SVE -->
<link rel="stylesheet" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css" />
<script src="https://www.fernandosalguero.com/cdn/assets/javascript/framework.js" defer></script>

<!-- Material Icons -->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

<div class="content">
    <!-- Filtros mínimos -->
    <div class="card" style="background-color:#5b21b6;">
        <h3 style="color:white;">Buscar proyecto de vuelo</h3>
        <form class="form-grid grid-4" id="form-search" autocomplete="off">
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
                <label for="estado" style="color:white;">Estado</label>
                <div class="input-icon input-icon-globe">
                    <select id="estado" name="estado">
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
    <div id="cards" class="triple-tarjetas card-grid grid-4"></div>

    <!-- Drawer vacío (solo UI) -->
<div id="drawer" class="sv-drawer hidden" aria-hidden="true">
    <div class="sv-drawer__overlay" data-close></div>
    <aside class="sv-drawer__panel" role="dialog" aria-modal="true" aria-labelledby="drawer-title">
        <div class="sv-drawer__header">
            <h3 id="drawer-title">Solicitud <span id="drawer-id"></span></h3>
            <button class="sv-drawer__close" id="drawer-close" aria-label="Cerrar">×</button>
        </div>

        <div class="sv-drawer__body">
            <!-- FORMULARIO COMPLETO -->
            <form id="form-solicitud" class="form" autocomplete="off" novalidate>
                <!-- Datos base -->
                <div class="card">
                    <div class="form-separator"><span class="material-icons mi">manage_search</span>Datos de la solicitud</div>
                    <div class="form-grid grid-3">
                        <div class="input-group">
                            <label for="productor_id_real">Productor ID real</label>
                            <div class="input-icon input-icon-id">
                                <input type="text" id="productor_id_real" name="productor_id_real" placeholder="P0000" />
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="ses_usuario">Nombre productor</label>
                            <div class="input-icon input-icon-name">
                                <input type="text" id="ses_usuario" name="ses_usuario" placeholder="Nombre y apellido" />
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="superficie_ha">Superficie (ha)</label>
                            <div class="input-icon input-icon-hashtag">
                                <input type="number" step="0.01" min="0" id="superficie_ha" name="superficie_ha" placeholder="0.00" />
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="fecha_visita">Fecha visita</label>
                            <div class="input-icon input-icon-date">
                                <input type="date" id="fecha_visita" name="fecha_visita" />
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="hora_visita_desde">Hora desde</label>
                            <div class="input-icon input-icon-time">
                                <input type="time" id="hora_visita_desde" name="hora_visita_desde" />
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="hora_visita_hasta">Hora hasta</label>
                            <div class="input-icon input-icon-time">
                                <input type="time" id="hora_visita_hasta" name="hora_visita_hasta" />
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="estado">Estado</label>
                            <div class="input-icon input-icon-globe">
                                <select id="estado" name="estado" aria-describedby="estadoHelp">
                                    <option value="ingresada">Ingresada</option>
                                    <option value="procesando">Procesando</option>
                                    <option value="aprobada_coop">Aprobada coop</option>
                                    <option value="cancelada">Cancelada</option>
                                    <option value="completada">Completada</option>
                                </select>
                            </div>
                            <small id="estadoHelp" class="helper-text">Seleccioná el estado actual.</small>
                        </div>

                        <div class="input-group">
                            <label for="motivo_cancelacion">Motivo cancelación</label>
                            <div class="input-icon input-icon-edit">
                                <input type="text" id="motivo_cancelacion" name="motivo_cancelacion" placeholder="Si estado=cancelada" />
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="observaciones">Observaciones</label>
                            <div class="input-icon input-icon-edit">
                                <input type="text" id="observaciones" name="observaciones" placeholder="Notas internas" />
                            </div>
                        </div>
                    </div>

                    <div class="form-grid grid-4">
                        <!-- Flags SI/NO -->
                        <div class="input-group">
                            <label for="representante">Representante</label>
                            <div class="input-icon input-icon-toggle">
                                <select id="representante" name="representante">
                                    <option value="no">no</option>
                                    <option value="si">si</option>
                                </select>
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="linea_tension">Línea de tensión</label>
                            <div class="input-icon input-icon-toggle">
                                <select id="linea_tension" name="linea_tension">
                                    <option value="no">no</option>
                                    <option value="si">si</option>
                                </select>
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="zona_restringida">Zona restringida</label>
                            <div class="input-icon input-icon-toggle">
                                <select id="zona_restringida" name="zona_restringida">
                                    <option value="no">no</option>
                                    <option value="si">si</option>
                                </select>
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="corriente_electrica">Corriente eléctrica</label>
                            <div class="input-icon input-icon-toggle">
                                <select id="corriente_electrica" name="corriente_electrica">
                                    <option value="no">no</option>
                                    <option value="si">si</option>
                                </select>
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="agua_potable">Agua potable</label>
                            <div class="input-icon input-icon-toggle">
                                <select id="agua_potable" name="agua_potable">
                                    <option value="no">no</option>
                                    <option value="si">si</option>
                                </select>
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="libre_obstaculos">Libre obstáculos</label>
                            <div class="input-icon input-icon-toggle">
                                <select id="libre_obstaculos" name="libre_obstaculos">
                                    <option value="no">no</option>
                                    <option value="si">si</option>
                                </select>
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="area_despegue">Área de despegue</label>
                            <div class="input-icon input-icon-toggle">
                                <select id="area_despegue" name="area_despegue">
                                    <option value="no">no</option>
                                    <option value="si">si</option>
                                </select>
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="en_finca">¿En finca?</label>
                            <div class="input-icon input-icon-toggle">
                                <select id="en_finca" name="en_finca">
                                    <option value="no">no</option>
                                    <option value="si">si</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Dirección -->
                    <div class="form-grid grid-4">
                        <div class="input-group">
                            <label for="dir_provincia">Provincia</label>
                            <div class="input-icon input-icon-location">
                                <input type="text" id="dir_provincia" name="dir_provincia" placeholder="Provincia" />
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="dir_localidad">Localidad</label>
                            <div class="input-icon input-icon-location">
                                <input type="text" id="dir_localidad" name="dir_localidad" placeholder="Localidad" />
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="dir_calle">Calle</label>
                            <div class="input-icon input-icon-location">
                                <input type="text" id="dir_calle" name="dir_calle" placeholder="Calle" />
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="dir_numero">Número</label>
                            <div class="input-icon input-icon-hash">
                                <input type="text" id="dir_numero" name="dir_numero" placeholder="N°" />
                            </div>
                        </div>
                    </div>

                    <!-- Ubicación -->
                    <div class="form-grid grid-4">
                        <div class="input-group">
                            <label for="ubicacion_lat">Latitud</label>
                            <div class="input-icon input-icon-location">
                                <input type="number" step="0.0000001" id="ubicacion_lat" name="ubicacion_lat" placeholder="-32.0000000" />
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="ubicacion_lng">Longitud</label>
                            <div class="input-icon input-icon-location">
                                <input type="number" step="0.0000001" id="ubicacion_lng" name="ubicacion_lng" placeholder="-68.0000000" />
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="ubicacion_acc">Precisión (m)</label>
                            <div class="input-icon input-icon-hashtag">
                                <input type="number" step="0.01" id="ubicacion_acc" name="ubicacion_acc" placeholder="0.00" />
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="ubicacion_ts">Fecha/hora ubicación</label>
                            <div class="input-icon input-icon-date">
                                <input type="datetime-local" id="ubicacion_ts" name="ubicacion_ts" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Asignaciones -->
                <div class="card">
                    <div class="form-separator"><span class="material-icons mi">group</span>Asignación de piloto y forma de pago</div>
                    <div class="form-grid grid-3">
                        <div class="input-group">
                            <label for="piloto_id">Piloto (ID)</label>
                            <div class="input-icon input-icon-id">
                                <input type="number" id="piloto_id" name="piloto_id" min="1" placeholder="ID de dron_pilotos" />
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="forma_pago_id">Forma de pago (ID)</label>
                            <div class="input-icon input-icon-id">
                                <input type="number" id="forma_pago_id" name="forma_pago_id" min="1" placeholder="ID de dron_formas_pago" />
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="coop_descuento_nombre">Coop. descuento</label>
                            <div class="input-icon input-icon-edit">
                                <input type="text" id="coop_descuento_nombre" name="coop_descuento_nombre" placeholder="Cód./Nombre coop." />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Costos -->
                <div class="card">
                    <div class="form-separator"><span class="material-icons mi">attach_money</span>Costos</div>
                    <div class="form-grid grid-4">
                        <div class="input-group">
                            <label for="costo_moneda">Moneda</label>
                            <div class="input-icon input-icon-currency">
                                <input type="text" id="costo_moneda" name="costo_moneda" placeholder="Pesos" />
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="costo_base_por_ha">Costo base/ha</label>
                            <div class="input-icon input-icon-hashtag">
                                <input type="number" step="0.01" id="costo_base_por_ha" name="costo_base_por_ha" />
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="base_ha">Base ha</label>
                            <div class="input-icon input-icon-hashtag">
                                <input type="number" step="0.01" id="base_ha" name="base_ha" />
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="base_total">Base total</label>
                            <div class="input-icon input-icon-hashtag">
                                <input type="number" step="0.01" id="base_total" name="base_total" />
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="productos_total">Productos total</label>
                            <div class="input-icon input-icon-hashtag">
                                <input type="number" step="0.01" id="productos_total" name="productos_total" />
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="total">Total</label>
                            <div class="input-icon input-icon-hashtag">
                                <input type="number" step="0.01" id="total" name="total" />
                            </div>
                        </div>
                    </div>
                    <div class="input-group">
                        <label for="desglose_json">Desglose JSON</label>
                        <div class="input-icon input-icon-code">
                            <textarea id="desglose_json" name="desglose_json" rows="3" placeholder='{"superficie_ha":...}'></textarea>
                        </div>
                    </div>
                </div>

                <!-- Motivos -->
                <div class="card">
                    <div class="form-separator"><span class="material-icons mi">bug_report</span>Motivos (patologías)</div>
                    <div class="tabla-wrapper">
                        <table class="data-table" id="tabla-motivos" aria-label="Motivos de la solicitud">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>patologia_id</th>
                                    <th>es_otros</th>
                                    <th>otros_text</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="form-buttons">
                        <button type="button" class="btn btn-info" id="add-motivo">Agregar motivo</button>
                    </div>
                </div>

                <!-- Items -->
                <div class="card">
                    <div class="form-separator"><span class="material-icons mi">inventory_2</span>Items (productos + recetas)</div>
                    <div class="tabla-wrapper">
                        <table class="data-table" id="tabla-items" aria-label="Items">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>patologia_id</th>
                                    <th>fuente</th>
                                    <th>producto_id</th>
                                    <th>nombre_producto</th>
                                    <th>costo_hectarea_snapshot</th>
                                    <th>total_producto_snapshot</th>
                                    <th>Recetas</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="form-buttons">
                        <button type="button" class="btn btn-info" id="add-item">Agregar item</button>
                    </div>
                </div>

                <!-- Rangos -->
                <div class="card">
                    <div class="form-separator"><span class="material-icons mi">date_range</span>Rangos (ventanas de aplicación)</div>
                    <div class="tabla-wrapper">
                        <table class="data-table" id="tabla-rangos" aria-label="Rangos">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>rango</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="form-buttons">
                        <button type="button" class="btn btn-info" id="add-rango">Agregar rango</button>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="form-buttons">
                    <button type="button" class="btn btn-aceptar" id="btn-guardar">Guardar cambios</button>
                    <button type="button" class="btn btn-cancelar" id="drawer-cancel">Cancelar</button>
                </div>
            </form>
        </div>

        <div class="sv-drawer__footer" aria-hidden="true"></div>
    </aside>
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

    .sv-drawer.hidden {
        display: none
    }

    .sv-drawer {
        position: fixed;
        inset: 0;
        z-index: 60
    }

    .sv-drawer__overlay {
        position: absolute;
        inset: 0;
        background: #0006;
        opacity: 0
    }

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

    .sv-drawer__body {
        flex: 1;
        overflow: auto;
        -webkit-overflow-scrolling: touch;
        padding: 16px 20px;
    }

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

.product-card .badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 999px;
        font-size: .8rem
    }
    /* ---- UI inline extra para el formulario ---- */
    .helper-text{font-size:.85rem;color:#6b7280}
    .mini-input{width:100%}
    .table-actions{display:flex;gap:8px;justify-content:center}
    .chip{display:inline-block;padding:2px 8px;border-radius:999px;background:#eef}
    .nowrap{white-space:nowrap}
    .sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
    textarea#desglose_json{min-height:96px}

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
</style>

<script>
    const DRONE_API = '../partials/drones/controller/drone_list_controller.php';

    (function() {
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
            return (...a) => {
                clearTimeout(id);
                id = setTimeout(() => fn(...a), t);
            }
        }

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

        function esc(s) {
            return (s ?? '').toString().replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        function getFilters() {
            return {
                piloto: els.piloto.value.trim(),
                ses_usuario: els.ses_usuario.value.trim(),
                estado: els.estado.value,
                fecha_visita: els.fecha_visita.value
            };
        }

        async function load() {
            const params = new URLSearchParams({
                action: 'list_solicitudes',
                ...getFilters()
            });
            try {
                const res = await fetch(`${DRONE_API}?${params.toString()}`, {
                    cache: 'no-store'
                });
                const json = await res.json();
                if (!json.ok) throw new Error(json.error || 'Error');
                renderCards(json.data.items || []);
            } catch (e) {
                console.error(e);
                els.cards.innerHTML = '<div class="card">Ocurrió un error cargando las solicitudes.</div>';
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
          <p>Pedido número: ${esc(it.id ?? '')}</p>
        </div>
        <div class="product-body">
          <div class="user-info">
            <div>
              <strong>${esc(it.piloto || 'Sin piloto asignado')}</strong>
              <div class="role">Fecha visita: ${esc(it.fecha_visita || '')} ${it.hora_visita ? `(${esc(it.hora_visita)})`:''}</div>
            </div>
          </div>
          <p class="description">${esc(it.observaciones || '')}</p>
          <hr />
          <div class="product-footer">
            <div class="metric">
              <span class="badge ${badgeClass(it.estado)}">${prettyEstado(it.estado)}</span>
            </div>
            <button class="btn-view" data-id="${it.id}">Ver detalle</button>
          </div>
        </div>
      `;
                els.cards.appendChild(card);
            });

            els.cards.querySelectorAll('.btn-view').forEach(btn=>{
  btn.addEventListener('click', async () => {
    const id = btn.dataset.id;
    try{
      const url  = `${DRONE_API}?action=get_solicitud&id=${encodeURIComponent(id)}`;
      const res  = await fetch(url, { cache:'no-store' });
      const json = await res.json();
      if(!json.ok) throw new Error(json.error || 'Error');

      // 🔎 Log completo en consola (solicitud base + costos + items + recetas + motivos + rangos + eventos + piloto + forma_pago + productor)
      console.group(`Solicitud #${id}`);
      console.log('Payload completo:', json.data);
      console.log('Solicitud:', json.data.solicitud);
      console.log('Piloto:', json.data.piloto);
      console.log('Forma de pago:', json.data.forma_pago);
      console.log('Productor:', json.data.productor);
      console.log('Costos:', json.data.costos);
      console.log('Items:', json.data.items);
      console.log('Motivos:', json.data.motivos);
      console.log('Rangos:', json.data.rangos);
      console.log('Eventos:', json.data.eventos);
      console.groupEnd();

      // Abrimos el drawer como siempre (mostrar #id)
      openDrawer({ id });
    }catch(err){
      console.error('No se pudo obtener la solicitud', err);
      openDrawer({ id }); // abrimos igual para mantener UX
    }
  });
});

        }

        // Drawer (solo UI, sin guardar)
        const drawer = document.getElementById('drawer');
        const drawerPanel = drawer.querySelector('.sv-drawer__panel');
        const drawerOverlay = drawer.querySelector('.sv-drawer__overlay');
        const drawerClose = document.getElementById('drawer-close');
        const drawerCancel = document.getElementById('drawer-cancel');
        const drawerId = document.getElementById('drawer-id');
        let lastFocus = null;

        async function openDrawer({
            id
        }) {
            lastFocus = document.activeElement;
            drawerId.textContent = `#${id}`;
            drawer.setAttribute('aria-hidden', 'false');
            drawer.classList.remove('hidden', 'closing');
            drawer.classList.add('opening');
            drawerPanel.setAttribute('tabindex', '-1');
            setTimeout(() => drawerPanel.focus(), 0);
            const onEnd = (e) => {
                if (e.target !== drawerPanel) return;
                drawer.classList.remove('opening');
                drawer.removeEventListener('animationend', onEnd, true);
            };
            drawer.addEventListener('animationend', onEnd, true);
        }

        function closeDrawer() {
            const active = document.activeElement;
            if (active && drawer.contains(active)) {
                if (lastFocus && typeof lastFocus.focus === 'function') {
                    lastFocus.focus();
                } else {
                    document.body.setAttribute('tabindex', '-1');
                    document.body.focus();
                    document.body.removeAttribute('tabindex');
                }
            }
            drawer.classList.add('closing');
            drawer.setAttribute('aria-hidden', 'true');
            const onEnd = (e) => {
                if (e.target !== drawerPanel) return;
                drawer.classList.remove('closing');
                drawer.classList.add('hidden');
                drawer.removeEventListener('animationend', onEnd, true);
            };
            drawer.addEventListener('animationend', onEnd, true);
        }
        drawerOverlay.addEventListener('click', closeDrawer);
        drawerClose.addEventListener('click', closeDrawer);
        drawerCancel.addEventListener('click', closeDrawer);

        // Filtro en vivo
        const debouncedLoad = debounce(load, 300);
        els.piloto.addEventListener('input', debouncedLoad);
        els.ses_usuario.addEventListener('input', debouncedLoad);
        els.estado.addEventListener('change', debouncedLoad);
        els.fecha_visita.addEventListener('change', debouncedLoad);

        load();
    })();
</script>