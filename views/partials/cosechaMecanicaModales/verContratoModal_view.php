<?php declare(strict_types=1); ?>
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

        <div id="modalVerContratoBody">
            Cargando contrato...
        </div>

        <div class="modal-footer">
            <button type="button"
                    class="btn btn-aceptar"
                    data-close-modal="modalVerContrato">
                Cerrar
            </button>
        </div>
    </div>
</div>
