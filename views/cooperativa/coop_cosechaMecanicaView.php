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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- Framework Success desde CDN -->
    <link rel="stylesheet" href="https://framework.impulsagroup.com/assets/css/framework.css">
    <script src="https://framework.impulsagroup.com/assets/javascript/framework.js" defer></script>

    <!-- Descarga de consolidado -->
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

</head>

<body>

                <!-- 📦 CONTENIDO -->
            <section class="content">

                <!-- Bienvenida -->
                <div class="card">
                    <h4>Hola <?php echo htmlspecialchars($nombre); ?> 👋</h4>
                    <p>En esta página, vas a poder visualizar los servicios disponibles para cosecha mecanizada e inscribir a tus productores</p>
                    <br>
                    <!-- Boton de tutorial
                    <button id="btnIniciarTutorial" class="btn btn-aceptar">
                        Tutorial
                    </button> -->
                </div>

                <!-- Listado de operativos de Cosecha Mecánica -->
                <div class="card">
                    <h4>Operativos de cosecha mecánica</h4>
                    <p>Seleccioná un operativo para ver el detalle y cargar la participación de tus productores.</p>
                    <div id="operativosContainer" class="card-grid">
                        <!-- Las tarjetas se inyectan por JS -->
                    </div>
                </div>

                <!-- Modal de participación -->
                <div id="modalParticipacion" class="modal hidden">
                    <div class="modal-content">
                        <h3 id="modalNombre">Operativo</h3>
                        <p>
                            <strong>Fecha apertura:</strong>
                            <span id="modalFechaApertura"></span>
                        </p>
                        <p>
                            <strong>Fecha cierre:</strong>
                            <span id="modalFechaCierre"></span>
                        </p>
                        <p>
                            <strong>Estado:</strong>
                            <span id="modalEstado"></span>
                        </p>
                        <p>
                            <strong>Descripción:</strong>
                            <span id="modalDescripcion"></span>
                        </p>

                        <!-- Tabla de participación (productor, superficie, etc.) -->
                        <div class="card tabla-card" style="margin-top: 1rem;">
                            <h4>Participación de la cooperativa</h4>
                            <div class="tabla-wrapper">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Productor</th>
                                            <th>Superficie (ha)</th>
                                            <th>Variedad</th>
                                            <th>Prod. estimada (tn)</th>
                                            <th>Fecha estimada</th>
                                            <th>Km a la finca</th>
                                            <th>Flete</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tablaParticipacionBody">
                                        <tr>
                                            <td>
                                                <div class="input-group">
                                                    <div class="input-icon input-icon-name">
                                                        <input type="text" name="productor[]" placeholder="Nombre del productor" />
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <div class="input-icon input-icon-name">
                                                        <input type="number" step="0.01" min="0" name="superficie[]" placeholder="0.00" />
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <div class="input-icon input-icon-name">
                                                        <input type="text" name="variedad[]" placeholder="Variedad" />
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <div class="input-icon input-icon-name">
                                                        <input type="number" step="0.01" min="0" name="prod_estimada[]" placeholder="0.00" />
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <div class="input-icon input-icon-name">
                                                        <input type="date" name="fecha_estimada[]" />
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <div class="input-icon input-icon-name">
                                                        <input type="number" step="0.01" min="0" name="km_finca[]" placeholder="0.00" />
                                                    </div>
                                                </div>
                                            </td>
                                            <td style="text-align: center;">
                                                <input type="checkbox" name="flete[]" value="1" />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="form-buttons" style="margin-top: 1rem;">
                            <button type="button" class="btn btn-info" onclick="agregarFilaParticipacion()">
                                Agregar fila
                            </button>
                            <button type="button" class="btn btn-cancelar" onclick="cerrarModalParticipacion()">
                                Cerrar
                            </button>
                        </div>
                    </div>
                </div>

        </div>

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
                const container = document.getElementById('operativosContainer');
                if (!container) return;

                container.innerHTML = '<p>Cargando operativos...</p>';

                fetch('../../controllers/coop_cosechaMecanicaController.php?action=listar_operativos')
                    .then(response => response.json())
                    .then(json => {
                        if (!json.success) {
                            container.innerHTML = '<p>No se pudieron cargar los operativos.</p>';
                            if (typeof showAlert === 'function') {
                                showAlert('error', json.message || 'No se pudieron cargar los operativos.');
                            }
                            return;
                        }

                        const operativos = json.data || [];
                        if (operativos.length === 0) {
                            container.innerHTML = '<p>No hay operativos disponibles.</p>';
                            return;
                        }

                        container.innerHTML = '';

                        operativos.forEach(op => {
                            const dias = op.dias_restantes;
                            let textoDias = '';

                            if (dias === null || typeof dias === 'undefined') {
                                textoDias = 'Sin información de cierre';
                            } else if (dias <= 0 || op.estado === 'cerrado') {
                                textoDias = 'Operativo cerrado';
                            } else if (dias === 1) {
                                textoDias = 'Cierra en 1 día';
                            } else {
                                textoDias = `Cierra en ${dias} días`;
                            }

                            const card = document.createElement('div');
                            card.className = 'card';

                            card.innerHTML = `
                                <h4>${escapeHtml(op.nombre)}</h4>
                                <p><strong>Fecha apertura:</strong> ${escapeHtml(op.fecha_apertura)}</p>
                                <p><strong>Fecha cierre:</strong> ${escapeHtml(op.fecha_cierre)}</p>
                                <p><strong>Estado:</strong> <span class="badge ${getEstadoBadgeClass(op.estado)}">${escapeHtml(op.estado)}</span></p>
                                <p><strong>${textoDias}</strong></p>
                                <div class="form-buttons">
                                    <button type="button" class="btn btn-aceptar">Participar</button>
                                </div>
                            `;

                            const btn = card.querySelector('button');
                            btn.addEventListener('click', function () {
                                abrirModalParticipacion(op);
                            });

                            container.appendChild(card);
                        });
                    })
                    .catch(err => {
                        console.error(err);
                        container.innerHTML = '<p>No se pudieron cargar los operativos.</p>';
                        if (typeof showAlert === 'function') {
                            showAlert('error', 'No se pudieron cargar los operativos.');
                        }
                    });
            }

            function escapeHtml(text) {
                if (text === null || typeof text === 'undefined') return '';
                return String(text)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function getEstadoBadgeClass(estado) {
                switch (estado) {
                    case 'abierto':
                        return 'success';
                    case 'cerrado':
                        return 'danger';
                    case 'borrador':
                    default:
                        return 'warning';
                }
            }

            function abrirModalParticipacion(operativo) {
                const modal = document.getElementById('modalParticipacion');
                if (!modal) return;

                const nombreEl = document.getElementById('modalNombre');
                const aperturaEl = document.getElementById('modalFechaApertura');
                const cierreEl = document.getElementById('modalFechaCierre');
                const estadoEl = document.getElementById('modalEstado');
                const descEl = document.getElementById('modalDescripcion');

                if (nombreEl) nombreEl.textContent = operativo.nombre || '';
                if (aperturaEl) aperturaEl.textContent = operativo.fecha_apertura || '';
                if (cierreEl) cierreEl.textContent = operativo.fecha_cierre || '';
                if (estadoEl) estadoEl.textContent = operativo.estado || '';
                if (descEl) descEl.textContent = operativo.descripcion || '';

                const tbody = document.getElementById('tablaParticipacionBody');
                if (tbody) {
                    const primeraFila = tbody.querySelector('tr');
                    if (primeraFila) {
                        tbody.innerHTML = '';
                        const nuevaFila = primeraFila.cloneNode(true);
                        nuevaFila.querySelectorAll('input').forEach(input => {
                            if (input.type === 'checkbox') {
                                input.checked = false;
                            } else {
                                input.value = '';
                            }
                        });
                        tbody.appendChild(nuevaFila);
                    }
                }

                modal.classList.remove('hidden');
            }

            function cerrarModalParticipacion() {
                const modal = document.getElementById('modalParticipacion');
                if (!modal) return;
                modal.classList.add('hidden');
            }

            function agregarFilaParticipacion() {
                const tbody = document.getElementById('tablaParticipacionBody');
                if (!tbody) return;

                const filas = tbody.querySelectorAll('tr');
                if (filas.length === 0) return;

                const ultima = filas[filas.length - 1];
                const nueva = ultima.cloneNode(true);

                nueva.querySelectorAll('input').forEach(input => {
                    if (input.type === 'checkbox') {
                        input.checked = false;
                    } else {
                        input.value = '';
                    }
                });

                tbody.appendChild(nueva);
            }
        </script>

        </section>

    </div>
    </div>

</body>

</html>