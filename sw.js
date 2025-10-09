const PRECACHE = 'sve-precache-v1';
const RUNTIME = 'sve-runtime-v1';

const CORE = [
    '/',
    '/index.php',
    '/views/drone_pilot/drone_pilot_dashboard.php',
    '/assets/js/offlineDb.js',
    '/assets/js/offlineApi.js',
    '/assets/js/syncEngine.js',
    '/assets/js/connectivity.js',
    '/assets/png/logo_con_color_original.png'
];

self.addEventListener('install', (event) => {
    event.waitUntil((async () => {
        const cache = await caches.open(PRECACHE);
        await cache.addAll(CORE);
        await self.skipWaiting();
    })());
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => ![PRECACHE, RUNTIME].includes(k)).map(k => caches.delete(k)))
        ).then(() => self.clients.claim())
    );
});

function isControllerUrl(u) {
    try {
        const url = new URL(u, self.location.origin);
        const p = url.pathname;
        return url.origin === self.location.origin &&
               (p.startsWith('/controllers/') || p.includes('/api/') || p.endsWith('Controller.php') || p.endsWith('Api.php'));
    } catch { return false; }
}

self.addEventListener('fetch', (event) => {
    const req = event.request;
    const url = new URL(req.url);
    const isGET = req.method === 'GET';
    const isSameOrigin = url.origin === location.origin;
    const isStaticCdn = url.href.startsWith('https://www.fernandosalguero.com/cdn/');
    const isJsonAccept = (req.headers.get('accept') || '').includes('application/json');

    // Controladores GET o JSON same-origin => SWR
    if (isStaticCdn || (isGET && (isControllerUrl(req.url) || (isSameOrigin && isJsonAccept)))) {
        event.respondWith(staleWhileRevalidate(req));
    }
});

async function staleWhileRevalidate(request) {
    const cache = await caches.open(RUNTIME);
    const cached = await cache.match(request);
    const networkFetch = fetch(request).then(resp => {
        if (resp && resp.ok && (resp.type === 'basic' || resp.type === 'cors')) {
            cache.put(request, resp.clone()).catch(() => {});
        }
        return resp;
    }).catch((e) => {
        // Evitar silencios
        return null;
    });

    if (cached) return cached;

    const net = await networkFetch;
    return net || new Response(
        JSON.stringify({ ok: false, message: 'offline', data: [] }),
        { status: 504, headers: { 'Content-Type': 'application/json' } }
    );
}
