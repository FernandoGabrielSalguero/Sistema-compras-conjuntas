// views/partials/tutorials/cooperativas/pulverizacion.js
document.addEventListener('DOMContentLoaded', () => {
    const boton = document.getElementById('btnIniciarTutorial');
    if (boton) {
        boton.addEventListener('click', iniciarTutorialPulverizacion);
    }
});

function iniciarTutorialPulverizacion() {
    const pasos = [
        // Nos aseguramos que los dos primeros pasos obliguen la tab de Solicitudes
        {
            selector: '#tab-solicitudes',
            mensaje: 'Acá cambiás a la pestaña “Solicitudes”. Mostrará la lista de servicios solicitados.',
            posicion: 'bottom',
            switchTo: '#panel-solicitudes'
        },
        {
            selector: '.tutorial-TablaSolicitudes',
            mensaje: 'Esta sección contiene la tabla con las solicitudes. Podés revisar estados, datos y acciones.',
            posicion: 'top',
            switchTo: '#panel-solicitudes'
        },
        {
            selector: '#btn-refresh.tutorial-BotonActualizar',
            mensaje: 'Con “Actualizar” recargás la página manteniendo la pestaña actual.',
            posicion: 'left'
        },
        {
            selector: '#tab-formulario',
            mensaje: 'Acá cambiás a la pestaña “Nuevo servicio” para crear una solicitud.',
            posicion: 'bottom'
        },
        {
            selector: '.tutorial-FormularioNuevoServicio',
            mensaje: 'Este es el formulario de alta. Completá los campos requeridos y enviá para crear el servicio.',
            posicion: 'top',
            switchTo: '#panel-formulario' // <-- cambia automáticamente a la tab de formulario
        }
    ];

    let pasoActual = 0;

    // Overlay
    const overlay = document.createElement('div');
    overlay.id = 'tutorial-overlay';
    overlay.style = `
        position: fixed;
        top: 0; left: 0;
        width: 100vw; height: 100vh;
        background: rgba(0, 0, 0, 0.5);
        z-index: 10000;
    `;
    document.body.appendChild(overlay);

    // Estilo highlight (una sola vez)
    if (!document.getElementById('estilo-tutorial-highlight')) {
        const estilo = document.createElement('style');
        estilo.id = 'estilo-tutorial-highlight';
        estilo.innerHTML = `
            .tutorial-highlight {
                position: relative !important;
                z-index: 10002 !important;
                box-shadow: 0 0 0 4px #ffffff, 0 0 0 8px #5b21b6;
                border-radius: 10px;
                transition: box-shadow 0.3s ease;
            }
            .tutorial-tooltip {
                position: absolute;
                background: linear-gradient(45deg, #5b21b6, #9333ea);
                color: #fff;
                padding: 1rem;
                border-radius: 10px;
                max-width: 320px;
                box-shadow: 0 0 15px rgba(0,0,0,.4);
                z-index: 10003;
                font-size: .95rem;
            }
        `;
        document.head.appendChild(estilo);
    }

    mostrarPaso();

    function activarTab(panelSelector) {
        const mapa = {
            '#panel-solicitudes': '#tab-solicitudes',
            '#panel-formulario':  '#tab-formulario'
        };
        const btnSel = mapa[panelSelector];
        const btn = btnSel ? document.querySelector(btnSel) : null;
        if (btn) btn.click(); // usa la lógica existente de tabs
    }

    function mostrarPaso() {
        limpiarPaso();

        const paso = pasos[pasoActual];

        // Si este paso requiere una tab específica, la activamos antes de buscar el selector
        if (paso.switchTo) {
            activarTab(paso.switchTo);
        }

        // Pequeño delay para permitir que el DOM actualice clases/hidden
        setTimeout(() => {
            const target = document.querySelector(paso.selector);

            if (!target) {
                console.warn(`No se encontró el selector: ${paso.selector}`);
                avanzar();
                return;
            }

            target.classList.add('tutorial-highlight');

            const tooltip = document.createElement('div');
            tooltip.className = 'tutorial-tooltip';
            tooltip.innerHTML = `
                <div>${paso.mensaje}</div>
                <br>
                <div style="display:flex; justify-content:flex-end; gap:.5rem;">
                    <button id="btnSiguienteTutorial" class="btn btn-info" type="button">Siguiente</button>
                    <button id="btnCerrarTutorial" class="btn btn-cancelar" type="button">Cerrar</button>
                </div>
            `;
            document.body.appendChild(tooltip);

            const { top, left } = calcularPosicionTooltip(target, tooltip, paso.posicion);
            tooltip.style.top = `${top}px`;
            tooltip.style.left = `${left}px`;

            // Enlaces de los botones
            document.getElementById('btnSiguienteTutorial').onclick = avanzar;
            document.getElementById('btnCerrarTutorial').onclick = terminarTutorial;
        }, 80);
    }

    function calcularPosicionTooltip(target, tooltip, posicion = 'bottom') {
        const rect = target.getBoundingClientRect();
        const scrollY = window.scrollY || window.pageYOffset;
        const scrollX = window.scrollX || window.pageXOffset;

        const tooltipHeight = tooltip.offsetHeight || 140;
        const tooltipWidth  = tooltip.offsetWidth  || 300;

        switch (posicion) {
            case 'top':
                return { top: rect.top + scrollY - tooltipHeight - 10, left: rect.left + scrollX };
            case 'left':
                return { top: rect.top + scrollY, left: rect.left + scrollX - tooltipWidth - 10 };
            case 'right':
                return { top: rect.top + scrollY, left: rect.right + scrollX + 10 };
            case 'center':
                return {
                    top: rect.top + scrollY + rect.height / 2 - tooltipHeight / 2,
                    left: rect.left + scrollX + rect.width / 2 - tooltipWidth / 2
                };
            case 'bottom':
            default:
                return { top: rect.bottom + scrollY + 10, left: rect.left + scrollX };
        }
    }

    function avanzar() {
        pasoActual++;
        if (pasoActual < pasos.length) {
            mostrarPaso();
        } else {
            terminarTutorial();
        }
    }

    function limpiarPaso() {
        document.querySelectorAll('.tutorial-tooltip').forEach(el => el.remove());
        document.querySelectorAll('.tutorial-highlight').forEach(el => el.classList.remove('tutorial-highlight'));
    }

    function terminarTutorial() {
        const ov = document.getElementById('tutorial-overlay');
        if (ov) ov.remove();
        limpiarPaso();
        // No removemos el <style> para permitir relanzar sin parpadeos
    }
}
