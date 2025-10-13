/*! SVE Service Worker v2.0 - cache-first + SWR + offline fallback */
const CACHE_VERSION = 'v2';
const PRECACHE = 'sve-precache-' + CACHE_VERSION;
const RUNTIME  = 'sve-runtime-'  + CACHE_VERSION;

const PRECACHE_URLS = [
  '/', '/index.php',
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
      } catch (e) { console.warn('[SW][PRECACHE] Falló', u, e?.message || e); }
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

  const url = new URL(req.url);
  if (!(url.protocol === 'http:' || url.protocol === 'https:')) return;

  event.respondWith((async () => {
    const cached = await caches.match(req);
    const fetchPromise = fetch(req).then(async (networkResp) => {
      if (networkResp && (networkResp.status === 200 || networkResp.type === 'opaque')) {
        try {
          const cache = await caches.open(RUNTIME);
          const cacheReq = (req.mode === 'no-cors') ? new Request(req.url, { mode: 'no-cors' }) : req;
          cache.put(cacheReq, networkResp.clone());
        } catch {}
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
