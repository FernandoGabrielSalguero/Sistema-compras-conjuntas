<div id="participacionModal" class="modal hidden">
    <div class="modal-content">
        <h3>Inscribir productores en operativo de Cosecha Mecánica</h3>
<br>
        <!-- ID de contrato (oculto) para guardar participación -->
        <span id="modalContratoId" class="hidden"></span>

        <div class="card tabla-card" id="tablaParticipacionCard">
            <h4>Productores participantes</h4>

            <p>Cargá los productores que van a participar en este operativo.</p>

            <div class="tabla-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Productor</th>
                            <th>Superficie<br>(ha)</th>
                            <th>Variedad</th>
                            <th>Finca</th>
                            <th>Fecha<br>estimada</th>
                            <th>Km<br>a finca</th>
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
        width: 95vw;
        max-width: 1400px;
        /* Ancho del modal */
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

    .operativo-info-grid p {
        margin: 0.25rem 0;
    }

    .tabla-card .data-table th,
    .tabla-card .data-table td {
        min-width: 220px;      /* más ancho para ver el texto completo */
        vertical-align: top;
        white-space: normal;   /* permite saltos de línea en el contenido */
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

    /* Que los selects se vean como los inputs redondeados */
    .tabla-card .input-group .input-icon select,
    .tabla-card .input-group select.select-standard {
        border-radius: 9999px;
        border: 1px solid #d1d5db;
        padding: 0.5rem 0.75rem;
        height: 38px;
        background-color: #fff;
    }

    .checkbox-firma input[type="checkbox"] {
        margin-top: 0.2rem;
    }

    .firma-contrato-aviso small {
        font-size: 0.8rem;
        color: #6b7280;
    }

    .estado-firma-contrato .firmado {
        font-weight: 600;
        color: #16a34a;
    }

    .estado-firma-contrato .no-firmado {
        font-weight: 600;
        color: #dc2626;
    }
</style>

<script>
    // Estado interno del modal de participación
    let filaParticipacionIndex = 0;
    let productoresCoop = [];
    let productorFincasCache = {};
    let anioOperativoActivo = (new Date()).getFullYear();

    document.addEventListener('DOMContentLoaded', function() {

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

                // Por seguridad, si el contrato no está firmado, no permitimos abrir el modal
                if (!data.contrato_firmado) {
                    showAlert('error', 'Debés firmar el contrato antes de inscribir productores.');
                    return;
                }

                const modal = document.getElementById('participacionModal');
                if (!modal) return;

                const spanId = document.getElementById('modalContratoId');
                if (spanId) {
                    spanId.textContent = op.id;
                }

                anioOperativoActivo = obtenerAnioDesdeOperativo(op);

                // Primero cargamos productores, luego armamos la tabla y abrimos el modal
                cargarProductores(function() {
                    inicializarTablaParticipacion(participaciones);
                    modal.classList.remove('hidden');
                });


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
                        <select id="finca_${indice}" name="finca[]" class="select-standard">
                            <option value="">Seleccionar finca</option>
                        </select>
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
                    <div class="input-icon input-icon-name">
                        <select id="flete_${indice}" name="flete[]" class="select-standard">
                            <option value="0">No</option>
                            <option value="1">Sí</option>
                        </select>
                    </div>
                </div>
            </td>
            <td>
                <button type="button" class="btn btn-cancelar btn-sm" onclick="eliminarFilaParticipacion(this)">Eliminar</button>
            </td>
        `;

        tbody.appendChild(fila);

        const productorInput = fila.querySelector(`#productor_${indice}`);
        const fincaSelect = fila.querySelector(`#finca_${indice}`);

        if (productorInput && fincaSelect) {
            productorInput.addEventListener('change', function() {
                const nombreSeleccionado = this.value.trim();
                const productor = productoresCoop.find(function(p) {
                    return (p.nombre || '').trim() === nombreSeleccionado;
                });

                if (!productor) {
                    poblarSelectFincas(fincaSelect, [], null);
                    return;
                }

                cargarFincasParaProductor(productor.id_real, fincaSelect, null);
            });
        }

        // Setear valores si vienen desde la BD (incluyendo finca_id)
        if (datos && typeof datos === 'object') {
            const superficieInput = fila.querySelector(`#superficie_${indice}`);
            const variedadInput = fila.querySelector(`#variedad_${indice}`);
            const fechaSelect = fila.querySelector(`#fecha_estimada_${indice}`);
            const kmFincaInput = fila.querySelector(`#km_finca_${indice}`);
            const fleteSelect = fila.querySelector(`#flete_${indice}`);

            const productorNombre = datos.productor || '';

            if (productorInput) productorInput.value = productorNombre;
            if (superficieInput) superficieInput.value = datos.superficie !== undefined ? datos.superficie : '';
            if (variedadInput) variedadInput.value = datos.variedad || '';
            if (fechaSelect && datos.fecha_estimada) fechaSelect.value = datos.fecha_estimada;
            if (kmFincaInput) kmFincaInput.value = datos.km_finca !== undefined ? datos.km_finca : '';
            if (fleteSelect && datos.flete !== undefined) fleteSelect.value = String(datos.flete);

            // Si tenemos finca_id guardada, la precargamos
            if (fincaSelect && datos.finca_id) {
                const productorObj = productoresCoop.find(function(p) {
                    return (p.nombre || '').trim() === productorNombre.trim();
                });

                if (productorObj && productorObj.id_real) {
                    cargarFincasParaProductor(productorObj.id_real, fincaSelect, datos.finca_id);
                } else {
                    // si no encontramos el productor igual dejamos el select en estado consistente
                    poblarSelectFincas(fincaSelect, [], null);
                }
            }
        }

        actualizarEstadoEdicionParticipacion();
    }

    function actualizarEstadoEdicionParticipacion() {
        // El contrato ya viene firmado desde el otro modal,
        // acá siempre se permite editar mientras el modal esté abierto.
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

    function cargarProductores(callback) {
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
                    if (typeof callback === 'function') {
                        callback();
                    }
                    return;
                }
                productoresCoop = Array.isArray(json.data) ? json.data : [];
                actualizarDatalistProductores();

                if (typeof callback === 'function') {
                    callback();
                }
            })
            .catch(function(error) {
                console.error('Error al obtener productores:', error);
                showAlert('error', 'Error de conexión al obtener los productores.');
                if (typeof callback === 'function') {
                    callback();
                }
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
            const fechaSelect = row.querySelector('select[name="fecha_estimada[]"]');
            const kmFincaInput = row.querySelector('input[name="km_finca[]"]');
            const fleteSelect = row.querySelector('select[name="flete[]"]');
            const fincaSelect = row.querySelector('select[name="finca[]"]');

            const productor = productorInput ? productorInput.value.trim() : '';

            if (!productor) {
                return;
            }

            filas.push({
                productor: productor,
                superficie: superficieInput ? superficieInput.value : '',
                variedad: variedadInput ? variedadInput.value : '',
                fecha_estimada: fechaSelect ? fechaSelect.value : '',
                km_finca: kmFincaInput ? kmFincaInput.value : '',
                flete: fleteSelect ? fleteSelect.value : '0',
                finca_id: fincaSelect ? fincaSelect.value : ''
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

    function cargarFincasParaProductor(productorIdReal, selectElement, selectedFincaId) {
        if (!productorIdReal || !selectElement) return;

        // Cache en memoria para evitar múltiples requests
        if (productorFincasCache[productorIdReal]) {
            poblarSelectFincas(selectElement, productorFincasCache[productorIdReal], selectedFincaId);
            return;
        }

        const url = '../../controllers/coop_cosechaMecanicaController.php?action=listar_fincas_productor&productor_id_real=' +
            encodeURIComponent(productorIdReal);

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
                    showAlert('error', json && json.message ? json.message : 'No se pudieron obtener las fincas del productor.');
                    poblarSelectFincas(selectElement, [], selectedFincaId);
                    return;
                }

                const fincas = Array.isArray(json.data) ? json.data : [];
                productorFincasCache[productorIdReal] = fincas;
                poblarSelectFincas(selectElement, fincas, selectedFincaId);
            })
            .catch(function(error) {
                console.error('Error al obtener fincas del productor:', error);
                showAlert('error', 'Error de conexión al obtener las fincas del productor.');
                poblarSelectFincas(selectElement, [], selectedFincaId);
            });
    }

    function poblarSelectFincas(selectElement, fincas, selectedFincaId) {
        if (!selectElement) return;

        selectElement.innerHTML = '';

        const opcionDefault = document.createElement('option');
        opcionDefault.value = '';
        opcionDefault.textContent = fincas.length ? 'Seleccionar finca' : 'Sin fincas disponibles';
        selectElement.appendChild(opcionDefault);

        fincas.forEach(function(finca) {
            const opt = document.createElement('option');
            opt.value = String(finca.id);
            const nombre = (finca.nombre_finca || '').trim();
            const codigo = (finca.codigo_finca || '').trim();
            opt.textContent = nombre || codigo || ('Finca ' + finca.id);
            if (selectedFincaId && String(selectedFincaId) === String(finca.id)) {
                opt.selected = true;
            }
            selectElement.appendChild(opt);
        });

        if (!selectedFincaId) {
            selectElement.value = '';
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