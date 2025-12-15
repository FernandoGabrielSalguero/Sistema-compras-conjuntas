<?php declare(strict_types=1); ?>
<div id="modalEliminarContrato" class="modal hidden" aria-hidden="true" role="dialog" aria-modal="true"
     aria-labelledby="modalEliminarContratoTitulo">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalEliminarContratoTitulo">Eliminar contrato</h3>
            <button type="button"
                    class="modal-close-btn"
                    aria-label="Cerrar"
                    data-close-modal="modalEliminarContrato">
                <span class="material-icons">close</span>
            </button>
        </div>

        <p>¿Estás seguro de que querés eliminar este contrato de cosecha mecánica? Esta acción no se puede deshacer. <br> Al eliminar un contrato, se elimina tambien el historial de participacion de las cooperativas y sus productores asociados.</p>

        <div class="modal-footer">
            <button type="button"
                    class="btn btn-cancelar"
                    data-close-modal="modalEliminarContrato">
                Cancelar
            </button>
            <button type="button"
                    id="btnConfirmEliminarContrato"
                    class="btn btn-aceptar">
                Eliminar
            </button>
        </div>
    </div>
</div>
