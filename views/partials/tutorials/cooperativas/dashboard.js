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
            mensaje: 'En esta tarjeta vas a tener la información principal del operativo, como su fecha de apertura y cierre y una breve descripción del mismo.',
        },
        {
            selector: '#contenedorOperativos',
            mensaje: 'Acá vas a ver los operativos disponibles.',
        },
        {
            selector: '.sidebar-menu li:nth-child(2)',
            mensaje: 'Entrá al Mercado Digital desde aquí.',
        },
    ];

    let pasoActual = 0;

    const overlay = document.createElement('div');
    overlay.id = 'tutorial-overlay';
    overlay.style = `
        position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
        background: rgba(0,0,0,0.5); z-index: 10000;
        display: flex; align-items: center; justify-content: center;
        padding: 2rem;
    `;
    document.body.appendChild(overlay);

    function mostrarPaso() {
        const paso = pasos[pasoActual];
        const target = document.querySelector(paso.selector);
        if (!target) {
            console.warn(`No se encontró el selector: ${paso.selector}`);
            pasoActual++;
            if (pasoActual < pasos.length) mostrarPaso();
            else terminarTutorial();
            return;
        }

        const rect = target.getBoundingClientRect();

        const tooltip = document.createElement('div');
        tooltip.className = 'tutorial-tooltip';
        tooltip.style = `
            position: absolute;
            top: ${rect.bottom + window.scrollY + 10}px;
            left: ${rect.left + window.scrollX}px;
            background: #5b21b6;
            color: white;
            padding: 1rem;
            border-radius: 10px;
            max-width: 300px;
            box-shadow: 0 0 10px black;
            z-index: 10001;
        `;

        tooltip.innerHTML = `
            <div>${paso.mensaje}</div>
            <br>
            <button id="btnSiguienteTutorial" class="btn btn-info" style="margin-right: 1rem;">Siguiente</button>
            <button id="btnCerrarTutorial" class="btn btn-cancelar">Cerrar</button>
        `;

        document.body.appendChild(tooltip);

        document.getElementById('btnSiguienteTutorial').onclick = () => {
            tooltip.remove();
            pasoActual++;
            if (pasoActual < pasos.length) {
                mostrarPaso();
            } else {
                terminarTutorial();
            }
        };

        document.getElementById('btnCerrarTutorial').onclick = () => {
            tooltip.remove();
            terminarTutorial();
        };
    }

    function terminarTutorial() {
        const overlay = document.getElementById('tutorial-overlay');
        if (overlay) overlay.remove();

        document.querySelectorAll('.tutorial-tooltip').forEach(el => el.remove());
    }

    mostrarPaso();
}
