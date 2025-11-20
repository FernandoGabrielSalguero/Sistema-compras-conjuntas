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
    // Modal de participación (información del contrato + tabla de productores)
    require_once __DIR__ . '/../partials/cosechaMecanicaModales/coop_participaciónModal_view.php';
    ?>

    <!-- contenedor del toastify -->
    <div id="toast-container"></div>
    <div id="toast-container-boton"></div>

    <!-- Spinner Global -->
    <script src="../../views/partials/spinner-global.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
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
                .then(function (response) {
                    return response.json();
                })
                .then(function (json) {
                    if (!json || json.success !== true) {
                        showAlert('error', json && json.message ? json.message : 'No se pudieron obtener los operativos.');
                        return;
                    }
                    renderOperativos(json.data || []);
                })
                .catch(function (error) {
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

            operativos.forEach(function (op) {
                const card = document.createElement('div');
                card.className = 'card operativo-card';

                const estado = op.estado || '';
                const diasRestantes = (op.dias_restantes !== null && op.dias_restantes !== undefined)
                    ? op.dias_restantes
                    : '-';

                const estadoClase = obtenerClaseEstado(estado);

                card.innerHTML = `
                    <h4>${escapeHtml(op.nombre || '')}</h4>
                    <p><strong>Apertura:</strong> ${formatearFecha(op.fecha_apertura)}</p>
                    <p><strong>Cierre:</strong> ${formatearFecha(op.fecha_cierre)}</p>
                    <p><strong>Estado:</strong> <span class="badge ${estadoClase}">${escapeHtml(estado)}</span></p>
                    <p><strong>Días para cierre:</strong> ${diasRestantes}</p>
                    <div class="form-buttons">
                        <button
                            type="button"
                            class="btn btn-aceptar btn-participar"
                            data-id="${op.id}"
                        >
                            Participar
                        </button>
                    </div>
                `;

                contenedor.appendChild(card);
            });

            const botonesParticipar = contenedor.querySelectorAll('.btn-participar');
            botonesParticipar.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const contratoId = this.getAttribute('data-id');
                    if (contratoId) {
                        abrirParticipacionModal(contratoId);
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
            // fechaIso viene como 'YYYY-MM-DD'
            const partes = fechaIso.split('-');
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

        function abrirParticipacionModal(contratoId) {
            const url = '../../controllers/coop_cosechaMecanicaController.php?action=obtener_operativo&id=' + encodeURIComponent(contratoId);

            fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (json) {
                    if (!json || json.success !== true || !json.data) {
                        showAlert('error', json && json.message ? json.message : 'No se pudo obtener la información del operativo.');
                        return;
                    }

                    const op = json.data;

                    const modal = document.getElementById('participacionModal');
                    if (!modal) return;

                    const spanId = document.getElementById('modalContratoId');
                    const spanNombre = document.getElementById('modalNombre');
                    const spanFechaApertura = document.getElementById('modalFechaApertura');
                    const spanFechaCierre = document.getElementById('modalFechaCierre');
                    const spanEstado = document.getElementById('modalEstado');
                    const spanDescripcion = document.getElementById('modalDescripcion');

                    if (spanId) spanId.textContent = op.id;
                    if (spanNombre) spanNombre.textContent = op.nombre || '';
                    if (spanFechaApertura) spanFechaApertura.textContent = formatearFecha(op.fecha_apertura);
                    if (spanFechaCierre) spanFechaCierre.textContent = formatearFecha(op.fecha_cierre);
                    if (spanEstado) spanEstado.textContent = op.estado || '';
                    if (spanDescripcion) spanDescripcion.textContent = op.descripcion || '';

                    inicializarTablaParticipacion();

                    modal.classList.remove('hidden');
                })
                .catch(function (error) {
                    console.error('Error al obtener operativo:', error);
                    showAlert('error', 'Error de conexión al obtener la información del operativo.');
                });
        }

        function cerrarParticipacionModal() {
            const modal = document.getElementById('participacionModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        function inicializarTablaParticipacion() {
            const tbody = document.getElementById('participacionBody');
            if (!tbody) return;

            tbody.innerHTML = '';
            agregarFilaParticipacion();
        }

        function agregarFilaParticipacion() {
            const tbody = document.getElementById('participacionBody');
            if (!tbody) return;

            const fila = document.createElement('tr');
            fila.innerHTML = `
                <td><input type="text" name="productor[]" placeholder="Productor" /></td>
                <td><input type="number" step="0.01" name="superficie[]" placeholder="Ha" /></td>
                <td><input type="text" name="variedad[]" placeholder="Variedad" /></td>
                <td><input type="number" step="0.01" name="prod_estimada[]" placeholder="Prod. estimada" /></td>
                <td><input type="date" name="fecha_estimada[]" /></td>
                <td><input type="number" step="0.01" name="km_finca[]" placeholder="Km finca" /></td>
                <td>
                    <select name="flete[]">
                        <option value="0">No</option>
                        <option value="1">Sí</option>
                    </select>
                </td>
                <td>
                    <button type="button" class="btn btn-cancelar btn-sm" onclick="eliminarFilaParticipacion(this)">Eliminar</button>
                </td>
            `;
            tbody.appendChild(fila);
        }

        function eliminarFilaParticipacion(btn) {
            if (!btn) return;
            const fila = btn.closest('tr');
            if (fila) {
                fila.remove();
            }
        }

        // Exponer funciones de modal al ámbito global para los onclick del modal
        window.cerrarParticipacionModal = cerrarParticipacionModal;
        window.agregarFilaParticipacion = agregarFilaParticipacion;
    </script>

</body>

</html>
