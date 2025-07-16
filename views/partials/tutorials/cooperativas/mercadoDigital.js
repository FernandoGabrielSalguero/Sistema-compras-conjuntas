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
            mensaje: 'Una vez seleccionado el operativo, podrás ver los productos disponibles. Puedes seleccionar los productos que deseas comprar tocando en su categoria y colocando la cantidad que necesitas.',
            posicion: 'top'
        },
        {
            selector: '.tutorial-ResumenPedido',
            mensaje: 'Cuando selecciones todos los productos que necesitas, podrás ver un resumen de tu pedido. Aquí podrás revisar los productos seleccionados y las cantidades con su precio total e IVA incluido.',
            posicion: 'top'
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

    mostrarPaso();

    function mostrarPaso() {
        const paso = pasos[pasoActual];
        const target = document.querySelector(paso.selector);
        if (!target) {
            console.warn(`No se encontró el selector: ${paso.selector}`);
            avanzar();
            return;
        }

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
            z-index: 10001;
            font-size: 0.95rem;
        `;

        tooltip.innerHTML = `
            <div>${paso.mensaje}</div>
            <br>
            <div style="display:flex; justify-content:flex-end; gap:0.5rem;">
                <button id="btnSiguienteTutorial" class="btn  btn-info">Siguiente</button>
                <button id="btnCerrarTutorial" class="btn btn-cancelar">Cerrar</button>
            </div>
        `;

        const { top, left } = calcularPosicionTooltip(target, paso.posicion);
        tooltip.style.top = `${top}px`;
        tooltip.style.left = `${left}px`;

        document.body.appendChild(tooltip);

        // Botones
        document.getElementById('btnSiguienteTutorial').onclick = () => {
            tooltip.remove();
            avanzar();
        };

        document.getElementById('btnCerrarTutorial').onclick = () => {
            tooltip.remove();
            terminarTutorial();
        };
    }

    function calcularPosicionTooltip(target, posicion = 'bottom') {
        const rect = target.getBoundingClientRect();
        const scrollY = window.scrollY;
        const scrollX = window.scrollX;

        switch (posicion) {
            case 'top':
                return { top: rect.top + scrollY - 110, left: rect.left + scrollX };
            case 'left':
                return { top: rect.top + scrollY, left: rect.left + scrollX - 320 };
            case 'right':
                return { top: rect.top + scrollY, left: rect.right + scrollX + 20 };
            case 'center':
                return {
                    top: rect.top + scrollY + rect.height / 2 - 60,
                    left: rect.left + scrollX + rect.width / 2 - 150
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

    function terminarTutorial() {
        const overlay = document.getElementById('tutorial-overlay');
        if (overlay) overlay.remove();
        document.querySelectorAll('.tutorial-tooltip').forEach(el => el.remove());
    }
}
