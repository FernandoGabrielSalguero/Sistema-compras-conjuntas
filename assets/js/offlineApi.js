// Patch de fetch: GET => cache-first con revalidación; POST/PUT => encolar si offline o falla red.
(() => {
    const ORIGINAL_FETCH = window.fetch.bind(window);

    function isController(url) {
        try {
            const u = new URL(url, location.origin);
            return u.pathname.startsWith('/controllers/');
        } catch { return false; }
    }

    async function serializeBody(body) {
        if (!body) return { bodyType: null };
        if (body instanceof FormData) {
            // Serializamos pares simples y archivos
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
            // Intentamos parsear; si no, lo mandamos como json string
            try { return { bodyType: 'json', json: JSON.parse(body) }; }
            catch { return { bodyType: 'json', json: { _raw: body } }; }
        }
        // Fallback: no soportado -> lo dejamos pasar sin offline
        return { bodyType: null };
    }

    async function cacheGet(url) {
        return await window.OfflineDB.cacheGet(url);
    }

    async function cachePut(url, resp) {
        try { await window.OfflineDB.cachePut(url, resp); } catch { }
    }

    async function offlineFetch(input, init = {}) {
        const req = new Request(input, init);
        const url = new URL(req.url, location.origin).toString();
        const method = (req.method || 'GET').toUpperCase();

        // Sólo aplicamos política a endpoints del proyecto
        const eligible = isController(url);

        // GET: cache-first con revalidación
        if (eligible && method === 'GET') {
            const net = ORIGINAL_FETCH(req).then(async (resp) => {
                if (resp && resp.ok) await cachePut(url, resp.clone());
                return resp;
            }).catch(() => null);

            const cached = await cacheGet(url);
            if (cached) {
                // Disparamos revalidación en segundo plano
                net.catch(() => { });
                return cached;
            }
            // Sin cache: esperamos a la red o devolvemos 504 si falla
            const n = await net;
            return n || new Response(JSON.stringify({ ok: false, message: 'Sin conexión y sin cache' }), { status: 504 });
        }

        // POST/PUT: si offline o falla la red => encolamos en outbox
        if (eligible && (method === 'POST' || method === 'PUT' || method === 'DELETE')) {
            const headers = {};
            (init.headers || req.headers || []).forEach?.((v, k) => { headers[k] = v; });

            // Intento online primero si hay conexión
            if (navigator.onLine) {
                try {
                    const onlineResp = await ORIGINAL_FETCH(req);
                    if (onlineResp.ok) return onlineResp;
                    // Si el servidor responde error, devolvemos ese error y no encolamos.
                    return onlineResp;
                } catch {
                    // cae a encolado
                }
            }

            // Serializamos body y guardamos en outbox
            let body = init.body || null;
            if (!body && req.body) {
                // No siempre accesible; en la práctica nuestros llamados pasan body en init.
            }
            const serialized = await serializeBody(body);
            if (!serialized.bodyType) {
                // No serializable -> devolvemos error honesto
                return new Response(JSON.stringify({ ok: false, message: 'Body no serializable offline' }), { status: 400 });
            }
            await window.OfflineDB.outboxAdd({
                url, method, headers, ...serialized
            });

            // Respuesta optimista para la UI (permite seguir)
            return new Response(JSON.stringify({ ok: true, message: 'Pendiente de sincronizar (offline)' }), { status: 200, headers: { 'Content-Type': 'application/json' } });
        }

        // Otros requests: pasar directo
        return ORIGINAL_FETCH(req);
    }

    // Parche global
    window.fetch = offlineFetch;

    // Exponer helpers por si la UI quiere forzar sync
    window.OfflineApi = {
        sync: () => window.SyncEngine?.processOutbox(),
    };
})();
