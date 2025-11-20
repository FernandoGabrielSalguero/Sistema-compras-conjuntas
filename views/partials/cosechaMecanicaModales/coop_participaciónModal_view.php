<div id="participacionModal" class="modal hidden">
    <div class="modal-content">
        <h3>Participar en operativo de Cosecha Mecánica</h3>

        <div class="card">
            <div class="accordion-header" id="accordionContratoHeader">
                <h4>Información del operativo</h4>
                <button type="button" class="btn btn-info btn-sm" id="toggleContratoDetalle">
                    Ver / ocultar contrato
                </button>
            </div>

            <div id="accordionContratoBody" class="accordion-body">
                <div class="operativo-info-grid">
                    <div>
                        <p><strong>ID contrato:</strong> <span id="modalContratoId"></span></p>
                        <p><strong>Estado:</strong> <span id="modalEstado"></span></p>
                    </div>
                    <div>
                        <p><strong>Nombre:</strong> <span id="modalNombre"></span></p>
                        <p><strong>Fecha de apertura:</strong> <span id="modalFechaApertura"></span></p>
                    </div>
                    <div>
                        <p><strong>Fecha de cierre:</strong> <span id="modalFechaCierre"></span></p>
                    </div>
                </div>

                <p><strong>Descripción:</strong></p>
                <div id="modalDescripcion" class="descripcion-contrato"></div>

                <div class="firma-contrato-aviso">
                    <label class="checkbox-firma">
                        <input type="checkbox" id="aceptaContratoCheckbox">
                        <span>
                            Acepto los términos del contrato y firmo digitalmente en representación de los productores que cargue en la tabla.
                        </span>
                    </label>
                    <small>
                        La firma queda asociada a esta cooperativa y a este operativo de cosecha mecánica.
                    </small>
                </div>
            </div>
        </div>

        <div class="card tabla-card" id="tablaParticipacionCard">
            <h4>Productores participantes</h4>
            <p>Cargá los productores que van a participar en este operativo.</p>
            <p class="aviso-aceptacion-contrato">
                Al cargar o modificar productores estás aceptando los términos del contrato en representación de ellos.
            </p>
            <p class="estado-firma-contrato">
                Estado del contrato: <span id="estadoFirmaTexto" class="no-firmado">No firmado</span>
            </p>


            <div class="tabla-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Productor</th>
                            <th>Superficie (ha)</th>
                            <th>Variedad</th>
                            <th>Prod. estimada</th>
                            <th>Fecha estimada</th>
                            <th>Km a finca</th>
                            <th>Flete</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="participacionBody">
                        <!-- Filas agregadas dinámicamente por JS -->
                    </tbody>
                </table>
            </div>

            <datalist id="productoresDatalist">
                <!-- Opciones de productores agregadas dinámicamente por JS -->
            </datalist>

            <div class="form-buttons" style="margin-top: 1rem;">
                <button type="button" id="btnAgregarFilaParticipacion" class="btn btn-info" onclick="agregarFilaParticipacion()">
                    Agregar fila
                </button>
            </div>
        </div>

        <div class="form-buttons">
            <button type="button" id="btnGuardarParticipacion" class="btn btn-aceptar" onclick="guardarParticipacion()">
                Guardar participación
            </button>
            <button type="button" class="btn btn-cancelar" onclick="cerrarParticipacionModal()">
                Cerrar
            </button>
        </div>

    </div>
</div>

<style>
    /* Ajustes específicos para este modal de participación */
    #participacionModal .modal-content {
        width: 90vw;
        max-width: 1200px;
        height: 80vh;
        max-height: 80vh;
        overflow-y: auto;
    }


    .operativos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .operativo-card h4 {
        margin-bottom: 0.5rem;
    }

    .operativo-card p {
        margin: 0.25rem 0;
    }

    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
    }

    .operativo-info-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
        margin-top: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .operativo-info-grid p {
        margin: 0.25rem 0;
    }

    .descripcion-contrato {
        margin-top: 0.5rem;
        white-space: pre-wrap;
    }

    .tabla-card .data-table th,
    .tabla-card .data-table td {
        min-width: 140px;
        vertical-align: top;
    }

    .tabla-card .input-group {
        width: 100%;
    }

    .tabla-card .input-group .input-icon,
    .tabla-card .input-group input,
    .tabla-card .input-group select,
    .tabla-card .select-standard {
        width: 100%;
        box-sizing: border-box;
    }

    .accordion-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
    }

    .accordion-body {
        margin-top: 0.75rem;
    }

    .firma-contrato-aviso {
        margin-top: 0.75rem;
        padding-top: 0.5rem;
        border-top: 1px solid #e5e7eb;
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .checkbox-firma {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        font-size: 0.9rem;
    }

    .checkbox-firma input[type="checkbox"] {
        margin-top: 0.2rem;
    }

    .firma-contrato-aviso small {
        font-size: 0.8rem;
        color: #6b7280;
    }

    .estado-firma-contrato {
        margin-top: 0.25rem;
        font-size: 0.9rem;
    }

    .estado-firma-contrato .firmado {
        font-weight: 600;
        color: #16a34a;
    }

    .estado-firma-contrato .no-firmado {
        font-weight: 600;
        color: #dc2626;
    }

    .aviso-aceptacion-contrato {
        margin-top: 0.5rem;
        font-size: 0.9rem;
        color: #4b5563;
    }
</style>

<script>
    // Estado interno del modal de participación
    let filaParticipacionIndex = 0;
    let productoresCoop = [];
    let anioOperativoActivo = (new Date()).getFullYear();
    let contratoAceptado = false;

    document.addEventListener('DOMContentLoaded', function() {
        // Manejo del checkbox de aceptación de contrato
        const chkContrato = document.getElementById('aceptaContratoCheckbox');
        if (chkContrato) {
            chkContrato.addEventListener('change', function() {
                contratoAceptado = this.checked;
                actualizarEstadoEdicionParticipacion();
            });
        }

        // Botón para mostrar/ocultar el detalle del contrato
        const btnToggleContrato = document.getElementById('toggleContratoDetalle');
        const bodyContrato = document.getElementById('accordionContratoBody');
        if (btnToggleContrato && bodyContrato) {
            btnToggleContrato.addEventListener('click', function() {
                bodyContrato.classList.toggle('hidden');
            });
        }
    });

    /**
     * Abre el modal y carga la información del operativo y la participación de la cooperativa.
     * Esta función es llamada desde la vista principal pasando el ID del contrato.
     */
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

                if (spanId) spanId.textContent = op.id;
                if (spanNombre) spanNombre.textContent = op.nombre || '';
                if (spanFechaApertura) spanFechaApertura.textContent = formatearFechaModal(op.fecha_apertura);
                if (spanFechaCierre) spanFechaCierre.textContent = formatearFechaModal(op.fecha_cierre);
                if (spanEstado) spanEstado.textContent = op.estado || '';
                if (spanDescripcion) spanDescripcion.innerHTML = op.descripcion || '';

                if (chkContrato) {
                    chkContrato.checked = contratoAceptado;
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

    function formatearFechaModal(fechaIso) {
        if (!fechaIso) return '-';
        const partes = fechaIso.split('-'); // 'YYYY-MM-DD'
        if (partes.length !== 3) return fechaIso;
        return partes[2] + '/' + partes[1] + '/' + partes[0];
    }

    // Exponer funciones necesarias al ámbito global para los onclick del modal
    window.abrirParticipacionModal = abrirParticipacionModal;
    window.cerrarParticipacionModal = cerrarParticipacionModal;
    window.agregarFilaParticipacion = agregarFilaParticipacion;
    window.guardarParticipacion = guardarParticipacion;
</script>