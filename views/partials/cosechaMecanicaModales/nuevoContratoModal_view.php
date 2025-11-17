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
                            required
                            inputmode="numeric"
                            pattern="\d{4}-\d{2}-\d{2}"
                            placeholder="AAAA-MM-DD" />
                    </div>
                </div>

                <div class="input-group">
                    <label for="nuevoFechaCierre">Fecha cierre</label>
                    <div class="input-icon input-icon-name">
                        <input type="date"
                            id="nuevoFechaCierre"
                            name="fecha_cierre"
                            required
                            inputmode="numeric"
                            pattern="\d{4}-\d{2}-\d{2}"
                            placeholder="AAAA-MM-DD" />
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

                <div class="input-group">
                    <label for="nuevoCostoBase">Costo base</label>
                    <div class="input-icon input-icon-name">
                        <input type="number"
                            id="nuevoCostoBase"
                            name="costo_base"
                            placeholder="Ej: 1500.00"
                            step="0.01"
                            min="0"
                            required />
                    </div>
                </div>

                <div class="input-group">
                    <label for="nuevoBonOptima">Bonificación óptima (%)</label>
                    <div class="input-icon input-icon-name">
                        <input type="number"
                            id="nuevoBonOptima"
                            name="bon_optima"
                            placeholder="Ej: 10.00"
                            step="0.01"
                            min="0" />
                    </div>
                </div>

                <div class="input-group">
                    <label for="nuevoBonMuyBuena">Bonificación muy buena (%)</label>
                    <div class="input-icon input-icon-name">
                        <input type="number"
                            id="nuevoBonMuyBuena"
                            name="bon_muy_buena"
                            placeholder="Ej: 7.50"
                            step="0.01"
                            min="0" />
                    </div>
                </div>

                <div class="input-group">
                    <label for="nuevoBonBuena">Bonificación buena (%)</label>
                    <div class="input-icon input-icon-name">
                        <input type="number"
                            id="nuevoBonBuena"
                            name="bon_buena"
                            placeholder="Ej: 5.00"
                            step="0.01"
                            min="0" />
                    </div>
                </div>

                <div class="input-group">
                    <label for="nuevoAnticipo">Anticipo</label>
                    <div class="input-icon input-icon-name">
                        <input type="number"
                            id="nuevoAnticipo"
                            name="anticipo"
                            placeholder="Ej: 500.00"
                            step="0.01"
                            min="0" />
                    </div>
                </div>
            </div>

                        <div class="input-group" style="margin-top: 1rem;">
                <label for="nuevoDescripcionEditor">Descripción</label>
                <div class="input-icon input-icon-name">
                    <div id="nuevoDescripcionToolbar">
                        <span class="ql-formats">
                            <button class="ql-bold"></button>
                            <button class="ql-underline"></button>
                        </span>
                        <span class="ql-formats">
                            <button class="ql-list" value="ordered"></button>
                            <button class="ql-list" value="bullet"></button>
                            <button class="ql-indent" value="-1"></button>
                            <button class="ql-indent" value="+1"></button>
                        </span>
                    </div>
                    <div id="nuevoDescripcionEditor" style="height: 200px;"></div>
                    <!-- textarea oculta donde se envía el HTML al backend -->
                    <textarea id="nuevoDescripcion"
                        name="descripcion"
                        style="display: none;"></textarea>
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
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">

<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var toolbarOptions = [
        ['bold', 'underline'],
        [{ 'list': 'ordered' }, { 'list': 'bullet' }],
        [{ 'indent': '-1' }, { 'indent': '+1' }]
    ];

    var editorContainer = document.getElementById('nuevoDescripcionEditor');
    if (editorContainer) {
        var quill = new Quill('#nuevoDescripcionEditor', {
            theme: 'snow',
            modules: {
                toolbar: toolbarOptions
            }
        });

        var form = document.getElementById('formNuevoContrato');
        var hiddenTextarea = document.getElementById('nuevoDescripcion');

        if (form && hiddenTextarea) {
            form.addEventListener('submit', function () {
                hiddenTextarea.value = quill.root.innerHTML;
            });
        }
    }
});
</script>

