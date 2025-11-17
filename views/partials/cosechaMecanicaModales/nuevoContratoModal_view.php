<?php

declare(strict_types=1); ?>
<div id="modalNuevoContrato" class="modal hidden" aria-hidden="true" role="dialog" aria-modal="true"
    aria-labelledby="modalNuevoContratoTitulo">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalNuevoContratoTitulo">Nuevo contrato de Cosecha Mecánica</h3>
            <button type="button"
                class="modal-close-btn"
                aria-label="Cerrar"
                data-close-modal="modalNuevoContrato">
                <span class="material-icons">close</span>
            </button>
        </div>

        <form id="formNuevoContrato">
            <div class="form-grid grid-2">
                <div class="input-group">
                    <label for="nuevoNombre">Nombre del contrato</label>
                    <div class="input-icon input-icon-name">
                        <input type="text"
                            id="nuevoNombre"
                            name="nombre"
                            placeholder="Ej: Cosecha 2025 Valle Norte"
                            required />
                    </div>
                </div>

                <div class="input-group">
                    <label for="nuevoEstado">Estado</label>
                    <div class="input-icon input-icon-name">
                        <select id="nuevoEstado" name="estado">
                            <option value="borrador">Borrador</option>
                            <option value="abierto">Abierto</option>
                            <option value="cerrado">Cerrado</option>
                        </select>
                    </div>
                </div>

                <div class="input-group">
                    <label for="nuevoFechaApertura">Fecha apertura</label>
                    <div class="input-icon input-icon-name">
                        <input type="date"
                            id="nuevoFechaApertura"
                            name="fecha_apertura"
                            required />
                    </div>
                </div>

                <div class="input-group">
                    <label for="nuevoFechaCierre">Fecha cierre</label>
                    <div class="input-icon input-icon-name">
                        <input type="date"
                            id="nuevoFechaCierre"
                            name="fecha_cierre"
                            required />
                    </div>
                </div>
            </div>

            <div class="input-group" style="margin-top: 1rem;">
                <label for="nuevoDescripcion">Descripción</label>
                <div class="input-icon input-icon-name">
                    <textarea id="nuevoDescripcion"
                        name="descripcion"
                        placeholder="Detalles generales del contrato (zonas, condiciones, observaciones, etc.)"
                        rows="4"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button"
                    class="btn btn-cancelar"
                    data-close-modal="modalNuevoContrato">
                    Cancelar
                </button>
                <button type="submit" class="btn btn-aceptar">
                    Guardar contrato
                </button>
            </div>
        </form>
    </div>
</div>