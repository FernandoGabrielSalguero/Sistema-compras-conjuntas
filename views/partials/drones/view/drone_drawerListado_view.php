<?php
// Drawer desacoplado para ver JSON del pedido seleccionado.
// Requiere: ../controller/drone_drawerListado_controller.php
?>
<style>
    /* Estilos básicos del drawer (aislados por prefijo .sv-drawer) */
    .sv-drawer.hidden {
        display: none;
    }

    .sv-drawer {
        position: fixed;
        inset: 0;
        z-index: 60;
    }

    .sv-drawer__overlay {
        position: absolute;
        inset: 0;
        background: #0006;
        opacity: 0;
        transition: opacity .2s ease;
    }

    .sv-drawer[aria-hidden="false"] .sv-drawer__overlay {
        opacity: 1;
    }

    .sv-drawer__panel {
        position: absolute;
        top: 0;
        right: 0;
        height: 100%;
        width: min(720px, 100%);
        background: #fff;
        box-shadow: -6px 0 24px #00000022;
        display: flex;
        flex-direction: column;
        border-top-left-radius: 16px;
        border-bottom-left-radius: 16px;
        transform: translateX(100%);
        transition: transform .25s cubic-bezier(.22, .61, .36, 1);
    }

    .sv-drawer[aria-hidden="false"] .sv-drawer__panel {
        transform: translateX(0);
    }

    .sv-drawer__header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
        border-bottom: 1px solid #eee;
    }

    .sv-drawer__body {
        flex: 1;
        overflow: auto;
        -webkit-overflow-scrolling: touch;
        padding: 16px 20px;
        background: #f6f7fb;
    }

    .sv-drawer__footer {
        padding: 12px 20px;
        border-top: 1px solid #eee;
        display: flex;
        gap: 12px;
        justify-content: flex-end;
    }

    .sv-drawer__close {
        font-size: 24px;
        line-height: 1;
        border: none;
        background: transparent;
        cursor: pointer;
    }

    pre.json {
        background: #0b1021;
        color: #cfe3ff;
        border-radius: 12px;
        padding: 12px;
        overflow: auto;
    }
</style>

<div id="drawerListado" class="sv-drawer hidden" aria-hidden="true">
    <div class="sv-drawer__overlay" data-close></div>
    <aside class="sv-drawer__panel" role="dialog" aria-modal="true" aria-labelledby="drawerListado-title">
        <div class="sv-drawer__header">
            <h3 id="drawerListado-title">Detalle pedido <span id="drawerListado-id"></span></h3>
            <button class="sv-drawer__close" id="drawerListado-close" aria-label="Cerrar">×</button>
        </div>

        <div class="sv-drawer__body">
            <div class="card">
                <h4>Respuesta JSON</h4>
                <pre class="json" id="drawerListado-json" aria-live="polite" aria-busy="false">{}</pre>
            </div>
        </div>

        <div class="sv-drawer__footer">
            <button type="button" class="btn btn-cancelar" id="drawerListado-cancel">Cerrar</button>
        </div>
    </aside>
</div>

<script>
    (function() {
        if (window.DroneDrawerListado) return;

        const API = '../partials/drones/controller/drone_drawerListado_controller.php';
        const drawer = document.getElementById('drawerListado');
        const panel = drawer.querySelector('.sv-drawer__panel');
        const overlay = drawer.querySelector('.sv-drawer__overlay');
        const btnClose = document.getElementById('drawerListado-close');
        const btnCancel = document.getElementById('drawerListado-cancel');
        const lblId = document.getElementById('drawerListado-id');
        const boxJson = document.getElementById('drawerListado-json');

        let lastFocus = null;

        function open({
            id
        }) {
            lastFocus = document.activeElement;
            lblId.textContent = `#${id}`;
            drawer.classList.remove('hidden');
            drawer.setAttribute('aria-hidden', 'false');
            panel.setAttribute('tabindex', '-1');
            setTimeout(() => panel.focus(), 0);
            loadJson(id);
        }

        function close() {
            const active = document.activeElement;
            if (active && drawer.contains(active) && lastFocus && typeof lastFocus.focus === 'function') {
                lastFocus.focus();
            }
            drawer.setAttribute('aria-hidden', 'true');
            setTimeout(() => drawer.classList.add('hidden'), 200);
        }

        async function loadJson(id) {
            boxJson.setAttribute('aria-busy', 'true');
            boxJson.textContent = 'Cargando...';
            try {
                const url = `${API}?action=get_detalle&id=${encodeURIComponent(id)}`;
                const res = await fetch(url, {
                    cache: 'no-store'
                });
                const json = await res.json();
                if (!json.ok) throw new Error(json.error || 'Error');
                // imprimimos “lo que viene de la base”
                boxJson.textContent = JSON.stringify(json.data, null, 2);
            } catch (e) {
                console.error(e);
                boxJson.textContent = JSON.stringify({
                    error: e.message
                }, null, 2);
            } finally {
                boxJson.setAttribute('aria-busy', 'false');
            }
        }

        overlay.addEventListener('click', close);
        btnClose.addEventListener('click', close);
        btnCancel.addEventListener('click', close);

        window.DroneDrawerListado = {
            open,
            close
        };
    })();
</script>