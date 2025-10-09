// Procesa la outbox cuando hay conexión.
(() => {
    async function sendEntry(e) {
        const headers = new Headers(e.headers || {});
        let body;

        if (e.bodyType === 'json') {
            headers.set('Content-Type', 'application/json');
            body = JSON.stringify(e.json || {});
        } else if (e.bodyType === 'params') {
            body = new URLSearchParams(e.params || {});
        } else if (e.bodyType === 'form') {
            body = new FormData();
            (e.form || []).forEach(([k, v]) => body.append(k, v));
            // Archivos/blobs
            for (const f of (e.files || [])) {
                const blob = new Blob([new Uint8Array(f.data)], { type: f.type });
                body.append(f.field, blob, f.name);
            }
        }

        const resp = await fetch(e.url, { method: e.method, headers, body, credentials: 'same-origin' });
        if (!resp.ok) throw new Error('HTTP ' + resp.status);
        return resp.json().catch(() => ({}));
    }

    async function processOutbox() {
        const badge = document.getElementById('badge-sync');
        if (badge) badge.style.display = 'inline-block';
        try {
            const all = (await window.OfflineDB.outboxAll().catch(() => [])) || [];
            const list = Array.isArray(all) ? all : [];
            for (const entry of list) {
                try {
                    const result = await sendEntry(entry);
                    // Si el backend devuelve ids útiles (p.ej. reporte_id), podríamos actualizar caches aquí.
                    await window.OfflineDB.outboxDelete(entry.id);
                } catch (err) {
                    entry.tries = (entry.tries || 0) + 1;
                    // backoff simple: si falla >5 veces, lo dejamos para intervención manual (aquí lo retenemos).
                    if (entry.tries <= 5) {
                        // Reinsert con tries incrementados
                        await window.OfflineDB.outboxDelete(entry.id);
                        await window.OfflineDB.outboxAdd(entry);
                    }
                    // No frenamos toda la cola por un error.
                }
            }
        } finally {
            if (badge) badge.style.display = 'none';
        }
    }

    // Exponer para que offlineApi/ connectivity lo usen
    window.SyncEngine = { processOutbox };
})();
