<div id="participacionModal" class="modal hidden">
    <div class="modal-content">
        <h3>Participar en operativo de Cosecha Mecánica</h3>

        <div class="card">
            <h4>Información del operativo</h4>
            <p><strong>ID contrato:</strong> <span id="modalContratoId"></span></p>
            <p><strong>Nombre:</strong> <span id="modalNombre"></span></p>
            <p><strong>Fecha de apertura:</strong> <span id="modalFechaApertura"></span></p>
            <p><strong>Fecha de cierre:</strong> <span id="modalFechaCierre"></span></p>
            <p><strong>Estado:</strong> <span id="modalEstado"></span></p>
            <p><strong>Descripción:</strong></p>
            <p id="modalDescripcion"></p>
        </div>

        <div class="card tabla-card">
            <h4>Productores participantes</h4>
            <p>Cargá los productores que van a participar en este operativo.</p>

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

            <div class="form-buttons" style="margin-top: 1rem;">
                <button type="button" class="btn btn-info" onclick="agregarFilaParticipacion()">
                    Agregar fila
                </button>
            </div>
        </div>

        <div class="form-buttons">
            <button type="button" class="btn btn-aceptar"
                onclick="showAlert('info', 'La lógica de guardado se implementará en el siguiente paso.')">
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
        width: 60vw;
        max-width: 1000px;
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
</style>
