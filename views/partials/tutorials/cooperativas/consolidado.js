document.addEventListener('DOMContentLoaded', () => {
    const boton = document.getElementById('btnIniciarTutorial');
    if (boton) {
        boton.addEventListener('click', iniciarTutorialDashboard);
    }
});

function iniciarTutorialDashboard() {
    const pasos = [
        {
            selector: '.tutorial-operativos-disponibles',
            mensaje: 'En estas tarjetas vas a poder ver los operativos habilitados para participar. Es importante que te unas a uno para poder comprar productos dentro de él.',
            posicion: 'top'
        },
        {
            selector: '.tutorial-swich-participacion',
            mensaje: 'Para participar, simplemente tocá este botón una vez hasta que se pinte de color violeta. Si ya estás participando, el botón estará activo y vas a poder dejar de participar tocándolo nuevamente.',
            posicion: 'top'
        },
        {
            selector: '.sidebar-menu li:nth-child(2)',
            mensaje: 'Una vez que participes en al menos un operativo, entrá al Mercado Digital desde este acceso rápido para hacer tus compras a los productores.',
            posicion: 'right'
        }

    ];

    let pasoActual = 0;

    // Crear overlay
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

    // Agregar estilo para resaltar pasos
    if (!document.getElementById('estilo-tutorial-highlight')) {
        const estilo = document.createElement('style');
        estilo.id = 'estilo-tutorial-highlight';
        estilo.innerHTML = `
            .tutorial-highlight {
                position: relative !important;
                z-index: 10002 !important;
                box-shadow: 0 0 0 4px #ffffff, 0 0 0 8px #5b21b6;
                border-radius: 6px;
                transition: box-shadow 0.3s ease;
            }
        `;
        document.head.appendChild(estilo);
    }

    mostrarPaso();

    function mostrarPaso() {
        // Limpiar pasos anteriores
        document.querySelectorAll('.tutorial-tooltip').forEach(el => el.remove());
        document.querySelectorAll('.tutorial-highlight').forEach(el => el.classList.remove('tutorial-highlight'));

        const paso = pasos[pasoActual];
        const target = document.querySelector(paso.selector);
        if (!target) {
            console.warn(`No se encontró el selector: ${paso.selector}`);
            avanzar();
            return;
        }

        // Agregar clase para resaltar
        target.classList.add('tutorial-highlight');

        // Crear tooltip
        const tooltip = document.createElement('div');
        tooltip.className = 'tutorial-tooltip';
        tooltip.style = `
            position: absolute;
            background: linear-gradient(45deg, #5b21b6, #9333ea);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            max-width: 300px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.4);
            z-index: 10003;
            font-size: 0.95rem;
        `;

        tooltip.innerHTML = `
            <div>${paso.mensaje}</div>
            <br>
            <div style="display:flex; justify-content:flex-end; gap:0.5rem;">
                <button id="btnSiguienteTutorial" class="btn btn-info">Siguiente</button>
                <button id="btnCerrarTutorial" class="btn btn-cancelar">Cerrar</button>
            </div>
        `;

        document.body.appendChild(tooltip);

        // Asegurar que el elemento esté visible y ajustar posición del tooltip
        setTimeout(() => {
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });

            const { top, left } = calcularPosicionTooltip(target, tooltip, paso.posicion);
            tooltip.style.top = `${top}px`;
            tooltip.style.left = `${left}px`;
        }, 300); // esperar a que se complete el scroll


        // Botones
        document.getElementById('btnSiguienteTutorial').onclick = avanzar;
        document.getElementById('btnCerrarTutorial').onclick = terminarTutorial;
    }

    function calcularPosicionTooltip(target, tooltip, posicion = 'bottom') {
        const rect = target.getBoundingClientRect();
        const scrollY = window.scrollY;
        const scrollX = window.scrollX;

        const tooltipHeight = tooltip.offsetHeight || 120;
        const tooltipWidth = tooltip.offsetWidth || 280;

        switch (posicion) {
            case 'top':
                return {
                    top: rect.top + scrollY - tooltipHeight - 10,
                    left: rect.left + scrollX
                };
            case 'left':
                return {
                    top: rect.top + scrollY,
                    left: rect.left + scrollX - tooltipWidth - 10
                };
            case 'right':
                return {
                    top: rect.top + scrollY,
                    left: rect.right + scrollX + 10
                };
            case 'center':
                return {
                    top: rect.top + scrollY + rect.height / 2 - tooltipHeight / 2,
                    left: rect.left + scrollX + rect.width / 2 - tooltipWidth / 2
                };
            case 'bottom':
            default:
                return {
                    top: rect.bottom + scrollY + 10,
                    left: rect.left + scrollX
                };
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

    function terminarTutorial() {
        const overlay = document.getElementById('tutorial-overlay');
        if (overlay) overlay.remove();

        document.querySelectorAll('.tutorial-tooltip').forEach(el => el.remove());
        document.querySelectorAll('.tutorial-highlight').forEach(el => el.classList.remove('tutorial-highlight'));

        const estilo = document.getElementById('estilo-tutorial-highlight');
        if (estilo) estilo.remove();
    }
}
