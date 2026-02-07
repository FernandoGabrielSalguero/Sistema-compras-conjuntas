/*! SVE Service Worker v4.2 - offline-first para piloto_drone + Background Sync */
const CACHE_VERSION = 'v4.2';
const PRECACHE = 'sve-precache-' + CACHE_VERSION;
const RUNTIME = 'sve-runtime-' + CACHE_VERSION;

const PRECACHE_URLS = [
    '/', '/index.php',
    '/views/sve/sve_registro_login.php',
    '/views/drone_pilot/drone_pilot_dashboard.php',
    '/offline.js',
    '/offline-sync.js',
    '/offline-init.js',
    '/assets/js/sve_operativo.js',
    '/views/partials/spinner-global.js',
    '/assets/png/logo_con_color_original.png'
];

// Recursos externos se cachean dinámicamente (evita errores CORS en precache)
// Se cachearán automáticamente en RUNTIME la primera vez que se soliciten


self.addEventListener('install', (event) => {
    self.skipWaiting();
    event.waitUntil((async () => {
        const cache = await caches.open(PRECACHE);
        const safe = PRECACHE_URLS.filter(u => {
            try { const p = new URL(u, self.location.origin); return p.protocol === 'http:' || p.protocol === 'https:'; }
            catch { return false; }
        });
        await Promise.allSettled(safe.map(async (u) => {
            try {
                try { await cache.add(u); }
                catch {
                    const resp = await fetch(u, { mode: 'no-cors', cache: 'no-cache' });
                    await cache.put(new Request(u, { mode: 'no-cors' }), resp.clone());
                }
            } catch (e) { /* best-effort */ }
        }));
    })());
});

self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
        const keys = await caches.keys();
        await Promise.all(keys.map(k => ![PRECACHE, RUNTIME].includes(k) && caches.delete(k)));
        await self.clients.claim();
    })());
});

self.addEventListener('fetch', (event) => {
    const req = event.request;
    if (req.method !== 'GET') return;

    let url;
    try { url = new URL(req.url); } catch { return; }
    // Ignorar todo lo que no sea http/https (incluye chrome-extension:, data:, etc.)
    if (!(url.protocol === 'http:' || url.protocol === 'https:')) return;

    // Lista de dominios externos que causan problemas CORS - no intentar cachearlos
    const corsProblematicDomains = ['framework.impulsagroup.com'];
    const isCorsProblematic = corsProblematicDomains.some(domain => url.hostname.includes(domain));

    // Si es un dominio problemático, solo hacer fetch sin cachear
    if (isCorsProblematic) {
        event.respondWith(fetch(req).catch(() => {
            // Si falla, devolver respuesta vacía en lugar de error
            return new Response('', { status: 200 });
        }));
        return;
    }

    event.respondWith((async () => {
        const cached = await caches.match(req);

        const fetchPromise = fetch(req).then(async (networkResp) => {
            // Evitar cachear si por alguna razón el request no es http/https
            if (!/^https?:/i.test(req.url)) return networkResp;
            // Solo cachear respuestas exitosas del mismo origen
            if (networkResp && networkResp.status === 200 && url.origin === self.location.origin) {
                try {
                    const cache = await caches.open(RUNTIME);
                    await cache.put(req, networkResp.clone());
                } catch { /* no-op */ }
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
<p>Si ya activaste el modo offline, vuelve atrás e intenta otra ruta cacheada.</p>
</body></html>`, { headers: { 'Content-Type': 'text/html;charset=UTF-8' } });
            }
            return new Response('', { status: 504, statusText: 'Offline' });
        });

        // Stale-while-revalidate simple
        return cached || fetchPromise;
    })());
});

self.addEventListener('message', (event) => {
    if (event.data?.type === 'SKIP_WAITING') self.skipWaiting();
});

// Background Sync para sincronización automática de reportes offline
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-drone-reports') {
        event.waitUntil(syncReports());
    }
});

async function syncReports() {
    try {
        // Enviar mensaje a todos los clientes para que ejecuten la sincronización
        const clients = await self.clients.matchAll({ type: 'window' });
        for (const client of clients) {
            client.postMessage({
                type: 'SYNC_REPORTS',
                timestamp: Date.now()
            });
        }
        console.log('[SW] Background sync triggered for drone reports');
    } catch (error) {
        console.error('[SW] Error in background sync:', error);
        throw error;
    }
}
