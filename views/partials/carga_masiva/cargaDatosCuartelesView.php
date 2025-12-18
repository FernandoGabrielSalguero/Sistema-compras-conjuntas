<div style="display:flex;flex-direction:column;gap:10px;">
    <p style="margin:0;opacity:.8;">
        Prueba mínima del módulo <b>Cuarteles</b>: Vista → Controller → Model → DB.
    </p>

    <button class="btn btn-info" type="button" id="cuartelesPingBtn">Probar conexión</button>

    <pre id="cuartelesPingOut" style="margin:0;padding:10px;border:1px solid rgba(0,0,0,.08);border-radius:10px;white-space:pre-wrap;word-break:break-word;min-height:54px;"></pre>
</div>

<script>
    (() => {
        const btn = document.getElementById('cuartelesPingBtn');
        const out = document.getElementById('cuartelesPingOut');
        if (!btn || !out) return;

        const url = '../partials/carga_masiva/cargaDatosCuartelesController.php';

        btn.addEventListener('click', async () => {
            out.textContent = 'Consultando...';

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'ping'
                    })
                });

                const text = await res.text();
                let json;
                try {
                    json = JSON.parse(text);
                } catch (e) {
                    throw new Error('Respuesta no-JSON: ' + text);
                }

                if (!res.ok || !json.ok) {
                    throw new Error((json && json.error) ? json.error : 'Error HTTP ' + res.status);
                }

                out.textContent = JSON.stringify(json, null, 2);
            } catch (err) {
                out.textContent = 'ERROR: ' + (err && err.message ? err.message : String(err));
            }
        });
    })();
</script>