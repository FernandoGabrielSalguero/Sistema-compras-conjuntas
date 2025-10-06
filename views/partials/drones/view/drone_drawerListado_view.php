<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$__SV_ROLE__ = strtolower((string)($_SESSION['rol'] ?? ''));
?>
<script>
    // Dump de sesión en consola (solo debug)
    console.log("SESSION PHP:", <?php echo json_encode($_SESSION, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?>);
    console.log("SESSION ROLE:", "<?php echo htmlspecialchars($__SV_ROLE__, ENT_QUOTES, 'UTF-8'); ?>");
</script>

?>

<head>
    <!-- Íconos de Material Design -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- Framework Success desde CDN -->
    <link rel="stylesheet" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css">
    <script src="https://www.fernandosalguero.com/cdn/assets/javascript/framework.js" defer></script>
</head>
<style>
    /* Drawer */
    .sv-drawer.hidden {
        display: none;
    }

    .sv-drawer {
        position: fixed;
        inset: 0;
        z-index: 60;
    }

    .sv-drawer__overlay {
        position: absolute;
        inset: 0;
        background: #0006;
        opacity: 0;
        transition: opacity .2s ease;
    }

    .sv-drawer[aria-hidden="false"] .sv-drawer__overlay {
        opacity: 1;
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
        transform: translateX(100%);
        transition: transform .25s cubic-bezier(.22, .61, .36, 1);
    }

    .sv-drawer[aria-hidden="false"] .sv-drawer__panel {
        transform: translateX(0);
    }

    .sv-drawer__header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
        border-bottom: 1px solid #eee;
    }

    .sv-drawer__body {
        flex: 1;
        overflow: auto;
        -webkit-overflow-scrolling: touch;
        padding: 16px 20px;
        background: #f6f7fb;
    }

    .sv-drawer__footer {
        padding: 12px 20px;
        border-top: 1px solid #eee;
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        align-items: center;
    }

    .sv-drawer__close {
        font-size: 24px;
        line-height: 1;
        border: none;
        background: transparent;
        cursor: pointer;
    }

    .badge.warning {
        background: #FEF3C7;
        color: #92400E;
        padding: 2px 8px;
        border-radius: 999px;
    }

    .badge.info {
        background: #DBEAFE;
        color: #1E40AF;
        padding: 2px 8px;
        border-radius: 999px;
    }

    .badge.primary {
        background: #E0E7FF;
        color: #3730A3;
        padding: 2px 8px;
        border-radius: 999px;
    }

    .badge.success {
        background: #DCFCE7;
        color: #166534;
        padding: 2px 8px;
        border-radius: 999px;
    }

    .badge.danger {
        background: #FEE2E2;
        color: #B91C1C;
        padding: 2px 8px;
        border-radius: 999px;
    }

    .helper-text {
        font-size: .85rem;
        color: #6b7280;
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
        background: #fff;
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

    #grp_motivo_cancelacion {
        display: none;
    }

    .motivo-cancel {
        display: inline-block;
        margin-left: 10px;
        font-size: .82rem;
        color: #9b1c1c;
        background: #fee2e2;
        padding: 2px 8px;
        border-radius: 999px;
    }

    .mini-title {
        font-size: .9rem;
        color: #5b21b6;
        font-weight: 600;
        margin: .25rem 0;
    }

    pre.json-dump {
        background: #0b1021;
        color: #cfe3ff;
        border-radius: 12px;
        padding: 12px;
        overflow: auto;
    }

    button.icon-btn {
        border: 0;
        background: transparent;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    button.icon-btn .material-icons {
        font-size: 20px;
    }

    /* --- Control por rol (sin JS) --- */
    /* Ocultar tarjetas operativas para cooperativa/ingeniero */
    .role-cooperativa .card--ops-hide,
    .role-ingeniero .card--ops-hide {
        display: none !important;
    }

    /* Solo lectura la tarjeta de Costos */
    .role-cooperativa #card-costos input,
    .role-cooperativa #card-costos select,
    .role-cooperativa #card-costos textarea,
    .role-cooperativa #card-costos button,
    .role-ingeniero #card-costos input,
    .role-ingeniero #card-costos select,
    .role-ingeniero #card-costos textarea,
    .role-ingeniero #card-costos button {
        pointer-events: none;
    }

    /* Señal visual */
    .role-cooperativa #card-costos input,
    .role-cooperativa #card-costos select,
    .role-cooperativa #card-costos textarea,
    .role-ingeniero #card-costos input,
    .role-ingeniero #card-costos select,
    .role-ingeniero #card-costos textarea {
        background: #f9fafb;
        color: #374151;
        opacity: 0.95;
    }

    /* Mantener legible el resumen */
    .role-cooperativa #card-costos .costos-resumen,
    .role-ingeniero #card-costos .costos-resumen {
        opacity: 1;
    }
</style>

<div id="drawerListado" class="sv-drawer hidden role-<?php echo htmlspecialchars($__SV_ROLE__, ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true" data-role="<?php echo htmlspecialchars($__SV_ROLE__, ENT_QUOTES, 'UTF-8'); ?>">
    <div class="sv-drawer__overlay" data-close></div>
    <aside class="sv-drawer__panel" role="dialog" aria-modal="true" aria-labelledby="drawerListado-title">
        <div class="sv-drawer__header">
            <h3 id="drawerListado-title">Solicitud <span id="drawerListado-id"></span></h3>
            <button class="sv-drawer__close" id="drawerListado-close" aria-label="Cerrar">×</button>
        </div>

        <div class="sv-drawer__body">
            <form id="form-solicitud" class="form" autocomplete="off" novalidate>

                <!-- Datos iniciales -->
                <div class="card">
                    <h2 style="color:#5b21b6;">Datos iniciales</h2>
                    <div class="form-grid grid-2">
                        <div class="input-group">
                            <label for="productor_id_real">Productor ID real</label>
                            <div class="input-icon input-icon-id">
                                <input type="text" id="productor_id_real" name="productor_id_real" placeholder="P0000" />
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="ses_usuario_edit">Nombre productor</label>
                            <div class="input-icon input-icon-name">
                                <input type="text" id="ses_usuario_edit" name="ses_usuario_edit" placeholder="Nombre y apellido" />
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
                                    <option value="visita_realizada">Visita Realizada</option>
                                    <option value="completada">Completada</option>
                                </select>
                            </div>
                            <small id="estadoHelp" class="helper-text">Seleccioná el estado actual.</small>
                        </div>

                        <div class="input-group" id="grp_motivo_cancelacion">
                            <label for="motivo_cancelacion">Motivo cancelación</label>
                            <div class="input-icon input-icon-edit">
                                <input type="text" id="motivo_cancelacion" name="motivo_cancelacion" placeholder="Indicar motivo de cancelación" />
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

                <!-- Dirección -->
                <div class="card">
                    <h2 style="color:#5b21b6;">Dirección</h2>
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
                    <h5 style="color:#5b21b6;">Ubicación provista por el celular del productor</h5>
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
                    <h2 style="color:#5b21b6;">Datos estructurales</h2>
                    <div class="form-grid grid-4">
                        <?php
                        // flags (si/no)
                        $flags = ['representante', 'linea_tension', 'zona_restringida', 'corriente_electrica', 'agua_potable', 'libre_obstaculos', 'area_despegue', 'en_finca'];
                        foreach ($flags as $f): ?>
                            <div class="input-group">
                                <label for="<?= $f ?>"><?= ucwords(str_replace('_', ' ', $f)) ?></label>
                                <div class="input-icon input-icon-toggle">
                                    <select id="<?= $f ?>" name="<?= $f ?>">
                                        <option value="no">no</option>
                                        <option value="si">si</option>
                                    </select>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Forma de pago -->
                <div class="card">
                    <h2 style="color:#5b21b6;">Forma de pago</h2>
                    <div class="form-grid grid-2">
                        <div class="input-group">
                            <label for="forma_pago_id">Forma de pago</label>
                            <div class="input-icon input-icon-id">
                                <select id="forma_pago_id" name="forma_pago_id">
                                    <option value="">Seleccionar forma de pago</option>
                                </select>
                            </div>
                        </div>
                        <div class="input-group" id="grp_coop_descuento" style="display:none;">
                            <label for="coop_descuento_nombre">Coop. descuento</label>
                            <div class="input-icon input-icon-id">
                                <select id="coop_descuento_nombre" name="coop_descuento_nombre" aria-describedby="coopHelp">
                                    <option value="">Seleccionar cooperativa</option>
                                </select>
                            </div>
                        </div>
                        <div class="input-group" id="coop_etiqueta_wrap" style="display:none;">
                            <label>Representado por</label>
                            <div class="input-icon input-icon-info">
                                <input type="text" id="coop_etiqueta" readonly aria-readonly="true">
                            </div>
                        </div>
                    </div>
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

                    <div class="form-grid" style="margin-top:12px;">
                        <div class="input-group">
                            <label for="producto_new">Producto</label>
                            <div class="input-icon input-icon-edit">
                                <select id="producto_new"></select>
                            </div>
                        </div>

                        <div class="input-group" id="grp_producto_otro_text" style="display:none;">
                            <label for="producto_new_text">Nombre del producto (proveído por el productor)</label>
                            <div class="input-icon input-icon-edit">
                                <input type="text" id="producto_new_text" placeholder="Ingresá el nombre del producto" />
                            </div>
                            <small class="helper-text">Se guardará con fuente: productor.</small>
                        </div>
                    </div>

                    <div class="form-buttons">
                        <button type="button" class="btn btn-info" id="btn_add_producto">Agregar producto</button>
                    </div>
                </div>


                <!-- Receta -->
                <div id="card-receta" class="card card--ops-hide">
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

                <!-- Parámetros de vuelo -->
                <div id="card-parametros" class="card card--ops-hide">
                    <h2 style="color:#5b21b6;">Parámetros de vuelo</h2>

                    <div class="form-grid grid-4">
                        <div class="input-group">
                            <label for="volumen_ha">Vol / hectárea</label>
                            <div class="input-icon input-icon-hashtag">
                                <input type="number" step="0.01" min="0" id="volumen_ha" name="volumen_ha" />
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="velocidad_vuelo">Velocidad de vuelo</label>
                            <div class="input-icon input-icon-hashtag">
                                <input type="number" step="0.01" min="0" id="velocidad_vuelo" name="velocidad_vuelo" />
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="alto_vuelo">Alto vuelo</label>
                            <div class="input-icon input-icon-hashtag">
                                <input type="number" step="0.01" min="0" id="alto_vuelo" name="alto_vuelo" />
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="ancho_pasada">Ancho pasada</label>
                            <div class="input-icon input-icon-hashtag">
                                <input type="number" step="0.01" min="0" id="ancho_pasada" name="ancho_pasada" />
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="tamano_gota">Tamaño gota</label>
                            <div class="input-icon input-icon-edit">
                                <input type="text" id="tamano_gota" name="tamano_gota" placeholder="Fina/Media/Gruesa u otro" />
                            </div>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="param_observaciones">Observaciones</label>
                        <div class="input-icon input-icon-edit">
                            <input type="text" id="param_observaciones" name="param_observaciones" placeholder="Notas de parámetros de vuelo" />
                        </div>
                    </div>
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

                <!-- Programar visita -->
                <div id="card-visita" class="card card--ops-hide">
                    <h2 style="color:#5b21b6;">Programar visita</h2>

                    <div class="form-grid grid-4">
                        <div class="input-group">
                            <label for="fecha_visita_edit">Fecha visita</label>
                            <div class="input-icon input-icon-date">
                                <input type="date" id="fecha_visita_edit" name="fecha_visita_edit" />
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

                <!-- Costos -->
                <div id="card-costos" class="card">
                    <h2 style="color:#5b21b6;">Costos</h2>

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
                    <br>
                    <h4 style="color:#5b21b6;">Resumen de costo del servicio</h4>
                    <div id="costos-resumen" class="costos-resumen card" style="margin-top:8px;padding:12px;"></div>
                </div>

            </form>
        </div>

        <div class="sv-drawer__footer">
            <!-- Botón-ícono: ver JSON en consola -->
            <button type="button" id="btn-json-console" class="icon-btn" title="Imprimir JSON en consola" aria-label="Imprimir JSON en consola">
                <span class="material-icons">bug_report</span>
                Ver JSON
            </button>
            <!-- Acción principal -->
            <button type="button" id="btn-guardar" class="btn btn-aceptar">Actualizar pedido</button>
            <button type="button" id="drawerListado-cancel" class="btn btn-cancelar">Cerrar</button>
        </div>
    </aside>

    <!-- Alert -->
    <div class="alert-container" id="alertContainer"></div>
</div>

<script>
    (function() {
        if (window.DroneDrawerListado) return;

        const API = '../partials/drones/controller/drone_drawerListado_controller.php';

        const drawer = document.getElementById('drawerListado');
        const panel = drawer.querySelector('.sv-drawer__panel');
        const overlay = drawer.querySelector('.sv-drawer__overlay');
        const btnClose = document.getElementById('drawerListado-close');
        const btnCancel = document.getElementById('drawerListado-cancel');
        const btnJSON = document.getElementById('btn-json-console');
        const btnGuardar = document.getElementById('btn-guardar');
        const lblId = document.getElementById('drawerListado-id');

        // Campos/sections
        const $ = (s, ctx = document) => ctx.querySelector(s);
        const $$ = (s, ctx = document) => Array.from(ctx.querySelectorAll(s));
        const $chipsPat = $('#patologias-chips');
        const $chipsRan = $('#rangos-chips');
        const $listProd = $('#productos-list');

        // Catálogos y estado
        const catalog = {
            pilotos: [],
            formasPago: [],
            patologias: [],
            productos: [],
            cooperativas: []
        };
        const state = {
            motivos: [],
            rangos: [],
            items: []
        };
        let __DATA__ = null; // último JSON del pedido (para ver en consola)
        let __ID__ = null;
        let lastFocus = null;

        // Helpers
        function esc(s) {
            return (s ?? '').toString().replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        /** Para inputs type=number -> SIEMPRE punto y 2 decimales (o entero) */
        function fmtNumInput(v, dec = 2) {
            if (v === null || v === undefined || v === '') return '';
            const n = Number(v);
            if (!Number.isFinite(n)) return '';
            // si es entero, sin decimales; si no, fijo a "dec"
            return Number.isInteger(n) ? String(n) : n.toFixed(dec);
        }

        /** Para mostrar texto al usuario (resúmenes) -> coma decimal (es-AR) */
        function fmtNumText(v, dec = 2) {
            if (v === null || v === undefined || v === '') return '';
            const n = Number(v);
            if (!Number.isFinite(n)) return '';
            const nf = new Intl.NumberFormat('es-AR', {
                minimumFractionDigits: dec,
                maximumFractionDigits: dec
            });
            return nf.format(n);
        }

        /** Acepta coma o punto y devuelve Number (o null) */
        function parseNum(v) {
            if (v === '' || v === null || v === undefined) return null;
            const n = Number(String(v).replace(',', '.'));
            return Number.isFinite(n) ? n : null;
        }

        /** Redondeo seguro a 2 decimales (evita 0.1+0.2=0.30000000000004) */
        function round2(n) {
            if (n === null || n === undefined) return null;
            return Math.round(Number(n) * 100) / 100;
        }


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

        function badgeClass(e) {
            switch ((e || '').toLowerCase()) {
                case 'ingresada':
                    return 'warning';
                case 'procesando':
                    return 'info';
                case 'aprobada_coop':
                    return 'primary';
                case 'visita_realizada':
                    return 'primary';
                case 'completada':
                    return 'success';
                case 'cancelada':
                    return 'danger';
                default:
                    return 'secondary';
            }
        }

        // Drawer API
        async function open({
            id
        }) {
            lastFocus = document.activeElement;
            __ID__ = Number(id); // <— coerción aquí
            lblId.textContent = `#${__ID__}`;
            drawer.classList.remove('hidden');
            drawer.setAttribute('aria-hidden', 'false');
            panel.setAttribute('tabindex', '-1');
            setTimeout(() => panel.focus(), 0);

            await loadCatalogs();
            await loadDetalle(__ID__);
        }


        function close() {
            const active = document.activeElement;
            if (active && drawer.contains(active) && lastFocus && typeof lastFocus.focus === 'function') {
                lastFocus.focus();
            }
            drawer.setAttribute('aria-hidden', 'true');
            setTimeout(() => drawer.classList.add('hidden'), 200);
        }

        overlay.addEventListener('click', close);
        btnClose.addEventListener('click', close);
        btnCancel.addEventListener('click', close);

        // Ver JSON en consola
        btnJSON.addEventListener('click', () => {
            try {
                console.groupCollapsed('[Pedido] JSON drawer');
                console.log(JSON.stringify(__DATA__, null, 2));
            } finally {
                console.groupEnd();
            }
        });

        // Catálogos
        async function loadCatalogs() {
            const qs = (a) => fetch(`${API}?action=${a}`, {
                cache: 'no-store'
            }).then(r => r.json());
            const [pi, fp, pa, pr, co] = await Promise.all([
                qs('list_pilotos'), qs('list_formas_pago'), qs('list_patologias'), qs('list_productos'), qs('list_cooperativas')
            ]);
            if (!pi.ok || !fp.ok || !pa.ok || !pr.ok || !co.ok) {
                console.error('Error cargando catálogos', {
                    pi,
                    fp,
                    pa,
                    pr,
                    co
                });
                throw new Error('No se pudieron cargar los catálogos');
            }
            catalog.pilotos = pi.data || [];
            catalog.formasPago = fp.data || [];
            catalog.patologias = pa.data || [];
            catalog.productos = pr.data || [];
            catalog.cooperativas = co.data || [];
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

        // Detalle
        async function loadDetalle(id) {
            const url = `${API}?action=get_detalle&id=${encodeURIComponent(id)}`;
            const res = await fetch(url, {
                cache: 'no-store'
            });
            const json = await res.json();
            if (!json.ok) throw new Error(json.error || 'Error');
            __DATA__ = json.data;
            fillForm(json.data);

            // Nuevo: forzar un cálculo apenas se carga el detalle
            try {
                recalcCostos();
            } catch (e) {
                console.debug('recalcCostos init skip', e);
            }
        }

        // Interacciones específicas
        function toggleMotivo() {
            const sel = document.querySelector('#form-solicitud #estado');
            const grp = document.querySelector('#grp_motivo_cancelacion');
            const help = document.querySelector('#estadoHelp');
            const motivo = document.querySelector('#motivo_cancelacion');
            if (!sel || !grp || !help || !motivo) return;
            const isCancelada = String(sel.value).toLowerCase() === 'cancelada';
            grp.style.display = isCancelada ? 'block' : 'none';
            motivo.required = isCancelada;
            help.textContent = isCancelada ? 'Seleccionaste “Cancelada”. Indicá el motivo en el campo de abajo.' : 'Seleccioná el estado actual.';
            if (isCancelada) setTimeout(() => motivo.focus(), 0);
        }

        function toggleCoopField() {
            const sel = document.getElementById('forma_pago_id');
            const grp = document.getElementById('grp_coop_descuento');
            const coSel = document.getElementById('coop_descuento_nombre');
            if (!sel || !grp) return;
            const fp = catalog.formasPago.find(f => String(f.id) === String(sel.value));
            const isCoop = !!(fp && String(fp.nombre || '').toLowerCase().includes('cooperativa'));
            grp.style.display = isCoop ? '' : 'none';
            if (!isCoop && coSel) coSel.value = '';
        }

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
                const pat = it.patologia_id ? catalog.patologias.find(p => String(p.id) === String(it.patologia_id)) : null;
                const costo = it.costo_hectarea_snapshot ?? pInfo?.costo_hectarea ?? null;
                const wrapper = document.createElement('div');
                wrapper.className = 'producto-item';
                wrapper.innerHTML = `
        <div>
          <strong>${esc(nombre)}</strong>
          <div class="meta">Fuente: ${esc(it.fuente || 'sve')}${pat ? ` · Patología: ${esc(pat.nombre)}` : ''}</div>
          <div class="meta">Costo/ha: $${fmtNumText(costo)}</div>
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

        function ensureRecetaSlots() {
            state.items.forEach(it => {
                if (!it.receta) {
                    const first = Array.isArray(it.recetas) && it.recetas.length ? it.recetas[0] : null;
                    it.receta = {
                        principio_activo: first?.principio_activo ?? it.principio_activo ?? null,
                        dosis: first?.dosis ?? null,
                        unidad: (first?.unidad ?? it.unidad ?? 'ml/ha'),
                        orden_mezcla: first?.orden_mezcla ?? null,
                        notas: first?.notas ?? ''
                    };
                } else {
                    // si existe receta pero unidad vacía -> default
                    if (!it.receta.unidad || String(it.receta.unidad).trim() === '') {
                        it.receta.unidad = 'ml/ha';
                    }
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
<td><div class="input-icon input-icon-hashtag"><input type="number" step="0.01" value="${fmtNumInput(r.dosis)}"></div></td>
<td><div class="input-icon input-icon-edit"><input type="text" value="${esc((r.unidad && String(r.unidad).trim() !== '' ? r.unidad : 'ml/ha'))}"></div></td>
<td><div class="input-icon input-icon-hashtag"><input type="number" step="1" value="${fmtNumInput(r.orden_mezcla, 0)}"></div></td>
<td><div class="input-icon input-icon-edit"><input type="text" value="${esc(r.notas||'')}"></div></td>`;

                const [pa, dosis, uni, ord, notas] = tr.querySelectorAll('input');
                pa.addEventListener('input', e => it.receta.principio_activo = e.target.value);
                dosis.addEventListener('input', e => it.receta.dosis = parseNum(e.target.value));
                uni.addEventListener('input', e => it.receta.unidad = e.target.value);
                ord.addEventListener('input', e => it.receta.orden_mezcla = parseNum(e.target.value));
                notas.addEventListener('input', e => it.receta.notas = e.target.value);
                tb.appendChild(tr);
            });
        }

        function recalcCostos() {
            // base_ha: si está vacío, copiar superficie_ha
            let base_ha = parseNum($('#base_ha')?.value);
            if (base_ha === null) {
                const sup = parseNum($('#superficie_ha')?.value);
                if (sup !== null) {
                    base_ha = sup;
                    if ($('#base_ha')) $('#base_ha').value = fmtNumInput(base_ha); // punto y 2 dec
                }
            }

            const costo_base = parseNum($('#costo_base_por_ha')?.value);

            // cálculos con redondeo
            const base_total = round2((base_ha || 0) * (costo_base || 0));

            let productos_total = 0;
            state.items.forEach(it => {
                const pInfo = catalog.productos.find(p => String(p.id) === String(it.producto_id));
                const ch = Number((it.costo_hectarea_snapshot ?? pInfo?.costo_hectarea ?? 0));
                const tps = round2(ch * (base_ha || 0));
                productos_total = round2(productos_total + tps);
                it.total_producto_snapshot = tps;
            });

            const total = round2((base_total || 0) + (productos_total || 0));

            // Escribir SIEMPRE con formato input (punto) en inputs numéricos:
            if ($('#base_total')) $('#base_total').value = fmtNumInput(base_total);
            if ($('#productos_total')) $('#productos_total').value = fmtNumInput(productos_total);
            if ($('#total')) $('#total').value = fmtNumInput(total);

            // Y mostrar con formato de texto (coma) en resúmenes
            const resumen = $('#costos-resumen');
            if (resumen) {
                resumen.innerHTML =
                    `<p>Base: ${fmtNumText(base_ha||0)} ha × $${fmtNumText(costo_base||0)} = $${fmtNumText(base_total)}</p>
             <p>Productos: $${fmtNumText(productos_total)}</p>
             <p><strong>Total: $${fmtNumText(total)}</strong></p>`;
            }
        }


        // Llenar formulario
        function fillForm(d) {
            // cache para ver JSON
            __DATA__ = d;

            // id
            document.getElementById('drawerListado-id').textContent = d?.solicitud?.id ? `#${d.solicitud.id}` : '';

            const s = d.solicitud || {};
            setV('productor_id_real', s.productor_id_real);
            setV('ses_usuario_edit', s.ses_usuario ?? d?.productor?.usuario ?? '');
            setV('superficie_ha', fmtNumInput(s.superficie_ha));
            setV('fecha_visita_edit', s.fecha_visita);
            setV('hora_visita_desde', s.hora_visita_desde);
            setV('hora_visita_hasta', s.hora_visita_hasta);
            setV('estado', s.estado);
            toggleMotivo();
            setV('motivo_cancelacion', s.motivo_cancelacion);
            setV('observaciones', s.observaciones);

            ['representante', 'linea_tension', 'zona_restringida', 'corriente_electrica', 'agua_potable', 'libre_obstaculos', 'area_despegue', 'en_finca']
            .forEach(k => setV(k, s[k]));

            ['dir_provincia', 'dir_localidad', 'dir_calle', 'dir_numero', 'ubicacion_lat', 'ubicacion_lng', 'ubicacion_acc']
            .forEach(k => setV(k, s[k]));

            // selects catálogos
            fillSelect($('#piloto_id'), catalog.pilotos, {
                selected: s.piloto_id,
                placeholder: 'Seleccionar piloto'
            });
            fillSelect($('#forma_pago_id'), catalog.formasPago, {
                selected: s.forma_pago_id,
                placeholder: 'Seleccionar forma de pago'
            });
            fillSelect($('#coop_descuento_nombre'), catalog.cooperativas, {
                valueKey: 'id_real',
                labelKey: 'usuario',
                selected: s.coop_descuento_nombre,
                placeholder: 'Seleccionar cooperativa'
            });
            toggleCoopField();

            const selPatNew = $('#patologia_new');
            fillSelect(selPatNew, catalog.patologias, {
                placeholder: 'Seleccionar patología'
            });
            selPatNew.append(new Option('Otra', '__otra__'));

            const selProdNew = $('#producto_new');
            fillSelect(selProdNew, catalog.productos, {
                placeholder: 'Seleccionar producto'
            });
            // Agregar opción "Otro" y toggle del input
            selProdNew.append(new Option('Otro', '__otro__'));
            $('#grp_producto_otro_text').style.display = 'none';
            selProdNew.addEventListener('change', (e) => {
                $('#grp_producto_otro_text').style.display = e.target.value === '__otro__' ? '' : 'none';
            });

            // costos (si vienen guardados, llegan completos; si no, vienen calculados del backend)
            const c = d.costos || {};
            setV('costo_moneda', c.moneda);
            setV('costo_base_por_ha', fmtNumInput(c.costo_base_por_ha));
            setV('base_ha', fmtNumInput(c.base_ha));
            setV('base_total', fmtNumInput(c.base_total));
            setV('productos_total', fmtNumInput(c.productos_total));
            setV('total', fmtNumInput(c.total));

            // --- Parámetros de vuelo (FIX: mostrar valores guardados) ---
            const p = d.parametros || {};
            setV('volumen_ha', fmtNumInput(p.volumen_ha));
            setV('velocidad_vuelo', fmtNumInput(p.velocidad_vuelo));
            setV('alto_vuelo', fmtNumInput(p.alto_vuelo));
            setV('ancho_pasada', fmtNumInput(p.ancho_pasada));
            setV('tamano_gota', p.tamano_gota ?? '');
            setV('param_observaciones', p.observaciones ?? '');

            // Asegurar que Base ha copie la Superficie si no vino seteada
            const sup = parseNum(s.superficie_ha);
            if ((getV('base_ha') === null || getV('base_ha') === '') && sup !== null) {
                setV('base_ha', fmtNumInput(sup));
            }


            // Recalcular y refrescar resumen
            recalcCostos();

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

            // items
            state.items = (d.items || []).map(it => ({
                patologia_id: it.patologia_id,
                fuente: it.fuente || 'sve',
                producto_id: it.producto_id,
                nombre_producto: it.producto_nombre || it.nombre_producto || null,
                costo_hectarea_snapshot: it.costo_hectarea_snapshot ?? it.producto_costo_hectarea ?? null,
                receta: (() => {
                    const r0 = (it.recetas && it.recetas[0]) ? it.recetas[0] : null;
                    const unidad = (r0?.unidad ?? it.unidad ?? '').trim() || 'ml/ha';
                    return {
                        principio_activo: r0?.principio_activo ?? it.principio_activo ?? null,
                        dosis: r0?.dosis ?? null,
                        unidad,
                        orden_mezcla: r0?.orden_mezcla ?? null,
                        notas: r0?.notas ?? ''
                    };
                })()
            }));

            renderProductos();
            renderRecetaCombinada();
            recalcCostos();

            // listeners dependientes
            document.querySelector('#form-solicitud #estado')?.addEventListener('change', toggleMotivo);
            document.getElementById('forma_pago_id')?.addEventListener('change', toggleCoopField);

            $('#btn-abrir-ubicacion')?.addEventListener('click', () => {
                const lat = getV('ubicacion_lat'),
                    lng = getV('ubicacion_lng');
                if (!lat || !lng) return showAlert('error', 'Cargá latitud y longitud primero');
                window.open(`https://www.google.com/maps?q=${lat},${lng}`, '_blank');
            });

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
            $('#btn_add_rango')?.addEventListener('click', () => {
                const r = $('#rango_new').value;
                if (!r) return;
                state.rangos.push({
                    rango: r
                });
                $('#rango_new').value = '';
                renderRangos();
            });
            $('#btn_add_producto')?.addEventListener('click', () => {
                const val = $('#producto_new').value;
                const patologiaIdAuto = state.motivos[0]?.patologia_id ?? null;

                if (!val) {
                    return showAlert('error', 'Elegí un producto');
                }

                if (val === '__otro__') {
                    const nombreLibre = ($('#producto_new_text').value || '').trim();
                    if (!nombreLibre) {
                        return showAlert('error', 'Escribí el nombre del producto');
                    }
                    // Producto ingresado por el productor
                    state.items.push({
                        patologia_id: patologiaIdAuto,
                        fuente: 'productor',
                        producto_id: null,
                        nombre_producto: nombreLibre,
                        costo_hectarea_snapshot: null, // sin costo (no impacta en cálculos)
                        receta: {
                            principio_activo: null,
                            dosis: null,
                            unidad: 'ml/ha',
                            orden_mezcla: null,
                            notas: ''
                        }
                    });
                    // reset y ocultar input
                    $('#producto_new_text').value = '';
                    $('#grp_producto_otro_text').style.display = 'none';
                    $('#producto_new').value = '';
                } else {
                    const prod = catalog.productos.find(p => String(p.id) === String(val));
                    state.items.push({
                        patologia_id: patologiaIdAuto,
                        fuente: 'sve',
                        producto_id: Number(val),
                        nombre_producto: prod?.nombre || null,
                        costo_hectarea_snapshot: prod?.costo_hectarea ?? null,
                        receta: {
                            principio_activo: null,
                            dosis: null,
                            unidad: 'ml/ha',
                            orden_mezcla: null,
                            notas: ''
                        }
                    });
                    $('#producto_new').value = '';
                }

                renderProductos();
                renderRecetaCombinada();
                recalcCostos();
            });


            // listeners dependientes existentes...
            $('#base_ha')?.addEventListener('input', recalcCostos);
            $('#costo_base_por_ha')?.addEventListener('input', recalcCostos);

            // Nuevo: recalcular cuando cambien items/rangos/patologías relevantes
            const recalcSafe = () => {
                try {
                    recalcCostos();
                } catch {}
            };

            const _addProd = $('#btn_add_producto');
            if (_addProd) _addProd.addEventListener('click', () => setTimeout(recalcSafe, 0));

            // ya lo llamamos tras quitar producto dentro de renderProductos()
            // si querés robustecer, podés colocar:
            document.addEventListener('change', (e) => {
                const t = e.target;
                if (!t) return;
                // si se tocan inputs de receta, base_ha, costo_base_por_ha
                if (['base_ha', 'costo_base_por_ha'].includes(t.id)) recalcSafe();
            });

            // Normalizar inputs numéricos al perder foco: coma -> punto y 2 dec
            ['superficie_ha', 'costo_base_por_ha', 'base_ha', 'base_total', 'productos_total', 'total',
                'volumen_ha', 'velocidad_vuelo', 'alto_vuelo', 'ancho_pasada', 'ubicacion_lat', 'ubicacion_lng', 'ubicacion_acc'
            ].forEach(id => {
                const el = document.getElementById(id);
                if (!el) return;
                el.addEventListener('blur', () => {
                    const n = parseNum(el.value);
                    el.value = (n === null) ? '' : fmtNumInput(n);
                    // recalcular si afecta costos
                    if (['superficie_ha', 'costo_base_por_ha', 'base_ha'].includes(id)) {
                        try {
                            recalcCostos();
                        } catch {}
                    }
                });
            });



        }

        // Guardar
        btnGuardar.addEventListener('click', async () => {
            const payload = {
                id: Number(__ID__),
                solicitud: {
                    productor_id_real: getV('productor_id_real'),
                    ses_usuario: getV('ses_usuario_edit'),
                    superficie_ha: parseNum(getV('superficie_ha')),
                    fecha_visita: getV('fecha_visita_edit'),
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
                    coop_descuento_nombre: getV('coop_descuento_nombre')
                },
                costos: (function() {
                    const obj = {
                        moneda: getV('costo_moneda'),
                        costo_base_por_ha: parseNum(getV('costo_base_por_ha')),
                        base_ha: parseNum(getV('base_ha')),
                        base_total: parseNum(getV('base_total')),
                        productos_total: parseNum(getV('productos_total')),
                        total: parseNum(getV('total')),
                        desglose_json: null
                    };

                    // Enviar 'costos' SOLO si al menos un campo numérico viene cargado
                    const hasAnyNumeric = [obj.costo_base_por_ha, obj.base_ha, obj.base_total, obj.productos_total, obj.total]
                        .some(v => v !== null);

                    // Si el usuario solo cambió la moneda o nada, NO tocamos costos en backend
                    if (!hasAnyNumeric) return undefined;

                    return obj;
                })(),
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
                })),
                parametros: (function() {
                    const obj = {
                        volumen_ha: parseNum(getV('volumen_ha')),
                        velocidad_vuelo: parseNum(getV('velocidad_vuelo')),
                        alto_vuelo: parseNum(getV('alto_vuelo')),
                        ancho_pasada: parseNum(getV('ancho_pasada')),
                        tamano_gota: getV('tamano_gota'),
                        observaciones: getV('param_observaciones')
                    };
                    const hasAny = [obj.volumen_ha, obj.velocidad_vuelo, obj.alto_vuelo, obj.ancho_pasada].some(v => v !== null) ||
                        (obj.tamano_gota ?? '').toString().trim() !== '' ||
                        (obj.observaciones ?? '').toString().trim() !== '';
                    return hasAny ? obj : undefined; // si no hay nada, no enviar -> no borra en backend
                })()
            };

            if (!payload.id) {
                showAlert('error', 'ID de solicitud no válido');
                return;
            }

            try {
                // Usa SIEMPRE la misma base (API) que los GET y muestra el 'detail' si viene:
                const postUrl = `${API}?action=update_solicitud`;

                const res = await fetch(postUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload),
                    cache: 'no-store'
                });

                let json;
                try {
                    json = await res.json();
                } catch {
                    json = {
                        ok: false,
                        error: `HTTP ${res.status} ${res.statusText}`,
                        detail: 'Respuesta no JSON'
                    };
                }

                if (!res.ok || !json.ok) {
                    console.error('Update error', {
                        status: res.status,
                        resp: json
                    });
                    const msg = json.detail || json.error || `HTTP ${res.status}`;
                    throw new Error(msg);
                }

                showAlert('success', '¡Servicio actualizado con éxito!');
                await loadDetalle(payload.id);

            } catch (err) {
                showAlert('error', `No se pudo guardar: ${err.message}`);
            }

        });

        // Exponer API global para el listado
        window.DroneDrawerListado = {
            open,
            close
        };
    })();
</script>