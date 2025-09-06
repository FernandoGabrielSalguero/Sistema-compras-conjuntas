<?php
?>

<!-- Íconos de Material Design -->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />


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
                        <option value="ingresada">Ingresada</option>
                        <option value="procesando">Procesando</option>
                        <option value="aprobada_coop">Aprobada coop</option>
                        <option value="cancelada">Cancelada</option>
                        <option value="completada">Completada</option>
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

                        <!-- ======= Identificación ======= -->
                        <div class="form-separator"><span class="material-icons mi">badge</span>Datos generales del servicio</div>

                        <div class="input-group">
                            <label for="f-id">Número de solicitud</label>
                            <div class="input-icon material">
                                <span class="material-icons mi">tag</span>
                                <input type="text" id="f-id" name="id" placeholder="ID interno" readonly />
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="f-productor_id_real">Id real Productor</label>
                            <div class="input-icon material">
                                <span class="material-icons mi">badge</span>
                                <input type="text" id="f-productor_id_real" name="productor_id_real" placeholder="Id real del productor" readonly />
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="f-ses_usuario">Nombre/Usuario del productor</label>
                            <div class="input-icon material">
                                <span class="material-icons mi">person</span>
                                <input type="text" id="f-ses_usuario" name="ses_usuario" placeholder="Nombre del productor" readonly />
                            </div>
                        </div>

                        <div class="input-group" style="grid-column:1/-1;">
                            <label>Motivos por el cúal contrata el servicio</label>
                            <div id="f-motivos" class="pill-list" aria-live="polite"></div>
                        </div>

                        <div class="input-group" style="grid-column:1/-1;">
                            <label for="f-observaciones">Observaciones del productor</label>
                            <div class="input-icon material">
                                <span class="material-icons mi">notes</span>
                                <textarea id="f-observaciones" name="observaciones" rows="3" placeholder="Notas del productor"></textarea>
                            </div>
                        </div>

                        <!-- ======= Seguridad ======= -->
                        <div class="form-separator"><span class="material-icons mi">shield</span>Condiciones de la finca</div>

                        <div class="input-group">
                            <label for="f-linea_tension">Línea de tensión</label>
                            <div class="input-icon material">
                                <span class="material-icons mi">bolt</span>
                                <select id="f-linea_tension" name="linea_tension">
                                    <option>si</option>
                                    <option>no</option>
                                </select>
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="f-zona_restringida">Zona restringida</label>
                            <div class="input-icon material">
                                <span class="material-icons mi">block</span>
                                <select id="f-zona_restringida" name="zona_restringida">
                                    <option>si</option>
                                    <option>no</option>
                                </select>
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="f-corriente_electrica">Corriente eléctrica</label>
                            <div class="input-icon material">
                                <span class="material-icons mi">electrical_services</span>
                                <select id="f-corriente_electrica" name="corriente_electrica">
                                    <option>si</option>
                                    <option>no</option>
                                </select>
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="f-agua_potable">Agua potable</label>
                            <div class="input-icon material">
                                <span class="material-icons mi">water_drop</span>
                                <select id="f-agua_potable" name="agua_potable">
                                    <option>si</option>
                                    <option>no</option>
                                </select>
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="f-libre_obstaculos">Libre de obstáculos</label>
                            <div class="input-icon material">
                                <span class="material-icons mi">landscape</span>
                                <select id="f-libre_obstaculos" name="libre_obstaculos">
                                    <option>si</option>
                                    <option>no</option>
                                </select>
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="f-area_despegue">Área despegue</label>
                            <div class="input-icon material">
                                <span class="material-icons mi">flight_takeoff</span>
                                <select id="f-area_despegue" name="area_despegue">
                                    <option>si</option>
                                    <option>no</option>
                                </select>
                            </div>
                        </div>

                        <!-- ======= Estado y notas ======= -->
                        <div class="form-separator"><span class="material-icons mi">flag</span>Estado y notas</div>

                        <div class="input-group">
                            <label for="f-estado">Estado</label>
                            <div class="input-icon material">
                                <span class="material-icons mi">flag</span>
                                <select id="f-estado" name="estado">
                                    <option value="ingresada">Ingresada</option>
                                    <option value="procesando">Procesando</option>
                                    <option value="aprobada_coop">Aprobada coop</option>
                                    <option value="cancelada">Cancelada</option>
                                    <option value="completada">Completada</option>
                                </select>
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="f-motivo_cancelacion">Motivo cancelación</label>
                            <div class="input-icon material">
                                <span class="material-icons mi">cancel</span>
                                <input type="text" id="f-motivo_cancelacion" name="motivo_cancelacion" placeholder="Motivo de cancelación (opcional)" />
                            </div>
                        </div>

                        <div class="input-group" style="grid-column:1/-1;">
                            <label for="f-obs_piloto">Observaciones para el piloto</label>
                            <div class="input-icon material">
                                <span class="material-icons mi">description</span>
                                <textarea id="f-obs_piloto" name="obs_piloto" rows="3" placeholder="Notas del piloto"></textarea>
                            </div>
                        </div>

                        <!-- ======= Dirección y ubicación ======= -->
                        <div class="form-separator"><span class="material-icons mi">map</span>Dirección y ubicación</div>

                        <div class="input-group">
                            <label for="f-dir_provincia">Provincia</label>
                            <div class="input-icon material">
                                <span class="material-icons mi">public</span>
                                <input type="text" id="f-dir_provincia" name="dir_provincia" placeholder="Provincia" />
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="f-dir_localidad">Localidad</label>
                            <div class="input-icon material">
                                <span class="material-icons mi">location_city</span>
                                <input type="text" id="f-dir_localidad" name="dir_localidad" placeholder="Localidad" />
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="f-dir_calle">Calle</label>
                            <div class="input-icon material">
                                <span class="material-icons mi">route</span>
                                <input type="text" id="f-dir_calle" name="dir_calle" placeholder="Calle" />
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="f-dir_numero">Número</label>
                            <div class="input-icon material">
                                <span class="material-icons mi">pin</span>
                                <input type="text" id="f-dir_numero" name="dir_numero" placeholder="Número" />
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="f-en_finca">En finca</label>
                            <div class="input-icon material">
                                <span class="material-icons mi">agriculture</span>
                                <select id="f-en_finca" name="en_finca">
                                    <option value="si">si</option>
                                    <option value="no">no</option>
                                </select>
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="f-ubicacion_lat">Lat</label>
                            <div class="input-icon material">
                                <span class="material-icons mi">place</span>
                                <input type="number" id="f-ubicacion_lat" name="ubicacion_lat" placeholder="-32.12345678" step="0.00000001" />
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="f-ubicacion_lng">Long</label>
                            <div class="input-icon material">
                                <span class="material-icons mi">place</span>
                                <input type="number" id="f-ubicacion_lng" name="ubicacion_lng" placeholder="-68.12345678" step="0.00000001" />
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="f-superficie_ha">Superficie (ha)</label>
                            <div class="input-icon material">
                                <span class="material-icons mi">square_foot</span>
                                <input type="number" id="f-superficie_ha" name="superficie_ha" placeholder="0.00" step="0.01" readonly />
                            </div>
                        </div>

                        <!-- ======= Agenda ======= -->
                        <div class="form-separator"><span class="material-icons mi">event</span>Agenda</div>

                        <div class="input-group" style="grid-column:1/-1;">
                            <label>Rango preferido por el productor</label>
                            <div id="f-rangos" class="pill-list" aria-live="polite"></div>
                        </div>

                        <div class="input-group">
                            <label for="f-fecha_visita">Fecha visita</label>
                            <div class="input-icon material">
                                <span class="material-icons mi">event</span>
                                <input type="date" id="f-fecha_visita" name="fecha_visita" />
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="f-hora_visita">Hora visita</label>
                            <div class="input-icon material">
                                <span class="material-icons mi">schedule</span>
                                <input type="time" id="f-hora_visita" name="hora_visita" />
                            </div>
                        </div>

                        <!-- ======= Parámetros de vuelo ======= -->
                        <div class="form-separator"><span class="material-icons mi">tune</span>Parámetros de vuelo</div>

                        <div class="input-group">
                            <label for="f-piloto">Piloto</label>
                            <div class="input-icon material">
                                <span class="material-icons mi">flight</span>
                                <select id="f-piloto" name="piloto" aria-describedby="ayuda-piloto"></select>
                            </div>
                            <small id="ayuda-piloto" class="gform-helper">Seleccioná un piloto activo de SVE.</small>
                        </div>

                        <div class="input-group">
                            <label for="f-volumen_ha">Volumen (ha)</label>
                            <div class="input-icon material">
                                <span class="material-icons mi">square_foot</span>
                                <input type="number" id="f-volumen_ha" name="volumen_ha" placeholder="0.00" step="0.01" />
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="f-velocidad_vuelo">Velocidad (m/s)</label>
                            <div class="input-icon material">
                                <span class="material-icons mi">speed</span>
                                <input type="number" id="f-velocidad_vuelo" name="velocidad_vuelo" placeholder="0.00" step="0.01" />
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="f-alto_vuelo">Altura (m)</label>
                            <div class="input-icon material">
                                <span class="material-icons mi">height</span>
                                <input type="number" id="f-alto_vuelo" name="alto_vuelo" placeholder="0.00" step="0.01" />
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="f-tamano_gota">Tamaño gota</label>
                            <div class="input-icon material">
                                <span class="material-icons mi">water_drop</span>
                                <input type="text" id="f-tamano_gota" name="tamano_gota" placeholder="Tamaño de gota" />
                            </div>
                        </div>

                        <div class="form-separator"><span class="material-icons mi">science</span>Productos a utilizar</div>

                        <div class="input-group" style="grid-column:1/-1;">
                            <div class="table-responsive">
                                <table class="prod-table" id="tabla-productos">
                                    <thead>
                                        <tr>
                                            <th style="min-width:280px;">Producto</th>
                                            <th style="min-width:180px;">Principio activo</th>
                                            <th style="min-width:120px;">Dosis</th>
                                            <th style="min-width:110px;">Unidad</th>
                                            <th style="min-width:110px;">Orden mezcla</th>
                                            <th style="min-width:140px;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <button type="button" class="btn btn-aceptar" id="btn-add-prod" style="margin-top:8px;">
                                Añadir producto
                            </button>
                            <div class="gform-helper">Elegí la fuente (SVE/Productor). Si es SVE, seleccioná del stock; si es del productor, escribí el nombre y el principio activo.</div>
                        </div>

                        <!-- ======= Facturación y costos ======= -->
                        <div class="form-separator"><span class="material-icons mi">receipt_long</span>Facturación y costos</div>

                        <div class="input-group">
                            <label for="f-forma_pago_id">Forma de pago</label>
                            <div class="input-icon material">
                                <span class="material-icons mi">payments</span>
                                <select id="f-forma_pago_id" name="forma_pago_id" aria-describedby="ayuda-forma-pago"></select>
                            </div>
                            <small id="ayuda-forma-pago" class="gform-helper">Seleccioná el método de facturación.</small>
                        </div>

                        <div class="input-group" id="grp-aprob-coop" style="display:none;">
                            <label for="f-aprob_cooperativa">Aprobación de cooperativa</label>
                            <div class="input-icon material">
                                <span class="material-icons mi">rule</span>
                                <select id="f-aprob_cooperativa" name="aprob_cooperativa" aria-live="polite">
                                    <option value="Analizando">Analizando</option>
                                    <option value="Aprobado">Aprobado</option>
                                    <option value="Cancelado">Cancelado</option>
                                </select>
                            </div>
                        </div>

                        <div class="input-group" style="grid-column:1/-1;">
                            <div class="cost-grid">
                                <div class="cost-item">
                                    <label for="f-cost-moneda">Moneda</label>
                                    <div class="input-icon material">
                                        <span class="material-icons mi">attach_money</span>
                                        <input type="text" id="f-cost-moneda" readonly />
                                    </div>
                                </div>
                                <div class="cost-item">
                                    <label for="f-cost-base-ha">Costo base por ha</label>
                                    <div class="input-icon material">
                                        <span class="material-icons mi">calculate</span>
                                        <input type="number" id="f-cost-base-ha" step="0.01" readonly />
                                    </div>
                                </div>
                                <div class="cost-item">
                                    <label for="f-superficie_ha">Superficie (ha)</label>
                                    <div class="input-icon material">
                                        <span class="material-icons mi">square_foot</span>
                                        <input type="number" id="f-superficie_ha" name="superficie_ha" placeholder="0.00" step="0.01" readonly />
                                    </div>
                                </div>
                                <div class="cost-item">
                                    <label for="f-cost-base-total">Total base</label>
                                    <div class="input-icon material">
                                        <span class="material-icons mi">functions</span>
                                        <input type="number" id="f-cost-base-total" step="0.01" readonly />
                                    </div>
                                </div>
                                <div class="cost-item">
                                    <label for="f-cost-productos-total">Total productos</label>
                                    <div class="input-icon material">
                                        <span class="material-icons mi">science</span>
                                        <input type="number" id="f-cost-productos-total" step="0.01" readonly />
                                    </div>
                                </div>
                                <div class="cost-item">
                                    <label for="f-cost-total">Total del servicio</label>
                                    <div class="input-icon material">
                                        <span class="material-icons mi">receipt</span>
                                        <input type="number" id="f-cost-total" step="0.01" readonly />
                                    </div>
                                </div>
                            </div>
                            <div class="gform-helper">Los costos se recalculan al cambiar productos o al guardar.</div>
                        </div>


                    </form>
                </div>
            </div>

            <div class="sv-drawer__footer">
                <button class="btn btn-cancelar" id="drawer-cancel" type="button">Cerrar</button>
                <button class="btn btn-aceptar" id="drawer-save" type="submit" form="detalle-form">Guardar cambios</button>
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

    /* iconos y estilos de botones */
    /* Input con ícono Material dentro */
    .input-icon.material {
        position: relative;
        display: flex;
        align-items: center;
    }

    .input-icon.material>.mi,
    .input-icon.material>.ms {
        position: absolute;
        left: 12px;
        line-height: 1;
        pointer-events: none;
        font-size: 22px;
        opacity: .8;
    }

    .input-icon.material>input,
    .input-icon.material>select,
    .input-icon.material>textarea {
        padding-left: 44px !important;
        /* deja espacio para el ícono */
    }

    /* Títulos/separadores de secciones dentro del grid del form */
    .form-separator {
        grid-column: 1 / -1;
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 8px 0 2px;
        font-weight: 600;
        color: #5b21b6;
        /* tu violeta */
    }

    .form-separator .mi,
    .form-separator .ms {
        font-size: 20px;
        opacity: .9;
    }

    /* chips  */
    /* Chips (píldoras) */
    .pill-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px
    }

    .pill-list .pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 999px;
        background: #f3f4f6;
        color: #111827;
        font-size: .9rem;
        line-height: 1;
    }

    .pill-list .pill .mi {
        font-size: 18px;
        opacity: .75
    }

    /* Variantes (por si querés colorear alguna) */
    .pill--accent {
        background: #eef2ff;
        color: #4338ca
    }

    .pill--success {
        background: #dcfce7;
        color: #166534
    }

    .pill--empty {
        background: #f3f4f6;
        color: #6b7280;
        font-style: italic
    }

    /* Lista de productos */
    .product-list {
        list-style: none;
        padding: 0;
        margin: 0
    }

    .product-list li {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 4px;
        border-bottom: 1px solid #e5e7eb;
    }

    .product-list li:last-child {
        border-bottom: none
    }

    .product-list .mi {
        font-size: 18px;
        opacity: .8
    }

    .product-list .title {
        font-weight: 600
    }

    .product-list .sub {
        font-size: .85rem;
        color: #6b7280
    }

    .product-list .badge {
        margin-left: auto;
        font-size: .8rem;
        padding: 2px 8px;
        border-radius: 999px;
        background: #eef2ff;
        color: #4338ca;
        white-space: nowrap
    }

    /* Estilos para la tabla responsiva */
    .table-responsive {
        overflow: auto;
    }

    .prod-table {
        width: 100%;
        border-collapse: collapse;
    }

    .prod-table th,
    .prod-table td {
        border-bottom: 1px solid #e5e7eb;
        padding: 8px;
        vertical-align: middle;
    }

    .prod-table input[type="text"],
    .prod-table input[type="number"],
    .prod-table select {
        width: 100%;
        height: 36px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 0 8px;
    }

    .prod-row .campo-sve,
    .prod-row .campo-yo {
        display: none;
    }

    .prod-row.fuente-sve .campo-sve {
        display: block;
    }

    .prod-row.fuente-yo .campo-yo {
        display: block;
    }

    .prod-actions {
        display: flex;
        gap: 6px;
    }

    /* ---- Costos ---- */
    .cost-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(160px, 1fr));
        gap: 12px
    }

    @media (max-width: 1100px) {
        .cost-grid {
            grid-template-columns: repeat(2, minmax(160px, 1fr));
        }
    }

    .cost-item input[readonly] {
        background: #f9fafb;
    }
</style>

<script>
    // Endpoint global del módulo Drone (evita collisions con otras páginas)
    const DRONE_API = '../partials/drones/controller/drone_list_controller.php';

    (function() {

        // Usa la constante global para evitar scope issues
        const API = DRONE_API;

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

        // --- Pilotos dinámicos ---
        async function fetchPilotos() {
            try {
                const res = await fetch(`${DRONE_API}?action=list_pilotos`, {
                    cache: 'no-store'
                });
                const json = await res.json();
                if (!json.ok) throw new Error(json.error || 'No se pudo cargar pilotos');
                return json.data.items || [];
            } catch (e) {
                console.error(e);
                return [];
            }
        }

        async function populatePilotosSelect(selectedNombre = '') {
            const sel = document.getElementById('f-piloto');
            if (!sel) return;
            sel.innerHTML = '<option value="">— Seleccionar —</option>';
            const items = await fetchPilotos();
            items.forEach(p => {
                // p.nombre (mostrado/guardado), p.id disponible por si quisieras guardar el id en el futuro
                const opt = document.createElement('option');
                opt.value = p.nombre;
                opt.textContent = p.nombre;
                if (selectedNombre && selectedNombre === p.nombre) opt.selected = true;
                sel.appendChild(opt);
            });
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
                            console.log('[DEBUG] Respuesta completa get_solicitud:', json);
                            if (window.DEBUG) console.log('Detalle solicitud:', json.data);
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

    // ===== Utils globales (usados fuera del IIFE) =====
    function esc(s) {
        return (s ?? '').toString()
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
    // Wrapper de spinner para uso global (submit del form)
    function showSpinner(flag) {
        if (window.showSpinnerGlobal && window.hideSpinnerGlobal) {
            flag ? window.showSpinnerGlobal() : window.hideSpinnerGlobal();
        }
    }


    // --- Drawer refs y helpers ---
    const drawer = document.getElementById('drawer');
    const drawerPanel = drawer.querySelector('.sv-drawer__panel');
    const drawerOverlay = drawer.querySelector('.sv-drawer__overlay');
    const drawerClose = document.getElementById('drawer-close');
    const drawerCancel = document.getElementById('drawer-cancel');
    const drawerId = document.getElementById('drawer-id');
    const formDetalle = document.getElementById('detalle-form');
    let currentDetalle = null;

    let lastFocus = null;

    function openDrawer(data) {
        currentDetalle = data;
        lastFocus = document.activeElement;

        drawer.setAttribute('aria-hidden', 'false'); // <<< important
        drawer.classList.remove('hidden', 'closing');
        drawer.classList.add('opening');

        // Aseguramos que el panel sea focuseable y tomamos el foco
        drawerPanel.setAttribute('tabindex', '-1');
        setTimeout(() => drawerPanel.focus(), 0);

        fillForm(data);

        const onEnd = (e) => {
            if (e.target !== drawerPanel) return;
            drawer.classList.remove('opening');
            drawer.removeEventListener('animationend', onEnd, true);
        };
        drawer.addEventListener('animationend', onEnd, true);
    }

    function closeDrawer() {
        // 1) Mover el foco fuera del área que vamos a ocultar
        const active = document.activeElement;
        if (active && drawer.contains(active)) {
            if (lastFocus && typeof lastFocus.focus === 'function') {
                lastFocus.focus(); // vuelve al disparador del drawer
            } else {
                // fallback seguro al body
                document.body.setAttribute('tabindex', '-1');
                document.body.focus();
                document.body.removeAttribute('tabindex');
            }
        }

        // 2) Ahora sí ocultamos con aria-hidden
        drawer.classList.add('closing');
        drawer.setAttribute('aria-hidden', 'true');

        const onEnd = (e) => {
            if (e.target !== drawerPanel) return;
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

    const PRETTY_RANGO = {
        'enero_q1': 'Enero Q1',
        'enero_q2': 'Enero Q2',
        'febrero_q1': 'Febrero Q1',
        'febrero_q2': 'Febrero Q2',
        'octubre_q1': 'Octubre Q1',
        'octubre_q2': 'Octubre Q2',
        'noviembre_q1': 'Noviembre Q1',
        'noviembre_q2': 'Noviembre Q2',
        'diciembre_q1': 'Diciembre Q1',
        'diciembre_q2': 'Diciembre Q2'
    };
    const PRETTY_MOTIVO = {
        mildiu: 'Mildiu',
        oidio: 'Oídio',
        lobesia: 'Lobesia',
        podredumbre: 'Podredumbre',
        fertilizacion: 'Fertilización',
        otros: 'Otros'
    };
    // helper para capitalizar
    function cap(s) {
        s = (s || '').toString();
        return s ? s[0].toUpperCase() + s.slice(1) : s;
    }


    // --- Rellenar formulario con el detalle ---
    async function fillForm({
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
            'f-superficie_ha': 'superficie_ha'
        };
        Object.entries(map).forEach(([id, key]) => {
            const el = document.getElementById(id);
            if (!el) return;
            el.value = solicitud[key] ?? '';
        });
        await populatePilotosSelect(solicitud.piloto || '');

        // Motivos (chips)
        const contMotivos = document.getElementById('f-motivos');
        const prettyMotivos = (motivos || []).map(m => {
            const base = PRETTY_MOTIVO[m.motivo] || cap(m.motivo);
            const extra = m.otros_text ? `: ${esc(m.otros_text)}` : '';
            return `<span class="pill"><span class="material-icons mi">label</span>${esc(base+extra)}</span>`;
        });
        contMotivos.innerHTML = prettyMotivos.length ?
            prettyMotivos.join('') :
            `<span class="pill pill--empty">Sin motivos</span>`;

        // Productos (tabla mini)
        let __stockCache = null;
        async function loadStock(q = '', ids = []) {
            // cache sólo sirve para q vacío y sin ids forzados
            if (__stockCache && q === '' && (!ids || !ids.length)) return __stockCache;
            const u = new URLSearchParams();
            u.set('action', 'list_stock');
            if (q) u.set('q', q);
            if (ids && ids.length) u.set('ids', ids.join(','));
            const res = await fetch(`${DRONE_API}?${u.toString()}`);
            const json = await res.json();
            if (!json.ok) throw new Error(json.error || 'No se pudo cargar stock');
            // si pedimos por ids, mergeamos para que estén siempre presentes
            const items = json.data.items || [];
            if (!__stockCache) __stockCache = [];
            const byId = new Map(__stockCache.map(i => [String(i.id), i]));
            items.forEach(i => byId.set(String(i.id), i));
            __stockCache = Array.from(byId.values());
            return __stockCache;
        }

        function stockPAById(id) {
            const it = (__stockCache || []).find(x => String(x.id) === String(id));
            return it ? it.principio_activo || '' : '';
        }

        // --- Render de la tabla de productos
        const tablaProd = document.getElementById('tabla-productos');
        const tbodyProd = tablaProd.querySelector('tbody');
        const btnAdd = document.getElementById('btn-add-prod');
        // Reemplaza cualquier handler previo y evita multi-clicks
        let addingRow = false;
        btnAdd.onclick = async () => {
            if (addingRow) return;
            addingRow = true;
            try {
                await ensureStockLoaded();
                await addRow({});
            } finally {
                addingRow = false;
            }
        };

        async function ensureStockLoaded() {
            if (!__stockCache) await loadStock();
        }

        function unidadOptions(sel) {
            const opts = ['ml/ha', 'g/ha', 'L/ha', 'kg/ha'];
            return opts.map(u => `<option value="${u}" ${sel===u?'selected':''}>${u}</option>`).join('');
        }

        function productoCellHTML(p) {
            const fuente = (p.fuente === 'yo' ? 'yo' : 'sve');
            const prodId = p.producto_id ?? '';
            const marca = p.marca ?? '';
            const stockOpts = (__stockCache || []).map(s =>
                `<option value="${s.id}" ${String(s.id)===String(prodId)?'selected':''}>${esc(s.nombre)}</option>`
            ).join('');
            return `
      <div style="display:flex; gap:8px;">
        <select class="fuente" style="max-width:160px;">
          <option value="sve" ${fuente==='sve'?'selected':''}>SVE</option>
          <option value="yo"  ${fuente==='yo'?'selected':''}>Productor</option>
        </select>
        <div class="campo-sve" style="flex:1;">
          <select class="producto_id">
            <option value="">Seleccioná del stock…</option>
            ${stockOpts}
          </select>
        </div>
        <div class="campo-yo" style="flex:1;">
          <input type="text" class="marca" placeholder="Nombre comercial" value="${esc(marca)}" />
        </div>
      </div>
    `;
        }


        function rowHTML(p) {
            const fuente = (p.fuente === 'yo' ? 'yo' : 'sve');
            const pa = p.principio_activo ?? (p.producto_id ? stockPAById(p.producto_id) : '');
            return `
      <tr class="prod-row fuente-${fuente}" data-id="${p.id || ''}">
        <td>${productoCellHTML(p)}</td>
        <td><input type="text" class="principio_activo" ${fuente==='sve'?'readonly':''} value="${esc(pa)}"></td>
        <td><input type="number" step="0.01" class="dosis" value="${p.dosis ?? ''}"></td>
        <td>
          <select class="unidad">${unidadOptions(p.unidad)}</select>
        </td>
        <td><input type="number" step="1" min="1" class="orden_mezcla" value="${p.orden_mezcla ?? ''}"></td>
<td class="prod-actions">
  <button type="button" class="btn btn-cancelar btn-eliminar" title="Eliminar">
    <span class="material-icons mi" aria-hidden="true">delete</span>
  </button>
</td>
      </tr>
    `;
        }

        function bindRowEvents(tr) {
            const fuenteSel = tr.querySelector('.fuente');
            const prodSel = tr.querySelector('.producto_id');
            const paIn = tr.querySelector('.principio_activo');

            function syncFuente() {
                const f = fuenteSel.value === 'yo' ? 'yo' : 'sve';
                tr.classList.toggle('fuente-sve', f === 'sve');
                tr.classList.toggle('fuente-yo', f === 'yo');
                paIn.readOnly = (f === 'sve');
                if (f === 'sve' && prodSel?.value) paIn.value = stockPAById(prodSel.value);
                recalcTotalsClient();
            }
            fuenteSel?.addEventListener('change', syncFuente);
            syncFuente();

            prodSel?.addEventListener('change', () => {
                if (prodSel.value) paIn.value = stockPAById(prodSel.value);
                recalcTotalsClient();
            });

            ['input', 'change'].forEach(ev => {
                tr.querySelectorAll('.dosis,.unidad,.orden_mezcla,.marca,.principio_activo').forEach(el => {
                    el.addEventListener(ev, recalcTotalsClient);
                });
            });

            tr.querySelector('.btn-eliminar').addEventListener('click', () => {
                deleteRow(tr);
                recalcTotalsClient();
            });
        }

        // ---- Cálculo de costos en cliente (feedback) ----
        function recalcTotalsClient() {
            try {
                const sup = Number(document.getElementById('f-superficie_ha').value || 0);
                const baseHa = Number(document.getElementById('f-cost-base-ha').value || 0);
                const baseTotal = +(sup * baseHa).toFixed(2);

                // suma de costos por producto del stock (fuente SVE)
                let prodTotal = 0;
                document.querySelectorAll('#tabla-productos tbody tr').forEach(tr => {
                    const isSVE = (tr.querySelector('.fuente')?.value || 'sve') === 'sve';
                    if (!isSVE) return;
                    const id = tr.querySelector('.producto_id')?.value;
                    if (!id) return;
                    const it = (__stockCache || []).find(x => String(x.id) === String(id));
                    const ch = Number(it?.costo_hectarea || 0);
                    prodTotal += ch * sup;
                });

                document.getElementById('f-cost-base-total').value = baseTotal.toFixed(2);
                document.getElementById('f-cost-productos-total').value = (+prodTotal).toFixed(2);
                document.getElementById('f-cost-total').value = (baseTotal + prodTotal).toFixed(2);
            } catch (_) {}
        }

        async function addRow(p) {
            await ensureStockLoaded();
            const tmp = document.createElement('tbody');
            tmp.innerHTML = rowHTML(p);
            const tr = tmp.firstElementChild;
            tbodyProd.appendChild(tr);
            bindRowEvents(tr);
        }

        async function renderProductosTable(productos) {
            // cargamos stock incluyendo ids actuales para que el combo se pre-seleccione correctamente
            const ids = (productos || []).map(p => p.producto_id).filter(Boolean);
            await loadStock('', ids);
            tbodyProd.innerHTML = '';
            for (const p of (productos || [])) {
                // addRow es async
                // eslint-disable-next-line no-await-in-loop
                await addRow(p);
            }
            recalcTotalsClient();
        }

        async function saveRow(tr) {
            const id = tr.dataset.id ? parseInt(tr.dataset.id, 10) : null;
            const f = tr.querySelector('.fuente').value === 'yo' ? 'yo' : 'sve';
            const pid = tr.querySelector('.producto_id')?.value || null;
            const marca = tr.querySelector('.marca')?.value?.trim() || null;
            const pa = tr.querySelector('.principio_activo')?.value?.trim() || null;
            const dosis = tr.querySelector('.dosis')?.value || null;
            const unidad = tr.querySelector('.unidad')?.value || null;
            const orden = tr.querySelector('.orden_mezcla')?.value || null;

            const payload = {
                id,
                solicitud_id: currentDetalle.solicitud.id,
                fuente: f,
                producto_id: f === 'sve' ? (pid ? Number(pid) : null) : null,
                marca: f === 'yo' ? marca : null,
                principio_activo: f === 'yo' ? pa : null,
                dosis: dosis !== '' ? Number(dosis) : null,
                unidad: unidad || null,
                orden_mezcla: orden !== '' ? Number(orden) : null
            };

            try {
                showSpinner(true);
                const res = await fetch(`${DRONE_API}?action=upsert_producto`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        data: payload
                    })
                });
                const json = await res.json();
                if (!json.ok) throw new Error(json.error || 'No se pudo guardar el producto');
                if (!id && json.id) tr.dataset.id = String(json.id);
                window.showToast?.('success', 'Producto guardado');
            } catch (e) {
                console.error(e);
                window.showToast?.('error', 'No se pudo guardar');
            } finally {
                showSpinner(false);
            }
        }

        async function deleteRow(tr) {
            const id = tr.dataset.id ? Number(tr.dataset.id) : 0;
            if (!id) {
                tr.remove();
                return;
            }
            if (!confirm('¿Eliminar este producto?')) return;
            try {
                showSpinner(true);
                const res = await fetch(`${DRONE_API}?action=delete_producto`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id,
                        solicitud_id: currentDetalle.solicitud.id
                    })
                });
                const json = await res.json();
                if (!json.ok) throw new Error(json.error || 'No se pudo eliminar');
                tr.remove();
                window.showToast?.('success', 'Producto eliminado');
            } catch (e) {
                console.error(e);
                window.showToast?.('error', 'No se pudo eliminar');
            } finally {
                showSpinner(false);
            }
        }

        // Rangos (chips)
        const contRangos = document.getElementById('f-rangos');
        const prettyRangos = (rangos || []).map(r => PRETTY_RANGO[r.rango] || r.rango);
        contRangos.innerHTML = prettyRangos.length ?
            prettyRangos.map(txt => `<span class="pill"><span class="material-icons mi">calendar_month</span>${esc(txt)}</span>`).join('') :
            `<span class="pill pill--empty">Sin rangos</span>`;

        // Render de productos (luego de haber creado y enlazado la tabla)
        await renderProductosTable(productos);
        // costos / forma de pago / aprobación
        try {
            const moneda = solicitud.costo_moneda || 'Pesos';
            const costoBaseHa = Number(solicitud.costo_base_ha || 0);
            document.getElementById('f-cost-moneda').value = moneda;
            document.getElementById('f-cost-base-ha').value = costoBaseHa.toFixed(2);
        } catch (_) {}
        const selFP = document.getElementById('f-forma_pago_id');
        selFP.innerHTML = (solicitud.formas_pago || []).map(fp => `<option value="${fp.id}">${esc(fp.nombre)}</option>`).join('');
        if (solicitud.forma_pago_id) selFP.value = String(solicitud.forma_pago_id);
        // toggle aprobación por forma de pago (id 6)
        const grpAprob = document.getElementById('grp-aprob-coop');

        function toggleAprob() {
            grpAprob.style.display = (String(selFP.value) === '6') ? 'block' : 'none';
        }
        selFP.addEventListener('change', toggleAprob);
        toggleAprob();
        const aprob = document.getElementById('f-aprob_cooperativa');
        if (solicitud.aprob_cooperativa) aprob.value = solicitud.aprob_cooperativa;

        // superficie y costos guardados si hubiera
        const sup = Number(solicitud.superficie_ha || 0);
        document.getElementById('f-superficie_ha').value = sup ? sup.toFixed(2) : '';
        if (solicitud.costos && solicitud.costos.total) {
            document.getElementById('f-cost-base-total').value = Number(solicitud.costos.base_total || 0).toFixed(2);
            document.getElementById('f-cost-productos-total').value = Number(solicitud.costos.productos_total || 0).toFixed(2);
            document.getElementById('f-cost-total').value = Number(solicitud.costos.total || 0).toFixed(2);
        }
        recalcTotalsClient();


    }

    function collectProductosPayload() {
        const rows = Array.from(document.querySelectorAll('#tabla-productos tbody tr'));
        const data = [];
        const errors = [];

        rows.forEach((tr, idx) => {
            const id = tr.dataset.id ? parseInt(tr.dataset.id, 10) : null;
            const f = tr.querySelector('.fuente')?.value === 'yo' ? 'yo' : 'sve';
            const pid = tr.querySelector('.producto_id')?.value || null;
            const marca = tr.querySelector('.marca')?.value?.trim() || null;
            const pa = tr.querySelector('.principio_activo')?.value?.trim() || null;
            const dosis = tr.querySelector('.dosis')?.value || null;
            const unidad = tr.querySelector('.unidad')?.value || null;
            const orden = tr.querySelector('.orden_mezcla')?.value || null;

            const d = {
                id,
                solicitud_id: currentDetalle?.solicitud?.id,
                fuente: f,
                producto_id: f === 'sve' ? (pid ? Number(pid) : null) : null,
                marca: f === 'yo' ? marca : null,
                principio_activo: f === 'yo' ? pa : null,
                dosis: (dosis !== '' && dosis != null) ? Number(dosis) : null,
                unidad: unidad || null,
                orden_mezcla: (orden !== '' && orden != null) ? Number(orden) : null
            };

            // Reglas de mínimos
            if (!id) {
                // fila nueva: si no cumple, se omite
                if (f === 'sve' && !d.producto_id) return;
                if (f === 'yo' && !(d.marca && d.principio_activo)) return;
                data.push(d);
            } else {
                // fila existente: si no cumple, marcamos error (para no enviar datos inválidos al backend)
                if (f === 'sve' && !d.producto_id) {
                    errors.push(`Fila ${idx + 1}: en fuente SVE debes seleccionar un producto del stock.`);
                    return;
                }
                if (f === 'yo' && !(d.marca && d.principio_activo)) {
                    errors.push(`Fila ${idx + 1}: en fuente Productor completa Marca y Principio activo.`);
                    return;
                }
                data.push(d);
            }
        });

        return {
            data,
            errors
        };
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

    // Guardar cambios
    formDetalle.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!currentDetalle) return;

        const payloadSolicitud = formToJSON(formDetalle);
        payloadSolicitud.id = Number(payloadSolicitud.id || currentDetalle.solicitud.id);

        const productosPayload = collectProductosPayload();
        if (productosPayload.errors.length) {
            const msg = productosPayload.errors.join('\n');
            window.showToast?.('error', msg);
            alert('Corregí estos errores antes de guardar:\n\n' + msg);
            return;
        }

        showSpinner(true);
        try {
            const res = await fetch(`${DRONE_API}?action=save_all`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    solicitud: payloadSolicitud,
                    productos: productosPayload.data
                })
            });
            const json = await res.json();
            console.log('[SVE] Respuesta save_all ->', {
                status: res.status,
                json
            });
            if (!json.ok) throw new Error(json.error || 'No se pudieron guardar los cambios');

            window.showToast?.('success', 'Cambios guardados');
            closeDrawer();
            window.refreshSolicitudes?.();
        } catch (err) {
            console.error(err);
            window.showToast?.('error', err?.message || 'No se pudieron guardar los cambios');
            alert(err?.message || 'No se pudieron guardar los cambios.');
        } finally {
            showSpinner(false);
        }
    });
</script>