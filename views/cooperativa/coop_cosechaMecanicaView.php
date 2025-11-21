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
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- Framework Success desde CDN -->
    <link rel="stylesheet" href="https://framework.impulsagroup.com/assets/css/framework.css">
    <script src="https://framework.impulsagroup.com/assets/javascript/framework.js" defer></script>

    <!-- Descarga de consolidado (no se usa directamente aquí, pero se deja por consistencia) -->
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

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
                        <span class="material-icons"
                            style="color: #5b21b6;">shopping_cart</span><span class="link-text">Mercado Digital</span>
                    </li>
                    <li onclick="location.href='coop_listadoPedidos.php'">
                        <span class="material-icons"
                            style="color: #5b21b6;">receipt_long</span><span class="link-text">Listado Pedidos</span>
                    </li>
                    <li onclick="location.href='coop_consolidado.php'">
                        <span class="material-icons"
                            style="color: #5b21b6;">analytics</span><span class="link-text">Consolidado</span>
                    </li>
                    <li onclick="location.href='coop_pulverizacion.php'">
                        <span class="material-symbols-outlined"
                            style="color:#5b21b6;">drone</span><span class="link-text">Pulverización con Drone</span>
                    </li>
                    <li onclick="location.href='coop_usuarioInformacion.php'">
                        <span class="material-icons" style="color: #5b21b6;">person</span><span
                            class="link-text">Productores</span>
                    </li>
                    <li onclick="location.href='coop_cosechaMecanicaView.php'">
                        <span class="material-icons"
                            style="color: #5b21b6;">agriculture</span><span class="link-text">Cosecha Mecanica</span>
                    </li>
                    <li onclick="location.href='../../../logout.php'">
                        <span class="material-icons" style="color: red;">logout</span><span
                            class="link-text">Salir</span>
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
                <div class="navbar-title">Cosecha Mecanica</div>
            </header>

            <!-- 📦 CONTENIDO -->
            <section class="content">

                <!-- Bienvenida -->
                <div class="card">
                    <h4>Hola <?php echo htmlspecialchars($nombre); ?> 👋</h4>
                    <p>En esta página, vas a poder visualizar los servicios disponibles para cosecha mecanizada e
                        inscribir a tus productores.</p>
                    <br>
                    <!-- Botón de tutorial (reservado para futuro)
                    <button id="btnIniciarTutorial" class="btn btn-aceptar">
                        Tutorial
                    </button> -->
                </div>

                <!-- Listado de operativos de Cosecha Mecánica -->
                <div class="card">
                    <h3>Operativos disponibles</h3>
                    <p>Seleccioná un operativo para participar con tus productores.</p>
                    <div id="operativosList" class="operativos-grid">
                        <!-- JS inyecta aquí las tarjetas -->
                    </div>
                </div>

            </section>
            <!-- /content -->

        </div>
        <!-- /main -->

    </div>
    <!-- /layout -->

    <?php
    // Modal de participación (tabla de productores)
    require_once __DIR__ . '/../partials/cosechaMecanicaModales/coop_participaciónModal_view.php';

    // Modal de contrato (detalle + firma en conformidad)
    require_once __DIR__ . '/../partials/cosechaMecanicaModales/coop_firmaContratoModal_view.php';
    ?>

    <!-- contenedor del toastify -->
    <div id="toast-container"></div>
    <div id="toast-container-boton"></div>

    <!-- Spinner Global -->
    <script src="../../views/partials/spinner-global.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // La vista principal solo se encarga de cargar y mostrar los operativos
            cargarOperativos();
        });

        function cargarOperativos() {
            const url = '../../controllers/coop_cosechaMecanicaController.php?action=listar_operativos';

            fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(json) {
                    if (!json || json.success !== true) {
                        showAlert('error', json && json.message ? json.message : 'No se pudieron obtener los operativos.');
                        return;
                    }
                    renderOperativos(json.data || []);
                })
                .catch(function(error) {
                    console.error('Error al obtener operativos:', error);
                    showAlert('error', 'Error de conexión al obtener los operativos.');
                });
        }

        function renderOperativos(operativos) {
            const contenedor = document.getElementById('operativosList');
            if (!contenedor) return;

            contenedor.innerHTML = '';

            if (!Array.isArray(operativos) || operativos.length === 0) {
                contenedor.innerHTML = '<p>No hay operativos disponibles por el momento.</p>';
                return;
            }

            operativos.forEach(function(op) {
                const card = document.createElement('div');
                card.className = 'card operativo-card';

                const estado = op.estado || '';
                const diasRestantes = (op.dias_restantes !== null && op.dias_restantes !== undefined) ?
                    op.dias_restantes :
                    '-';

                const estadoClase = obtenerClaseEstado(estado);

                const contratoFirmado = op.contrato_firmado === 1 ||
                    op.contrato_firmado === '1' ||
                    op.contrato_firmado === true;

                const textoContrato = contratoFirmado ? 'Ver contrato' : 'Contrato';
                const claseInscribirOculta = contratoFirmado ? '' : 'hidden';

                card.innerHTML = `
                    <h4>${escapeHtml(op.nombre || '')}</h4>
                    <p><strong>Apertura:</strong> ${formatearFecha(op.fecha_apertura)}</p>
                    <p><strong>Cierre:</strong> ${formatearFecha(op.fecha_cierre)}</p>
                    <p><strong>Estado:</strong> <span class="badge ${estadoClase}">${escapeHtml(estado)}</span></p>
                    <p><strong>Días para cierre:</strong> ${diasRestantes}</p>
                    <div class="form-buttons">
                        <button
                            type="button"
                            class="btn btn-info btn-contrato"
                            data-id="${op.id}"
                        >
                            ${textoContrato}
                        </button>
                        <button
                            type="button"
                            class="btn btn-aceptar btn-inscribir ${claseInscribirOculta}"
                            data-id="${op.id}"
                        >
                            Inscribir productores
                        </button>
                    </div>
                `;

                contenedor.appendChild(card);
            });

            const botonesContrato = contenedor.querySelectorAll('.btn-contrato');
            botonesContrato.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const contratoId = this.getAttribute('data-id');
                    if (!contratoId) return;

                    if (typeof abrirContratoModal === 'function') {
                        abrirContratoModal(contratoId);
                    } else {
                        console.error('Función abrirContratoModal no disponible.');
                        showAlert('error', 'No se pudo abrir el modal de contrato.');
                    }
                });
            });

            const botonesInscribir = contenedor.querySelectorAll('.btn-inscribir');
            botonesInscribir.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const contratoId = this.getAttribute('data-id');
                    if (!contratoId) return;

                    if (typeof abrirParticipacionModal === 'function') {
                        abrirParticipacionModal(contratoId);
                    } else {
                        console.error('Función abrirParticipacionModal no disponible.');
                        showAlert('error', 'No se pudo abrir el modal de participación.');
                    }
                });
            });
        }

        function obtenerClaseEstado(estado) {
            switch (estado) {
                case 'abierto':
                    return 'success';
                case 'borrador':
                    return 'warning';
                case 'cerrado':
                    return 'danger';
                default:
                    return 'info';
            }
        }

        function formatearFecha(fechaIso) {
            if (!fechaIso) return '-';
            const partes = fechaIso.split('-'); // esperado 'YYYY-MM-DD'
            if (partes.length !== 3) return fechaIso;
            return partes[2] + '/' + partes[1] + '/' + partes[0];
        }

        function escapeHtml(texto) {
            if (texto === null || texto === undefined) return '';
            return String(texto)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }
    </script>

</body>

</html>