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
        document.addEventListener('DOMContentLoaded', function() {
            cargarOperativos();

            const chkContrato = document.getElementById('aceptaContratoCheckbox');
            if (chkContrato) {
                chkContrato.addEventListener('change', function() {
                    contratoAceptado = this.checked;
                    actualizarEstadoEdicionParticipacion();
                });
            }

            const btnToggleContrato = document.getElementById('toggleContratoDetalle');
            const bodyContrato = document.getElementById('accordionContratoBody');
            if (btnToggleContrato && bodyContrato) {
                btnToggleContrato.addEventListener('click', function() {
                    if (bodyContrato.classList.contains('hidden')) {
                        bodyContrato.classList.remove('hidden');
                    } else {
                        bodyContrato.classList.add('hidden');
                    }
                });
            }
        });

        let filaParticipacionIndex = 0;
        let productoresCoop = [];
        let anioOperativoActivo = (new Date()).getFullYear();
        let contratoAceptado = false;

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
            botonesParticipar.forEach(function(btn) {
                btn.addEventListener('click', function() {
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
                .then(function(response) {
                    return response.json();
                })
                .then(function(json) {
                    if (!json || json.success !== true || !json.data) {
                        showAlert('error', json && json.message ? json.message : 'No se pudo obtener la información del operativo.');
                        return;
                    }

                    const data = json.data;
                    const op = data.operativo || data;
                    const participaciones = Array.isArray(data.participaciones) ? data.participaciones : [];
                    contratoAceptado = data.contrato_firmado === true;

                    const modal = document.getElementById('participacionModal');
                    if (!modal) return;

                    const spanId = document.getElementById('modalContratoId');
                    const spanNombre = document.getElementById('modalNombre');
                    const spanFechaApertura = document.getElementById('modalFechaApertura');
                    const spanFechaCierre = document.getElementById('modalFechaCierre');
                    const spanEstado = document.getElementById('modalEstado');
                    const spanDescripcion = document.getElementById('modalDescripcion');
                    const chkContrato = document.getElementById('aceptaContratoCheckbox');
                    const bodyContrato = document.getElementById('accordionContratoBody');

                    if (spanId) spanId.textContent = op.id;
                    if (spanNombre) spanNombre.textContent = op.nombre || '';
                    if (spanFechaApertura) spanFechaApertura.textContent = formatearFecha(op.fecha_apertura);
                    if (spanFechaCierre) spanFechaCierre.textContent = formatearFecha(op.fecha_cierre);
                    if (spanEstado) spanEstado.textContent = op.estado || '';
                    if (spanDescripcion) spanDescripcion.innerHTML = op.descripcion || '';

                    if (chkContrato) {
                        chkContrato.checked = contratoAceptado;
                    }

                    if (bodyContrato && !bodyContrato.classList.contains('hidden')) {
                        // lo dejamos como esté (visible u oculto) según último estado
                    }

                    anioOperativoActivo = obtenerAnioDesdeOperativo(op);
                    inicializarTablaParticipacion(participaciones);
                    cargarProductores();
                    actualizarEstadoEdicionParticipacion();

                    modal.classList.remove('hidden');
                })
                .catch(function(error) {
                    console.error('Error al obtener operativo:', error);
                    showAlert('error', 'Error de conexión al obtener la información del operativo.');
                });
        }

        function cerrarParticipacionModal() {
            const modal = document.getElementById('participacionModal');
            if (modal) {
                modal.classList.add('hidden');
            }
            contratoAceptado = false;
            const chkContrato = document.getElementById('aceptaContratoCheckbox');
            if (chkContrato) {
                chkContrato.checked = false;
            }
            actualizarEstadoEdicionParticipacion();
        }

        function inicializarTablaParticipacion(participaciones) {
            const tbody = document.getElementById('participacionBody');
            if (!tbody) return;

            tbody.innerHTML = '';
            filaParticipacionIndex = 0;

            if (Array.isArray(participaciones) && participaciones.length > 0) {
                participaciones.forEach(function(p) {
                    agregarFilaParticipacion(p);
                });
            } else {
                agregarFilaParticipacion();
            }
        }

        function agregarFilaParticipacion(datos) {
            const tbody = document.getElementById('participacionBody');
            if (!tbody) return;

            filaParticipacionIndex++;
            const indice = filaParticipacionIndex;

            const fila = document.createElement('tr');
            fila.innerHTML = `
                <td>
                    <div class="input-group">
                        <div class="input-icon input-icon-name">
                            <input
                                type="text"
                                id="productor_${indice}"
                                name="productor[]"
                                list="productoresDatalist"
                                placeholder="Productor"
                            />
                        </div>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <div class="input-icon input-icon-name">
                            <input
                                type="number"
                                step="0.01"
                                id="superficie_${indice}"
                                name="superficie[]"
                                placeholder="Ha"
                            />
                        </div>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <div class="input-icon input-icon-name">
                            <input
                                type="text"
                                id="variedad_${indice}"
                                name="variedad[]"
                                placeholder="Variedad"
                            />
                        </div>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <div class="input-icon input-icon-name">
                            <input
                                type="number"
                                step="0.01"
                                id="prod_estimada_${indice}"
                                name="prod_estimada[]"
                                placeholder="Prod. estimada"
                            />
                        </div>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <div class="input-icon input-icon-name">
                            <select id="fecha_estimada_${indice}" name="fecha_estimada[]">
                                ${getQuincenasOptionsHtml()}
                            </select>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <div class="input-icon input-icon-name">
                            <input
                                type="number"
                                step="0.01"
                                id="km_finca_${indice}"
                                name="km_finca[]"
                                placeholder="Km finca"
                            />
                        </div>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <select id="flete_${indice}" name="flete[]" class="select-standard">
                            <option value="0">No</option>
                            <option value="1">Sí</option>
                        </select>
                    </div>
                </td>
                <td>
                    <button type="button" class="btn btn-cancelar btn-sm" onclick="eliminarFilaParticipacion(this)">Eliminar</button>
                </td>
            `;

            tbody.appendChild(fila);

            // Setear valores si vienen desde la BD
            if (datos && typeof datos === 'object') {
                const productorInput = fila.querySelector(`#productor_${indice}`);
                const superficieInput = fila.querySelector(`#superficie_${indice}`);
                const variedadInput = fila.querySelector(`#variedad_${indice}`);
                const prodEstimadaInput = fila.querySelector(`#prod_estimada_${indice}`);
                const fechaSelect = fila.querySelector(`#fecha_estimada_${indice}`);
                const kmFincaInput = fila.querySelector(`#km_finca_${indice}`);
                const fleteSelect = fila.querySelector(`#flete_${indice}`);

                if (productorInput) productorInput.value = datos.productor || '';
                if (superficieInput) superficieInput.value = datos.superficie !== undefined ? datos.superficie : '';
                if (variedadInput) variedadInput.value = datos.variedad || '';
                if (prodEstimadaInput) prodEstimadaInput.value = datos.prod_estimada !== undefined ? datos.prod_estimada : '';
                if (fechaSelect && datos.fecha_estimada) fechaSelect.value = datos.fecha_estimada;
                if (kmFincaInput) kmFincaInput.value = datos.km_finca !== undefined ? datos.km_finca : '';
                if (fleteSelect && datos.flete !== undefined) fleteSelect.value = String(datos.flete);
            }

            actualizarEstadoEdicionParticipacion();
        }


        function actualizarEstadoEdicionParticipacion() {
            const tbody = document.getElementById('participacionBody');
            const btnAgregar = document.getElementById('btnAgregarFilaParticipacion');
            const btnGuardar = document.getElementById('btnGuardarParticipacion');
            const estadoFirmaSpan = document.getElementById('estadoFirmaTexto');
            const cardTabla = document.getElementById('tablaParticipacionCard');

            const inputs = tbody ? tbody.querySelectorAll('input, select') : [];

            inputs.forEach(function(input) {
                input.disabled = !contratoAceptado;
            });

            if (btnAgregar) {
                btnAgregar.disabled = !contratoAceptado;
            }

            if (btnGuardar) {
                btnGuardar.disabled = !contratoAceptado;
            }

            // Mostrar/ocultar la card de la tabla completa
            if (cardTabla) {
                if (contratoAceptado) {
                    cardTabla.classList.remove('hidden');
                } else {
                    cardTabla.classList.add('hidden');
                }
            }

            if (estadoFirmaSpan) {
                estadoFirmaSpan.textContent = contratoAceptado ? 'Firmado' : 'No firmado';
                estadoFirmaSpan.classList.toggle('firmado', contratoAceptado);
                estadoFirmaSpan.classList.toggle('no-firmado', !contratoAceptado);
            }
        }



        function getQuincenasOptionsHtml() {
            const year = anioOperativoActivo;
            return `
                <option value="${year}-01-01">Primera quincena de enero</option>
                <option value="${year}-01-16">Segunda quincena de enero</option>
                <option value="${year}-02-01">Primera quincena de febrero</option>
                <option value="${year}-02-16">Segunda quincena de febrero</option>
                <option value="${year}-03-01">Primera quincena de marzo</option>
                <option value="${year}-03-16">Segunda quincena de marzo</option>
                <option value="${year}-04-01">Primera quincena de abril</option>
                <option value="${year}-04-16">Segunda quincena de abril</option>
                <option value="${year}-05-01">Primera quincena de mayo</option>
                <option value="${year}-05-16">Segunda quincena de mayo</option>
                <option value="${year}-06-01">Primera quincena de junio</option>
                <option value="${year}-06-16">Segunda quincena de junio</option>
                <option value="${year}-07-01">Primera quincena de julio</option>
                <option value="${year}-07-16">Segunda quincena de julio</option>
                <option value="${year}-08-01">Primera quincena de agosto</option>
                <option value="${year}-08-16">Segunda quincena de agosto</option>
                <option value="${year}-09-01">Primera quincena de septiembre</option>
                <option value="${year}-09-16">Segunda quincena de septiembre</option>
                <option value="${year}-10-01">Primera quincena de octubre</option>
                <option value="${year}-10-16">Segunda quincena de octubre</option>
                <option value="${year}-11-01">Primera quincena de noviembre</option>
                <option value="${year}-11-16">Segunda quincena de noviembre</option>
                <option value="${year}-12-01">Primera quincena de diciembre</option>
                <option value="${year}-12-16">Segunda quincena de diciembre</option>
            `;
        }

        function obtenerAnioDesdeOperativo(op) {
            if (op && op.fecha_cierre && /^\d{4}/.test(op.fecha_cierre)) {
                return parseInt(op.fecha_cierre.substring(0, 4), 10);
            }
            if (op && op.fecha_apertura && /^\d{4}/.test(op.fecha_apertura)) {
                return parseInt(op.fecha_apertura.substring(0, 4), 10);
            }
            return (new Date()).getFullYear();
        }

        function cargarProductores() {
            const url = '../../controllers/coop_cosechaMecanicaController.php?action=listar_productores';

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
                        showAlert('error', json && json.message ? json.message : 'No se pudieron obtener los productores.');
                        return;
                    }
                    productoresCoop = Array.isArray(json.data) ? json.data : [];
                    actualizarDatalistProductores();
                })
                .catch(function(error) {
                    console.error('Error al obtener productores:', error);
                    showAlert('error', 'Error de conexión al obtener los productores.');
                });
        }

        function actualizarDatalistProductores() {
            const dataList = document.getElementById('productoresDatalist');
            if (!dataList) return;

            dataList.innerHTML = '';

            productoresCoop.forEach(function(prod) {
                const option = document.createElement('option');
                option.value = prod.nombre || '';
                option.setAttribute('data-id-real', prod.id_real || '');
                dataList.appendChild(option);
            });
        }

        function guardarParticipacion() {
            const spanId = document.getElementById('modalContratoId');
            const contratoId = spanId ? parseInt(spanId.textContent, 10) : 0;

            if (!contratoId) {
                showAlert('error', 'No se encontró el ID del contrato.');
                return;
            }

            const tbody = document.getElementById('participacionBody');
            if (!tbody) {
                showAlert('error', 'No se encontró la tabla de participación.');
                return;
            }

            const filasDom = tbody.querySelectorAll('tr');
            const filas = [];

            filasDom.forEach(function(row) {
                const productorInput = row.querySelector('input[name="productor[]"]');
                const superficieInput = row.querySelector('input[name="superficie[]"]');
                const variedadInput = row.querySelector('input[name="variedad[]"]');
                const prodEstimadaInput = row.querySelector('input[name="prod_estimada[]"]');
                const fechaSelect = row.querySelector('select[name="fecha_estimada[]"]');
                const kmFincaInput = row.querySelector('input[name="km_finca[]"]');
                const fleteSelect = row.querySelector('select[name="flete[]"]');

                const productor = productorInput ? productorInput.value.trim() : '';

                if (!productor) {
                    return;
                }

                filas.push({
                    productor: productor,
                    superficie: superficieInput ? superficieInput.value : '',
                    variedad: variedadInput ? variedadInput.value : '',
                    prod_estimada: prodEstimadaInput ? prodEstimadaInput.value : '',
                    fecha_estimada: fechaSelect ? fechaSelect.value : '',
                    km_finca: kmFincaInput ? kmFincaInput.value : '',
                    flete: fleteSelect ? fleteSelect.value : '0'
                });
            });

            const url = '../../controllers/coop_cosechaMecanicaController.php';
            const formData = new FormData();
            formData.append('action', 'guardar_participacion');
            formData.append('contrato_id', String(contratoId));
            formData.append('filas', JSON.stringify(filas));

            fetch(url, {
                    method: 'POST',
                    body: formData
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(json) {
                    if (!json || json.success !== true) {
                        showAlert('error', json && json.message ? json.message : 'No se pudo guardar la participación.');
                        return;
                    }

                    showAlert('success', json.message || 'Participación guardada correctamente.');
                    cerrarParticipacionModal();
                })
                .catch(function(error) {
                    console.error('Error al guardar participación:', error);
                    showAlert('error', 'Error de conexión al guardar la participación.');
                });
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
        window.guardarParticipacion = guardarParticipacion;
    </script>

</body>

</html>