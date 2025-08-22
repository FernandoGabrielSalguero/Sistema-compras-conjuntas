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
                <div class="card">
                    <h2>Completa con atención el siguiente formulario</h2>
                    <form class="form-modern">
                        <div class="form-grid grid-4">

                            <!-- Asistente en la finca -->
                            <div class="input-group">
                                <label for="nombre">¿A la hora de tomar el servicio podremos contar con un representante de la pripiedad en la finca?</label>
                                <h4>El representante de la propiedad, debera recibir al piloto e indicarle los cuarteles a pulverizar, darle asistencia si la requiere y firmar el registro fitosanitario</h4>
                                <div class="input-icon input-icon-name">
                                    <input type="text" id="nombre" name="nombre" placeholder="Juan Pérez" required />
                                </div>
                            </div>

                            <!-- Correo electrónico -->
                            <div class="input-group">
                                <label for="email">Correo electrónico</label>
                                <div class="input-icon input-icon-email">
                                    <input id="email" name="email" placeholder="usuario@correo.com" />
                                </div>
                            </div>

                            <!-- Fecha de nacimiento -->
                            <div class="input-group">
                                <label for="fecha">Fecha de nacimiento</label>
                                <div class="input-icon input-icon-date">
                                    <input id="fecha" name="fecha" />
                                </div>
                            </div>

                            <!-- Teléfono -->
                            <div class="input-group">
                                <label for="telefono">Teléfono</label>
                                <div class="input-icon input-icon-phone">
                                    <input id="telefono" name="telefono" />
                                </div>
                            </div>

                            <!-- DNI -->
                            <div class="input-group">
                                <label for="dni">DNI</label>
                                <div class="input-icon input-icon-dni">
                                    <input id="dni" name="dni" />
                                </div>
                            </div>

                            <!-- Edad -->
                            <div class="input-group">
                                <label for="edad">Edad</label>
                                <div class="input-icon input-icon-age">
                                    <input id="edad" name="edad" />
                                </div>
                            </div>

                            <!-- CUIT -->
                            <div class="input-group">
                                <label for="cuit">CUIT</label>
                                <div class="input-icon input-icon-cuit">
                                    <input id="cuit" name="cuit" />
                                </div>
                            </div>

                            <!-- Provincia -->
                            <div class="input-group">
                                <label for="provincia">Provincia</label>
                                <div class="input-icon input-icon-globe">
                                    <select id="provincia" name="provincia" required>
                                        <option value="">Seleccionar</option>
                                        <option>Buenos Aires</option>
                                        <option>Córdoba</option>
                                        <option>Santa Fe</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Localidad -->
                            <div class="input-group">
                                <label for="localidad">Localidad</label>
                                <div class="input-icon input-icon-city">
                                    <input type="text" id="localidad" name="localidad" required />
                                </div>
                            </div>

                            <!-- Código Postal -->
                            <div class="input-group">
                                <label for="cp">Código Postal</label>
                                <div class="input-icon input-icon-cp">
                                    <input type="text" id="cp" name="cp" />
                                </div>
                            </div>

                            <!-- Dirección -->
                            <div class="input-group">
                                <label for="direccion">Dirección</label>
                                <div class="input-icon input-icon-address">
                                    <input type="text" id="direccion" name="direccion" required />
                                </div>
                            </div>
                        </div>
                        <!-- Observaciones -->
                        <div class="input-group">
                            <label for="observaciones">Observaciones</label>
                            <div class="input-icon input-icon-comment">
                                <textarea id="observaciones" name="observaciones" maxlength="233" rows="3"
                                    placeholder="Escribí un comentario..."></textarea>
                            </div>
                            <small class="char-count" data-for="observaciones">Quedan 233 caracteres.</small>
                        </div>

                        <!-- Botones -->
                        <div class="form-buttons">
                            <button class="btn btn-aceptar" type="submit">Enviar</button>
                            <button class="btn btn-cancelar" type="reset">Cancelar</button>
                        </div>
                    </form>
                </div>


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