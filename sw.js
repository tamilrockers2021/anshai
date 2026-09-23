/* Ansh AI - Service Worker (PWA offline shell).
 * Cache-first for static assets, network-first for pages/API.
 * Bump CACHE_VERSION to invalidate old caches on deploy.
 */
'use strict';

const CACHE_VERSION = 'ansh-v1';
const STATIC_CACHE = CACHE_VERSION + '-static';

// Minimal app shell. Pages are handled network-first so content stays fresh.
const PRECACHE = [
  '/assets/css/app.css',
  '/assets/js/app.js',
  '/assets/images/favicon.svg',
  '/assets/images/icon-192.png',
  '/assets/images/icon-512.png',
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then((cache) => cache.addAll(PRECACHE).catch(() => {}))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys()
      .then((keys) => Promise.all(
        keys.filter((k) => k !== STATIC_CACHE).map((k) => caches.delete(k))
      ))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return;

  const url = new URL(req.url);
  if (url.origin !== self.location.origin) return;

  // Never cache API or auth-sensitive endpoints.
  if (url.pathname.startsWith('/api/') || url.pathname.startsWith('/admin/')) {
    return; // default network behaviour
  }

  // Static assets: cache-first.
  if (url.pathname.startsWith('/assets/')) {
    event.respondWith(
      caches.match(req).then((cached) =>
        cached || fetch(req).then((res) => {
          const copy = res.clone();
          caches.open(STATIC_CACHE).then((c) => c.put(req, copy)).catch(() => {});
          return res;
        }).catch(() => cached)
      )
    );
    return;
  }

  // Pages: network-first, fall back to cache when offline.
  event.respondWith(
    fetch(req)
      .then((res) => {
        const copy = res.clone();
        caches.open(STATIC_CACHE).then((c) => c.put(req, copy)).catch(() => {});
        return res;
      })
      .catch(() => caches.match(req))
  );
});
