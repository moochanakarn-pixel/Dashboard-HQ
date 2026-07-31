const CACHE = 'hq-v4';
const PRECACHE = ['./icons/icon-192.png', './icons/icon-512.png'];

self.addEventListener('install', e => {
  self.skipWaiting();
  e.waitUntil(caches.open(CACHE).then(c => c.addAll(PRECACHE).catch(() => {})));
});

self.addEventListener('activate', e => {
  e.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
    ).then(() => clients.claim())
  );
});

self.addEventListener('fetch', e => {
  // API requests: always network, never cache
  if (e.request.url.includes('api_realtime.php') || e.request.url.includes('api_dashboard.php')) {
    e.respondWith(fetch(e.request));
    return;
  }

  // HTML page navigation: network-first so code changes appear immediately;
  // fall back to cache only when offline
  if (e.request.mode === 'navigate') {
    e.respondWith(
      fetch(e.request).catch(() => caches.match(e.request).then(r => r || Response.error()))
    );
    return;
  }

  // Static assets (icons, etc.): cache-first
  e.respondWith(
    caches.match(e.request).then(cached => cached || fetch(e.request).then(resp => {
      const clone = resp.clone();
      caches.open(CACHE).then(c => c.put(e.request, clone));
      return resp;
    }))
  );
});
