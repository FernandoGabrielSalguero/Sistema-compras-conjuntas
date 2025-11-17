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
                    <div class="input-icon input-icon-calendar">
                        <input type="date"
                            id="nuevoFechaApertura"
                            name="fecha_apertura"
                            required
                            readonly />
                        <span class="material-icons calendar-icon">calendar_today</span>
                    </div>
                </div>

                <div class="input-group">
                    <label for="nuevoFechaCierre">Fecha cierre</label>
                    <div class="input-icon input-icon-calendar">
                        <input type="date"
                            id="nuevoFechaCierre"
                            name="fecha_cierre"
                            required
                            readonly />
                        <span class="material-icons calendar-icon">calendar_today</span>
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


                <div class="input-group" style="margin-top: 1rem;">
                    <label for="nuevoDescripcionEditor">Descripción</label>
                    <div id="nuevoDescripcionContainer" class="editor-card">
                        <div id="nuevoDescripcionEditor" class="quill-editor"></div>
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
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">

<style>
    /* Estilos modernos para el editor de descripción */
    #modalNuevoContrato .editor-card {
        margin-top: 0.5rem;
        background: #ffffff;
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    #modalNuevoContrato .editor-card .ql-toolbar.ql-snow {
        border: none;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
        padding: 0.5rem 0.75rem;
    }

    #modalNuevoContrato .editor-card .ql-container.ql-snow {
        border: none;
    }

    #modalNuevoContrato .editor-card .ql-editor {
        min-height: 160px;
        font-size: 0.95rem;
        line-height: 1.5;
        padding: 0.75rem 0.9rem;
    }

    #modalNuevoContrato .editor-card .ql-editor:focus {
        outline: none;
    }

    /* Input fecha con icono de calendario a la derecha */
    #modalNuevoContrato .input-icon-calendar {
        position: relative;
        display: flex;
        align-items: center;
    }

    #modalNuevoContrato .input-icon-calendar input[type="date"] {
        width: 100%;
        padding-right: 2.5rem;
    }

    #modalNuevoContrato .input-icon-calendar .calendar-icon {
        position: absolute;
        right: 1rem;
        font-size: 20px;
        color: #6b7280;
        pointer-events: none;
        /* clic pasa al input para abrir el calendario */
    }
</style>

<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var toolbarOptions = [
            ['bold', 'underline'],
            [{
                'list': 'ordered'
            }, {
                'list': 'bullet'
            }],
            [{
                'indent': '-1'
            }, {
                'indent': '+1'
            }]
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
                form.addEventListener('submit', function() {
                    hiddenTextarea.value = quill.root.innerHTML;
                });
            }
        }

        // Restringir campos numéricos a sólo números y punto decimal
        var numericInputs = document.querySelectorAll('#modalNuevoContrato input[type="number"]');
        numericInputs.forEach(function(input) {
            input.setAttribute('inputmode', 'decimal');
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9.]/g, '');
            });
        });
    });
</script>