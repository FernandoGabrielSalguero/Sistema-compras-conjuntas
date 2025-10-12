/*! SVE Service Worker v1.0 - cache-first with SWR and offline fallback */
const CACHE_VERSION = 'v1';
const PRECACHE = 'sve-precache-' + CACHE_VERSION;
const RUNTIME = 'sve-runtime-' + CACHE_VERSION;

const PRECACHE_URLS = [
    '/',
    '/index.php',
    '/views/sve/sve_registro_login.php',
    '/views/drone_pilot/drone_pilot_dashboard.php',
    '/assets/js/offline.js',
    '/assets/js/sve_operativo.js',
    '/views/partials/spinner-global.js',
    '/assets/png/logo_con_color_original.png',
    'https://www.fernandosalguero.com/index.html',
    'https://www.fernandosalguero.com/cdn/assets/css/framework.css',
    'https://www.fernandosalguero.com/cdn/assets/javascript/framework.js'
];

self.addEventListener('install', (event) => {
    self.skipWaiting();
    event.waitUntil((async () => {
        const cache = await caches.open(PRECACHE);
        // Precargar SOLO http/https válidos
        const safe = PRECACHE_URLS.filter(u => {
            try {
                const p = new URL(u, self.location.origin);
                return p.protocol === 'http:' || p.protocol === 'https:';
            } catch { return false; }
        });
        await Promise.allSettled(safe.map(async (u) => {
            try {
                // Usar add con fallback a no-cors si es necesario
                try {
                    await cache.add(u);
                } catch {
                    const resp = await fetch(u, { mode: 'no-cors', cache: 'no-cache' });
                    await cache.put(new Request(u, { mode: 'no-cors' }), resp.clone());
                }
            } catch (e) {
                console.warn('[SW][PRECACHE] Falló', u, e?.message || e);
            }
        }));
    })());
});



self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
        const names = await caches.keys();
        await Promise.all(
            names.map((key) => {
                if (![PRECACHE, RUNTIME].includes(key)) {
                    return caches.delete(key);
                }
            })
        );
        await self.clients.claim();
    })());
});

self.addEventListener('fetch', (event) => {
    const req = event.request;
    if (req.method !== 'GET') return;

    const url = new URL(req.url);
    const isHttp = (url.protocol === 'http:' || url.protocol === 'https:');
    if (!isHttp) return; // ignora chrome-extension://, data:, etc.

    event.respondWith((async () => {
        const cache = await caches.open(RUNTIME);
        const cached = await caches.match(req);

        const fetchPromise = fetch(req).then((networkResp) => {
            if (networkResp && (networkResp.status === 200 || networkResp.type === 'opaque')) {
                try {
                    const cacheReq = (req.mode === 'no-cors')
                        ? new Request(req.url, { mode: 'no-cors' })
                        : req;
                    cache.put(cacheReq, networkResp.clone());
                } catch (e) { /* noop */ }
            }
            return networkResp;
        }).catch(async () => {
            if (cached) return cached;
            if (req.mode === 'navigate') {
                const fallback = await caches.match('/index.php');
                if (fallback) return fallback;
                return new Response(`<!doctype html>
<html lang="es"><meta charset="utf-8"><title>Sin conexión</title>
<body style="font-family:system-ui;padding:1rem">
<h1>Estás sin conexión</h1>
<p>Intenta nuevamente cuando tengas internet. Si ya activaste el modo offline, vuelve atrás e intenta otra ruta cacheada.</p>
</body></html>`, { headers: { 'Content-Type': 'text/html;charset=UTF-8' } });
            }
            return new Response('', { status: 504, statusText: 'Offline' });
        });

        return cached || fetchPromise;
    })());
});

self.addEventListener('activate', (event) => {
    const allow = new Set([PRECACHE, RUNTIME]);
    event.waitUntil(
        caches.keys().then(keys => Promise.all(keys.map(k => !allow.has(k) && caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});
