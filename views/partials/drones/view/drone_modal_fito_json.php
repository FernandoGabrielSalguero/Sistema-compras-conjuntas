<?php
// Modal genérico para ver el JSON del Registro Fitosanitario (todas las tablas relacionadas).
?>
<div id="modal-fito-json" class="modal hidden" role="dialog" aria-modal="true" aria-labelledby="modal-fito-json-title" aria-describedby="modal-fito-json-desc">
    <div class="modal-content" style="max-width: 920px;">
        <h3 id="modal-fito-json-title">Registro Fitosanitario (JSON)</h3>
        <p id="modal-fito-json-desc">Vista consolidada de la solicitud y sus tablas relacionadas.</p>

        <div class="form-grid" style="gap:12px; margin-bottom:12px;">
            <button type="button" class="btn btn-info" id="btn-fito-copiar">Copiar JSON</button>
            <button type="button" class="btn btn-secondary" id="btn-fito-descargar">Descargar JSON</button>
        </div>

        <pre id="fito-json-pre" style="background:#0b1020; color:#d6e4ff; border-radius:12px; padding:14px; max-height:60vh; overflow:auto; font-size:12px; line-height:1.45; white-space:pre; tab-size:2;"></pre>

        <div class="form-buttons">
            <button type="button" class="btn btn-aceptar" id="btn-fito-aceptar">Aceptar</button>
            <button type="button" class="btn btn-cancelar" id="btn-fito-cancelar">Cancelar</button>
        </div>
    </div>
</div>

<script>
    (function() {
        if (window.__SVE_FITO_JSON_INIT__) return;
        window.__SVE_FITO_JSON_INIT__ = true;

        const DRONE_API = '../partials/drones/controller/drone_list_controller.php';

        const modal = document.getElementById('modal-fito-json');
        const pre = document.getElementById('fito-json-pre');
        const btnOk = document.getElementById('btn-fito-aceptar');
        const btnCa = document.getElementById('btn-fito-cancelar');
        const btnCp = document.getElementById('btn-fito-copiar');
        const btnDl = document.getElementById('btn-fito-descargar');

        function openModal() {
            modal.classList.remove('hidden');
            btnOk.focus();
        }

        function closeModal() {
            modal.classList.add('hidden');
            pre.textContent = '';
        }

        async function fetchDeepJSON(id) {
            const url = `${DRONE_API}?action=solicitud_json&id=${encodeURIComponent(id)}`;
            const res = await fetch(url, {
                cache: 'no-store'
            });
            const json = await res.json();
            if (!json.ok) throw new Error(json.error || 'Error');
            return json.data;
        }

        async function open(id) {
            try {
                pre.textContent = 'Cargando...';
                openModal();
                const data = await fetchDeepJSON(id);
                pre.textContent = JSON.stringify(data, null, 2);
            } catch (e) {
                console.error(e);
                pre.textContent = 'No se pudo cargar el JSON del registro.';
                if (typeof window.showAlert === 'function') {
                    window.showAlert('error', 'No se pudo cargar el Registro Fitosanitario.');
                }
            }
        }

        btnOk.addEventListener('click', closeModal);
        btnCa.addEventListener('click', closeModal);
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });

        btnCp.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(pre.textContent || '');
                if (typeof window.showAlert === 'function') window.showAlert('success', 'JSON copiado al portapapeles.');
            } catch (e) {
                if (typeof window.showAlert === 'function') window.showAlert('error', 'No se pudo copiar.');
            }
        });

        btnDl.addEventListener('click', () => {
            try {
                const blob = new Blob([pre.textContent || ''], {
                    type: 'application/json;charset=utf-8'
                });
                const a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                const now = new Date();
                const pad = n => String(n).padStart(2, '0');
                a.download = `registro_fitosanitario_${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}_${pad(now.getHours())}${pad(now.getMinutes())}${pad(now.getSeconds())}.json`;
                document.body.appendChild(a);
                a.click();
                a.remove();
            } catch (e) {
                if (typeof window.showAlert === 'function') window.showAlert('error', 'No se pudo descargar.');
            }
        });

        // Exponer API global sencilla
        window.FitoJSONModal = {
            open,
            close: closeModal
        };
    })();
</script>