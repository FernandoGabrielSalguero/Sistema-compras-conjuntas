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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />


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
            /* un poco más alto */
        }

        /* Título con icono */
        .card-title {
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .card-title .material-icons {
            font-size: 32px;
            color: #5b21b6;
            opacity: .9;
        }

        /* Pie de cada tarjeta: botón alineado a la derecha */
        .action-footer {
            margin-top: .75rem;
            display: flex;
            justify-content: flex-end;
        }

        .material-symbols-outlined {
            font-size: 32px;
            color: #5b21b6;
            opacity: .9;
        }
    </style>
</head>

<body>
    <div class="layout">
        <!-- SIN sidebar -->

        <div class="main">
            <header class="navbar">
                <h4>¡Qué bueno verte de nuevo <?php echo htmlspecialchars($nombre); ?>!</h4>
            </header>

            <section class="content">
                <!-- Header / Bienvenida -->
                <div class="card header-card">
                    <div>
                        <h4><?php echo htmlspecialchars($nombre); ?> 👋</h4>
                        <p>Esta es la nueva plataforma de SVE. Desde acá vas a poder acceder a los servicios brindados de una manera rápida y fácil.</p>
                    </div>
                    <a class="btn btn-info" href="prod_dashboard.php">Volver al inicio</a>
                </div>

                <!-- Tarjetas de acciones -->
                <div class="card-grid grid-4">

                    <div class="card">
                        <div class="card-title">
                            <span class="material-symbols-outlined">drone</span>
                            <h3>Pulverización con Drones</h3>
                        </div>
                        <p>Solicitá el servicio de drones para tu finca.</p>
                        <div class="action-footer">
                            <a class="btn btn-aceptar" href="prod_drones.php">Ir al mercado</a>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-title">
                            <span class="material-icons">shopping_cart</span>
                            <h3>Mercado Digital</h3>
                        </div>
                        <p>Ingresá al catálogo y realizá tus pedidos disponibles.</p>
                        <div class="action-footer">
                            <a class="btn btn-aceptar" href="prod_mercadoDigital.php">Ir al mercado</a>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-title">
                            <span class="material-icons">receipt_long</span>
                            <h3>Mis pedidos</h3>
                        </div>
                        <p>Revisá el estado de tus pedidos y descargá comprobantes.</p>
                        <div class="action-footer">
                            <a class="btn btn-aceptar" href="prod_listadoPedidos.php">Ver pedidos</a>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-title">
                            <span class="material-icons">analytics</span>
                            <h3>Consolidado</h3>
                        </div>
                        <p>Resumen de productos y montos por operativo.</p>
                        <div class="action-footer">
                            <a class="btn btn-aceptar" href="prod_consolidado.php">Abrir consolidado</a>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-title">
                            <span class="material-icons">person</span>
                            <h3>Mi información</h3>
                        </div>
                        <p>Datos de tu cuenta y medios de contacto.</p>
                        <div class="action-footer">
                            <a class="btn btn-aceptar" href="prod_usuarioInformacion.php">Editar datos</a>
                        </div>
                    </div>
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
        // Avisos de cierre de operativos
        window.addEventListener('DOMContentLoaded', () => {
            <?php if (!empty($cierre_info)): ?>
                const cierreData = <?= json_encode($cierre_info, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
                if (Array.isArray(cierreData.pendientes)) {
                    cierreData.pendientes.forEach(op => {
                        const mensaje = `El operativo "${op.nombre}" se cierra en ${op.dias_faltantes} día(s). Contactate con tu cooperativa para poder comprar productos en el operativo.`;
                        if (typeof showToastBoton === 'function') {
                            showToastBoton('info', mensaje);
                        } else if (typeof showToast === 'function') {
                            showToast('info', mensaje);
                        } else {
                            console.log('[AVISO]', mensaje);
                        }
                    });
                }
            <?php endif; ?>
        });
    </script>
</body>

</html>