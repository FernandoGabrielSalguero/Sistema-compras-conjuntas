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