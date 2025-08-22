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
                    <br>
                    <form class="form-modern">
                        <div class="form-grid grid-4">

                            <div class="input-group">
                                <label for="dni">¿Contamos con un representante de la propiedad al momento de visitar la finca?</label>
                                <small>El representante de la propiedad deberá, recibir al piloto, indicarle los cuarteles a pulverizar, dale asistencia si la requiere y firmar el registro fitosanitario</small>
                                <div class="card selector-list">
                                    <h3>Selecciona una opción</h3>
                                    <hr />
                                    <label>
                                        <input type="radio" name="Si" />
                                        <span>Si</span>
                                    </label>
                                    <label>
                                        <input type="radio" name="No" />
                                        <span>No</span>
                                    </label>
                                </div>
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