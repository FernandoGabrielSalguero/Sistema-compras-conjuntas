<div style="padding:10px;border:1px solid rgba(0,0,0,.08);border-radius:8px;">
    <b>KPI Compra Conjunta</b>
    <div id="sveKpiCompraConjuntaStatus" style="margin-top:6px;font-size:14px;opacity:.9;">Chequeando conexion...</div>
</div>

<script>
    (() => {
        const statusEl = document.getElementById('sveKpiCompraConjuntaStatus');
        if (!statusEl) return;

        async function check() {
            statusEl.textContent = 'Chequeando conexion con el controlador...';
            try {
                const res = await fetch('../partials/sve_kpi/sve_kpi_compraConjuntaController.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'ping' })
                });

                const text = await res.text();
                let json;
                try {
                    json = JSON.parse(text);
                } catch (e) {
                    throw new Error('Respuesta no JSON: ' + text);
                }

                if (!res.ok || !json.ok) {
                    throw new Error((json && json.error) ? json.error : 'Error en controlador');
                }

                const serverTime = (json.data && json.data.server_time) ? json.data.server_time : 'desconocida';
                statusEl.textContent = 'Modelo y controlador conectados correctamente. Hora servidor: ' + serverTime;
            } catch (err) {
                statusEl.textContent = 'Error al conectar: ' + (err && err.message ? err.message : String(err));
            }
        }

        check();
    })();
</script>
