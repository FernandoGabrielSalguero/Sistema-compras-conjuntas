<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión y configurar parámetros de seguridad
require_once '../../middleware/authMiddleware.php';
checkAccess('cooperativa');

// Datos del usuario en sesión
$nombre = $_SESSION['nombre'] ?? 'Sin nombre';
$correo = $_SESSION['correo'] ?? 'Sin correo';
$cuit = $_SESSION['cuit'] ?? 'Sin CUIT';
$telefono = $_SESSION['telefono'] ?? 'Sin teléfono';
$observaciones = $_SESSION['observaciones'] ?? 'Sin observaciones';

$cierre_info = $_SESSION['cierre_info'] ?? null;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SVE</title>

    <!-- Íconos de Material Design -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />

    <!-- Framework Success desde CDN -->
    <link rel="stylesheet" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css">
    <script src="https://www.fernandosalguero.com/cdn/assets/javascript/framework.js" defer></script>
</head>

<body>

    <!-- 🔲 CONTENEDOR PRINCIPAL -->
    <div class="layout">

        <!-- 🧭 SIDEBAR -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <span class="material-icons logo-icon">dashboard</span>
                <span class="logo-text">SVE</span>
            </div>

            <nav class="sidebar-menu">
                <ul>
                    <li onclick="location.href='coop_dashboard.php'">
                        <span class="material-icons" style="color: #5b21b6;">home</span><span class="link-text">Inicio</span>
                    </li>
                    <li onclick="location.href='coop_mercadoDigital.php'">
                        <span class="material-icons" style="color: #5b21b6;">shopping_cart</span><span class="link-text">Mercado Digital</span>
                    </li>
                    <li onclick="location.href='coop_listadoPedidos.php'">
                        <span class="material-icons" style="color: #5b21b6;">receipt_long</span><span class="link-text">Listado Pedidos</span>
                    </li>
                    <li onclick="location.href='coop_usuarioInformacion.php'">
                        <ure class="material-icons" style="color: #5b21b6;">agriculture</ure><span class="link-text">Productores</span>
                    </li>
                    <li onclick="location.href='coop_consolidado.php'">
                        <span class="material-icons" style="color: #5b21b6;">analytics</span><span class="link-text">Consolidado</span>
                    </li>
                    <li onclick="location.href='../../../logout.php'">
                        <span class="material-icons" style="color: red;">logout</span><span class="link-text">Salir</span>
                    </li>
                </ul>
            </nav>

            <div class="sidebar-footer">
                <button class="btn-icon" onclick="toggleSidebar()">
                    <span class="material-icons" id="collapseIcon">chevron_left</span>
                </button>
            </div>
        </aside>

        <!-- 🧱 MAIN -->
        <div class="main">

            <!-- 🟪 NAVBAR -->
            <header class="navbar">
                <button class="btn-icon" onclick="toggleSidebar()">
                    <span class="material-icons">menu</span>
                </button>
                <div class="navbar-title">Consolidado</div>
            </header>

            <!-- 📦 CONTENIDO -->
            <section class="content">

                <!-- Bienvenida -->
                <div class="card">
                    <h4>Hola <?php echo htmlspecialchars($nombre); ?> 👋</h4>
                    <p>En esta página vas a encontrar todos los operativos disponibles para que participes. Es importante que selecciones alguno para poder realizar compras a tus productores</p>
                    <br>
                    <!-- Boton de tutorial -->
                    <button id="btnIniciarTutorial" class="btn btn-aceptar">
                        Tutorial
                    </button>
                </div>

                <!-- contenedor de operativos -->
                <div class="card tutorial-operativos-disponibles">
                    <h2>Operativos disponibles</h2>
                    <p>Seleccioná en qué operativos querés participar para habilitar la compra a tus productores.</p>
                    <br>
                    <div class="card-grid grid-4" id="contenedorOperativos"></div>
                </div>
        </div>


        <!-- contenedor del toastify -->
        <div id="toast-container"></div>
        <div id="toast-container-boton"></div>
        <!-- Spinner Global -->
        <script src="../../views/partials/spinner-global.js"></script>

        </section>

    </div>
    </div>

    <script>
        // toast
        window.addEventListener('DOMContentLoaded', () => {
            console.log(<?php echo json_encode($_SESSION); ?>);

            <?php if (!empty($cierre_info)): ?>
                const cierreData = <?= json_encode($cierre_info, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
                cierreData.pendientes.forEach(op => {
                    const mensaje = `El operativo "${op.nombre}" se cierra en ${op.dias_faltantes} día(s).`;
                    console.log(mensaje);
                    if (typeof showToastBoton === 'function') {
                        showToastBoton('info', mensaje);
                    } else {
                        console.warn('showToast no está definido aún.');
                    }
                });
            <?php endif; ?>
        });

        // cargar operativos
        async function cargarOperativos() {
            const contenedor = document.getElementById('contenedorOperativos');
            contenedor.innerHTML = '<p>Cargando operativos...</p>';

            try {
                const res = await fetch('/controllers/coop_consolidadoController.php');
                const data = await res.json();

                if (!data.success) throw new Error(data.message);

                contenedor.innerHTML = '';

                data.operativos.forEach(op => {
                    const card = document.createElement('div');
                    card.className = 'user-card';

                    const switchId = `switch_${op.id}`;

                    card.innerHTML = `
    <h3 class="user-name">${op.nombre}</h3>

    <div class="user-info">
        <span class="material-icons icon-email">description</span>
        <span class="user-email">${op.descripcion || 'Sin descripción.'}</span>
    </div>

    <div class="user-info">
        <span class="material-icons icon-email">event</span>
        <span class="user-email"><strong>Inicio:</strong> ${formatearFechaArg(op.fecha_inicio)}</span>
    </div>

    <div class="user-info">
        <span class="material-icons icon-email">event_busy</span>
        <span class="user-email"><strong>Cierre:</strong> ${formatearFechaArg(op.fecha_cierre)}</span>
    </div>

    <div class="user-info">
        <span class="material-icons icon-email">how_to_reg</span>
        <span class="user-email"><strong>Participás:</strong></span>
        <label class="switch tutorial-swich-participacion" style="margin-left: 0.5rem;">
            <input type="checkbox" id="${switchId}" ${op.participa === 'si' ? 'checked' : ''}>
            <span class="slider round"></span>
        </label>
    </div>
`;

                    // Manejador de cambio
                    card.querySelector(`#${switchId}`).addEventListener('change', async (e) => {
                        const participa = e.target.checked ? 'si' : 'no';

                        try {
                            const res = await fetch('/controllers/coop_consolidadoController.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    operativo_id: op.id,
                                    participa: participa
                                })
                            });

                            const result = await res.json();
                            if (!result.success) {
                                alert('❌ Error al guardar participación: ' + result.message);
                            }
                        } catch (err) {
                            console.error('❌ Error en fetch:', err);
                            alert('Error de red al actualizar participación.');
                        }
                    });

                    contenedor.appendChild(card);
                });

            } catch (err) {
                contenedor.innerHTML = `<p style="color:red;">${err.message}</p>`;
            }
        }

        document.addEventListener('DOMContentLoaded', cargarOperativos);

        function formatearFechaArg(fechaISO) {
            const [a, m, d] = fechaISO.split("-");
            return `${d}/${m}/${a}`;
        }
    </script>

    <!-- llamada de tutorial -->
    <script src="../partials/tutorials/cooperativas/dashboard.js?v=<?= time() ?>" defer></script>


</body>

</html>