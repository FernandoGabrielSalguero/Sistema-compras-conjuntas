document.addEventListener('DOMContentLoaded', () => {
    const boton = document.getElementById('btnIniciarTutorial');
    if (boton) {
        boton.addEventListener('click', iniciarTutorialDashboard);
    }
});

function iniciarTutorialDashboard() {
    const pasos = [
        {
            selector: '.tutorial-formulario',
            mensaje: 'Este es el formulario para crear productores. Aquí podés ingresar los datos necesarios para registrar un nuevo productor en el sistema.',
            posicion: 'bottom'
        },
        {
            selector: '.tutorial-id_real',
            mensaje: 'Este campo se autocompleta con un ID único para cada productor. No es necesario que lo modifiques.',
            posicion: 'bottom'
        },
        {
            selector: '.tutorial-Boton',
            mensaje: 'Una vez que completes el formulario, podés hacer clic en el botón "Crear productor" para guardar la información.',
            posicion: 'top'
        },
        {
            selector: '.tutorial-buscar',
            mensaje: 'En esta sección podés buscar productores existentes. Es útil para verificar si un productor ya está registrado antes de crear uno nuevo.',
            posicion: 'top'
        },
        {
            selector: '.tutorial-listadoProductores',
            mensaje: 'En esta tarjeta vas a ver un listado de todos los productores asociados a tu cooperativa. Podés editar su información haciendo clic en el botón correspondiente.',
            posicion: 'top'
        },
        {
            selector: '.tutorial-EditarProductor',
            mensaje: 'Para editar un productor, simplemente hacé clic en el ícono de lápiz junto al nombre. Esto abrirá un modal donde vas a poder modificar sus datos.',
            posicion: 'right'
        }
    ];

    let pasoActual = 0;

    // Overlay oscuro
    const overlay = document.createElement('div');
    overlay.id = 'tutorial-overlay';
    overlay.style = `
        position: fixed;
        top: 0; left: 0;
        width: 100vw; height: 100vh;
        background: rgba(0,0,0,0.5);
        z-index: 10000;
    `;
    document.body.appendChild(overlay);

    // Estilo para highlight
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
        // Limpiar anteriores
        document.querySelectorAll('.tutorial-tooltip').forEach(el => el.remove());
        document.querySelectorAll('.tutorial-highlight').forEach(el => el.classList.remove('tutorial-highlight'));

        const paso = pasos[pasoActual];
        const target = document.querySelector(paso.selector);
        if (!target) {
            console.warn(`No se encontró el selector: ${paso.selector}`);
            avanzar();
            return;
        }

        target.classList.add('tutorial-highlight');

        const tooltip = document.createElement('div');
        tooltip.className = 'tutorial-tooltip';
        tooltip.style = `
            position: absolute;
            background: linear-gradient(45deg, #5b21b6, #9333ea);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            max-width: 300px;
            box-shadow: 0 0 15px rgba(0,0,0,0.4);
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

        // Scroll solo si el paso NO es el de listado de productores
        if (paso.selector !== '.tutorial-listadoProductores') {
            setTimeout(() => {
                target.scrollIntoView({ behavior: 'smooth', block: 'center' });

                const scrollableParent = findScrollableParent(target);
                if (scrollableParent) {
                    const rect = target.getBoundingClientRect();
                    const parentRect = scrollableParent.getBoundingClientRect();
                    const offsetLeft = rect.left - parentRect.left;
                    scrollableParent.scrollTo({
                        left: offsetLeft - 40,
                        behavior: 'smooth'
                    });
                }
            }, 300);
        }

        // Posicionar tooltip
        setTimeout(() => {
            const { top, left } = calcularPosicionTooltip(target, tooltip, paso.posicion);
            tooltip.style.top = `${top}px`;
            tooltip.style.left = `${left}px`;
        }, 310);

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

    function findScrollableParent(el) {
        while (el && el !== document.body) {
            const overflowX = window.getComputedStyle(el).overflowX;
            if (overflowX === 'auto' || overflowX === 'scroll') {
                return el;
            }
            el = el.parentElement;
        }
        return null;
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
