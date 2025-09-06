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
                <!-- OJO: ID distinto al del drawer -->
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
    <div id="cards" class="triple-tarjetas card-grid grid-4"></div>

    <!-- Drawer -->
    <div id="drawer" class="sv-drawer hidden" aria-hidden="true">
        <div class="sv-drawer__overlay" data-close></div>
        <aside class="sv-drawer__panel" role="dialog" aria-modal="true" aria-labelledby="drawer-title">
            <div class="sv-drawer__header">
                <h3 id="drawer-title">Solicitud <span id="drawer-id"></span></h3>
                <button class="sv-drawer__close" id="drawer-close" aria-label="Cerrar">×</button>
            </div>

            <div class="sv-drawer__body">
                <form id="form-solicitud" class="form" autocomplete="off" novalidate>

                    <!-- Datos iniciales -->
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

                            <div class="input-group" id="grp_motivo_cancelacion">
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
                        <div class="input-group">
                            <label for="observaciones">Observaciones</label>
                            <div class="input-icon input-icon-edit">
                                <input type="text" id="observaciones" name="observaciones" placeholder="Notas internas" />
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
                                <label for="piloto_id">Piloto</label>
                                <div class="input-icon input-icon-id">
                                    <select id="piloto_id" name="piloto_id">
                                        <option value="">Seleccionar piloto</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dirección -->
                    <div class="card">
                        <h2 style="color: #5b21b6;">Dirección</h2>
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

                        <br>
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
                                <label class="sr-only" for="btn-abrir-ubicacion">Abrir ubicación</label>
                                <button type="button" id="btn-abrir-ubicacion" class="btn btn-info">Abrir ubicación</button>
                            </div>
                        </div>
                    </div>

                    <!-- Datos estructurales -->
                    <div class="card">
                        <h2 style="color: #5b21b6;">Datos estructurales</h2>
                        <div class="form-grid grid-4">
                            <!-- flags -->
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

                    <!-- Forma de pago -->
                    <div class="card">
                        <h2 style="color: #5b21b6;">Forma de pago</h2>
                        <div class="form-grid grid-2">
                            <div class="input-group">
                                <label for="forma_pago_id">Forma de pago</label>
                                <div class="input-icon input-icon-id">
                                    <select id="forma_pago_id" name="forma_pago_id">
                                        <option value="">Seleccionar forma de pago</option>
                                    </select>
                                </div>
                            </div>
                            <div class="input-group">
                                <label for="coop_descuento_nombre">Coop. descuento</label>
                                <div class="input-icon input-icon-edit">
                                    <input type="text" id="coop_descuento_nombre" name="coop_descuento_nombre" placeholder="No aplica" />
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
                        <div id="costos-resumen" class="costos-resumen card" style="margin-top:8px;padding:12px;"></div>
                    </div>

                    <!-- Patologías -->
                    <div class="card">
                        <h2 style="color:#5b21b6;">Patologías</h2>

                        <div id="patologias-chips" class="chips"></div>

                        <div class="form-grid grid-3" style="margin-top:12px;">
                            <div class="input-group">
                                <label for="patologia_new">Agregar patología</label>
                                <div class="input-icon input-icon-edit">
                                    <select id="patologia_new"></select>
                                </div>
                            </div>
                            <div class="input-group">
                                <label>Otros</label>
                                <div class="input-icon input-icon-toggle">
                                    <select id="patologia_new_otro">
                                        <option value="0">No</option>
                                        <option value="1">Sí</option>
                                    </select>
                                </div>
                            </div>
                            <div class="input-group" id="grp_patologia_otro_text" style="display:none;">
                                <label for="patologia_new_text">Texto</label>
                                <div class="input-icon input-icon-edit">
                                    <input type="text" id="patologia_new_text" placeholder="Detalle otros" />
                                </div>
                            </div>
                        </div>

                        <div class="form-buttons">
                            <button type="button" class="btn btn-info" id="btn_add_patologia">Agregar</button>
                        </div>
                    </div>

                    <!-- Productos -->
                    <div class="card">
                        <h2 style="color:#5b21b6;">Productos</h2>

                        <div id="productos-list" class="productos-list"></div>

                        <!-- Alta de producto (sin costo manual) -->
                        <div class="form-grid grid-4" style="margin-top:12px;">
                            <div class="input-group">
                                <label for="producto_new">Producto</label>
                                <div class="input-icon input-icon-edit">
                                    <select id="producto_new"></select>
                                </div>
                            </div>
                            <div class="input-group">
                                <label for="producto_new_fuente">Fuente</label>
                                <div class="input-icon input-icon-toggle">
                                    <select id="producto_new_fuente">
                                        <option value="sve">sve</option>
                                        <option value="productor">productor</option>
                                    </select>
                                </div>
                            </div>
                            <div class="input-group">
                                <label for="producto_new_patologia">Patología</label>
                                <div class="input-icon input-icon-edit">
                                    <select id="producto_new_patologia"></select>
                                </div>
                            </div>
                        </div>

                        <div class="form-buttons">
                            <button type="button" class="btn btn-info" id="btn_add_producto">Agregar producto</button>
                        </div>
                    </div>

                    <!-- Receta (tabla única para todos los productos) -->
                    <div class="card">
                        <h2 style="color:#5b21b6;">Receta</h2>
                        <table class="data-table" id="tabla-receta-combinada" aria-label="Receta">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Principio activo</th>
                                    <th>Dosis</th>
                                    <th>Unidad</th>
                                    <th>Orden mezcla</th>
                                    <th>Notas</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <!-- Rangos -->
                    <div class="card">
                        <h2 style="color:#5b21b6;">Rangos de fechas seleccionadas</h2>

                        <div id="rangos-chips" class="chips"></div>

                        <div class="form-grid grid-3" style="margin-top:12px;">
                            <div class="input-group">
                                <label for="rango_new">Agregar rango</label>
                                <div class="input-icon input-icon-date">
                                    <select id="rango_new">
                                        <option value="">Seleccionar</option>
                                        <option>enero_q1</option>
                                        <option>enero_q2</option>
                                        <option>febrero_q1</option>
                                        <option>febrero_q2</option>
                                        <option>octubre_q1</option>
                                        <option>octubre_q2</option>
                                        <option>noviembre_q1</option>
                                        <option>noviembre_q2</option>
                                        <option>diciembre_q1</option>
                                        <option>diciembre_q2</option>
                                    </select>
                                </div>
                            </div>
                            <div class="input-group">
                                <label class="sr-only" for="btn_add_rango">Agregar</label>
                                <button type="button" id="btn_add_rango" class="btn btn-info">Agregar</button>
                            </div>
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

    .data-table td .input-icon {
        display: block;
    }

    .data-table td .input-icon input,
    .data-table td .input-icon select,
    .data-table td .input-icon textarea {
        width: 100%;
    }

    .card .form-separator {
        margin: 8px 0 16px;
    }

    .chips {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .chip-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #eef;
        border-radius: 999px;
        padding: 4px 10px;
    }

    .chip-pill .close {
        cursor: pointer;
        border: 0;
        background: transparent;
        font-weight: bold;
    }

    .producto-item {
        border: 1px solid #eee;
        border-radius: 12px;
        padding: 10px;
        margin-bottom: 8px;
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 6px;
    }

    .producto-item .meta {
        font-size: .9rem;
        color: #6b7280;
    }

    .producto-item .acciones {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .costos-resumen p {
        margin: .25rem 0;
    }

    /* Oculto por defecto el motivo hasta que el estado sea cancelada */
    #grp_motivo_cancelacion {
        display: none;
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
        const parseNum = (v) => (v === '' || v === null || v === undefined) ? null : Number(String(v).replace(',', '.'));

        const catalog = {
            pilotos: [],
            formasPago: [],
            patologias: [],
            productos: []
        };
        const state = {
            motivos: [],
            rangos: [],
            items: []
        }; // items incluyen receta única por producto

        const getFilters = () => ({
            piloto: els.piloto.value.trim(),
            ses_usuario: els.ses_usuario.value.trim(),
            estado: els.estado_filtro.value, // <- se envía como 'estado' al backend
            fecha_visita: els.fecha_visita.value
        });

        async function loadCatalogs() {
            const qs = (a) => fetch(`${DRONE_API}?action=${a}`, {
                cache: 'no-store'
            }).then(r => r.json());
            const [pi, fp, pa, pr] = await Promise.all([
                qs('list_pilotos'), qs('list_formas_pago'), qs('list_patologias'), qs('list_productos')
            ]);
            catalog.pilotos = pi.ok ? pi.data : [];
            catalog.formasPago = fp.ok ? fp.data : [];
            catalog.patologias = pa.ok ? pa.data : [];
            catalog.productos = pr.ok ? pr.data : [];
        }

        function fillSelect(sel, data, {
            valueKey = 'id',
            labelKey = 'nombre',
            selected = null,
            placeholder = null
        } = {}) {
            sel.innerHTML = '';
            if (placeholder !== null) {
                const op = document.createElement('option');
                op.value = '';
                op.textContent = placeholder;
                sel.appendChild(op);
            }
            data.forEach(r => {
                const op = document.createElement('option');
                op.value = r[valueKey];
                op.textContent = r[labelKey];
                if (selected !== null && String(selected) === String(r[valueKey])) op.selected = true;
                sel.appendChild(op);
            });
        }

        // listado
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

        function renderCards(items) {
            els.cards.innerHTML = '';
            items.forEach(it => {
                const card = document.createElement('div');
                card.className = 'product-card';
                card.innerHTML = `
        <div class="product-header">
          <h4>${esc(it.ses_usuario||'—')}</h4>
          <p>Pedido número: ${esc(it.id??'')}</p>
        </div>
        <div class="product-body">
          <div class="user-info">
            <div>
              <strong>${esc(it.piloto||'Sin piloto asignado')}</strong>
              <div class="role">Fecha visita: ${esc(it.fecha_visita||'')} ${it.hora_visita?`(${esc(it.hora_visita)})`:''}</div>
            </div>
          </div>
          <p class="description">${esc(it.observaciones||'')}</p>
          <hr />
          <div class="product-footer">
            <div class="metric"><span class="badge ${badgeClass(it.estado)}">${prettyEstado(it.estado)}</span></div>
            <button class="btn-view" data-id="${it.id}">Ver detalle</button>
          </div>
        </div>`;
                els.cards.appendChild(card);
            });

            els.cards.querySelectorAll('.btn-view').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const id = btn.dataset.id;
                    try {
                        await loadCatalogs();
                        const url = `${DRONE_API}?action=get_solicitud_full&id=${encodeURIComponent(id)}`;
                        const res = await fetch(url, {
                            cache: 'no-store'
                        });
                        const json = await res.json();
                        if (!json.ok) throw new Error(json.error || 'Error');
                        fillForm(json.data);
                        openDrawer({
                            id
                        });
                    } catch (err) {
                        console.error('No se pudo obtener la solicitud', err);
                        openDrawer({
                            id
                        });
                    }
                });
            });
        }

        // chips / listas
        const $chipsPat = $('#patologias-chips');
        const $chipsRan = $('#rangos-chips');
        const $listProd = $('#productos-list');

        function renderPatologias() {
            if (!$chipsPat) return;
            $chipsPat.innerHTML = '';
            state.motivos.forEach((m, i) => {
                const pat = catalog.patologias.find(p => String(p.id) === String(m.patologia_id));
                const label = pat ? pat.nombre : (m.patologia_nombre || 'Otra');
                const extra = m.es_otros ? ` (otros: ${m.otros_text || '—'})` : '';
                const div = document.createElement('div');
                div.className = 'chip-pill';
                div.innerHTML = `<span>${esc(label)}${esc(extra)}</span><button class="close" aria-label="Quitar">&times;</button>`;
                div.querySelector('.close').addEventListener('click', () => {
                    state.motivos.splice(i, 1);
                    renderPatologias();
                });
                $chipsPat.appendChild(div);
            });
        }

        function renderRangos() {
            if (!$chipsRan) return;
            $chipsRan.innerHTML = '';
            state.rangos.forEach((r, i) => {
                const div = document.createElement('div');
                div.className = 'chip-pill';
                div.innerHTML = `<span>${esc(r.rango)}</span><button class="close" aria-label="Quitar">&times;</button>`;
                div.querySelector('.close').addEventListener('click', () => {
                    state.rangos.splice(i, 1);
                    renderRangos();
                });
                $chipsRan.appendChild(div);
            });
        }

        function renderProductos() {
            if (!$listProd) return;
            $listProd.innerHTML = '';
            state.items.forEach((it, idx) => {
                const pInfo = catalog.productos.find(p => String(p.id) === String(it.producto_id));
                const nombre = it.nombre_producto || pInfo?.nombre || `Producto #${it.producto_id}`;
                const pat = catalog.patologias.find(p => String(p.id) === String(it.patologia_id));
                const costo = it.costo_hectarea_snapshot ?? pInfo?.costo_hectarea ?? null;

                const wrapper = document.createElement('div');
                wrapper.className = 'producto-item';
                wrapper.innerHTML = `
        <div>
          <strong>${esc(nombre)}</strong>
          <div class="meta">Fuente: ${esc(it.fuente||'sve')} · Patología: ${esc(pat ? pat.nombre : '—')}</div>
          <div class="meta">Costo/ha: $${fmtNum(costo)}</div>
        </div>
        <div class="acciones">
          <button type="button" class="btn btn-cancelar" data-role="quitar">Quitar</button>
        </div>`;
                wrapper.querySelector('[data-role="quitar"]').addEventListener('click', () => {
                    state.items.splice(idx, 1);
                    renderProductos();
                    renderRecetaCombinada();
                    recalcCostos();
                });
                $listProd.appendChild(wrapper);
            });
        }

        // receta combinada
        function ensureRecetaSlots() {
            state.items.forEach(it => {
                if (!it.receta) {
                    const first = Array.isArray(it.recetas) && it.recetas.length ? it.recetas[0] : null;
                    it.receta = {
                        principio_activo: first?.principio_activo ?? it.principio_activo ?? null,
                        dosis: first?.dosis ?? null,
                        unidad: first?.unidad ?? '',
                        orden_mezcla: first?.orden_mezcla ?? null,
                        notas: first?.notas ?? ''
                    };
                }
            });
        }

        function renderRecetaCombinada() {
            const tb = $('#tabla-receta-combinada tbody');
            if (!tb) return;
            ensureRecetaSlots();
            tb.innerHTML = '';

            state.items.forEach((it) => {
                const pInfo = catalog.productos.find(p => String(p.id) === String(it.producto_id));
                const nombre = it.nombre_producto || pInfo?.nombre || `Producto #${it.producto_id}`;
                const r = it.receta;

                const tr = document.createElement('tr');
                tr.innerHTML = `
        <td>${esc(nombre)}</td>
        <td><div class="input-icon input-icon-edit"><input type="text" value="${esc(r.principio_activo ?? (it.principio_activo || ''))}"></div></td>
        <td><div class="input-icon input-icon-hashtag"><input type="number" step="0.001" value="${fmtNum(r.dosis)}"></div></td>
        <td><div class="input-icon input-icon-edit"><input type="text" value="${esc(r.unidad||'')}"></div></td>
        <td><div class="input-icon input-icon-hashtag"><input type="number" value="${fmtNum(r.orden_mezcla)}"></div></td>
        <td><div class="input-icon input-icon-edit"><input type="text" value="${esc(r.notas||'')}"></div></td>
      `;
                const [pa, dosis, uni, ord, notas] = tr.querySelectorAll('input');
                pa.addEventListener('input', e => it.receta.principio_activo = e.target.value);
                dosis.addEventListener('input', e => it.receta.dosis = parseNum(e.target.value));
                uni.addEventListener('input', e => it.receta.unidad = e.target.value);
                ord.addEventListener('input', e => it.receta.orden_mezcla = parseNum(e.target.value));
                notas.addEventListener('input', e => it.receta.notas = e.target.value);

                tb.appendChild(tr);
            });
        }

        // costos
        function recalcCostos() {
            const base_ha = parseNum($('#base_ha')?.value);
            const costo_base = parseNum($('#costo_base_por_ha')?.value);
            const base_total = (base_ha || 0) * (costo_base || 0);
            $('#base_total') && ($('#base_total').value = fmtNum(base_total));

            let productos_total = 0;
            state.items.forEach(it => {
                const ch = Number(it.costo_hectarea_snapshot || 0);
                productos_total += ch * (base_ha || 0);
                it.total_producto_snapshot = ch * (base_ha || 0);
            });
            $('#productos_total') && ($('#productos_total').value = fmtNum(productos_total));

            const total = base_total + productos_total;
            $('#total') && ($('#total').value = fmtNum(total));

            const resumen = $('#costos-resumen');
            if (resumen) {
                const baseTxt = `Base: ${fmtNum(base_ha||0)} ha × $${fmtNum(costo_base||0)} = $${fmtNum(base_total)}`;
                const prodsTxt = `Productos: $${fmtNum(productos_total)}`;
                const totalTxt = `<strong>Total: $${fmtNum(total)}</strong>`;
                resumen.innerHTML = `<p>${baseTxt}</p><p>${prodsTxt}</p><p>${totalTxt}</p>`;
            }
        }

        // utils set/get
        function setV(id, val) {
            const n = document.getElementById(id);
            if (!n) return;
            if (n.type === 'checkbox') n.checked = Boolean(val);
            else n.value = (val ?? '') === null ? '' : String(val ?? '');
        }

        function getV(id) {
            const n = document.getElementById(id);
            if (!n) return null;
            return n.value === '' ? null : n.value;
        }

        // estado cancelada -> mostrar motivo
        function toggleMotivo() {
            const sel = document.querySelector('#form-solicitud #estado'); // <- el del drawer
            const grp = $('#grp_motivo_cancelacion');
            if (!sel || !grp) return;
            grp.style.display = (String(sel.value).toLowerCase() === 'cancelada') ? '' : 'none';
        }

        // rellenar formulario
        function fillForm(d) {
            $('#drawer-id').textContent = d?.solicitud?.id ? `#${d.solicitud.id}` : '';

            const s = d.solicitud || {};
            setV('productor_id_real', s.productor_id_real);
            setV('ses_usuario', s.ses_usuario ?? d?.productor?.usuario ?? '');
            setV('superficie_ha', fmtNum(s.superficie_ha));
            setV('fecha_visita', s.fecha_visita);
            setV('hora_visita_desde', s.hora_visita_desde);
            setV('hora_visita_hasta', s.hora_visita_hasta);
            setV('estado', s.estado);
            toggleMotivo();
            setV('motivo_cancelacion', s.motivo_cancelacion);
            setV('observaciones', s.observaciones);

            // flags
            ['representante', 'linea_tension', 'zona_restringida', 'corriente_electrica', 'agua_potable', 'libre_obstaculos', 'area_despegue', 'en_finca']
            .forEach(k => setV(k, s[k]));

            // dir + ubica
            ['dir_provincia', 'dir_localidad', 'dir_calle', 'dir_numero', 'ubicacion_lat', 'ubicacion_lng', 'ubicacion_acc']
            .forEach(k => setV(k, s[k]));

            // selects de catálogos
            fillSelect($('#piloto_id'), catalog.pilotos, {
                selected: s.piloto_id,
                placeholder: 'Seleccionar piloto'
            });
            fillSelect($('#forma_pago_id'), catalog.formasPago, {
                selected: s.forma_pago_id,
                placeholder: 'Seleccionar forma de pago'
            });
            fillSelect($('#patologia_new'), catalog.patologias, {
                placeholder: 'Seleccionar patología'
            });
            fillSelect($('#producto_new'), catalog.productos, {
                placeholder: 'Seleccionar producto'
            });
            fillSelect($('#producto_new_patologia'), catalog.patologias, {
                placeholder: 'Patología asociada'
            });

            // opción "Otra" en patologías
            const selPatNew = $('#patologia_new');
            selPatNew.append(new Option('Otra', '__otra__'));
            selPatNew.addEventListener('change', e => {
                const show = e.target.value === '__otra__';
                $('#grp_patologia_otro_text').style.display = show ? '' : 'none';
                // al elegir "Otra" marcamos "Sí" en el toggle de otros para coherencia visual
                $('#patologia_new_otro').value = show ? '1' : '0';
            });

            // costos
            const c = d.costos || {};
            setV('costo_moneda', c.moneda);
            setV('costo_base_por_ha', fmtNum(c.costo_base_por_ha));
            setV('base_ha', fmtNum(c.base_ha));
            setV('base_total', fmtNum(c.base_total));
            setV('productos_total', fmtNum(c.productos_total));
            setV('total', fmtNum(c.total));
            recalcCostos();

            // forma de pago - placeholder "No aplica" si viene vacío
            setV('coop_descuento_nombre', s.coop_descuento_nombre || '');
            $('#coop_descuento_nombre').placeholder = 'No aplica';

            // motivos
            state.motivos = (d.motivos || []).map(m => ({
                patologia_id: m.patologia_id,
                es_otros: Number(m.es_otros) ? 1 : 0,
                otros_text: m.otros_text || '',
                patologia_nombre: m.patologia_nombre
            }));
            renderPatologias();

            // rangos
            state.rangos = (d.rangos || []).map(r => ({
                rango: r.rango
            }));
            renderRangos();

            // items -> receta única por producto
            state.items = (d.items || []).map(it => ({
                patologia_id: it.patologia_id,
                fuente: it.fuente || 'sve',
                producto_id: it.producto_id,
                nombre_producto: it.producto_nombre || it.nombre_producto || null,
                costo_hectarea_snapshot: it.costo_hectarea_snapshot ?? it.producto_costo_hectarea ?? null,
                receta: (() => {
                    const r0 = (it.recetas && it.recetas[0]) ? it.recetas[0] : null;
                    return {
                        principio_activo: r0?.principio_activo ?? it.principio_activo ?? null,
                        dosis: r0?.dosis ?? null,
                        unidad: r0?.unidad ?? '',
                        orden_mezcla: r0?.orden_mezcla ?? null,
                        notas: r0?.notas ?? ''
                    };
                })()
            }));
            renderProductos();
            renderRecetaCombinada();
            recalcCostos();
        }

        // listeners
        document.querySelector('#form-solicitud #estado')?.addEventListener('change', toggleMotivo);

        $('#btn-abrir-ubicacion')?.addEventListener('click', () => {
            const lat = getV('ubicacion_lat');
            const lng = getV('ubicacion_lng');
            if (!lat || !lng) {
                showAlert('error', 'Cargá latitud y longitud primero');
                return;
            }
            window.open(`https://www.google.com/maps?q=${lat},${lng}`, '_blank');
        });

        // Patologías
        $('#patologia_new_otro')?.addEventListener('change', (e) => {
            $('#grp_patologia_otro_text').style.display = e.target.value === '1' ? '' : 'none';
        });
        $('#btn_add_patologia')?.addEventListener('click', () => {
            const val = $('#patologia_new').value;
            if (!val) return;

            if (val === '__otra__') {
                const txt = ($('#patologia_new_text').value || '').trim();
                if (!txt) return showAlert('error', 'Escribí el detalle para "Otra".');
                state.motivos.push({
                    patologia_id: null,
                    es_otros: 1,
                    otros_text: txt
                });
            } else {
                state.motivos.push({
                    patologia_id: Number(val),
                    es_otros: 0,
                    otros_text: ''
                });
            }

            $('#patologia_new').value = '';
            $('#patologia_new_text').value = '';
            $('#grp_patologia_otro_text').style.display = 'none';
            renderPatologias();
        });

        // Rangos
        $('#btn_add_rango')?.addEventListener('click', () => {
            const r = $('#rango_new').value;
            if (!r) return;
            state.rangos.push({
                rango: r
            });
            $('#rango_new').value = '';
            renderRangos();
        });

        // Alta de producto (toma costo del stock automáticamente)
        $('#btn_add_producto')?.addEventListener('click', () => {
            const pid = $('#producto_new').value;
            const fuente = $('#producto_new_fuente').value || 'sve';
            const patId = $('#producto_new_patologia').value;
            if (!pid || !patId) return showAlert('error', 'Elegí producto y patología');

            const prod = catalog.productos.find(p => String(p.id) === String(pid));
            state.items.push({
                patologia_id: Number(patId),
                fuente,
                producto_id: Number(pid),
                nombre_producto: prod?.nombre || null,
                costo_hectarea_snapshot: prod?.costo_hectarea ?? null,
                receta: {
                    principio_activo: null,
                    dosis: null,
                    unidad: '',
                    orden_mezcla: null,
                    notas: ''
                }
            });

            $('#producto_new').value = '';
            $('#producto_new_patologia').value = '';
            renderProductos();
            renderRecetaCombinada();
            recalcCostos();
        });

        // Costos live
        $('#base_ha')?.addEventListener('input', recalcCostos);
        $('#costo_base_por_ha')?.addEventListener('input', recalcCostos);

        // Guardar
        $('#btn-guardar')?.addEventListener('click', async () => {
            const payload = {
                id: Number(($('#drawer-id').textContent || '').replace('#', '')) || null,
                solicitud: {
                    productor_id_real: getV('productor_id_real'),
                    ses_usuario: getV('ses_usuario'),
                    superficie_ha: parseNum(getV('superficie_ha')),
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
                    ubicacion_lat: parseNum(getV('ubicacion_lat')),
                    ubicacion_lng: parseNum(getV('ubicacion_lng')),
                    ubicacion_acc: parseNum(getV('ubicacion_acc')),
                    ubicacion_ts: null,
                    piloto_id: getV('piloto_id') ? parseInt(getV('piloto_id'), 10) : null,
                    forma_pago_id: getV('forma_pago_id') ? parseInt(getV('forma_pago_id'), 10) : null,
                    coop_descuento_nombre: (() => {
                        const v = getV('coop_descuento_nombre') || '';
                        return v.trim().toLowerCase() === 'no aplica' ? null : (v || null);
                    })()
                },
                costos: {
                    moneda: getV('costo_moneda'),
                    costo_base_por_ha: parseNum(getV('costo_base_por_ha')),
                    base_ha: parseNum(getV('base_ha')),
                    base_total: parseNum(getV('base_total')),
                    productos_total: parseNum(getV('productos_total')),
                    total: parseNum(getV('total')),
                    desglose_json: null
                },
                motivos: state.motivos.map(m => ({
                    patologia_id: m.patologia_id ? Number(m.patologia_id) : null,
                    es_otros: m.es_otros ? 1 : 0,
                    otros_text: m.es_otros ? (m.otros_text || null) : null
                })),
                items: state.items.map(it => ({
                    patologia_id: it.patologia_id ?? null,
                    fuente: it.fuente || 'sve',
                    producto_id: it.producto_id ?? null,
                    nombre_producto: it.nombre_producto || null,
                    costo_hectarea_snapshot: it.costo_hectarea_snapshot ?? null,
                    total_producto_snapshot: it.total_producto_snapshot ?? null,
                    recetas: [{
                        principio_activo: it.receta?.principio_activo || null,
                        dosis: it.receta?.dosis ?? null,
                        unidad: it.receta?.unidad || null,
                        orden_mezcla: it.receta?.orden_mezcla ?? null,
                        notas: it.receta?.notas || null
                    }]
                })),
                rangos: state.rangos.map(r => ({
                    rango: r.rango
                }))
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

        // Drawer
        const drawer = document.getElementById('drawer');
        const drawerPanel = drawer.querySelector('.sv-drawer__panel');
        const drawerOverlay = drawer.querySelector('.sv-drawer__overlay');
        const drawerClose = document.getElementById('drawer-close');
        const drawerCancel = document.getElementById('drawer-cancel');
        let lastFocus = null;

        async function openDrawer({
            id
        }) {
            lastFocus = document.activeElement;
            $('#drawer-id').textContent = `#${id}`;
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
                if (lastFocus && typeof lastFocus.focus === 'function') lastFocus.focus();
                else {
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

        // Filtros en vivo
        const debouncedLoad = debounce(load, 300);
        els.piloto.addEventListener('input', debouncedLoad);
        els.ses_usuario.addEventListener('input', debouncedLoad);
        els.estado_filtro.addEventListener('change', debouncedLoad);
        els.fecha_visita.addEventListener('change', debouncedLoad);

        load(); // arranque
    })();
</script>