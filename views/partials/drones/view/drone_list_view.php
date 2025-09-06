<?php

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


                    <!-- <div class="card">
                        <h2 style="color: #5b21b6;">Datos inciales</h2>
                        <div class="form-grid grid-3">

                        </div>
                    </div> -->


                    <!-- datos iniciales del pedido -->
                    <div class="card">
                        <h2 style="color: #5b21b6;">Datos inciales</h2>
                        <div class="form-grid grid-2">
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
                                <label for="superficie_ha">Superficie (ha)</label>
                                <div class="input-icon input-icon-hashtag">
                                    <input type="number" step="0.01" min="0" id="superficie_ha" name="superficie_ha" placeholder="0.00" />
                                </div>
                            </div>
                        </div>
                        <div class="form-grid grid-4">
                            <div class="input-group">
                                <label for="observaciones">Observaciones</label>
                                <div class="input-icon input-icon-edit">
                                    <input type="text" id="observaciones" name="observaciones" placeholder="Notas internas" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Programar visita -->
                    <div class="card">
                        <h2 style="color: #5b21b6;">Programar visita</h2>
                        <div class="form-grid grid-4">
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
                                <label for="piloto_id">Piloto (ID)</label>
                                <div class="input-icon input-icon-id">
                                    <input type="number" id="piloto_id" name="piloto_id" min="1" placeholder="ID de dron_pilotos" />
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Dirección brindada por el productor -->
                    <div class="card">
                        <h2 style="color: #5b21b6;">Dirección</h2>
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
                        <h5 style="color: #5b21b6;">Ubicación provista por el celular del productor</h5>
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

                    <!-- datos estructurales -->
                    <div class="card">
                        <h2 style="color: #5b21b6;">Datos estructurales</h2>
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
                    </div>

                    <!-- Asignaciones -->
                    <div class="card">
                        <h2 style="color: #5b21b6;">Forma de pago</h2>
                        <div class="form-grid grid-2">
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
                        <h2 style="color: #5b21b6;">Costos</h2>
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
        background-color: darkgray;
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
    .helper-text {
        font-size: .85rem;
        color: #6b7280
    }

    .mini-input {
        width: 100%
    }

    .table-actions {
        display: flex;
        gap: 8px;
        justify-content: center
    }

    .chip {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 999px;
        background: #eef
    }

    .nowrap {
        white-space: nowrap
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
        border: 0
    }

    textarea#desglose_json {
        min-height: 96px
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

    /* Shim para inputs dentro de tablas con estructura .input-icon */
    .data-table td .input-icon {
        display: block;
    }

    .data-table td .input-icon input,
    .data-table td .input-icon select,
    .data-table td .input-icon textarea {
        width: 100%;
    }

    /* Asegura que .form-separator tenga margen incluso en tablas/entornos estrechos */
    .card .form-separator {
        margin: 8px 0 16px;
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

            els.cards.querySelectorAll('.btn-view').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const id = btn.dataset.id;
                    try {
                        const url = `${DRONE_API}?action=get_solicitud_full&id=${encodeURIComponent(id)}`;

                        const res = await fetch(url, {
                            cache: 'no-store'
                        });
                        const json = await res.json();
                        if (!json.ok) throw new Error(json.error || 'Error');

                        // Cargar formulario con detalle completo
                        const detalle = json.data;
                        fillForm(detalle);
                        openDrawer({
                            id
                        });

                    } catch (err) {
                        console.error('No se pudo obtener la solicitud', err);
                        openDrawer({
                            id
                        }); // abrimos igual para mantener UX
                    }
                });
            });

        }

        // ---------- Helpers de UI de edición ----------
        function el(tag, attrs = {}, children = []) {
            const n = document.createElement(tag);
            Object.entries(attrs).forEach(([k, v]) => {
                if (k === 'class') n.className = v;
                else if (k === 'dataset') Object.entries(v).forEach(([dk, dv]) => n.dataset[dk] = dv);
                else if (k.startsWith('on') && typeof v === 'function') n.addEventListener(k.slice(2), v);
                else n.setAttribute(k, v);
            });
            children.forEach(c => n.appendChild(typeof c === 'string' ? document.createTextNode(c) : c));
            return n;
        }

        function setV(id, val) {
            const node = document.getElementById(id);
            if (!node) return;
            if (node.type === 'checkbox') node.checked = Boolean(val);
            else node.value = (val ?? '') === null ? '' : String(val ?? '');
        }

        function getV(id) {
            const node = document.getElementById(id);
            if (!node) return null;
            return node.value === '' ? null : node.value;
        }

        // Render filas dinámicas (motivos/items/rangos)
        function makeMotivoRow(i, m = {}) {
            const tr = el('tr', {}, [
                el('td', {}, [String(i + 1)]),
                el('td', {}, [el('div', {
                    class: 'input-icon input-icon-hashtag'
                }, [
                    el('input', {
                        type: 'number',
                        min: '1',
                        value: m.patologia_id ?? '',
                        'aria-label': 'patologia_id'
                    })
                ])]),
                el('td', {}, [el('div', {
                    class: 'input-icon input-icon-toggle'
                }, [
                    el('select', {
                        'aria-label': 'es_otros'
                    }, [
                        el('option', {
                            value: '0'
                        }, ['0']),
                        el('option', {
                            value: '1',
                            selected: m.es_otros ? 'selected' : null
                        }, ['1'])
                    ])
                ])]),
                el('td', {}, [el('div', {
                    class: 'input-icon input-icon-edit'
                }, [
                    el('input', {
                        type: 'text',
                        value: m.otros_text ?? '',
                        'aria-label': 'otros_text'
                    })
                ])]),
                el('td', {
                    class: 'table-actions'
                }, [
                    el('button', {
                        type: 'button',
                        class: 'btn btn-cancelar',
                        onClick: () => tr.remove()
                    }, ['Quitar'])
                ])
            ]);
            return tr;
        }


        function makeRecetaTable(recetas = []) {
            const wrap = el('div');
            const table = el('table', {
                class: 'data-table',
                'aria-label': 'Recetas'
            }, [
                el('thead', {}, [el('tr', {}, [
                    el('th', {}, ['#']),
                    el('th', {}, ['principio_activo']),
                    el('th', {}, ['dosis']),
                    el('th', {}, ['unidad']),
                    el('th', {}, ['orden_mezcla']),
                    el('th', {}, ['notas']),
                    el('th', {}, ['Acciones'])
                ])]),
                el('tbody', {}, [])
            ]);
            const tbody = table.querySelector('tbody');
            recetas.forEach((r, idx) => {
                tbody.appendChild(makeRecetaRow(idx, r));
            });
            const btnAdd = el('button', {
                type: 'button',
                class: 'btn btn-info',
                onClick: () => {
                    tbody.appendChild(makeRecetaRow(tbody.children.length, {}));
                }
            }, ['Agregar receta']);
            wrap.appendChild(table);
            wrap.appendChild(el('div', {
                class: 'form-buttons'
            }, [btnAdd]));
            return wrap;
        }

        function makeRecetaRow(i, r = {}) {
            const tr = el('tr', {}, [
                el('td', {}, [String(i + 1)]),
                el('td', {}, [el('div', {
                    class: 'input-icon input-icon-edit'
                }, [
                    el('input', {
                        type: 'text',
                        value: r.principio_activo ?? '',
                        'aria-label': 'principio_activo'
                    })
                ])]),
                el('td', {}, [el('div', {
                    class: 'input-icon input-icon-hashtag'
                }, [
                    el('input', {
                        type: 'number',
                        step: '0.001',
                        value: r.dosis ?? '',
                        'aria-label': 'dosis'
                    })
                ])]),
                el('td', {}, [el('div', {
                    class: 'input-icon input-icon-edit'
                }, [
                    el('input', {
                        type: 'text',
                        value: r.unidad ?? '',
                        'aria-label': 'unidad'
                    })
                ])]),
                el('td', {}, [el('div', {
                    class: 'input-icon input-icon-hashtag'
                }, [
                    el('input', {
                        type: 'number',
                        value: r.orden_mezcla ?? '',
                        'aria-label': 'orden_mezcla'
                    })
                ])]),
                el('td', {}, [el('div', {
                    class: 'input-icon input-icon-edit'
                }, [
                    el('input', {
                        type: 'text',
                        value: r.notas ?? '',
                        'aria-label': 'notas'
                    })
                ])]),
                el('td', {
                    class: 'table-actions'
                }, [
                    el('button', {
                        type: 'button',
                        class: 'btn btn-cancelar',
                        onClick: () => tr.remove()
                    }, ['Quitar'])
                ])
            ]);
            return tr;
        }


        function makeItemRow(i, it = {}) {
            const tr = el('tr', {}, [
                el('td', {}, [String(i + 1)]),
                el('td', {}, [el('div', {
                    class: 'input-icon input-icon-hashtag'
                }, [
                    el('input', {
                        type: 'number',
                        min: '1',
                        value: it.patologia_id ?? '',
                        'aria-label': 'patologia_id'
                    })
                ])]),
                el('td', {}, [el('div', {
                    class: 'input-icon input-icon-toggle'
                }, [
                    el('select', {
                        'aria-label': 'fuente'
                    }, [
                        el('option', {
                            value: 'sve',
                            selected: it.fuente === 'sve' ? 'selected' : null
                        }, ['sve']),
                        el('option', {
                            value: 'productor',
                            selected: it.fuente === 'productor' ? 'selected' : null
                        }, ['productor'])
                    ])
                ])]),
                el('td', {}, [el('div', {
                    class: 'input-icon input-icon-id'
                }, [
                    el('input', {
                        type: 'number',
                        min: '1',
                        value: it.producto_id ?? '',
                        'aria-label': 'producto_id'
                    })
                ])]),
                el('td', {}, [el('div', {
                    class: 'input-icon input-icon-edit'
                }, [
                    el('input', {
                        type: 'text',
                        value: it.nombre_producto ?? '',
                        'aria-label': 'nombre_producto'
                    })
                ])]),
                el('td', {}, [el('div', {
                    class: 'input-icon input-icon-hashtag'
                }, [
                    el('input', {
                        type: 'number',
                        step: '0.01',
                        value: it.costo_hectarea_snapshot ?? '',
                        'aria-label': 'costo_hectarea_snapshot'
                    })
                ])]),
                el('td', {}, [el('div', {
                    class: 'input-icon input-icon-hashtag'
                }, [
                    el('input', {
                        type: 'number',
                        step: '0.01',
                        value: it.total_producto_snapshot ?? '',
                        'aria-label': 'total_producto_snapshot'
                    })
                ])]),
                el('td', {}, [makeRecetaTable(it.recetas || [])]),
                el('td', {
                    class: 'table-actions'
                }, [
                    el('button', {
                        type: 'button',
                        class: 'btn btn-cancelar',
                        onClick: () => tr.remove()
                    }, ['Quitar'])
                ])
            ]);
            return tr;
        }


        function makeRangoRow(i, r = {}) {
            const tr = el('tr', {}, [
                el('td', {}, [String(i + 1)]),
                el('td', {}, [el('div', {
                    class: 'input-icon input-icon-date'
                }, [
                    el('input', {
                        type: 'text',
                        value: r.rango ?? '',
                        placeholder: 'octubre_q1',
                        'aria-label': 'rango'
                    })
                ])]),
                el('td', {
                    class: 'table-actions'
                }, [
                    el('button', {
                        type: 'button',
                        class: 'btn btn-cancelar',
                        onClick: () => tr.remove()
                    }, ['Quitar'])
                ])
            ]);
            return tr;
        }


        function fillForm(d) {
            // ID
            setV('drawer-id', d?.solicitud?.id ? `#${d.solicitud.id}` : '');
            // Base
            const s = d.solicitud || {};
            setV('productor_id_real', s.productor_id_real);
            setV('ses_usuario', s.ses_usuario ?? d?.productor?.usuario ?? '');
            setV('superficie_ha', s.superficie_ha);
            setV('fecha_visita', s.fecha_visita);
            setV('hora_visita_desde', s.hora_visita_desde);
            setV('hora_visita_hasta', s.hora_visita_hasta);
            setV('estado', s.estado);
            setV('motivo_cancelacion', s.motivo_cancelacion);
            setV('observaciones', s.observaciones);
            // Flags
            ['representante', 'linea_tension', 'zona_restringida', 'corriente_electrica', 'agua_potable', 'libre_obstaculos', 'area_despegue', 'en_finca']
            .forEach(k => setV(k, s[k]));
            // Dirección + ubicación
            ['dir_provincia', 'dir_localidad', 'dir_calle', 'dir_numero', 'ubicacion_lat', 'ubicacion_lng', 'ubicacion_acc', 'ubicacion_ts']
            .forEach(k => setV(k, s[k]));
            // Asignaciones y costos
            setV('piloto_id', s.piloto_id);
            setV('forma_pago_id', s.forma_pago_id);
            setV('coop_descuento_nombre', s.coop_descuento_nombre);
            const c = d.costos || {};
            setV('costo_moneda', c.moneda);
            setV('costo_base_por_ha', c.costo_base_por_ha);
            setV('base_ha', c.base_ha);
            setV('base_total', c.base_total);
            setV('productos_total', c.productos_total);
            setV('total', c.total);
            document.getElementById('desglose_json').value = c.desglose_json ?? '';

            // Motivos
            const tbm = document.querySelector('#tabla-motivos tbody');
            tbm.innerHTML = '';
            (d.motivos || []).forEach((m, idx) => tbm.appendChild(makeMotivoRow(idx, m)));

            // Items
            const tbi = document.querySelector('#tabla-items tbody');
            tbi.innerHTML = '';
            (d.items || []).forEach((it, idx) => tbi.appendChild(makeItemRow(idx, it)));

            // Rangos
            const tbr = document.querySelector('#tabla-rangos tbody');
            tbr.innerHTML = '';
            (d.rangos || []).forEach((r, idx) => tbr.appendChild(makeRangoRow(idx, r)));
        }

        function serializeTable(tbody, schema) {
            // schema: [{k:'patologia_id', type:'number'}, ...]
            const rows = [];
            Array.from(tbody.children).forEach(tr => {
                const cells = Array.from(tr.querySelectorAll('td'));
                const row = {};
                let ci = 0;
                schema.forEach(col => {
                    // Busca el primer input/select dentro de la celda desplazada
                    const cell = cells[++ci]; // el 0 es el contador "#"
                    const input = cell ? cell.querySelector('input,select,textarea') : null;
                    let val = input ? input.value : null;
                    if (col.type === 'number') val = val === '' ? null : Number(val);
                    if (col.type === 'int') val = val === '' ? null : parseInt(val, 10);
                    if (col.type === 'bool01') val = input && input.value === '1' ? 1 : 0;
                    row[col.k] = (val === '' ? null : val);
                });
                rows.push(row);
            });
            return rows;
        }

        // Botones de agregar
        document.getElementById('add-motivo').addEventListener('click', () => {
            document.querySelector('#tabla-motivos tbody').appendChild(makeMotivoRow(document.querySelector('#tabla-motivos tbody').children.length, {}));
        });
        document.getElementById('add-item').addEventListener('click', () => {
            document.querySelector('#tabla-items tbody').appendChild(makeItemRow(document.querySelector('#tabla-items tbody').children.length, {}));
        });
        document.getElementById('add-rango').addEventListener('click', () => {
            document.querySelector('#tabla-rangos tbody').appendChild(makeRangoRow(document.querySelector('#tabla-rangos tbody').children.length, {}));
        });

        // Guardar
        document.getElementById('btn-guardar').addEventListener('click', async () => {
            const tbm = document.querySelector('#tabla-motivos tbody');
            const tbi = document.querySelector('#tabla-items tbody');
            const tbr = document.querySelector('#tabla-rangos tbody');

            const motivos = serializeTable(tbm, [{
                    k: 'patologia_id',
                    type: 'int'
                },
                {
                    k: 'es_otros',
                    type: 'bool01'
                },
                {
                    k: 'otros_text',
                    type: 'text'
                }
            ]);

            // Items + recetas
            const items = [];
            Array.from(tbi.children).forEach(tr => {
                const tds = tr.querySelectorAll('td');
                const obj = {
                    patologia_id: parseInt(tds[1].querySelector('input').value || '0', 10) || null,
                    fuente: tds[2].querySelector('select').value || null,
                    producto_id: tds[3].querySelector('input').value === '' ? null : parseInt(tds[3].querySelector('input').value, 10),
                    nombre_producto: tds[4].querySelector('input').value || null,
                    costo_hectarea_snapshot: tds[5].querySelector('input').value === '' ? null : parseFloat(tds[5].querySelector('input').value),
                    total_producto_snapshot: tds[6].querySelector('input').value === '' ? null : parseFloat(tds[6].querySelector('input').value),
                    recetas: []
                };
                // recetas
                const recetasTable = tds[7].querySelector('tbody');
                const recetas = [];
                Array.from(recetasTable.children).forEach((rr, idx) => {
                    const rtd = rr.querySelectorAll('td');
                    recetas.push({
                        principio_activo: rtd[1].querySelector('input').value || null,
                        dosis: rtd[2].querySelector('input').value === '' ? null : parseFloat(rtd[2].querySelector('input').value),
                        unidad: rtd[3].querySelector('input').value || null,
                        orden_mezcla: rtd[4].querySelector('input').value === '' ? null : parseInt(rtd[4].querySelector('input').value, 10),
                        notas: rtd[5].querySelector('input').value || null
                    });
                });
                obj.recetas = recetas;
                items.push(obj);
            });

            const rangos = serializeTable(tbr, [{
                k: 'rango',
                type: 'text'
            }]);

            // Base
            const payload = {
                id: Number((document.getElementById('drawer-id').textContent || '').replace('#', '')) || null,
                solicitud: {
                    productor_id_real: getV('productor_id_real'),
                    ses_usuario: getV('ses_usuario'),
                    superficie_ha: getV('superficie_ha') ? parseFloat(getV('superficie_ha')) : null,
                    fecha_visita: getV('fecha_visita'),
                    hora_visita_desde: getV('hora_visita_desde'),
                    hora_visita_hasta: getV('hora_visita_hasta'),
                    estado: getV('estado'),
                    motivo_cancelacion: getV('motivo_cancelacion'),
                    observaciones: getV('observaciones'),
                    representante: getV('representante'),
                    linea_tension: getV('linea_tension'),
                    zona_restringida: getV('zona_restringida'),
                    corriente_electrica: getV('corriente_electrica'),
                    agua_potable: getV('agua_potable'),
                    libre_obstaculos: getV('libre_obstaculos'),
                    area_despegue: getV('area_despegue'),
                    en_finca: getV('en_finca'),
                    dir_provincia: getV('dir_provincia'),
                    dir_localidad: getV('dir_localidad'),
                    dir_calle: getV('dir_calle'),
                    dir_numero: getV('dir_numero'),
                    ubicacion_lat: getV('ubicacion_lat') ? parseFloat(getV('ubicacion_lat')) : null,
                    ubicacion_lng: getV('ubicacion_lng') ? parseFloat(getV('ubicacion_lng')) : null,
                    ubicacion_acc: getV('ubicacion_acc') ? parseFloat(getV('ubicacion_acc')) : null,
                    ubicacion_ts: getV('ubicacion_ts'),
                    piloto_id: getV('piloto_id') ? parseInt(getV('piloto_id'), 10) : null,
                    forma_pago_id: getV('forma_pago_id') ? parseInt(getV('forma_pago_id'), 10) : null,
                    coop_descuento_nombre: getV('coop_descuento_nombre')
                },
                costos: {
                    moneda: getV('costo_moneda'),
                    costo_base_por_ha: getV('costo_base_por_ha') ? parseFloat(getV('costo_base_por_ha')) : null,
                    base_ha: getV('base_ha') ? parseFloat(getV('base_ha')) : null,
                    base_total: getV('base_total') ? parseFloat(getV('base_total')) : null,
                    productos_total: getV('productos_total') ? parseFloat(getV('productos_total')) : null,
                    total: getV('total') ? parseFloat(getV('total')) : null,
                    desglose_json: document.getElementById('desglose_json').value || null
                },
                motivos,
                items,
                rangos
            };

            if (!payload.id) {
                showAlert('error', 'ID de solicitud no válido');
                return;
            }

            try {
                const res = await fetch(`${DRONE_API}?action=update_solicitud`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload),
                    cache: 'no-store'
                });
                const json = await res.json();
                if (!json.ok) throw new Error(json.error || 'Error');
                showAlert('success', '¡Operación completada con éxito!');
                closeDrawer();
                debouncedLoad();
            } catch (err) {
                showAlert('error', `No se pudo guardar: ${err.message}`);
            }
        });


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