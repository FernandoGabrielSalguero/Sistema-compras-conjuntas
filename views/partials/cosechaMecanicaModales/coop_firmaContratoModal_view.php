<div id="firmaContratoModal" class="modal hidden">
    <div class="modal-content">
        <h3>Contrato de Cosecha Mecánica</h3>

        <div class="card">
            <h4 id="firmaContratoNombre"></h4>
            <p><strong>ID contrato:</strong> <span id="firmaContratoId"></span></p>
            <p><strong>Estado:</strong> <span id="firmaContratoEstado"></span></p>
            <p><strong>Fecha de apertura:</strong> <span id="firmaContratoFechaApertura"></span></p>
            <p><strong>Fecha de cierre:</strong> <span id="firmaContratoFechaCierre"></span></p>
        </div>

        <div class="card">
            <h4>Detalle del contrato</h4>
            <div id="firmaContratoDescripcion" class="descripcion-contrato"></div>
        </div>

        <div class="form-buttons" id="firmaContratoAcciones">
            <button type="button" class="btn btn-aceptar" id="btnFirmarContrato">
                Firmar en conformidad
            </button>
            <button type="button" class="btn btn-cancelar" onclick="cerrarContratoModal()">
                Cerrar
            </button>
        </div>

        <div class="form-buttons hidden" id="firmaContratoCerrarSolo">
            <button type="button" class="btn btn-cancelar" onclick="cerrarContratoModal()">
                Cerrar
            </button>
        </div>
    </div>
</div>

<style>
    #firmaContratoModal .modal-content {
        width: 80vw;
        max-width: 900px;
        max-height: 80vh;
        overflow-y: auto;
    }

    #firmaContratoModal .descripcion-contrato {
        margin-top: 0.5rem;
        white-space: pre-wrap;
    }
</style>

<script>
    let contratoModalOperativoId = null;
    let contratoModalFirmado = false;

    function abrirContratoModal(contratoId) {
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
                    showAlert('error', json && json.message ? json.message : 'No se pudo obtener el contrato.');
                    return;
                }

                const data = json.data;
                const op = data.operativo || data;
                const contratoFirmado = data.contrato_firmado === true ||
                    data.contrato_firmado === 1 ||
                    data.contrato_firmado === '1';

                contratoModalOperativoId = op.id;
                contratoModalFirmado = contratoFirmado;

                const modal = document.getElementById('firmaContratoModal');
                if (!modal) return;

                const spanId = document.getElementById('firmaContratoId');
                const spanNombre = document.getElementById('firmaContratoNombre');
                const spanFechaApertura = document.getElementById('firmaContratoFechaApertura');
                const spanFechaCierre = document.getElementById('firmaContratoFechaCierre');
                const spanEstado = document.getElementById('firmaContratoEstado');
                const divDescripcion = document.getElementById('firmaContratoDescripcion');
                const acciones = document.getElementById('firmaContratoAcciones');
                const cerrarSolo = document.getElementById('firmaContratoCerrarSolo');
                const btnFirmar = document.getElementById('btnFirmarContrato');

                if (spanId) spanId.textContent = op.id;
                if (spanNombre) spanNombre.textContent = op.nombre || '';
                if (spanFechaApertura) spanFechaApertura.textContent = formatearFechaModal(op.fecha_apertura);
                if (spanFechaCierre) spanFechaCierre.textContent = formatearFechaModal(op.fecha_cierre);
                if (spanEstado) spanEstado.textContent = op.estado || '';
                if (divDescripcion) divDescripcion.innerHTML = op.descripcion || '';

                if (contratoFirmado) {
                    if (acciones) acciones.classList.add('hidden');
                    if (cerrarSolo) cerrarSolo.classList.remove('hidden');
                } else {
                    if (acciones) acciones.classList.remove('hidden');
                    if (cerrarSolo) cerrarSolo.classList.add('hidden');
                    if (btnFirmar) btnFirmar.disabled = false;
                }

                modal.classList.remove('hidden');
            })
            .catch(function(error) {
                console.error('Error al obtener contrato:', error);
                showAlert('error', 'Error de conexión al obtener el contrato.');
            });
    }

    function cerrarContratoModal() {
        const modal = document.getElementById('firmaContratoModal');
        if (modal) {
            modal.classList.add('hidden');
        }
        contratoModalOperativoId = null;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const btnFirmar = document.getElementById('btnFirmarContrato');
        if (btnFirmar) {
            btnFirmar.addEventListener('click', function() {
                if (!contratoModalOperativoId) {
                    showAlert('error', 'No se encontró el ID del contrato.');
                    return;
                }

                const url = '../../controllers/coop_cosechaMecanicaController.php';
                const formData = new FormData();
                formData.append('action', 'firmar_contrato');
                formData.append('contrato_id', String(contratoModalOperativoId));

                btnFirmar.disabled = true;

                fetch(url, {
                        method: 'POST',
                        body: formData
                    })
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(json) {
                        if (!json || json.success !== true) {
                            showAlert('error', json && json.message ? json.message : 'No se pudo firmar el contrato.');
                            btnFirmar.disabled = false;
                            return;
                        }

                        showAlert('success', json.message || 'Contrato firmado correctamente.');
                        contratoModalFirmado = true;
                        cerrarContratoModal();

                        // Actualizar listado para que aparezca el botón "Inscribir productores"
                        if (typeof cargarOperativos === 'function') {
                            cargarOperativos();
                        }
                    })
                    .catch(function(error) {
                        console.error('Error al firmar contrato:', error);
                        showAlert('error', 'Error de conexión al firmar el contrato.');
                        btnFirmar.disabled = false;
                    });
            });
        }
    });

    // Reutilizamos formateo de fecha del otro modal
    function formatearFechaModal(fechaIso) {
        if (!fechaIso) return '-';
        const partes = fechaIso.split('-'); // 'YYYY-MM-DD'
        if (partes.length !== 3) return fechaIso;
        return partes[2] + '/' + partes[1] + '/' + partes[0];
    }

    // Exponer funciones al ámbito global
    window.abrirContratoModal = abrirContratoModal;
    window.cerrarContratoModal = cerrarContratoModal;
</script>
