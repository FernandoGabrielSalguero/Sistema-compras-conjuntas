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

    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />

    <!-- Framework SVE -->
    <link rel="stylesheet" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css">
    <script src="https://www.fernandosalguero.com/cdn/assets/javascript/framework.js" defer></script>

    <style>
        /* Grid responsive para las tarjetas */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1rem;
            align-items: stretch;
        }

        .action-card .material-icons {
            font-size: 32px;
            opacity: .8;
        }

        .action-card .card-footer {
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
            <!-- Navbar simple -->
            <header class="navbar">
                <div class="navbar-title">Inicio</div>
            </header>

            <section class="content">
                <!-- Bienvenida -->
                <div class="card">
                    <h4>Hola <?php echo htmlspecialchars($nombre); ?> 👋</h4>
                    <p>Elegí una opción para continuar.</p>
                    <!-- Botón de tutorial (temporalmente oculto) -->
                    <!--
          <button id="btnIniciarTutorial" class="btn btn-aceptar">Tutorial</button>
          -->
                </div>

                <!-- Tarjetas de acciones -->
                <div class="cards-grid">
                    <div class="card action-card">
                        <div class="flex items-center gap-2">
                            <span class="material-icons">shopping_cart</span>
                            <h3>Mercado Digital</h3>
                        </div>
                        <p>Ingresá al catálogo y realizá tus pedidos disponibles.</p>
                        <div class="card-footer">
                            <a class="btn btn-aceptar" href="prod_mercadoDigital.php">Ir al mercado</a>
                        </div>
                    </div>

                    <div class="card action-card">
                        <div class="flex items-center gap-2">
                            <span class="material-icons">receipt_long</span>
                            <h3>Mis pedidos</h3>
                        </div>
                        <p>Revisá el estado de tus pedidos y descargá comprobantes.</p>
                        <div class="card-footer">
                            <a class="btn btn-aceptar" href="prod_listadoPedidos.php">Ver pedidos</a>
                        </div>
                    </div>

                    <div class="card action-card">
                        <div class="flex items-center gap-2">
                            <span class="material-icons">analytics</span>
                            <h3>Consolidado</h3>
                        </div>
                        <p>Resumen de productos y montos por operativo.</p>
                        <div class="card-footer">
                            <a class="btn btn-aceptar" href="prod_consolidado.php">Abrir consolidado</a>
                        </div>
                    </div>

                    <div class="card action-card">
                        <div class="flex items-center gap-2">
                            <span class="material-icons">person</span>
                            <h3>Mi información</h3>
                        </div>
                        <p>Datos de tu cuenta y medios de contacto.</p>
                        <div class="card-footer">
                            <a class="btn btn-aceptar" href="prod_usuarioInformacion.php">Editar datos</a>
                        </div>
                    </div>
                </div>

                <!-- contenedores para Toast -->
                <div id="toast-container"></div>
                <div id="toast-container-boton"></div>
            </section>
        </div>
    </div>

    <!-- Spinner Global -->
    <script src="../../views/partials/spinner-global.js"></script>

    <script>
        // ⚠️ Avisos de cierre de operativos (se mantiene)
        window.addEventListener('DOMContentLoaded', () => {
            <?php if (!empty($cierre_info)): ?>
                const cierreData = <?= json_encode($cierre_info, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
                if (Array.isArray(cierreData.pendientes)) {
                    cierreData.pendientes.forEach(op => {
                        const mensaje = `El operativo "${op.nombre}" se cierra en ${op.dias_faltantes} día(s). Contactate con tu cooperativa.`;
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

        // Resto de scripts relacionados a tutoriales u otros: temporalmente comentados.
        // <script src="../partials/tutorials/cooperativas/dashboard.js" defer>
    </script>
    </script>
</body>

</html>