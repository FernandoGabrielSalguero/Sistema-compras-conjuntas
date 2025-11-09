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

                /* Mejora accesible del modal: pequeña animación sin layout shift */
        #modalCompletarDatos .modal-content {
            transition: transform .2s ease, opacity .2s ease;
            transform: translateY(-4px);
            opacity: 0.98;
        }
        .hidden #modalCompletarDatos .modal-content { transform: translateY(0); }
        /* Inputs pequeños ajustes si hiciera falta */
        #modalCompletarDatos .input-group { margin-bottom: .75rem; }

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

                    <div class="card">
                        <div class="card-title">
                            <span class="material-symbols-outlined">drone</span>
                            <h3>Pulverización con Drones</h3>
                        </div>
                        <p>Solicitá el servicio de drones para tu finca.</p>
                        <div class="action-footer">
                            <a class="btn btn-aceptar" href="prod_drones.php">Solicitar Drone</a>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-title">
                            <span class="material-icons">receipt_long</span>
                            <h3>Mis pedidos de Drone</h3>
                        </div>
                        <p>Revisá el estado de tus pedidos y descargá comprobantes.</p>
                        <div class="action-footer">
                            <a class="btn btn-aceptar" href="prod_listadoPedidos.php">Ver pedidos</a>
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
    <!-- <script src="https://www.fernandosalguero.com/cdn/components/spinner-global.js"></script> -->

    <!-- Modal completar datos de contacto -->
    <div id="modalCompletarDatos" class="modal hidden" aria-hidden="true" role="dialog" aria-labelledby="modal-title" aria-describedby="modal-desc">
        <div class="modal-content" role="document">
            <h3 id="modal-title">Tus datos de contacto</h3>
            <p id="modal-desc">Necesitamos tu <strong>correo</strong> y <strong>teléfono</strong> para notificaciones y coordinación de servicios.</p>

            <form id="formContacto" novalidate>
                <div class="input-group">
                    <label for="correo">Correo electrónico</label>
                    <div class="input-icon input-icon-mail">
                        <input type="email" id="correo" name="correo" placeholder="tu@mail.com" autocomplete="email" required aria-required="true" />
                    </div>
                </div>

                <div class="input-group">
                    <label for="telefono">Teléfono</label>
                    <div class="input-icon input-icon-phone">
                        <input type="tel" id="telefono" name="telefono" placeholder="2618895420" autocomplete="tel" required aria-required="true" />
                    </div>
                </div>

                <div class="form-buttons">
                    <button type="button" class="btn btn-cancelar oculto" onclick="closeModalContacto()">Cancelar</button>
                    <button type="submit" class="btn btn-aceptar">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Evitar FOUC del modal
    document.documentElement.style.visibility = 'visible';

    // Helpers accesibles
    function openModalContacto() {
        const m = document.getElementById('modalCompletarDatos');
        m.classList.remove('hidden');
        m.setAttribute('aria-hidden', 'false');
        document.getElementById('correo').focus();
    }
    function closeModalContacto() {
        const m = document.getElementById('modalCompletarDatos');
        m.classList.add('hidden');
        m.setAttribute('aria-hidden', 'true');
    }

    (function () {
        const spinner = document.getElementById('globalSpinner');
        const form = document.getElementById('formContacto');
        const inputCorreo = document.getElementById('correo');
        const inputTelefono = document.getElementById('telefono');

        // Cargar estado actual al entrar
        const cargarDatos = async () => {
            try {
                spinner.classList.remove('hidden');
                const res = await fetch('../../controllers/Prod_dashboardController.php', {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                const json = await res.json();
                if (!json.ok) {
                    showAlert('error', json.error || 'No se pudo verificar tus datos de contacto.');
                    return;
                }
                // json.data = { correo, telefono, completo }
                if (json.data) {
                    if (json.data.correo) inputCorreo.value = json.data.correo;
                    if (json.data.telefono) inputTelefono.value = json.data.telefono;
                    if (!json.data.completo) {
                        // Mostrar modal solo si falta alguno
                        openModalContacto();
                    }
                }
            } catch (e) {
                showAlert('error', 'Error de comunicación al consultar datos de contacto.');
            } finally {
                spinner.classList.add('hidden');
            }
        };

        // Validaciones mínimas en front (sin romper layout)
        const emailValido = (v) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(v).trim());
        const telValido = (v) => String(v).trim().length >= 6;

        form.addEventListener('submit', async (ev) => {
            ev.preventDefault();
            const correo = inputCorreo.value.trim();
            const telefono = inputTelefono.value.trim();

            if (!emailValido(correo)) {
                inputCorreo.setAttribute('aria-invalid', 'true');
                inputCorreo.focus();
                showAlert('error', 'Ingresá un correo válido.');
                return;
            } else {
                inputCorreo.removeAttribute('aria-invalid');
            }

            if (!telValido(telefono)) {
                inputTelefono.setAttribute('aria-invalid', 'true');
                inputTelefono.focus();
                showAlert('error', 'Ingresá un teléfono válido (mínimo 6 caracteres).');
                return;
            } else {
                inputTelefono.removeAttribute('aria-invalid');
            }

            try {
                spinner.classList.remove('hidden');
                const res = await fetch('../../controllers/Prod_dashboardController.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ correo, telefono })
                });
                const json = await res.json();
                if (json.ok) {
                    showAlert('success', 'Datos de contacto guardados.');
                    closeModalContacto();
                } else {
                    showAlert('error', json.error || 'No se pudieron guardar tus datos.');
                }
            } catch (e) {
                showAlert('error', 'Error de comunicación al guardar.');
            } finally {
                spinner.classList.add('hidden');
            }
        });

        // Inicio
        cargarDatos();
    })();
    </script>



</body>

</html>