<div id="modalFacturacion" class="modal hidden" aria-hidden="true">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Datos de facturación</h3>
            <button type="button" class="modal-close-btn" data-close-modal="modalFacturacion" aria-label="Cerrar">
                <span class="material-icons">close</span>
            </button>
        </div>

        <form id="formFacturacion" class="form-modern" novalidate>
            <input type="hidden" id="facturacionParticipacionId" name="participacion_id" value="" />

            <div class="form-grid grid-2">
                <div class="input-group">
                    <label for="facturacionProductor">Productor</label>
                    <div class="input-icon">
                        <input type="text" id="facturacionProductor" name="productor" readonly />
                    </div>
                </div>

                <div class="input-group">
                    <label for="facturacionCuit">CUIT</label>
                    <div class="input-icon">
                        <input type="text" id="facturacionCuit" name="cuit" readonly />
                    </div>
                </div>

                <div class="input-group">
                    <label for="facturacionCooperativa">Cooperativa</label>
                    <div class="input-icon">
                        <input type="text" id="facturacionCooperativa" name="cooperativa" readonly />
                    </div>
                </div>

                <div class="input-group">
                    <label for="facturacionCondicionPago">Condición de pago</label>
                    <div class="input-icon">
                        <select id="facturacionCondicionPago" name="condicion_pago">
                            <option value="">Seleccionar</option>
                            <option value="Descuento por cooperativa">Descuento por cooperativa</option>
                            <option value="E-check">E-check</option>
                            <option value="Transferencia Bancaria">Transferencia Bancaria</option>
                        </select>
                    </div>
                </div>

                <div class="input-group">
                    <label for="facturacionFechaServicio">Fecha del servicio</label>
                    <div class="input-icon">
                        <input type="date" id="facturacionFechaServicio" name="fecha_servicio" />
                    </div>
                </div>

                <div class="input-group">
                    <label for="facturacionBonificacion">Bonificación por aptitud de finca</label>
                    <div class="input-icon">
                        <input type="text" id="facturacionBonificacion" name="bonificacion_aptitud_finca" readonly />
                    </div>
                </div>

                <div class="input-group">
                    <label for="facturacionHectareasCosechadas">Hectáreas cosechadas</label>
                    <div class="input-icon">
                        <input type="number" id="facturacionHectareasCosechadas" name="hectareas_cosechadas" min="0" step="0.01" inputmode="decimal" />
                    </div>
                </div>

                <div class="input-group">
                    <label for="facturacionHectareasAnticipadas">Hectáreas anticipadas</label>
                    <div class="input-icon">
                        <input type="number" id="facturacionHectareasAnticipadas" name="hectareas_anticipadas" min="0" step="0.01" inputmode="decimal" />
                    </div>
                </div>
            </div>
        </form>

        <div class="modal-footer">
            <button type="button" class="btn btn-aceptar" id="btnGuardarFacturacion">Guardar</button>
            <button type="button" class="btn btn-cancelar" data-close-modal="modalFacturacion">Cerrar</button>
        </div>
    </div>
</div>
