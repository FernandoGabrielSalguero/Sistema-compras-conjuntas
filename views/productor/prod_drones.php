<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión y seguridad
require_once '../../middleware/authMiddleware.php';
checkAccess('productor');

// Datos del usuario en sesión
$nombre = $_SESSION['nombre'] ?? 'Sin nombre';
$cierre_info = $_SESSION['cierre_info'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SVE - Productor</title>

    <!-- Íconos de Material Design -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />

    <!-- Tu framework (CSS/JS) -->
    <link rel="stylesheet" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css">
    <script src="https://www.fernandosalguero.com/cdn/assets/javascript/framework.js" defer></script>

    <style>
        /* Ocupa todo el ancho: no hay sidebar en esta página */
        .main {
            margin-left: 0;
        }

        /* Header-card más alto y con botón a la derecha */
        .header-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 2rem 1.5rem;
            /* un poco más alto que el default */
        }

        /* Pie de cada tarjeta: botón alineado a la derecha */
        .action-footer {
            margin-top: .75rem;
            display: flex;
            justify-content: flex-end;
        }
    </style>
</head>

<body>
    <div class="layout">
        <!-- SIN sidebar -->

        <div class="main">
            <header class="navbar">
                <h4>Formulario para solicitar el servicio de pulverización con dron.</h4>
            </header>

            <section class="content">
                <!-- Header-->
                <div class="card header-card">
                    <div>
                        <h4><?php echo htmlspecialchars($nombre); ?></h4>
                        <p>¿Queres ir al inicio?</p>
                    </div>
                    <a class="btn btn-info" href="prod_dashboard.php">Apreta acá</a>
                </div>

                <!-- Bienvenida -->
                <div class="card">
                    <p>Señor productor a continuación se le realizaran preguntas de gran importancia para la prestación del servicio. Por favor leer y contestar cada una con detenimiento.</p>
                    <br>
                </div>

                <!-- Formulario para solicitar el drone -->
                <form id="form-dron" class="gform-grid cols-4" novalidate>

                    <!-- representante -->
                    <div class="gform-question" role="group" aria-labelledby="q_representante_label" id="q_representante">
                        <div id="q_representante_label" class="gform-legend">
                            ¿A LA HORA DE TOMAR EL SERVICIO PODREMOS CONTAR CON UN REPRESENTATE DE LA PROPIEDAD EN LA FINCA? <span class="gform-required">*</span>
                        </div>
                        <div class="gform-helper">
                            El represéntate de la propiedad deberá, recibir al piloto indicarle los cuarteles a pulverizar, darle asistencia si la requiere y firmar el registro fitosanitario.
                        </div>
                        <div class="gform-options">
                            <label class="gform-option">
                                <input type="radio" id="representante_si" name="representante" value="si">
                                <span>SI</span>
                            </label>
                            <label class="gform-option">
                                <input type="radio" id="representante_no" name="representante" value="no">
                                <span>NO</span>
                            </label>
                        </div>
                        <div class="gform-error">Esta pregunta es obligatoria.</div>
                    </div>

                    <!-- danger_electric -->
                    <div class="gform-question" role="group" aria-labelledby="q_linea_tension_label" id="q_linea_tension">
                        <div id="q_linea_tension_label" class="gform-legend">
                            ¿EL/ LOS CUARTELES A PULVERIZAR CUENTAN CON ALGUNA LINEA DE MEDIA O ALTA TENSION A MENOS DE 30 METROS? <span class="gform-required">*</span>
                        </div>
                        <div class="gform-options">
                            <label class="gform-option">
                                <input type="radio" id="linea_tension_si" name="linea_tension" value="si">
                                <span>SI</span>
                            </label>
                            <label class="gform-option">
                                <input type="radio" id="linea_tension_no" name="linea_tension" value="no">
                                <span>NO</span>
                            </label>
                        </div>
                        <div class="gform-error">Esta pregunta es obligatoria.</div>
                    </div>

                    <!-- danger_airport -->
                    <div class="gform-question" role="group" aria-labelledby="q_zona_restringida_label" id="q_zona_restringida">
                        <div id="q_zona_restringida_label" class="gform-legend">
                            ¿EL/LOS CUARTELES A PULVERIZAR SE ENCUENTRA A MENOS DE 3 KM DE UN AEROPUERTO O ZONA DE VUELO RESTRINGIDA? <span class="gform-required">*</span>
                        </div>
                        <div class="gform-options">
                            <label class="gform-option">
                                <input type="radio" id="zona_restringida_si" name="zona_restringida" value="si">
                                <span>SI</span>
                            </label>
                            <label class="gform-option">
                                <input type="radio" id="zona_restringida_no" name="zona_restringida" value="no">
                                <span>NO</span>
                            </label>
                        </div>
                        <div class="gform-error">Esta pregunta es obligatoria.</div>
                    </div>

                    <!-- assist_electric -->
                    <div class="gform-question" role="group" aria-labelledby="q_corriente_electrica_label" id="q_corriente_electrica">
                        <div id="q_corriente_electrica_label" class="gform-legend">
                            ¿CUENTA CON DISPONIBILIDAD DE CORRIENTE ELÉCTRICA?<span class="gform-required">*</span>
                        </div>
                        <div class="gform-helper">
                            Esto se requiere para la carga de baterías a medida que se realiza la pulverización. Se necesita toma corriente de 35 amperes para poder recargar las baterías.
                        </div>
                        <div class="gform-options">
                            <label class="gform-option">
                                <input type="radio" id="corriente_electrica_si" name="corriente_electrica" value="si">
                                <span>SI</span>
                            </label>
                            <label class="gform-option">
                                <input type="radio" id="corriente_electrica_no" name="corriente_electrica" value="no">
                                <span>NO</span>
                            </label>
                        </div>
                        <div class="gform-error">Esta pregunta es obligatoria.</div>
                    </div>

                    <!-- assist_water -->
                    <div class="gform-question" role="group" aria-labelledby="q_agua_potable_label" id="q_agua_potable">
                        <div id="q_agua_potable_label" class="gform-legend">
                            ¿EN LA PROPIEDAD HAY DISPONIBILIDAD DE AGUA POTABLE? <span class="gform-required">*</span>
                        </div>
                        <div class="gform-helper">
                            Se desea agua de red en condiciones apropiadas para poder realizar la preparación de los caldos de pulverización y la limpieza del dron una vez concluida la aplicación.
                        </div>
                        <div class="gform-options">
                            <label class="gform-option">
                                <input type="radio" id="agua_potable_si" name="agua_potable" value="si">
                                <span>SI</span>
                            </label>
                            <label class="gform-option">
                                <input type="radio" id="agua_potable_no" name="agua_potable" value="no">
                                <span>NO</span>
                            </label>
                        </div>
                        <div class="gform-error">Esta pregunta es obligatoria.</div>
                    </div>

                    <!-- danger_obstacul -->
                    <div class="gform-question" role="group" aria-labelledby="q_obstaculos_label" id="q_obstaculos">
                        <div id="q_obstaculos_label" class="gform-legend">
                            ¿ EL/LOS CUARTELES A PULVERIZAR ESTAN LIBRES DE OBSTÁCULOS? <span class="gform-required">*</span>
                        </div>
                        <div class="gform-helper">
                            Por obstáculos se entiende:
                            - Árboles que estén dentro del cuartel.
                            - Árboles de gran porte a menos de 4 metros del cuartel.
                            - Cables, alambres o postes que superen la altura del viñedo o parral.
                        </div>
                        <div class="gform-options">
                            <label class="gform-option">
                                <input type="radio" id="libre_obstaculos_si" name="libre_obstaculos" value="si">
                                <span>SI</span>
                            </label>
                            <label class="gform-option">
                                <input type="radio" id="libre_obstaculos_no" name="libre_obstaculos" value="no">
                                <span>NO</span>
                            </label>
                        </div>
                        <div class="gform-error">Esta pregunta es obligatoria.</div>
                    </div>

                    <!-- assist_airport (área de despegue) -->
                    <div class="gform-question" role="group" aria-labelledby="q_area_despegue_label" id="q_area_despegue">
                        <div id="q_area_despegue_label" class="gform-legend">
                            ¿EL/ LOS CUARTELES A PULVERIZAR CUENTAN CON UN ÁREA DE DESPEGUE APROPIADA? <span class="gform-required">*</span>
                        </div>
                        <div class="gform-helper">
                            Por área apropiada se refiere a un callejón despejado y libre de obstáculos en un área de 4 m x 4 m.
                        </div>
                        <div class="gform-options">
                            <label class="gform-option">
                                <input type="radio" id="area_despegue_si" name="area_despegue" value="si">
                                <span>SI</span>
                            </label>
                            <label class="gform-option">
                                <input type="radio" id="area_despegue_no" name="area_despegue" value="no">
                                <span>NO</span>
                            </label>
                        </div>
                        <div class="gform-error">Esta pregunta es obligatoria.</div>
                    </div>

                    <!-- hectareas -->
                    <div class="gform-question" data-required="true" id="q_superficie">
                        <label class="gform-label" for="superficie_ha">SUPERFICIE (en hectáreas) PARA LAS QUE DESEA CONTRATAR EL SERVICIO<span class="gform-required">*</span></label>
                        <div class="gform-helper">Debe colocar solamente el número de hectáreas a pulverizar</div>
                        <input class="gform-input" id="superficie_ha" name="superficie_ha" type="text" placeholder="Tu respuesta" />
                        <div class="gform-error">Esta pregunta es obligatoria.</div>
                    </div>

                    <!-- tratamiento / motivo -->
                    <div class="gform-question" role="group" aria-labelledby="q_motivo_label" id="q_motivo">
                        <div id="q_motivo_label" class="gform-legend">INDICAR EL MOTIVO POR EL QUE DESEA CONTRATAR EL SERVICIO<span class="gform-required">*</span></div>
                        <div class="gform-options">
                            <label class="gform-option">
                                <input type="checkbox" id="motivo_mildiu" name="motivo[]" value="mildiu">
                                <span>Curación para Peronospora o Mildiu</span>
                            </label>
                            <label class="gform-option">
                                <input type="checkbox" id="motivo_oidio" name="motivo[]" value="oidio">
                                <span>Curación para Oidio o Quintal</span>
                            </label>
                            <label class="gform-option">
                                <input type="checkbox" id="motivo_lobesia" name="motivo[]" value="lobesia">
                                <span>Curación para Lobesia</span>
                            </label>
                            <label class="gform-option">
                                <input type="checkbox" id="motivo_podredumbre" name="motivo[]" value="podredumbre">
                                <span>Curación para Podredumbre</span>
                            </label>
                            <label class="gform-option">
                                <input type="checkbox" id="motivo_fertilizacion" name="motivo[]" value="fertilizacion">
                                <span>Fertilización Foliar</span>
                            </label>
                            <label class="gform-option gform-option-otros">
                                <input type="checkbox" id="motivo_otros_chk" name="motivo[]" value="otros" aria-controls="motivo_otros">
                                <span>Otros:</span>
                                <input type="text" id="motivo_otros" name="motivo_otros" class="gform-input gform-input-inline oculto" placeholder="Especificar" disabled>
                            </label>
                        </div>
                        <div class="gform-error">Seleccioná al menos una opción.</div>
                    </div>

                    <!-- rango_fecha -->
                    <div class="gform-question" role="group" aria-labelledby="q_rango_label" id="q_rango">
                        <div id="q_rango_label" class="gform-legend">INDICAR EN QUE MOMENTO DESEA CONTRATAR EL SERVICIO<span class="gform-required">*</span></div>
                        <div class="gform-options">
                            <label class="gform-option"><input type="checkbox" id="rango_ene_1" name="rango_fecha[]" value="enero_q1"><span>Primera quincena de Enero</span></label>
                            <label class="gform-option"><input type="checkbox" id="rango_ene_2" name="rango_fecha[]" value="enero_q2"><span>Segunda quincena de Enero</span></label>
                            <label class="gform-option"><input type="checkbox" id="rango_feb_1" name="rango_fecha[]" value="febrero_q1"><span>Primera quincena de Febrero</span></label>
                            <label class="gform-option"><input type="checkbox" id="rango_feb_2" name="rango_fecha[]" value="febrero_q2"><span>Segunda quincena de Febrero</span></label>
                            <label class="gform-option"><input type="checkbox" id="rango_oct_1" name="rango_fecha[]" value="octubre_q1"><span>Primera quincena de Octubre</span></label>
                            <label class="gform-option"><input type="checkbox" id="rango_oct_2" name="rango_fecha[]" value="octubre_q2"><span>Segunda quincena de Octubre</span></label>
                            <label class="gform-option"><input type="checkbox" id="rango_nov_1" name="rango_fecha[]" value="noviembre_q1"><span>Primera quincena de Noviembre</span></label>
                            <label class="gform-option"><input type="checkbox" id="rango_nov_2" name="rango_fecha[]" value="noviembre_q2"><span>Segunda quincena de Noviembre</span></label>
                            <label class="gform-option"><input type="checkbox" id="rango_dic_1" name="rango_fecha[]" value="diciembre_q1"><span>Primera quincena de Diciembre</span></label>
                            <label class="gform-option"><input type="checkbox" id="rango_dic_2" name="rango_fecha[]" value="diciembre_q2"><span>Segunda quincena de Diciembre</span></label>
                        </div>
                        <div class="gform-error">Seleccioná al menos una opción.</div>
                    </div>

                    <!-- assist_product -->
                    <div class="gform-question" role="group" aria-labelledby="q_productos_label" id="q_productos">
                        <div id="q_productos_label" class="gform-legend">
                            En el caso de necesitar productos fitosanitarios para realizar la pulverización indicar los que sean necesarios. <span class="gform-required">*</span>
                        </div>

                        <div class="gform-options gopts-with-complement">
                            <!-- Lobesia -->
                            <div class="gform-optbox" id="opt_lobesia">
                                <label class="gform-option">
                                    <input type="checkbox" id="prod_lobesia" name="productos[]" value="lobesia" data-complement="#cmp-lobesia">
                                    <span>Productos para Lobesia/Polilla de la Vid</span>
                                </label>
                                <div id="cmp-lobesia" class="gform-complement" hidden>
                                    <div class="gform-miniopts">
                                        <span>¿Tenés el producto?</span>
                                        <label><input type="radio" id="src_lobesia_sve" name="src-lobesia" value="sve" checked> No</label>
                                        <label><input type="radio" id="src_lobesia_yo" name="src-lobesia" value="yo"> Sí</label>
                                    </div>
                                    <div class="gform-brand" id="brand_lobesia" hidden>
                                        <input type="text" class="gform-input gform-input-inline" id="marca_lobesia" name="marca-lobesia" placeholder="Marca del producto">
                                        <div class="gform-helper">Indicá marca y, si aplica, concentración/composición.</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Peronospora -->
                            <div class="gform-optbox" id="opt_peronospora">
                                <label class="gform-option">
                                    <input type="checkbox" id="prod_peronospora" name="productos[]" value="peronospora" data-complement="#cmp-peronospora">
                                    <span>Productos para Peronospora de la vid</span>
                                </label>
                                <div id="cmp-peronospora" class="gform-complement" hidden>
                                    <div class="gform-miniopts">
                                        <span>¿Tenés el producto?</span>
                                        <label><input type="radio" id="src_peronospora_sve" name="src-peronospora" value="sve" checked> No</label>
                                        <label><input type="radio" id="src_peronospora_yo" name="src-peronospora" value="yo"> Sí</label>
                                    </div>
                                    <div class="gform-brand" id="brand_peronospora" hidden>
                                        <input type="text" class="gform-input gform-input-inline" id="marca_peronospora" name="marca-peronospora" placeholder="Marca del producto">
                                        <div class="gform-helper">Indicá marca y, si aplica, concentración/composición.</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Oidio -->
                            <div class="gform-optbox" id="opt_oidio">
                                <label class="gform-option">
                                    <input type="checkbox" id="prod_oidio" name="productos[]" value="oidio" data-complement="#cmp-oidio">
                                    <span>Productos para Oidio/Quintal de la vid</span>
                                </label>
                                <div id="cmp-oidio" class="gform-complement" hidden>
                                    <div class="gform-miniopts">
                                        <span>¿Tenés el producto?</span>
                                        <label><input type="radio" id="src_oidio_sve" name="src-oidio" value="sve" checked> No</label>
                                        <label><input type="radio" id="src_oidio_yo" name="src-oidio" value="yo"> Sí</label>
                                    </div>
                                    <div class="gform-brand" id="brand_oidio" hidden>
                                        <input type="text" class="gform-input gform-input-inline" id="marca_oidio" name="marca-oidio" placeholder="Marca del producto">
                                        <div class="gform-helper">Indicá marca y, si aplica, concentración/composición.</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Podredumbre -->
                            <div class="gform-optbox" id="opt_podredumbre">
                                <label class="gform-option">
                                    <input type="checkbox" id="prod_podredumbre" name="productos[]" value="podredumbre" data-complement="#cmp-podredumbre">
                                    <span>Productos para Podredimbre de los racimos</span>
                                </label>
                                <div id="cmp-podredumbre" class="gform-complement" hidden>
                                    <div class="gform-miniopts">
                                        <span>¿Tenés el producto?</span>
                                        <label><input type="radio" id="src_podredumbre_sve" name="src-podredumbre" value="sve" checked> No</label>
                                        <label><input type="radio" id="src_podredumbre_yo" name="src-podredumbre" value="yo"> Sí</label>
                                    </div>
                                    <div class="gform-brand" id="brand_podredumbre" hidden>
                                        <input type="text" class="gform-input gform-input-inline" id="marca_podredumbre" name="marca-podredumbre" placeholder="Marca del producto">
                                        <div class="gform-helper">Indicá marca y, si aplica, concentración/composición.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="gform-error">Seleccioná al menos una opción.</div>
                    </div>

                    <!-- assist_geolocation -->
                    <div class="gform-question span-2" role="group" aria-labelledby="q_ubicacion_label" id="q_ubicacion">
                        <div id="q_ubicacion_label" class="gform-label">
                            ¿Estás en la ubicación de la finca? <span class="gform-required">*</span>
                        </div>
                        <div class="gform-helper">
                            Solo selecciona que SI, si estpas en la ubicación de la finca, ya que se capturarán las coordenadas GPS del lugar.
                            Si no estás en la finca, selecciona NO y las coordenadas no se capturararan.
                            SOLO TOCA SI, CUANDO LLENES EL FORMULARIO DESDE UN CELULAR.
                        </div>

                        <div class="gform-miniopts">
                            <label><input type="radio" id="en_finca_no" name="en_finca" value="no" checked>No</label>
                            <label><input type="radio" id="en_finca_si" name="en_finca" value="si">Si</label>
                        </div>

                        <div class="gform-helper" id="ubicacion_status">No se capturarán coordenadas.</div>

                        <!-- Campos que se envían por AJAX -->
                        <input type="hidden" name="lat" id="ubicacion_lat">
                        <input type="hidden" name="lng" id="ubicacion_lng">
                        <input type="hidden" name="acc" id="ubicacion_acc">
                        <input type="hidden" name="ubicacion_ts" id="ubicacion_ts">
                    </div>

                    <!-- information -->
                    <div class="gform-question span-2" data-required="true" id="q_observaciones">
                        <label class="gform-label" for="observaciones">OBSERVACIONES <span class="gform-required">*</span></label>
                        <div class="gform-helper">Podes dejar tu comentario o consulta aquí.</div>
                        <textarea class="gform-input gform-textarea" id="observaciones" name="observaciones" rows="3" placeholder="Tu respuesta"></textarea>
                        <div class="gform-error">Esta pregunta es obligatoria.</div>
                    </div>

                    <!-- Acciones-->
                    <div class="gform-actions span-1">
                        <button type="submit" id="btn_solicitar" class="gform-btn gform-primary">Solicitar el servicio</button>
                    </div>
                </form>


                <!-- Contenedores para Toast -->
                <div id="toast-container"></div>
                <div id="toast-container-boton"></div>
            </section>
        </div>
    </div>

    <!-- Spinner Global (desde tu CDN) -->
    <div id="globalSpinner" class="spinner-overlay hidden">
        <div class="spinner"></div>
    </div>
    <script src="https://www.fernandosalguero.com/cdn/components/spinner-global.js"></script>

    <script>
        (() => {
            const $ = (sel, ctx = document) => ctx.querySelector(sel);
            const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));

            const form = $('#form-dron');

            // ---- UI: "Otros" en Motivo
            const chkOtros = $('#motivo_otros_chk');
            const inputOtros = $('#motivo_otros');
            if (chkOtros && inputOtros) {
                const syncOtros = () => {
                    inputOtros.disabled = !chkOtros.checked;
                    inputOtros.classList.toggle('oculto', !chkOtros.checked);
                    if (!chkOtros.checked) inputOtros.value = '';
                };
                chkOtros.addEventListener('change', syncOtros);
                syncOtros();
            }

            // ---- UI: mostrar complementos cuando se elige un producto
            $$('input[type="checkbox"][data-complement]').forEach(cb => {
                const cmp = document.querySelector(cb.dataset.complement);
                const sync = () => cmp && (cmp.hidden = !cb.checked);
                cb.addEventListener('change', sync);
                sync();
            });

            // ---- UI: mostrar "marca" cuando el radio == "yo" en cada producto
            [{
                    radios: ['#src_lobesia_sve', '#src_lobesia_yo'],
                    brandBox: '#brand_lobesia'
                },
                {
                    radios: ['#src_peronospora_sve', '#src_peronospora_yo'],
                    brandBox: '#brand_peronospora'
                },
                {
                    radios: ['#src_oidio_sve', '#src_oidio_yo'],
                    brandBox: '#brand_oidio'
                },
                {
                    radios: ['#src_podredumbre_sve', '#src_podredumbre_yo'],
                    brandBox: '#brand_podredumbre'
                },
            ].forEach(({
                radios,
                brandBox
            }) => {
                const rbNo = $(radios[0]);
                const rbSi = $(radios[1]);
                const box = $(brandBox);
                if (!rbNo || !rbSi || !box) return;
                const sync = () => {
                    box.hidden = !rbSi.checked;
                    if (rbNo.checked) box.querySelector('input').value = '';
                };
                rbNo.addEventListener('change', sync);
                rbSi.addEventListener('change', sync);
                sync();
            });

            // ---- Geolocalización condicional
            const enFincaSi = $('#en_finca_si');
            const enFincaNo = $('#en_finca_no');
            const status = $('#ubicacion_status');
            const lat = $('#ubicacion_lat');
            const lng = $('#ubicacion_lng');
            const acc = $('#ubicacion_acc');
            const ts = $('#ubicacion_ts');

            function clearGeo() {
                [lat, lng, acc, ts].forEach(i => i && (i.value = ''));
                if (status) status.textContent = 'No se capturarán coordenadas.';
            }

            function captureGeo() {
                if (!navigator.geolocation) {
                    if (status) status.textContent = 'Geolocalización no soportada por el navegador.';
                    return;
                }
                if (status) status.textContent = 'Obteniendo coordenadas…';
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        const {
                            latitude,
                            longitude,
                            accuracy
                        } = pos.coords;
                        if (lat) lat.value = latitude;
                        if (lng) lng.value = longitude;
                        if (acc) acc.value = accuracy;
                        if (ts) ts.value = new Date(pos.timestamp).toISOString();
                        if (status) status.textContent = `Coordenadas capturadas (±${Math.round(accuracy)} m).`;
                    },
                    (err) => {
                        if (status) status.textContent = `No se pudo obtener ubicación: ${err.message}`;
                        clearGeo();
                    }, {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
            }

            if (enFincaSi && enFincaNo) {
                enFincaSi.addEventListener('change', () => enFincaSi.checked ? captureGeo() : clearGeo());
                enFincaNo.addEventListener('change', clearGeo);
            }

            // ---- Helper para obtener radios/checkboxes
            const getRadioValue = (name) => {
                const el = form.querySelector(`input[type="radio"][name="${name}"]:checked`);
                return el ? el.value : null;
            };

            const getCheckboxValues = (name) => {
                return $$(`input[type="checkbox"][name="${name}"]:checked`, form).map(i => i.value);
            };

            // ---- Submit: construir payload y loguear
            form.addEventListener('submit', (e) => {
                e.preventDefault();

                const motivos = getCheckboxValues('motivo[]');
                const payload = {
                    representante: getRadioValue('representante'),
                    linea_tension: getRadioValue('linea_tension'),
                    zona_restringida: getRadioValue('zona_restringida'),
                    corriente_electrica: getRadioValue('corriente_electrica'),
                    agua_potable: getRadioValue('agua_potable'),
                    libre_obstaculos: getRadioValue('libre_obstaculos'),
                    area_despegue: getRadioValue('area_despegue'),
                    superficie_ha: $('#superficie_ha')?.value?.trim() || null,
                    motivo: {
                        opciones: motivos,
                        otros: chkOtros?.checked ? (inputOtros?.value?.trim() || null) : null,
                    },
                    rango_fecha: getCheckboxValues('rango_fecha[]'),
                    productos: (() => {
                        const result = [];
                        [{
                                key: 'lobesia',
                                chk: '#prod_lobesia',
                                srcName: 'src-lobesia',
                                marca: '#marca_lobesia'
                            },
                            {
                                key: 'peronospora',
                                chk: '#prod_peronospora',
                                srcName: 'src-peronospora',
                                marca: '#marca_peronospora'
                            },
                            {
                                key: 'oidio',
                                chk: '#prod_oidio',
                                srcName: 'src-oidio',
                                marca: '#marca_oidio'
                            },
                            {
                                key: 'podredumbre',
                                chk: '#prod_podredumbre',
                                srcName: 'src-podredumbre',
                                marca: '#marca_podredumbre'
                            },
                        ].forEach(({
                            key,
                            chk,
                            srcName,
                            marca
                        }) => {
                            const isChecked = $(chk)?.checked;
                            if (!isChecked) return;
                            const fuente = getRadioValue(srcName);
                            const marcaVal = fuente === 'yo' ? ($(marca)?.value?.trim() || null) : null;
                            result.push({
                                tipo: key,
                                fuente,
                                marca: marcaVal
                            });
                        });
                        return result;
                    })(),
                    ubicacion: {
                        en_finca: getRadioValue('en_finca'),
                        lat: lat?.value || null,
                        lng: lng?.value || null,
                        acc: acc?.value || null,
                        timestamp: ts?.value || null,
                    },
                    observaciones: $('#observaciones')?.value?.trim() || null,
                };

                console.log('DRON :: payload listo para enviar', payload);

                // Ejemplo de cómo lo enviarías:
                // fetch('/api/solicitudes/dron', {
                //   method: 'POST',
                //   headers: { 'Content-Type': 'application/json' },
                //   body: JSON.stringify(payload)
                // }).then(r => r.json()).then(data => console.log('Respuesta backend', data));
            });
        })();
    </script>
</body>

</html>