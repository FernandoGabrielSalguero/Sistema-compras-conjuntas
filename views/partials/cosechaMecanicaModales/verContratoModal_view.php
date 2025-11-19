<?php

declare(strict_types=1); ?>
<div id="modalVerContrato" class="modal hidden" aria-hidden="true" role="dialog" aria-modal="true"
    aria-labelledby="modalVerContratoTitulo">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalVerContratoTitulo">Detalle del contrato</h3>
            <button type="button"
                class="modal-close-btn"
                aria-label="Cerrar"
                data-close-modal="modalVerContrato">
                <span class="material-icons">close</span>
            </button>
        </div>

        <form id="formVerContrato">
            <input type="hidden" id="verContratoId" name="id" />

            <div class="form-grid grid-3">
                <div class="input-group">
                    <label for="verNombre">Nombre del contrato</label>
                    <div class="input-icon input-icon-name">
                        <span class="material-icons input-leading-icon">description</span>
                        <input type="text"
                            id="verNombre"
                            name="nombre"
                            placeholder="Ej: Cosecha 2025 Valle Norte"
                            required />
                    </div>
                </div>

                <div class="input-group">
                    <label for="verEstado">Estado</label>
                    <div class="input-icon input-icon-name">
                        <span class="material-icons input-leading-icon">flag_circle</span>
                        <select id="verEstado" name="estado">
                            <option value="borrador">Borrador</option>
                            <option value="abierto">Abierto</option>
                            <option value="cerrado">Cerrado</option>
                        </select>
                    </div>
                </div>

                <div class="input-group">
                    <label for="verFechaApertura">Fecha apertura</label>
                    <div class="input-icon input-icon-date">
                        <input type="date"
                            id="verFechaApertura"
                            name="fecha_apertura"
                            required />
                    </div>
                </div>

                <div class="input-group">
                    <label for="verFechaCierre">Fecha cierre</label>
                    <div class="input-icon input-icon-date">
                        <input type="date"
                            id="verFechaCierre"
                            name="fecha_cierre"
                            required />
                    </div>
                </div>

                <div class="input-group">
                    <label for="verCostoBase">Costo base</label>
                    <div class="input-icon input-icon-name">
                        <span class="material-icons input-leading-icon">attach_money</span>
                        <input type="number"
                            id="verCostoBase"
                            name="costo_base"
                            placeholder="Ej: 1500.00"
                            step="0.01"
                            min="0"
                            required />
                    </div>
                </div>

                <div class="input-group">
                    <label for="verBonOptima">Bonificación óptima</label>
                    <div class="input-icon input-icon-name">
                        <span class="material-icons input-leading-icon">attach_money</span>
                        <input type="number"
                            id="verBonOptima"
                            name="bon_optima"
                            placeholder="Ej: 10.00"
                            step="0.01"
                            min="0" />
                    </div>
                </div>

                <div class="input-group">
                    <label for="verBonMuyBuena">Bonificación muy buena</label>
                    <div class="input-icon input-icon-name">
                        <span class="material-icons input-leading-icon">attach_money</span>
                        <input type="number"
                            id="verBonMuyBuena"
                            name="bon_muy_buena"
                            placeholder="Ej: 7.50"
                            step="0.01"
                            min="0" />
                    </div>
                </div>

                <div class="input-group">
                    <label for="verBonBuena">Bonificación buena</label>
                    <div class="input-icon input-icon-name">
                        <span class="material-icons input-leading-icon">attach_money</span>
                        <input type="number"
                            id="verBonBuena"
                            name="bon_buena"
                            placeholder="Ej: 5.00"
                            step="0.01"
                            min="0" />
                    </div>
                </div>

                <div class="input-group">
                    <label for="verAnticipo">Anticipo</label>
                    <div class="input-icon input-icon-name">
                        <span class="material-icons input-leading-icon">attach_money</span>
                        <input type="number"
                            id="verAnticipo"
                            name="anticipo"
                            placeholder="Ej: 500.00"
                            step="0.01"
                            min="0" />
                    </div>
                </div>
            </div>

            <div class="input-group input-group-descripcion">
                <label for="verDescripcionEditor">Descripción</label>
                <div id="verDescripcionContainer" class="editor-card editor-card-full">
                    <div id="verDescripcionEditor" class="quill-editor"></div>
                    <textarea id="verDescripcion"
                        name="descripcion"
                        style="display: none;"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button"
                    class="btn btn-secundario"
                    id="btnEditarContrato">
                    Modificar
                </button>
                <button type="button"
                    class="btn btn-aceptar hidden"
                    id="btnActualizarContrato">
                    Actualizar
                </button>
                <button type="button"
                    class="btn btn-cancelar"
                    data-close-modal="modalVerContrato">
                    Cerrar
                </button>
            </div>
        </form>
    </div>
</div>

<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">

<style>
    /* Estilos para el modal de ver/editar contrato */
    #modalVerContrato .input-group-descripcion {
        margin-top: 1rem;
        width: 100%;
    }

    #modalVerContrato .input-group-descripcion label {
        display: block;
        margin-bottom: 0.35rem;
        font-weight: 500;
    }

    #modalVerContrato .editor-card {
        margin-top: 0.25rem;
        background: #ffffff;
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
        overflow: hidden;
        width: 100%;
    }

    #modalVerContrato .editor-card-full {
        max-width: 100%;
    }

    #modalVerContrato .editor-card .ql-toolbar.ql-snow {
        border: none;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
        padding: 0.5rem 0.75rem;
    }

    #modalVerContrato .editor-card .ql-container.ql-snow {
        border: none;
    }

    #modalVerContrato .editor-card .ql-editor {
        min-height: 160px;
        font-size: 0.95rem;
        line-height: 1.5;
        padding: 0.75rem 0.9rem;
    }

    #modalVerContrato .editor-card .ql-editor:focus {
        outline: none;
    }

    #modalVerContrato .modal-content {
        width: 95%;
        max-width: 60vw;
    }

    #modalVerContrato .form-grid.grid-3 {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
    }

    #modalVerContrato .input-icon {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    #modalVerContrato .input-icon .input-leading-icon {
        font-size: 20px;
        color: #6b7280;
    }

    #modalVerContrato .input-icon input,
    #modalVerContrato .input-icon select {
        flex: 1 1 auto;
    }

    #modalVerContrato input[type="date"] {
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

        var quillVer = null;
        var editorContainer = document.getElementById('verDescripcionEditor');
        if (editorContainer) {
            quillVer = new Quill('#verDescripcionEditor', {
                theme: 'snow',
                modules: {
                    toolbar: toolbarOptions
                },
                readOnly: true
            });
        }

        var btnEditar = document.getElementById('btnEditarContrato');
        var btnActualizar = document.getElementById('btnActualizarContrato');
        var formVerContrato = document.getElementById('formVerContrato');
        var hiddenDescripcion = document.getElementById('verDescripcion');

        function setEditable(editable) {
            if (!formVerContrato) return;

            var inputs = formVerContrato.querySelectorAll('input, select');
            inputs.forEach(function(el) {
                // El ID se mantiene siempre habilitado para enviar al backend,
                // pero no se edita porque es hidden.
                if (el.id === 'verContratoId') {
                    return;
                }
                el.disabled = !editable;
            });

            if (quillVer) {
                quillVer.enable(editable);
            }

            if (editable) {
                if (btnEditar) btnEditar.classList.add('hidden');
                if (btnActualizar) btnActualizar.classList.remove('hidden');
            } else {
                if (btnEditar) btnEditar.classList.remove('hidden');
                if (btnActualizar) btnActualizar.classList.add('hidden');
            }
        }

        // Vista inicial sólo lectura
        setEditable(false);

        if (btnEditar) {
            btnEditar.addEventListener('click', function() {
                setEditable(true);
            });
        }

        if (btnActualizar) {
            btnActualizar.addEventListener('click', function() {
                if (!formVerContrato) return;

                if (quillVer && hiddenDescripcion) {
                    hiddenDescripcion.value = quillVer.root.innerHTML;
                }

                var formData = new FormData(formVerContrato);
                var id = document.getElementById('verContratoId') ? document.getElementById('verContratoId').value : '';

                formData.set('id', id);
                formData.set('action', 'actualizar');

                // Ajustar la ruta si tu controlador está en otra ubicación
                var CONTRATO_ENDPOINT = 'controllers/sve_cosechaMecanicaController.php';

                fetch(CONTRATO_ENDPOINT, {
                        method: 'POST',
                        body: formData
                    })
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(resp) {
                        if (resp.ok) {
                            setEditable(false);
                            alert('Contrato actualizado correctamente.');
                        } else {
                            alert(resp.error || 'No se pudo actualizar el contrato.');
                        }
                    })
                    .catch(function() {
                        alert('Error de conexión al actualizar el contrato.');
                    });
            });
        }

        // Restricción de campos numéricos
        var numericInputs = document.querySelectorAll('#modalVerContrato input[type="number"]');
        numericInputs.forEach(function(input) {
            input.setAttribute('inputmode', 'decimal');
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9.]/g, '');
            });
        });

        // Mejorar UX en date inputs
        var dateWrappers = document.querySelectorAll('#modalVerContrato .input-icon-date');
        dateWrappers.forEach(function(wrapper) {
            var input = wrapper.querySelector('input[type="date"]');
            if (input) {
                wrapper.addEventListener('click', function() {
                    if (typeof input.showPicker === 'function') {
                        input.showPicker();
                    } else {
                        input.focus();
                    }
                });
            }
        });

        // Función auxiliar para cargar datos del contrato desde fuera
        // Uso esperado desde otro JS:
        //   window.cargarContratoEnModal(contrato);
        window.cargarContratoEnModal = function(contrato) {
            if (!formVerContrato) return;

            if (document.getElementById('verContratoId')) {
                document.getElementById('verContratoId').value = contrato.id || '';
            }
            if (document.getElementById('verNombre')) {
                document.getElementById('verNombre').value = contrato.nombre || '';
            }
            if (document.getElementById('verEstado')) {
                document.getElementById('verEstado').value = contrato.estado || 'borrador';
            }
            if (document.getElementById('verFechaApertura')) {
                document.getElementById('verFechaApertura').value = contrato.fecha_apertura || '';
            }
            if (document.getElementById('verFechaCierre')) {
                document.getElementById('verFechaCierre').value = contrato.fecha_cierre || '';
            }
            if (document.getElementById('verCostoBase')) {
                document.getElementById('verCostoBase').value = contrato.costo_base || '';
            }
            if (document.getElementById('verBonOptima')) {
                document.getElementById('verBonOptima').value = contrato.bon_optima || '';
            }
            if (document.getElementById('verBonMuyBuena')) {
                document.getElementById('verBonMuyBuena').value = contrato.bon_muy_buena || '';
            }
            if (document.getElementById('verBonBuena')) {
                document.getElementById('verBonBuena').value = contrato.bon_buena || '';
            }
            if (document.getElementById('verAnticipo')) {
                document.getElementById('verAnticipo').value = contrato.anticipo || '';
            }

            if (quillVer) {
                quillVer.root.innerHTML = contrato.descripcion || '';
            }

            setEditable(false);
        };
    });
</script>