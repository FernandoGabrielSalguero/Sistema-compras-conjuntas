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

// Armar un payload simple con lo que quieras exponer al front
$sesion_payload = [
    'usuario'        => $_SESSION['usuario']   ?? null,
    'rol'            => $_SESSION['rol']       ?? null,
    'nombre'         => $_SESSION['nombre']    ?? '',
    'correo'         => $_SESSION['correo']    ?? '',
    'telefono'       => $_SESSION['telefono']  ?? '',
    'direccion'      => $_SESSION['direccion'] ?? '',
    'id_real'        => $_SESSION['id_real']   ?? null,
    'cuit'           => $_SESSION['cuit']      ?? null,
];

// Lo dejamos disponible como JSON embebido para que el JS lo lea sin riesgos de XSS
?>
<script id="session-data" type="application/json">
    <?= json_encode($sesion_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
</script>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SVE - Productor</title>

    <!-- Íconos de Material Design -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />

    <!-- Tu framework (CSS/JS) -->
    <link rel="stylesheet" href="https://framework.impulsagroup.com/assets/css/framework.css">
    <script src="https://framework.impulsagroup.com/assets/javascript/framework.js" defer></script>

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

        /* Estilos del modal */


        .modal {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, .55);
            padding: 1rem;
            z-index: 10000;
        }

        .modal.is-open {
            display: flex;
        }

        .modal-content {
            width: min(720px, 100%);
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 24px 60px rgba(0, 0, 0, .25);
            overflow: hidden;
        }

        /* Secciones del modal */
        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #eee;
        }

        .modal-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .modal-close {
            border: 0;
            background: transparent;
            cursor: pointer;
            font-size: 0;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-close .material-icons {
            font-size: 22px;
            color: #666;
        }

        .modal-body {
            padding: 1rem 1.25rem;
            max-height: 65vh;
            overflow: auto;
        }

        .modal-actions {
            padding: 1rem 1.25rem;
            display: flex;
            gap: .75rem;
            justify-content: flex-end;
            border-top: 1px solid #eee;
        }

        /* Tipografías/colores dentro del modal */
        .modal-body .muted {
            color: #666;
        }

        /* Resumen key/value en dos columnas en desktop */
        .modal-summary dl {
            display: grid;
            grid-template-columns: 1fr;
            gap: .5rem 1rem;
            margin: 1rem 0 0;
        }

        .modal-summary dt {
            font-weight: 600;
            color: #333;
        }

        .modal-summary dd {
            margin: 0;
            color: #111;
        }

        @media (min-width:640px) {
            .modal-summary dl {
                grid-template-columns: 1.2fr 1fr;
            }
        }

        /* Listado de productos dentro del value */
        .modal-summary .prod-list {
            margin: .25rem 0 0;
            padding-left: 1rem;
        }

        .modal-summary .note {
            white-space: pre-wrap;
            border: 1px solid #eee;
            border-radius: 10px;
            padding: .75rem;
            background: #fafafa;
        }

        /* === Costos (mobile-first) === */
        .costos-block {
            margin-top: 1rem;
        }

        .costos-title {
            font-size: 1.05rem;
            font-weight: 700;
            margin: 0 0 .5rem 0;
        }

        .costos-list {
            list-style: none;
            margin: 0;
            padding: 0;
            border: 1px solid #eee;
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
        }

        .costos-item {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: .75rem;
            padding: .75rem .9rem;
            line-height: 1.2;
        }

        .costos-item+.costos-item {
            border-top: 1px solid #f2f2f2;
        }

        .costos-label {
            font-weight: 500;
            color: #333;
            flex: 1 1 auto;
            min-width: 0;
            /* evita overflow en textos largos */
        }

        .costos-help {
            display: block;
            font-size: .85rem;
            color: #666;
            margin-top: .15rem;
            word-break: break-word;
        }

        .costos-amount {
            flex: 0 0 auto;
            font-variant-numeric: tabular-nums;
            text-align: right;
            white-space: nowrap;
        }

        .costos-total {
            font-weight: 800;
        }

        .costos-total .costos-amount {
            font-size: 1.05rem;
        }

        /* asegura que nada se salga del modal en mobile */
        .modal-body {
            overflow-x: hidden;
        }



        /* --- GForm validation feedback --- */
        .gform-question .gform-error {
            display: none;
            color: #dc2626;
            font-size: .9rem;
            margin-top: .5rem
        }

        .gform-question.has-error .gform-error {
            display: block
        }

        .gform-question.has-error .gform-legend,
        .gform-question.has-error .gform-label {
            color: #dc2626
        }

        .gform-question.has-error .gform-options,
        .gform-question.has-error .gform-input,
        .gform-question.has-error textarea {
            outline: 2px solid #dc2626;
            outline-offset: 2px
        }

        /* --- GForm base styles (no vienen del CDN) --- */
        .gform-grid {
            display: grid;
            gap: 1rem 1.5rem;
        }

        .gform-grid.cols-2 {
            grid-template-columns: 1fr;
        }

        .gform-grid.cols-4 {
            grid-template-columns: 1fr;
        }

        .gform-grid .span-2 {
            grid-column: 1 / -1;
        }

        @media (min-width: 768px) {
            .gform-grid.cols-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .gform-grid.cols-4 {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        .gform-question {
            display: flex;
            flex-direction: column;
            gap: .4rem;
            margin-bottom: 1rem;
            font-size: .95rem;
        }

        .gform-label,
        .gform-legend {
            font-weight: 600;
        }

        .gform-helper {
            font-size: .85rem;
            color: #555;
        }

        .gform-options {
            display: flex;
            flex-direction: column;
            gap: .25rem;
        }

        .gform-option {
            display: flex;
            align-items: center;
            gap: .35rem;
        }

        .gform-option input[type="radio"],
        .gform-option input[type="checkbox"] {
            accent-color: #0284c7;
        }

        .gform-input,
        .gform-input-inline,
        .gform-textarea {
            display: block;
            width: 100%;
            padding: .45rem .6rem;
            border-radius: .5rem;
            border: 1px solid #d4d4d4;
            font: inherit;
            background-color: #fff;
        }

        .gform-input:focus,
        .gform-input-inline:focus,
        .gform-textarea:focus {
            outline: 2px solid #0284c7;
            outline-offset: 2px;
            border-color: #0284c7;
        }

        .gform-miniopts {
            display: flex;
            flex-wrap: wrap;
            gap: .75rem;
            font-size: .9rem;
        }

        .gform-miniopts label {
            display: inline-flex;
            align-items: center;
            gap: .25rem;
        }

        .gform-actions {
            margin-top: 1rem;
        }

        .gform-btn.gform-primary {
            min-width: 200px;
        }

        .gform-option-otros .gform-input-inline {
            max-width: 240px;
        }

        .oculto {
            display: none !important;
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

                <!-- Bienvenida -->
                <div class="card">
                    <p>Señor productor a continuación se le realizaran preguntas de gran importancia para la prestación del servicio. Por favor leer y contestar cada una con detenimiento.</p>
                    <br>
                </div>

                <!-- Header-->
                <div class="card header-card">
                    <div>
                        <h4><?php echo htmlspecialchars($nombre); ?></h4>
                        <p>¿Queres ir atrás?</p>
                    </div>
                    <a class="btn btn-info" href="prod_dashboard.php">Apreta acá</a>
                </div>


                <!-- Formulario para solicitar el drone -->
                <form id="form-dron" class="gform-grid cols-2" novalidate>

                    <!-- representante -->
                    <div class="gform-question" role="group" aria-labelledby="q_representante_label" id="q_representante">
                        <div id="q_representante_label" class="gform-legend">
                            ¿A LA HORA DE TOMAR EL SERVICIO PODREMOS CONTAR CON UN REPRESENTATE DE LA PROPIEDAD EN LA FINCA? <span class="gform-required">*</span>
                        </div>
                        <div class="gform-helper">
                            El represéntate de la propiedad deberá recibir al piloto, indicarle los cuarteles a pulverizar, darle asistencia si la requiere y firmar el registro fitosanitario.
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
                            Por área apropiada se refiere a un callejón despejado y libre de obstáculos en un área de 5 m x 5 m.
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
                        <div class="gform-helper">Ingresá sólo el número de hectáreas a pulverizar. Máximo 20 héctareas por mes por productor.</div>
                        <input class="gform-input" id="superficie_ha" name="superficie_ha" type="number" inputmode="decimal" min="0.01" step="0.01" placeholder="Ej.: 3.5" />
                        <div class="gform-error">Debe ser un número &gt; 0.</div>
                    </div>

                    <!-- método de pago -->
                    <div class="gform-question" data-required="true" id="q_metodo_pago">
                        <label class="gform-label" for="metodo_pago">MÉTODO DE PAGO <span class="gform-required">*</span></label>
                        <div class="gform-helper">Elegí una opción disponible.</div>
                        <select class="gform-input" id="metodo_pago" name="metodo_pago" aria-required="true">
                            <option value="">Cargando métodos…</option>
                        </select>
                        <!-- acá va la descripción del método -->
                        <div id="metodo_pago_desc" class="gform-helper" style="margin-top:.35rem;"></div>

                        <!-- Input condicional para ID=6 -->
                        <div id="wrap_coop_cuota" class="gform-field" style="margin-top:.6rem; display:none;">
                            <label class="gform-label" for="coop_descuento_id_real">Seleccioná la cooperativa</label>
                            <select class="gform-input" id="coop_descuento_id_real" name="coop_descuento_id_real">
                                <option value="">Cargando cooperativas…</option>
                            </select>
                            <div class="gform-helper">Se listan sólo cooperativas habilitadas en la plataforma.</div>
                            <div class="gform-error" id="coop_descuento_error" style="display:none;">Debés seleccionar una cooperativa.</div>
                        </div>
                    </div>

                    <!-- tratamiento / motivo -->
                    <div class="gform-question" role="group" aria-labelledby="q_motivo_label" id="q_motivo">
                        <div id="q_motivo_label" class="gform-legend">INDICAR EL MOTIVO POR EL QUE DESEA CONTRATAR EL SERVICIO<span class="gform-required">*</span></div>
                        <div class="gform-options" id="motivo_dynamic">
                            <div class="gform-helper">Cargando patologías…</div>
                        </div>
                        <div class="gform-error">Seleccioná al menos una opción.</div>
                    </div>

                    <!-- rango_fecha -->
                    <div class="gform-question" role="group" aria-labelledby="q_rango_label" id="q_rango">
                        <div id="q_rango_label" class="gform-legend">INDICAR EN QUE MOMENTO DESEA CONTRATAR EL SERVICIO<span class="gform-required">*</span></div>
                        <div class="gform-options">
                            <label class="gform-option"><input type="radio" id="rango_oct_1" name="rango_fecha" value="octubre_q1"><span>Primera quincena de Octubre</span></label>
                            <label class="gform-option"><input type="radio" id="rango_oct_2" name="rango_fecha" value="octubre_q2"><span>Segunda quincena de Octubre</span></label>
                            <label class="gform-option"><input type="radio" id="rango_nov_1" name="rango_fecha" value="noviembre_q1"><span>Primera quincena de Noviembre</span></label>
                            <label class="gform-option"><input type="radio" id="rango_nov_2" name="rango_fecha" value="noviembre_q2"><span>Segunda quincena de Noviembre</span></label>
                            <label class="gform-option"><input type="radio" id="rango_dic_1" name="rango_fecha" value="diciembre_q1"><span>Primera quincena de Diciembre</span></label>
                            <label class="gform-option"><input type="radio" id="rango_dic_2" name="rango_fecha" value="diciembre_q2"><span>Segunda quincena de Diciembre</span></label>
                            <label class="gform-option"><input type="radio" id="rango_ene_1" name="rango_fecha" value="enero_q1"><span>Primera quincena de Enero</span></label>
                            <label class="gform-option"><input type="radio" id="rango_ene_2" name="rango_fecha" value="enero_q2"><span>Segunda quincena de Enero</span></label>
                            <label class="gform-option"><input type="radio" id="rango_feb_1" name="rango_fecha" value="febrero_q1"><span>Primera quincena de Febrero</span></label>
                            <label class="gform-option"><input type="radio" id="rango_feb_2" name="rango_fecha" value="febrero_q2"><span>Segunda quincena de Febrero</span></label>
                        </div>
                        <div class="gform-error">Seleccioná una opción.</div>
                    </div>

                    <!-- assist_product -->
                    <div class="gform-question" role="group" aria-labelledby="q_productos_label" id="q_productos">
                        <div id="q_productos_label" class="gform-legend">
                            En el caso de necesitar productos fitosanitarios para realizar la pulverización indicar los que sean necesarios. <span class="gform-required">*</span>
                        </div>

                        <div class="gform-options gopts-with-complement" id="productos_dynamic">
                            <div class="gform-helper">Primero seleccioná una o más patologías arriba. Acá se habilitarán las opciones por cada una.</div>
                        </div>

                        <div class="gform-error">Seleccioná al menos una opción.</div>
                    </div>

                    <!-- dirección -->
                    <div class="gform-question span-2" role="group" aria-labelledby="q_direccion_label" id="q_direccion">
                        <div id="q_direccion_label" class="gform-legend">
                            DIRECCIÓN DE LA FINCA
                        </div>
                        <div class="gform-helper">
                            Estos datos ayudan si no capturaste coordenadas desde la finca.
                        </div>

                        <div class="gform-grid cols-4">
                            <div class="gform-field">
                                <label class="gform-label" for="dir_provincia">Provincia</label>
                                <input class="gform-input" id="dir_provincia" name="dir_provincia" type="text" placeholder="Provincia">
                            </div>

                            <div class="gform-field">
                                <label class="gform-label" for="dir_localidad">Localidad</label>
                                <input class="gform-input" id="dir_localidad" name="dir_localidad" type="text" placeholder="Localidad">
                            </div>

                            <div class="gform-field">
                                <label class="gform-label" for="dir_calle">Calle</label>
                                <input class="gform-input" id="dir_calle" name="dir_calle" type="text" placeholder="Calle">
                            </div>

                            <div class="gform-field">
                                <label class="gform-label" for="dir_numero">Numeración</label>
                                <input class="gform-input" id="dir_numero" name="dir_numero" type="text" inputmode="numeric" placeholder="Nº">
                            </div>
                        </div>
                    </div>

                    <!-- assist_geolocation -->
                    <div class="gform-question span-2" role="group" aria-labelledby="q_ubicacion_label" id="q_ubicacion">
                        <div id="q_ubicacion_label" class="gform-label">
                            ¿Estás en la ubicación de la finca? <span class="gform-required">*</span>
                        </div>
                        <div class="gform-helper">
                            Solo selecciona que SI, si estás en la ubicación de la finca, ya que se capturarán las coordenadas GPS del lugar.
                            Si no estás en la finca, selecciona NO y las coordenadas no se capturararan.
                            SOLO TOCA SI, CUANDO RESPONDAS EL FORMULARIO DESDE UN CELULAR.
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

                    <!-- observaciones -->
                    <div class="gform-question span-2" id="q_observaciones">
                        <label class="gform-label" for="observaciones">OBSERVACIONES</label>
                        <div class="gform-helper">Opcional: dejá tu comentario o consulta aquí.</div>
                        <textarea class="gform-input gform-textarea" id="observaciones" name="observaciones" rows="3" placeholder="Tu respuesta (opcional)"></textarea>
                    </div>

                    <!-- Resumen de costos dinámico -->
                    <div id="resumen-costos-inline" class="card" style="margin-top:1rem;">
                        <h4 style="margin:0 0 .5rem 0;">Resumen de costos</h4>
                        <div class="gform-grid cols-2">
                            <div>
                                <div class="gform-helper">Servicio base</div>
                                <div id="rc_base" style="font-weight:700;">—</div>
                            </div>
                            <div>
                                <div class="gform-helper">Productos SVE</div>
                                <div id="rc_prod" style="font-weight:700;">—</div>
                            </div>
                            <div class="span-2" style="border-top:1px solid #eee; padding-top:.5rem; margin-top:.35rem;">
                                <div class="gform-helper">Total estimado</div>
                                <div id="rc_total" style="font-weight:800; font-size:1.1rem;">—</div>
                            </div>
                        </div>
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

    <!-- Spinner Global -->
    <script src="../../views/partials/spinner-global.js"></script>


    <!-- Modal de confirmación -->
    <div id="modalConfirmacion" class="modal" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="modalTitle">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle" class="modal-title">¿Confirmar pedido?</h3>
                <button type="button" class="modal-close" aria-label="Cerrar" onclick="cerrarModal()">
                    <span class="material-icons">close</span>
                </button>
            </div>

            <div class="modal-body">
                <p class="muted">Estás por enviar el pedido. Revisá el detalle:</p>
                <div id="resumenModal"></div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-cancelar" onclick="cerrarModal()">Cancelar</button>
                <button type="button" id="btnConfirmarModal" class="btn btn-aceptar" onclick="confirmarEnvio()">Sí, enviar</button>
            </div>
        </div>
    </div>

    <script>
        // ====== API ======
        const API_URL = '../../controllers/prod_dronesController.php'; // ajustá si tu ruta es distinta

        function setConfirmBtnLoading(loading = true) {
            const btn = document.getElementById('btnConfirmarModal');
            if (!btn) return;
            btn.disabled = loading;
            btn.textContent = loading ? 'Enviando…' : 'Sí, enviar';
        }

        (() => {
            const $ = (sel, ctx = document) => ctx.querySelector(sel);
            const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));

            const form = $('#form-dron');

            // --- ENDPOINTS auxiliares ---
            const apiGet = async (params) => {
                const url = `${API_URL}?${new URLSearchParams(params).toString()}`;
                const res = await fetch(url, {
                    method: 'GET',
                    credentials: 'same-origin',
                    cache: 'no-store'
                });
                const json = await res.json().catch(() => ({}));
                if (!json?.ok) throw new Error(json?.error || 'Error de red');
                return json.data || json; // data.items o items
            };

            // Cache de costo por hectárea (servicio base)
            let COSTO_BASE = {
                costo: 0,
                moneda: 'Pesos'
            };

            async function cargarCostoBase() {
                try {
                    const data = await apiGet({
                        action: 'costo'
                    });
                    COSTO_BASE.costo = Number(data.costo || 0);
                    COSTO_BASE.moneda = data.moneda || 'Pesos';
                } catch {
                    /* silencio intencional */
                }
            }

            // Cargar cooperativas habilitadas (usuarios.rol='cooperativa' y permiso_ingreso='Habilitado')
            async function cargarCooperativas() {
                const sel = document.getElementById('coop_descuento_id_real');
                if (!sel) return;
                try {
                    const data = await apiGet({
                        action: 'cooperativas'
                    });
                    const items = data.items || [];
                    sel.innerHTML = `<option value="">Seleccioná…</option>` +
                        items.map(c => `<option value="${c.id_real}">${c.usuario}</option>`).join('');
                    sel.dataset.loaded = '1';
                } catch {
                    sel.innerHTML = `<option value="">No disponible</option>`;
                }
            }

            // ===== Helpers de costos y resumen (scope superior: disponibles desde el inicio) =====
            const fmtARS = (n) =>
                new Intl.NumberFormat('es-AR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                })
                .format(Number(n || 0));

            function calcularCostos(payload) {
                const sup = Math.max(0, Number(payload.superficie_ha || 0));
                const base = sup * Number(COSTO_BASE.costo || 0);

                let costoProductos = 0;
                (payload.productos || []).forEach(p => {
                    if (p.fuente === 'sve' && p.producto_id) {
                        const sel = document.querySelector(`#sel_prod_${p.patologia_id}`);
                        const costo = sel?.selectedOptions?.[0]?.dataset?.costo;
                        costoProductos += sup * Number(costo || 0);
                    }
                });

                return {
                    moneda: COSTO_BASE.moneda || 'Pesos',
                    base,
                    productos: costoProductos,
                    total: base + costoProductos
                };
            }

            function buildPayloadMin() {
                const prods = [];
                document.querySelectorAll('.gform-optbox[data-patologia-id]').forEach(box => {
                    const pid = parseInt(box.dataset.patologiaId, 10);
                    const chk = box.querySelector('input[type="checkbox"][name="productos[]"]');
                    if (!chk || !chk.checked) return;
                    const fuente = form.querySelector(`input[type="radio"][name="src-${pid}"]:checked`)?.value;
                    if (fuente === 'yo') {
                        const marca = box.querySelector(`#marca_${pid}`)?.value?.trim() || null;
                        prods.push({
                            patologia_id: pid,
                            fuente: 'yo',
                            marca
                        });
                    } else {
                        const sel = box.querySelector(`#sel_prod_${pid}`);
                        const producto_id = sel && sel.value ? parseInt(sel.value, 10) : null;
                        const producto_nombre = sel ? sel.options[sel.selectedIndex]?.textContent : null;
                        prods.push({
                            patologia_id: pid,
                            fuente: 'sve',
                            producto_id,
                            producto_nombre
                        });
                    }
                });

                return {
                    superficie_ha: document.getElementById('superficie_ha')?.value?.trim() || null,
                    productos: prods
                };
            }

            function actualizarResumenInline() {
                const rcBase = document.getElementById('rc_base');
                const rcProd = document.getElementById('rc_prod');
                const rcTotal = document.getElementById('rc_total');
                if (!rcBase || !rcProd || !rcTotal) return;

                const payload = buildPayloadMin();
                const costos = calcularCostos(payload);
                rcBase.textContent = `${fmtARS(costos.base)} ${costos.moneda || ''}`;
                rcProd.textContent = `${fmtARS(costos.productos)} ${costos.moneda || ''}`;
                rcTotal.textContent = `${fmtARS(costos.total)} ${costos.moneda || ''}`;
            }

            // Llenar método de pago
            async function cargarFormasPago() {
                const sel = document.getElementById('metodo_pago');
                const desc = document.getElementById('metodo_pago_desc');
                const coopWrap = document.getElementById('wrap_coop_cuota');
                const coopSel = document.getElementById('coop_descuento_id_real');
                if (!sel) return;
                try {
                    const data = await apiGet({
                        action: 'formas_pago'
                    });
                    const items = data.items || [];
                    sel.innerHTML = `<option value="">Seleccioná…</option>` +
                        items.map(it =>
                            `<option value="${it.id}" data-descripcion="${(it.descripcion || '').replace(/"/g,'&quot;')}">${it.nombre}</option>`
                        ).join('');

                    const onMetodoChange = async () => {
                        const op = sel.selectedOptions[0];
                        const id = parseInt(sel.value || '0', 10);
                        const d = op ? (op.dataset.descripcion || '') : '';
                        if (desc) desc.textContent = d || '';

                        if (coopWrap) {
                            coopWrap.style.display = (id === 6 ? 'block' : 'none');
                            if (id === 6 && coopSel && !coopSel.dataset.loaded) {
                                await cargarCooperativas(); // llena el select de cooperativas
                            }
                        }
                        actualizarResumenInline();
                    };
                    sel.addEventListener('change', onMetodoChange);
                    onMetodoChange(); // init
                } catch (e) {
                    sel.innerHTML = `<option value="">No disponible</option>`;
                    if (desc) desc.textContent = '';
                    if (coopWrap) coopWrap.style.display = 'none';
                }
            }

            // Cargar patologías dinámicas
            async function cargarPatologias() {
                const data = await apiGet({
                    action: 'patologias'
                });
                const items = data.items || [];
                const cont = document.getElementById('motivo_dynamic');
                if (!cont) return;

                const otrosHTML = `
    <label class="gform-option gform-option-otros">
      <input type="checkbox" id="motivo_otros_chk" name="motivo[]" value="otros" aria-controls="motivo_otros">
      <span>Otros:</span>
      <input type="text" id="motivo_otros" name="motivo_otros" class="gform-input gform-input-inline oculto" placeholder="Especificar" disabled>
    </label>
  `;

                if (!items.length) {
                    cont.innerHTML = `<div class="gform-helper">No hay patologías activas.</div>${otrosHTML}`;
                    return;
                }

                cont.innerHTML = items.map(p => `
    <label class="gform-option">
      <input type="checkbox" name="motivo[]" value="${p.id}" data-patologia-nombre="${p.nombre}">
      <span>${p.nombre}</span>
    </label>
  `).join('') + otrosHTML;

                // vincular "Otros"
                const chkOtros = document.getElementById('motivo_otros_chk');
                const inputOtros = document.getElementById('motivo_otros');
                if (chkOtros && inputOtros) {
                    const syncOtros = () => {
                        inputOtros.disabled = !chkOtros.checked;
                        inputOtros.classList.toggle('oculto', !chkOtros.checked);
                        if (!chkOtros.checked) inputOtros.value = '';
                    };
                    chkOtros.addEventListener('change', syncOtros);
                    syncOtros();
                }

                // cada cambio de patología reconstruye la sección productos
                cont.addEventListener('change', () => reconstruirProductos());
            }

            // Construir sección de productos según patologías seleccionadas
            async function reconstruirProductos() {
                const wrap = document.getElementById('productos_dynamic');
                if (!wrap) return;
                const patChecks = Array.from(document.querySelectorAll('#motivo_dynamic input[type="checkbox"][name="motivo[]"]'))
                    .filter(i => i.value !== 'otros' && i.checked);

                if (!patChecks.length) {
                    wrap.innerHTML = `<div class="gform-helper">Primero seleccioná una o más patologías arriba. Acá se habilitarán las opciones por cada una.</div>`;
                    return;
                }

                // Render base (lazy fetch de productos por patología)
                wrap.innerHTML = patChecks.map(chk => {
                    const pid = chk.value;
                    const pnom = chk.dataset.patologiaNombre || chk.nextElementSibling?.textContent || `Patología #${pid}`;
                    return `
      <div class="gform-optbox" data-patologia-id="${pid}" data-patologia-nombre="${pnom}">
        <label class="gform-option">
          <input type="checkbox" name="productos[]" value="${pid}" data-complement="#cmp-pat-${pid}">
          <span>Productos para ${pnom}</span>
        </label>
        <div id="cmp-pat-${pid}" class="gform-complement" hidden>
          <div class="gform-miniopts">
            <span>¿Tenés el producto?</span>
            <label><input type="radio" name="src-${pid}" value="sve" checked> No</label>
            <label><input type="radio" name="src-${pid}" value="yo"> Sí</label>
          </div>
          <div class="gform-brand" id="brand_${pid}" hidden>
            <input type="text" class="gform-input gform-input-inline" id="marca_${pid}" placeholder="Marca del producto">
            <div class="gform-helper">Indicá marca y, si aplica, concentración/composición.</div>
          </div>
          <div class="gform-brand" id="sve_${pid}">
            <select class="gform-input gform-input-inline" id="sel_prod_${pid}">
              <option value="">Seleccioná un producto SVE…</option>
            </select>
            <div class="gform-helper">Mostrando productos del stock asociados a ${pnom}.</div>
          </div>
        </div>
      </div>
    `;
                }).join('');

                // activar complementos
                Array.from(wrap.querySelectorAll('input[type="checkbox"][data-complement]')).forEach(cb => {
                    const cmpSel = cb.dataset.complement;
                    const cmp = document.querySelector(cmpSel);
                    const sync = () => {
                        if (cmp) cmp.hidden = !cb.checked;
                        actualizarResumenInline();
                    };
                    cb.addEventListener('change', sync);
                    sync();
                });

                // radios -> mostrar marca o select
                Array.from(wrap.querySelectorAll('.gform-optbox')).forEach(async (box) => {
                    const pid = box.dataset.patologiaId;
                    const rbNo = box.querySelector(`input[type="radio"][name="src-${pid}"][value="sve"]`);
                    const rbSi = box.querySelector(`input[type="radio"][name="src-${pid}"][value="yo"]`);
                    const brand = box.querySelector(`#brand_${pid}`);
                    const sveWrap = box.querySelector(`#sve_${pid}`);
                    const sel = box.querySelector(`#sel_prod_${pid}`);

                    const sync = () => {
                        const yo = rbSi?.checked;
                        if (brand) brand.hidden = !yo;
                        if (sveWrap) sveWrap.hidden = !!yo;
                        actualizarResumenInline();
                        if (!yo && sel && !sel.dataset.loaded) {
                            // cargar productos SVE asociados
                            apiGet({
                                    action: 'productos',
                                    patologia_id: pid
                                })
                                .then(data => {
                                    const items = (data.items || []);
                                    sel.innerHTML = `<option value="">Seleccioná un producto SVE…</option>` +
                                        items.map(it => `<option value="${it.id}" data-costo="${Number(it.costo_hectarea || 0)}">${it.nombre}</option>`).join('');
                                    sel.dataset.loaded = '1';
                                })
                                .catch(() => {
                                    sel.innerHTML = `<option value="">No se pudieron cargar productos</option>`;
                                });
                        }
                    };
                    rbNo?.addEventListener('change', sync);
                    rbSi?.addEventListener('change', sync);
                    sel?.addEventListener('change', actualizarResumenInline);
                    sync();
                });

                // recálculo inicial tras reconstruir
                actualizarResumenInline();
            }


            // Carga inicial
            cargarPatologias().catch(() => {
                const cont = document.getElementById('motivo_dynamic');
                if (cont) cont.innerHTML = `<div class="gform-helper">No se pudieron cargar las patologías.</div>`;
            });

            cargarFormasPago();
            cargarCostoBase().then(actualizarResumenInline);

            // superficie cambia
            document.getElementById('superficie_ha')?.addEventListener('input', actualizarResumenInline);

            // ---- Sesión (inyectada desde PHP)
            const sessionData = (() => {
                try {
                    return JSON.parse($('#session-data')?.textContent || '{}');
                } catch {
                    return {};
                }
            })();

            // -------- Modal
            const modal = $('#modalConfirmacion');
            const resumenModal = $('#resumenModal');
            let __ultimoPayload = null;

            const abrirModal = () => {
                if (!modal) return;
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
                // foco al confirmar
                setTimeout(() => $('#btnConfirmarModal')?.focus(), 0);
            };
            const cerrarModal = () => {
                if (!modal) return;
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
                // devuelve foco al botón de submit del form
                $('#btn_solicitar')?.focus();
            };
            // Exponer a los botones con onclick=""
            window.cerrarModal = cerrarModal;

            modal?.addEventListener('click', (e) => {
                if (e.target === modal) cerrarModal();
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && modal?.classList.contains('is-open')) cerrarModal();
            });

            // Exponer utilidades
            window.cerrarModal = cerrarModal;

            window.confirmarEnvio = async () => {
                if (!__ultimoPayload) return cerrarModal();

                try {
                    setConfirmBtnLoading(true);

                    const res = await fetch(API_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify(__ultimoPayload)
                    });

                    const raw = await res.text();
                    let data = null;
                    try {
                        data = JSON.parse(raw);
                    } catch {}

                    if (!res.ok || !data?.ok) {
                        const msg = data?.error || `Error ${res.status} al registrar la solicitud.`;
                        window.showToast?.('error', msg);
                        return;
                    }

                    window.showToast?.('success', `Solicitud registrada (#${data.id}).`);
                    cerrarModal();
                    form.reset();

                    // Reset UI dinámicos
                    $$('input[type="checkbox"][data-complement]').forEach(cb => {
                        const cmp = document.querySelector(cb.dataset.complement);
                        if (cmp) cmp.hidden = true;
                    });
                    const otrosChk = document.getElementById('motivo_otros_chk');
                    const otrosTxt = document.getElementById('motivo_otros');
                    if (otrosChk && otrosTxt) {
                        otrosChk.checked = false;
                        otrosTxt.value = '';
                        otrosTxt.disabled = true;
                        otrosTxt.classList.add('oculto');
                    }
                    if (typeof clearGeo === 'function') clearGeo();
                    reconstruirProductos(); // vuelve a estado base

                    // Redirigir al inicio tras guardar
                    setTimeout(() => {
                        window.location.href = 'prod_dashboard.php';
                    }, 1200);

                } catch {
                    window.showToast?.('error', 'No se pudo enviar la solicitud. Verificá tu conexión.');
                } finally {
                    setConfirmBtnLoading(false);
                }
            };


            // -------- UI: "Otros" en Motivo
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

            // -------- UI: complementos por producto
            $$('input[type="checkbox"][data-complement]').forEach(cb => {
                const cmp = document.querySelector(cb.dataset.complement);
                const sync = () => cmp && (cmp.hidden = !cb.checked);
                cb.addEventListener('change', sync);
                sync();
            });

            // -------- UI: mostrar "marca" si fuente == "yo"
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

            // -------- Geolocalización condicional
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

            // -------- Helpers
            const getRadioValue = (name) => {
                const el = form.querySelector(`input[type="radio"][name="${name}"]:checked`);
                return el ? el.value : null;
            };
            const getCheckboxValues = (name) => {
                return $$(`input[type="checkbox"][name="${name}"]:checked`, form).map(i => i.value);
            };
            const toSiNo = (v) => v === 'si' ? 'Sí' : v === 'no' ? 'No' : '—';
            const escapeHTML = (s) => (s ?? '')
                .toString()
                .replace(/[&<>"'`=\/]/g, c => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;',
                    '/': '&#x2F;',
                    '`': '&#x60;',
                    '=': '&#x3D;'
                } [c]));

            const labelMotivo = {
                mildiu: 'Peronospora/Mildiu',
                oidio: 'Oidio/Quintal',
                lobesia: 'Lobesia',
                podredumbre: 'Podredumbre',
                fertilizacion: 'Fertilización foliar',
                otros: 'Otros'
            };
            const labelRango = {
                enero_q1: '1ª quincena de Enero',
                enero_q2: '2ª quincena de Enero',
                febrero_q1: '1ª quincena de Febrero',
                febrero_q2: '2ª quincena de Febrero',
                octubre_q1: '1ª quincena de Octubre',
                octubre_q2: '2ª quincena de Octubre',
                noviembre_q1: '1ª quincena de Noviembre',
                noviembre_q2: '2ª quincena de Noviembre',
                diciembre_q1: '1ª quincena de Diciembre',
                diciembre_q2: '2ª quincena de Diciembre',
            };
            const labelProducto = {
                lobesia: 'Productos para Lobesia/Polilla de la Vid',
                peronospora: 'Productos para Peronospora',
                oidio: 'Productos para Oidio/Quintal',
                podredumbre: 'Productos para Podredumbre'
            };

            function getDireccionFromForm() {
                const provincia = $('#dir_provincia')?.value?.trim() || null;
                const localidad = $('#dir_localidad')?.value?.trim() || null;
                const calle = $('#dir_calle')?.value?.trim() || null;
                const numero = $('#dir_numero')?.value?.trim() || null;
                return {
                    provincia,
                    localidad,
                    calle,
                    numero
                };
            }

            function formatDireccion(dir) {
                if (!dir) return '—';
                const parts = [];
                if (dir.calle) {
                    let c = escapeHTML(dir.calle);
                    if (dir.numero) c += ' ' + escapeHTML(dir.numero);
                    parts.push(c);
                }
                if (dir.localidad) parts.push(escapeHTML(dir.localidad));
                if (dir.provincia) parts.push(escapeHTML(dir.provincia));
                return parts.length ? parts.join(', ') : '—';
            }

            // Resumen render
            function renderResumenHTML(payload) {
                const motivos = (payload.motivo.opciones || []).map(v => labelMotivo[v] || v);
                if (payload.motivo.otros) motivos.push(`Otros: ${escapeHTML(payload.motivo.otros)}`);

                const rangoSel = payload.rango_fecha ? (labelRango[payload.rango_fecha] || payload.rango_fecha) : '—';

                const prodsItems = (payload.productos || []).map(p => {
                    const fuente = p.fuente === 'yo' ? 'Proveedor propio' : 'SVE';
                    const detalle = p.fuente === 'yo' ?
                        (p.marca ? ` — Marca: ${escapeHTML(p.marca)}` : '') :
                        (p.producto_nombre ? ` — Producto: ${escapeHTML(p.producto_nombre)}` : '');
                    const pat = p.patologia_nombre || `Patología #${p.patologia_id}`;
                    return `<li>${escapeHTML(pat)} <small>(${fuente}${detalle})</small></li>`;
                }).join('');

                const formaPagoSel = document.getElementById('metodo_pago');
                const formaPagoTxt = (() => {
                    if (!payload.forma_pago_id || !formaPagoSel) return '—';
                    const opt = formaPagoSel.querySelector(`option[value="${payload.forma_pago_id}"]`);
                    return opt ? opt.textContent : '—';
                })();

                const fmtARS = (n) => new Intl.NumberFormat('es-AR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(Number(n || 0));

                const costos = calcularCostos(payload);
                const fmt = (n) => new Intl.NumberFormat('es-AR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(Number(n || 0));

                const costosHTML = `
                  <div class="costos-block" role="group" aria-label="Costo estimado">
                    <h4 class="costos-title">Costo estimado</h4>
                    <ul class="costos-list" role="list">
                      <li class="costos-item">
                        <div class="costos-label">
                          Servicio base
                          <span class="costos-help">${fmt(payload.superficie_ha)} ha × ${fmt(COSTO_BASE.costo)}/${escapeHTML(costos.moneda)}</span>
                        </div>
                        <div class="costos-amount">${fmt(costos.base)}</div>
                      </li>
                      <li class="costos-item">
                        <div class="costos-label">
                          Productos SVE
                          <span class="costos-help">${fmt(payload.superficie_ha)} ha</span>
                        </div>
                        <div class="costos-amount">${fmt(costos.productos)}</div>
                      </li>
                      <li class="costos-item costos-total" aria-label="Total estimado">
                        <div class="costos-label">Total estimado</div>
                        <div class="costos-amount">${fmt(costos.total)}</div>
                      </li>
                    </ul>
                  </div>
                `;

                return `
                  <div class="modal-summary">
                    <dl>
                      <dt>Representante en finca</dt>
                      <dd>${toSiNo(payload.representante)}</dd>

                      <dt>Líneas de media/alta tensión (&lt;30m)</dt>
                      <dd>${toSiNo(payload.linea_tension)}</dd>

                      <dt>Zona de vuelo restringida (&lt;3km)</dt>
                      <dd>${toSiNo(payload.zona_restringida)}</dd>

                      <dt>Corriente eléctrica disponible</dt>
                      <dd>${toSiNo(payload.corriente_electrica)}</dd>

                      <dt>Agua potable disponible</dt>
                      <dd>${toSiNo(payload.agua_potable)}</dd>

                      <dt>Cuarteles libres de obstáculos</dt>
                      <dd>${toSiNo(payload.libre_obstaculos)}</dd>

                      <dt>Área de despegue apropiada</dt>
                      <dd>${toSiNo(payload.area_despegue)}</dd>

                      <dt>Superficie (ha)</dt>
                      <dd>${escapeHTML(payload.superficie_ha ?? '—')}</dd>

                      <dt>Dirección</dt>
                      <dd>${formatDireccion(payload.direccion)}</dd>

                      <dt>Motivo</dt>
                      <dd>${motivos.length ? escapeHTML(motivos.join(', ')) : '—'}</dd>

                      <dt>Método de pago</dt>
                      <dd>${escapeHTML(formaPagoTxt)}</dd>

                      
                      <dt>Momento deseado</dt>
                      <dd>${escapeHTML(rangoSel)}</dd>

                      <dt>Observaciones</dt>
                      <dd><div class="note">${escapeHTML(payload.observaciones ?? '—')}</div></dd>


                      ${costosHTML}

                    </dl>
                  </div>
                `;
            }


            // ------- VALIDACIÓN GFORM
            function flag(container, ok) {
                if (!container) return ok;
                container.classList.toggle('has-error', !ok);
                // aria-invalid para accesibilidad
                const grp = container.querySelector('[role="group"], .gform-options, .gform-input, textarea');
                if (grp) grp.setAttribute('aria-invalid', String(!ok));
                return ok;
            }

            function atLeastOneChecked(selector, ctx = document) {
                return !!ctx.querySelector(`${selector}:checked`);
            }

            function getValuesChecked(selector, ctx = document) {
                return Array.from(ctx.querySelectorAll(`${selector}:checked`)).map(i => i.value);
            }

            function validateGForm() {
                let ok = true,
                    firstBad = null;
                const must = (container, condition) => {
                    const good = !!condition;
                    ok = ok && good;
                    if (!good && !firstBad) firstBad = container;
                    return flag(container, good);
                };

                // Método de pago ya validado arriba
                const mpSel = document.getElementById('metodo_pago');
                flag(document.getElementById('q_metodo_pago'), !!mpSel && !!mpSel.value);
                if (!mpSel || !mpSel.value) {
                    window.showToast?.('error', 'Seleccioná un método de pago.');
                    return false;
                }
                // Si es 6, exigir selección de cooperativa (guardaremos su id_real)
                let coopOK = true;
                if (parseInt(mpSel.value, 10) === 6) {
                    const wrap = document.getElementById('wrap_coop_cuota');
                    const selCoop = document.getElementById('coop_descuento_id_real');
                    const err = document.getElementById('coop_descuento_error');
                    coopOK = !!(selCoop && selCoop.value);
                    if (wrap) wrap.classList.toggle('has-error', !coopOK);
                    if (err) err.style.display = coopOK ? 'none' : 'block';
                }
                if (!coopOK) return false;

                // Radios obligatorios
                must(document.getElementById('q_representante'), atLeastOneChecked('input[type="radio"][name="representante"]', form));
                must(document.getElementById('q_linea_tension'), atLeastOneChecked('input[type="radio"][name="linea_tension"]', form));
                must(document.getElementById('q_zona_restringida'), atLeastOneChecked('input[type="radio"][name="zona_restringida"]', form));
                must(document.getElementById('q_corriente_electrica'), atLeastOneChecked('input[type="radio"][name="corriente_electrica"]', form));
                must(document.getElementById('q_agua_potable'), atLeastOneChecked('input[type="radio"][name="agua_potable"]', form));
                must(document.getElementById('q_obstaculos'), atLeastOneChecked('input[type="radio"][name="libre_obstaculos"]', form));
                must(document.getElementById('q_area_despegue'), atLeastOneChecked('input[type="radio"][name="area_despegue"]', form));
                must(document.getElementById('q_ubicacion'), atLeastOneChecked('input[type="radio"][name="en_finca"]', form)); // ya viene con NO marcado, igual chequeo

                // Superficie (número > 0, sin tope máximo)
                const supEl = document.getElementById('superficie_ha');
                let supOk = false;
                if (supEl) {
                    const val = parseFloat(supEl.value);
                    supOk = !isNaN(val) && val > 0;
                    const err = document.querySelector('#q_superficie .gform-error');
                    if (err) err.textContent = 'Debe ser un número mayor a 0.';
                }
                must(document.getElementById('q_superficie'), supOk);

                // Motivo (al menos uno) y "Otros" con texto si está marcado
                const motivos = getValuesChecked('input[type="checkbox"][name="motivo[]"]', form);
                let motivoOk = motivos.length > 0;
                if (motivoOk && document.getElementById('motivo_otros_chk')?.checked) {
                    motivoOk = !!document.getElementById('motivo_otros')?.value.trim();
                }
                must(document.getElementById('q_motivo'), motivoOk);

                // Rango de fechas (selección única)
                must(document.getElementById('q_rango'), atLeastOneChecked('input[type="radio"][name="rango_fecha"]', form));


                // Productos (al menos uno) + si fuente = "yo", exigir marca
                // Productos (dinámico): al menos un checkbox por patología + validar fuente
                const productosMarcados = getValuesChecked('input[type="checkbox"][name="productos[]"]', form);
                let prodOk = productosMarcados.length > 0;
                if (prodOk) {
                    for (const pid of productosMarcados) {
                        const box = document.querySelector(`.gform-optbox[data-patologia-id="${pid}"]`);
                        if (!box) continue;
                        const fuente = form.querySelector(`input[type="radio"][name="src-${pid}"]:checked`)?.value;
                        if (!fuente) {
                            prodOk = false;
                            break;
                        }
                        if (fuente === 'yo') {
                            const marca = box.querySelector(`#marca_${pid}`)?.value?.trim();
                            if (!marca) {
                                prodOk = false;
                                break;
                            }
                        } else {
                            const sel = box.querySelector(`#sel_prod_${pid}`);
                            if (!sel || !sel.value) {
                                prodOk = false;
                                break;
                            }
                        }
                    }
                }
                must(document.getElementById('q_productos'), prodOk);

                // Observaciones (opcional)
                flag(document.getElementById('q_observaciones'), true);

                // Dirección requerida si no está en la finca
                const enFincaVal = getRadioValue('en_finca');
                const dir = getDireccionFromForm();
                let dirOk = true;
                if (enFincaVal === 'no') {
                    dirOk = !!(dir.provincia && dir.localidad && dir.calle && dir.numero);
                }
                if (!flag(document.getElementById('q_direccion'), dirOk)) {
                    ok = false;
                    if (!firstBad) firstBad = document.getElementById('q_direccion');
                }


                // Enfocar/scroll al primero con error
                if (!ok && firstBad) firstBad.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });

                return ok;
            }

            // -------- Submit: construir payload y abrir modal
            form.addEventListener('submit', (e) => {
                e.preventDefault();

                // 1) Validación custom
                if (!validateGForm()) {
                    window.showToast?.('error', 'Revisá los campos marcados en rojo.');
                    return;
                }

                const mpSel = document.getElementById('metodo_pago');
                flag(document.getElementById('q_metodo_pago'), !!mpSel && !!mpSel.value);
                if (!mpSel || !mpSel.value) {
                    window.showToast?.('error', 'Seleccioná un método de pago.');
                    return;
                }

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
                    forma_pago_id: (() => {
                        const v = $('#metodo_pago')?.value;
                        return v ? parseInt(v, 10) : null;
                    })(),
                    // Enviamos el id_real seleccionado en el combo (se guarda en coop_descuento_nombre)
                    coop_descuento_nombre: (() => {
                        const mp = $('#metodo_pago')?.value;
                        const sel = document.getElementById('coop_descuento_id_real');
                        return (parseInt(mp || '0', 10) === 6 && sel) ? (sel.value || null) : null;
                    })(),
                    motivo: {
                        opciones: motivos,
                        otros: chkOtros?.checked ? (inputOtros?.value?.trim() || null) : null,
                    },
                    rango_fecha: getRadioValue('rango_fecha'),
                    productos: (() => {
                        const out = [];
                        document.querySelectorAll('.gform-optbox[data-patologia-id]').forEach(box => {
                            const pid = parseInt(box.dataset.patologiaId, 10);
                            const pnom = box.dataset.patologiaNombre || '';
                            const chk = box.querySelector('input[type="checkbox"][name="productos[]"]');
                            if (!chk || !chk.checked) return;

                            const fuente = getRadioValue(`src-${pid}`);
                            if (fuente === 'yo') {
                                const marca = box.querySelector(`#marca_${pid}`)?.value?.trim() || null;
                                out.push({
                                    patologia_id: pid,
                                    patologia_nombre: pnom,
                                    fuente: 'yo',
                                    marca
                                });
                            } else {
                                const sel = box.querySelector(`#sel_prod_${pid}`);
                                const producto_id = sel && sel.value ? parseInt(sel.value, 10) : null;
                                const producto_nombre = sel ? sel.options[sel.selectedIndex]?.textContent : null;
                                out.push({
                                    patologia_id: pid,
                                    patologia_nombre: pnom,
                                    fuente: 'sve',
                                    producto_id,
                                    producto_nombre
                                });
                            }
                        });
                        return out;
                    })(),
                    direccion: getDireccionFromForm(),
                    ubicacion: {
                        en_finca: getRadioValue('en_finca'),
                        lat: lat?.value || null,
                        lng: lng?.value || null,
                        acc: acc?.value || null,
                        timestamp: ts?.value || null,
                    },
                    observaciones: $('#observaciones')?.value?.trim() || null,

                    sesion: sessionData
                };

                // Guardamos para confirmar y mostramos el resumen en el modal
                __ultimoPayload = payload;
                if (resumenModal) resumenModal.innerHTML = renderResumenHTML(payload);
                abrirModal();
            });

        })();
    </script>

</body>

</html>