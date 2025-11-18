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
            <div class="form-grid grid-3">
                <div class="input-group">
                    <label for="nuevoNombre">Nombre del contrato</label>
                    <div class="input-icon input-icon-name">
                        <span class="material-icons input-leading-icon">description</span>
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
                        <span class="material-icons input-leading-icon">flag_circle</span>
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
                            required />
                        <span class="material-icons calendar-icon">calendar_today</span>
                    </div>
                </div>

                <div class="input-group">
                    <label for="nuevoFechaCierre">Fecha cierre</label>
                    <div class="input-icon input-icon-calendar">
                        <input type="date"
                            id="nuevoFechaCierre"
                            name="fecha_cierre"
                            required />
                        <span class="material-icons calendar-icon">calendar_today</span>
                    </div>
                </div>

                <div class="input-group">
                    <label for="nuevoCostoBase">Costo base</label>
                    <div class="input-icon input-icon-name">
                        <span class="material-icons input-leading-icon">attach_money</span>
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
                        <span class="material-icons input-leading-icon">percent</span>
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
                        <span class="material-icons input-leading-icon">percent</span>
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
                        <span class="material-icons input-leading-icon">percent</span>
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
                        <span class="material-icons input-leading-icon">attach_money</span>
                        <input type="number"
                            id="nuevoAnticipo"
                            name="anticipo"
                            placeholder="Ej: 500.00"
                            step="0.01"
                            min="0" />
                    </div>
                </div>
            </div>

            <div class="input-group input-group-descripcion">
                <label for="nuevoDescripcionEditor">Descripción</label>
                <div id="nuevoDescripcionContainer" class="editor-card editor-card-full">
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
    #modalNuevoContrato .input-group-descripcion {
        margin-top: 1rem;
        width: 100%;
    }

    #modalNuevoContrato .input-group-descripcion label {
        display: block;
        margin-bottom: 0.35rem;
        font-weight: 500;
    }

    #modalNuevoContrato .editor-card {
        margin-top: 0.25rem;
        background: #ffffff;
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
        overflow: hidden;
        width: 100%;
    }

    #modalNuevoContrato .editor-card-full {
        max-width: 100%;
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
        cursor: pointer;
    }

    #modalNuevoContrato .input-icon-calendar .calendar-icon {
        position: absolute;
        right: 1rem;
        font-size: 20px;
        color: #6b7280;
        cursor: pointer;
    }

    /* Grid 3 columnas y modal más ancho */
    #modalNuevoContrato .modal-content {
        max-width: 1100px;
        width: 95%;
    }

    #modalNuevoContrato .form-grid.grid-3 {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
    }

    /* Iconos en inputs de texto/número/select */
    #modalNuevoContrato .input-icon {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    #modalNuevoContrato .input-icon .input-leading-icon {
        font-size: 20px;
        color: #6b7280;
    }

    #modalNuevoContrato .input-icon input,
    #modalNuevoContrato .input-icon select {
        flex: 1 1 auto;
    }

    /* Ocultar ícono nativo del date para dejar sólo el de Material Icons */
    #modalNuevoContrato input[type="date"]::-webkit-calendar-picker-indicator {
        opacity: 0;
    }

    #modalNuevoContrato input[type="date"] {
        position: relative;
        z-index: 1;
        background-color: transparent;
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

        // Evitar escritura manual en fechas y abrir el calendario con el icono
        var dateInputs = document.querySelectorAll('#modalNuevoContrato input[type="date"]');
        dateInputs.forEach(function(input) {
            input.addEventListener('keydown', function(e) {
                e.preventDefault(); // bloquea teclado
            });
        });

        var calendarIcons = document.querySelectorAll('#modalNuevoContrato .calendar-icon');
        calendarIcons.forEach(function(icon) {
            icon.addEventListener('click', function() {
                var input = this.previousElementSibling;
                if (input && input.type === 'date') {
                    if (typeof input.showPicker === 'function') {
                        input.showPicker(); // navegadores compatibles
                    } else {
                        input.focus(); // fallback
                    }
                }
            });
        });
    });
</script>