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
                <h4>¡Que bueno verte de nuevo <?php echo htmlspecialchars($nombre); ?>!</h4>
            </header>

            <section class="content">
                <!-- Header / Bienvenida -->
                <div class="card header-card">
                    <div>
                        <h4><?php echo htmlspecialchars($nombre); ?></h4>
                        <p>¿Queres ir al inicio?</p>
                    </div>
                    <a class="btn btn-info" href="prod_dashboard.php">Apreta acá</a>
                </div>

                <!-- Formulario para solicitar el drone -->

                <h2 style="margin-bottom: 1rem;">Formulario estilo Google (demo)</h2>

                <form id="form-dron" class="gform-grid cols-4" novalidate>

                    <!-- asistente -->
                    <div class="gform-question" role="group" aria-labelledby="q_corriente_label">
                        <div id="q_corriente_label" class="gform-legend">
                            ¿A LA HORA DE TOMAR EL SERVICIO PODREMOS CONTAR CON UN REPRESENTATE DE LA PROPIEDAD EN LA FINCA? <span class="gform-required">*</span>
                        </div>
                        <div class="gform-helper">
                            El represéntate de la propiedad deberá, recibir al piloto indicarle los cuarteles a pulverizar, darle asistencia si la requiere y firmar el registro fitosanitario.
                        </div>
                        <div class="gform-options">
                            <label class="gform-option"><input type="radio" name="g_corriente" value="si">
                                <span>SI</span></label>
                            <label class="gform-option"><input type="radio" name="g_corriente" value="no">
                                <span>NO</span></label>
                        </div>
                        <div class="gform-error">Esta pregunta es obligatoria.</div>
                    </div>

                    <!-- danger_electric -->
                    <div class="gform-question" role="group" aria-labelledby="q_corriente_label">
                        <div id="q_corriente_label" class="gform-legend">
                            ¿EL/ LOS CUARTELES A PULVERIZAR CUENTAN CON ALGUNA LINEA DE MEDIA O ALTA TENSION A MENOS DE 30 METROS? <span class="gform-required">*</span>
                        </div>
                        <div class="gform-options">
                            <label class="gform-option"><input type="radio" name="g_corriente" value="si">
                                <span>SI</span></label>
                            <label class="gform-option"><input type="radio" name="g_corriente" value="no">
                                <span>NO</span></label>
                        </div>
                        <div class="gform-error">Esta pregunta es obligatoria.</div>
                    </div>

                    <!-- danger_airport -->
                    <div class="gform-question" role="group" aria-labelledby="q_corriente_label">
                        <div id="q_corriente_label" class="gform-legend">
                            ¿EL/LOS CUARTELES A PULVERIZAR SE ENCUENTRA A MENOS DE 3 KM DE UN AEROPUERTO O ZONA DE VUELO RESTRINGIDA? <span class="gform-required">*</span>
                        </div>
                        <div class="gform-options">
                            <label class="gform-option"><input type="radio" name="g_corriente" value="si">
                                <span>SI</span></label>
                            <label class="gform-option"><input type="radio" name="g_corriente" value="no">
                                <span>NO</span></label>
                        </div>
                        <div class="gform-error">Esta pregunta es obligatoria.</div>
                    </div>


                    <!-- asistente -->
                    <div class="gform-question" role="group" aria-labelledby="q_corriente_label">
                        <div id="q_corriente_label" class="gform-legend">
                            ¿CUENTA CON DISPONIBILIDAD DE CORRIENTE ELÉCTRICA?<span class="gform-required">*</span>
                        </div>
                        <div class="gform-helper">
                            Esto se requiere para la carga de baterías a medida que se realiza la pulverización. Se necesita toma corriente de 35 amperes para poder recargar las baterías.
                        </div>
                        <div class="gform-options">
                            <label class="gform-option"><input type="radio" name="g_corriente" value="si">
                                <span>SI</span></label>
                            <label class="gform-option"><input type="radio" name="g_corriente" value="no">
                                <span>NO</span></label>
                        </div>
                        <div class="gform-error">Esta pregunta es obligatoria.</div>
                    </div>

                    <!-- asistente -->
                    <div class="gform-question" role="group" aria-labelledby="q_corriente_label">
                        <div id="q_corriente_label" class="gform-legend">
                            ¿EN LA PROPIEDAD HAY DISPONIBILIDAD DE AGUA POTABLE? <span class="gform-required">*</span>
                        </div>
                        <div class="gform-helper">
                            Se desea agua de red en condiciones apropiadas para poder realizar la preparación de los caldos de pulverización y la limpieza del dron una vez concluida la aplicación.
                        </div>
                        <div class="gform-options">
                            <label class="gform-option"><input type="radio" name="g_corriente" value="si">
                                <span>SI</span></label>
                            <label class="gform-option"><input type="radio" name="g_corriente" value="no">
                                <span>NO</span></label>
                        </div>
                        <div class="gform-error">Esta pregunta es obligatoria.</div>
                    </div>

                    <!-- asistente -->
                    <div class="gform-question" role="group" aria-labelledby="q_corriente_label">
                        <div id="q_corriente_label" class="gform-legend">
                            ¿ EL/LOS CUARTELES A PULVERIZAR ESTAN LIBRES DE OBSTÁCULOS? <span class="gform-required">*</span>
                        </div>
                        <div class="gform-helper">
                            Por obstáculos se entiende:
                            - Árboles que estén dentro del cuartel.
                            - Árboles de gran porte a menos de 4 metros del cuartel.
                            - Cables, alambres o postes que superen la altura del viñedo o parral.
                        </div>
                        <div class="gform-options">
                            <label class="gform-option"><input type="radio" name="g_corriente" value="si">
                                <span>SI</span></label>
                            <label class="gform-option"><input type="radio" name="g_corriente" value="no">
                                <span>NO</span></label>
                        </div>
                        <div class="gform-error">Esta pregunta es obligatoria.</div>
                    </div>

                    <!-- asistente -->
                    <div class="gform-question" role="group" aria-labelledby="q_corriente_label">
                        <div id="q_corriente_label" class="gform-legend">
                            ¿EL/ LOS CUARTELES A PULVERIZAR CUENTAN CON UN ÁREA DE DESPEGUE APROPIADA? <span class="gform-required">*</span>
                        </div>
                        <div class="gform-helper">
                            Por área apropiada se refiere a un callejón despejado y libre de obstáculos en un área de 4 m x 4 m.
                        </div>
                        <div class="gform-options">
                            <label class="gform-option"><input type="radio" name="g_corriente" value="si">
                                <span>SI</span></label>
                            <label class="gform-option"><input type="radio" name="g_corriente" value="no">
                                <span>NO</span></label>
                        </div>
                        <div class="gform-error">Esta pregunta es obligatoria.</div>
                    </div>

                    <!-- 1) Short answer -->
                    <div class="gform-question" data-required="true">
                        <label class="gform-label" for="g_razon">RAZON SOCIAL <span
                                class="gform-required">*</span></label>
                        <div class="gform-helper">Debe ser la razón social vinculada a la cooperativa.</div>
                        <input class="gform-input" id="g_razon" name="g_razon" type="text" placeholder="Tu respuesta" />
                        <div class="gform-error">Esta pregunta es obligatoria.</div>
                    </div>

                    <!-- 2) Select / dropdown -->
                    <div class="gform-question" data-required="true">
                        <label class="gform-label" for="g_coop">COOPERATIVA A LA QUE PERTENECE <span
                                class="gform-required">*</span></label>
                        <div class="gform-select">
                            <select id="g_coop" name="g_coop">
                                <option value="">Elegir</option>
                                <option>Cooperativa La Dormida</option>
                                <option>Cooperativa Las Trincheras</option>
                                <option>Cooperativa Productores de Junín</option>
                            </select>
                            <span class="material-icons gform-arrow">expand_more</span>
                        </div>
                        <div class="gform-error">Esta pregunta es obligatoria.</div>
                    </div>

                    <!-- 3) Radio (SI/NO) con helper -->


                    <!-- 4) Checkbox (con “Otros” inline) -->
                    <div class="gform-question" role="group" aria-labelledby="q_motivo_label">
                        <div id="q_motivo_label" class="gform-legend">
                            MOTIVO DEL SERVICIO <span class="gform-required">*</span>
                        </div>
                        <div class="gform-options">
                            <label class="gform-option"><input type="checkbox" name="g_motivo[]" value="mildiu">
                                <span>Curación para Mildiu</span></label>
                            <label class="gform-option"><input type="checkbox" name="g_motivo[]" value="oidio">
                                <span>Curación para Oidio</span></label>
                            <label class="gform-option gform-option-otros">
                                <input type="checkbox" id="g_motivo_otros_chk" value="otros">
                                <span>Otros:</span>
                                <input type="text" id="g_motivo_otros" class="gform-input gform-input-inline oculto"
                                    placeholder="Especificar">
                            </label>
                        </div>
                        <div class="gform-error">Seleccioná al menos una opción.</div>
                    </div>

                    <!-- 5) Checkbox list (quincenas) -->
                    <div class="gform-question" role="group" aria-labelledby="q_momento_label">
                        <div id="q_momento_label" class="gform-legend">
                            MOMENTO EN QUE DESEA CONTRATAR EL SERVICIO <span class="gform-required">*</span>
                        </div>
                        <div class="gform-options">
                            <label class="gform-option"><input type="checkbox" name="g_quince[]" value="oct1">
                                <span>Primera quincena de Octubre</span></label>
                            <label class="gform-option"><input type="checkbox" name="g_quince[]" value="oct2">
                                <span>Segunda quincena de Octubre</span></label>
                            <label class="gform-option"><input type="checkbox" name="g_quince[]" value="nov1">
                                <span>Primera quincena de Noviembre</span></label>
                        </div>
                        <div class="gform-error">Seleccioná al menos una opción.</div>
                    </div>

                    <!-- 6) Long answer / párrafo -->
                    <div class="gform-question span-2" data-required="true">
                        <label class="gform-label" for="g_obs">OBSERVACIONES <span
                                class="gform-required">*</span></label>
                        <textarea class="gform-input gform-textarea" id="g_obs" name="g_obs" rows="3"
                            placeholder="Tu respuesta"></textarea>
                        <div class="gform-error">Esta pregunta es obligatoria.</div>
                    </div>

                    <!-- Acciones (podés dejarlas full width) -->
                    <div class="gform-actions span-4">
                        <button type="button" class="gform-btn">Atrás</button>
                        <button type="submit" class="gform-btn gform-primary">Siguiente</button>
                        <a href="#" class="gform-clear"
                            onclick="document.getElementById('form-dron').reset();return false;">Borrar formulario</a>
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

    </script>
</body>

</html>