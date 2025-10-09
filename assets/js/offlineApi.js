// Patch de fetch: GET => cache-first con revalidación; POST/PUT/DELETE => encolar si offline o falla red.
(() => {
    const ORIGINAL_FETCH = window.fetch.bind(window);

    function isSameOrigin(u) {
        try { return new URL(u, location.origin).origin === location.origin; }
        catch { return false; }
    }

    // Detecta endpoints del backend: controladores de /controllers y también de /views/**/controller/*
    function isController(url) {
        if (!isSameOrigin(url)) return false;
        try {
            const p = new URL(url, location.origin).pathname;
            return (
                p.startsWith('/controllers/') ||
                p.includes('/api/') ||
                p.endsWith('Controller.php') ||
                p.endsWith('Api.php') ||
                /\/views\/[^/]+\/partials?\/.*\/controller\/.*_controller\.php$/i.test(p) ||
                p.endsWith('_controller.php') // p.ej. drone_list_controller.php
            );
        } catch { return false; }
    }


    async function serializeBody(body) {
        if (!body) return { bodyType: null };
        if (body instanceof FormData) {
            const pairs = [];
            const files = [];
            for (const [k, v] of body.entries()) {
                if (v instanceof File || v instanceof Blob) {
                    const arrayBuffer = await v.arrayBuffer();
                    files.push({ field: k, name: v.name || 'blob', type: v.type || 'application/octet-stream', data: arrayBuffer });
                } else {
                    pairs.push([k, String(v)]);
                }
            }
            return { bodyType: 'form', form: pairs, files };
        }
        if (body instanceof URLSearchParams) {
            const obj = {};
            for (const [k, v] of body.entries()) obj[k] = v;
            return { bodyType: 'params', params: obj };
        }
        if (typeof body === 'string') {
            try { return { bodyType: 'json', json: JSON.parse(body) }; }
            catch { return { bodyType: 'json', json: { _raw: body } }; }
        }
        return { bodyType: null };
    }

    async function cacheGet(url) {
        try {
            if (!window.OfflineDB?.cacheGet) return null;
            return await window.OfflineDB.cacheGet(url);
        } catch (e) {
            console.warn('[offlineApi] cacheGet fallo', e);
            return null;
        }
    }

    async function cachePut(url, resp) {
        try {
            if (resp && resp.ok && window.OfflineDB?.cachePut) {
                await window.OfflineDB.cachePut(url, resp);
            }
        } catch (e) {
            console.warn('[offlineApi] cachePut fallo', e);
        }
    }

    async function offlineFetch(input, init = {}) {
        const req = new Request(input, init);
        const url = new URL(req.url, location.origin).toString();
        const method = (req.method || 'GET').toUpperCase();
        const eligible = isController(url);

        // GET: cache-first + revalidate
        if (eligible && method === 'GET') {
            const net = ORIGINAL_FETCH(req).then(async (resp) => {
                if (resp && resp.ok) await cachePut(url, resp.clone());
                return resp;
            }).catch((err) => {
                console.warn('[offlineApi] GET red fallo', err);
                return null;
            });

            const cached = await cacheGet(url);
            if (cached) {
                // Revalida en segundo plano
                net.catch(() => { });
                console.debug('[offlineApi] GET desde cache', url);
                return cached;
            }

            const n = await net;
            if (n) return n;

            return new Response(
                JSON.stringify({ ok: false, message: 'Sin conexión y sin cache', data: [] }),
                { status: 504, headers: { 'Content-Type': 'application/json' } }
            );
        }

        // POST/PUT/DELETE: intento online, si no encolo
        if (eligible && (method === 'POST' || method === 'PUT' || method === 'DELETE')) {
            const headers = {};
            try {
                const hdrs = init.headers || req.headers || [];
                hdrs.forEach?.((v, k) => { headers[k] = v; });
                if (hdrs instanceof Headers) hdrs.forEach((v, k) => { headers[k] = v; });
            } catch { }

            if (navigator.onLine) {
                try {
                    const onlineResp = await ORIGINAL_FETCH(req);
                    if (onlineResp.ok) return onlineResp;
                    return onlineResp; // si el servidor responde error, lo devolvemos y no encolamos
                } catch (e) {
                    console.warn('[offlineApi] POST online fallo, encolando', e);
                }
            }

            const body = init.body || null;
            const serialized = await serializeBody(body);
            if (!serialized.bodyType) {
                return new Response(JSON.stringify({ ok: false, message: 'Body no serializable offline' }), { status: 400 });
            }

            try {
                await window.OfflineDB?.outboxAdd?.({ url, method, headers, ...serialized });
            } catch (e) {
                console.error('[offlineApi] No se pudo encolar', e);
                return new Response(JSON.stringify({ ok: false, message: 'No se pudo encolar para sync' }), { status: 500 });
            }

            return new Response(
                JSON.stringify({ ok: true, message: 'Pendiente de sincronizar (offline)' }),
                { status: 200, headers: { 'Content-Type': 'application/json' } }
            );
        }

        // Otros: pasa directo
        return ORIGINAL_FETCH(req);
    }

    // Parche global
    window.fetch = offlineFetch;

    // Helpers
    window.OfflineApi = {
        sync: () => window.SyncEngine?.processOutbox(),
    };
})();
