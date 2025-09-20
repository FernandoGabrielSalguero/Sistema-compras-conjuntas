<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión y seguridad
require_once '../../middleware/authMiddleware.php';
checkAccess('ingeniero');

// Datos del usuario en sesión
$nombre = $_SESSION['nombre'] ?? 'Sin nombre';
$cierre_info = $_SESSION['cierre_info'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SVE - Ingeniero</title>

    <!-- Íconos de Material Design -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />


    <!-- Tu framework (CSS/JS) -->
    <link rel="stylesheet" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css">
    <script src="https://www.fernandosalguero.com/cdn/assets/javascript/framework.js" defer></script>

    <style>




    </style>
</head>

<body>
    <div class="layout">
        <!-- SIN sidebar -->

        <div class="main">
            <header class="navbar">
                <h4>¡Qué bueno verte de nuevo <?php echo htmlspecialchars($nombre); ?>!</h4>
                <div class="action-footer">
                    <a class="btn btn-cancelar" onclick="location.href='../../../logout.php'">Salir</a>
                </div>
            </header>

            <section class="content">
                <!-- Header / Bienvenida -->
                <div class="card header-card">
                    <div>
                        <h4><?php echo htmlspecialchars($nombre); ?></h4>
                        <p>Esta es la nueva plataforma de SVE. Desde acá vas a poder acceder a los servicios brindados de una manera rápida y fácil.</p>
                    </div>
                </div>

                <!-- Tarjetas de acciones -->
                <div class="card-grid grid-4">





                </div>

                <!-- Contenedores para Toast -->
                <div id="toast-container"></div>
                <div id="toast-container-boton"></div>
            </section>
        </div>
    </div>

    <!-- Spinner Global -->
    <div id="globalSpinner" class="spinner-overlay hidden">
        <div class="spinner"></div>
    </div>
    <script src="https://www.fernandosalguero.com/cdn/components/spinner-global.js"></script>


    <script>
        // Evitar FOUC del modal
        document.documentElement.style.visibility = 'visible';



        (function() {
            const spinner = document.getElementById('globalSpinner');

        })();
    </script>



</body>

</html>