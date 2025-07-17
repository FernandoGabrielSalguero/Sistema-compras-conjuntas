document.addEventListener('DOMContentLoaded', () => {
    const boton = document.getElementById('btnIniciarTutorial');
    if (boton) {
        boton.addEventListener('click', iniciarTutorialDashboard);
    }
});

function iniciarTutorialDashboard() {
    const pasos = [
        {
            selector: '.tutorial-seleccionarProductor',
            mensaje: 'Para comenzar, selecciona un productor de la lista. Puedes buscarlo escribiendo su nombre en el campo de búsqueda o su ID',
            posicion: 'bottom'
        },
        {
            selector: '.tutorial-SeleccionarOperativo',
            mensaje: 'Es importante que selecciones un operativo para poder ver los productos disponibles y realizar compras. Si no participas de ningún operativo, es importante que vayas a la página inicio y participes en alguno',
            posicion: 'bottom'
        },
        {
            selector: '.tutorial-SeleccionarProductos',
            mensaje: 'Una vez seleccionado el operativo, podrás ver los productos disponibles. Puedes seleccionar los productos que deseas comprar tocando en su categoría y colocando la cantidad que necesitas.',
            posicion: 'top'
        },
        {
            selector: '.tutorial-ResumenPedido',
            mensaje: 'Cuando selecciones todos los productos que necesitas, podrás ver un resumen de tu pedido. Aquí podrás revisar los productos seleccionados y las cantidades con su precio total e IVA incluido.',
            posicion: 'top'
        },
        {
            selector: '.tutorial-botonGuardar',
            mensaje: 'Una vez finalizada la creación del pedido, puedes guardarlo. Asegúrate de que toda la información sea correcta antes de guardar.',
            posicion: 'top'
        },
        {
            selector: '.sidebar-menu li:nth-child(3)',
            mensaje: 'Cuando guardes tu pedido, vas a encontrarlo en el menú Listado Pedidos.',
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

    // Agregar estilos para resaltado si no existen
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

        // Resaltar
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

        // Esperar al render y hacer scroll
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

            const { top, left } = calcularPosicionTooltip(target, tooltip, paso.posicion);
            tooltip.style.top = `${top}px`;
            tooltip.style.left = `${left}px`;
        }, 300);

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
