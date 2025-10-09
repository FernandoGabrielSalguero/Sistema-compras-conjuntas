// sw.js - PWA básico para SVE (precaching + runtime)
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
    '/assets/png/logo_con_color_original.png',
    'https://www.fernandosalguero.com/cdn/assets/css/framework.css',
    'https://www.fernandosalguero.com/cdn/assets/javascript/framework.js'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(PRECACHE).then((cache) => cache.addAll(CORE)).then(self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => ![PRECACHE, RUNTIME].includes(k)).map(k => caches.delete(k)))
        ).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const req = event.request;
    const url = new URL(req.url);

    const isGET = req.method === 'GET';
    const isSameOrigin = url.origin === location.origin;
    const isStaticCdn = url.href.startsWith('https://www.fernandosalguero.com/cdn/');
    const isControllerGET = isSameOrigin && url.pathname.startsWith('/controllers/') && isGET;

    if (isStaticCdn || isControllerGET) {
        event.respondWith(staleWhileRevalidate(event.request));
    }
});

async function staleWhileRevalidate(request) {
    const cache = await caches.open(RUNTIME);
    const cached = await cache.match(request);
    const networkFetch = fetch(request).then(resp => {
        if (resp && resp.status === 200 && (resp.type === 'basic' || resp.type === 'cors')) {
            cache.put(request, resp.clone()).catch(() => { });
        }
        return resp;
    }).catch(() => null);

    return cached || networkFetch || new Response(
        JSON.stringify({ ok: false, message: 'offline', data: [] }),
        { status: 504, headers: { 'Content-Type': 'application/json' } }
    );
}
