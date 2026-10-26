/**
 * Passly - Open source password manager for teams
 * Copyright (c) 2026 Svaroh
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Svaroh
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://passly.svaroh.net Passly
 */

const CACHE_NAME = 'passly-app-shell-v3';

const PRECACHE_ASSETS = [
  '/',
  '/auth/login',
  '/app/passwords',
  '/favicon.ico',
  '/favicon.svg',
  '/favicon_32.png',
  '/favicon_57.png',
  '/favicon_76.png',
  '/favicon_96.png',
  '/favicon_128.png',
  '/favicon_192.png',
  '/favicon_228.png',
  '/js/app/service-worker-register.js',
  '/js/app/stylesheet.js',
  '/js/app/api-vendors.js',
  '/js/app/api-triage.js',
  '/js/app/api-app.js',
  '/css/themes/default/api_main.min.css',
  '/css/themes/default/api_authentication.min.css',
  '/css/themes/default/ext_authentication.min.css',
  '/css/themes/midgar/api_main.min.css',
  '/css/themes/midgar/api_authentication.min.css'
];

self.addEventListener('install', (event) => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return Promise.allSettled(
        PRECACHE_ASSETS.map((url) =>
          cache.add(new Request(url, { credentials: 'same-origin' })).catch((err) => {
            console.warn(`[ServiceWorker] Failed to precache asset ${url}:`, err);
          })
        )
      );
    })
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    Promise.all([
      self.clients.claim(),
      caches.keys().then((cacheNames) =>
        Promise.all(
          cacheNames.map((name) => {
            if (name.startsWith('passly-') && name !== CACHE_NAME) {
              return caches.delete(name);
            }
            return null;
          })
        )
      )
    ])
  );
});

self.addEventListener('fetch', (event) => {
  const request = event.request;

  // Only handle GET requests
  if (request.method !== 'GET') {
    return;
  }

  const url = new URL(request.url);

  // Ignore cross-origin requests
  if (url.origin !== self.location.origin) {
    return;
  }

  // API endpoints and dynamic json calls must not be served stale from cache
  const isApiRequest =
    url.pathname.endsWith('.json') ||
    url.pathname.startsWith('/auth/is-authenticated') ||
    url.pathname.startsWith('/auth/verify') ||
    url.pathname.startsWith('/healthcheck');

  if (isApiRequest) {
    event.respondWith(
      fetch(request).catch(() => {
        return new Response(
          JSON.stringify({
            header: {
              status: 'error',
              title: 'Offline',
              message: 'You are currently offline. Local replica and cached vault state are active.',
              code: 503
            },
            body: {
              isOffline: true
            }
          }),
          {
            status: 503,
            headers: { 'Content-Type': 'application/json; charset=utf-8' }
          }
        );
      })
    );
    return;
  }

  // Navigation requests: Network-First with quick timeout and fallback to cached App Shell
  if (
    request.mode === 'navigate' ||
    (request.headers.get('accept') && request.headers.get('accept').includes('text/html'))
  ) {
    event.respondWith(
      (async () => {
        try {
          const fetchPromise = fetch(request);
          // 2.5s network timeout for sluggish/offline connections before falling back to cached shell
          const timeoutPromise = new Promise((_, reject) =>
            setTimeout(() => reject(new Error('Network timeout')), 2500)
          );

          const networkResponse = await Promise.race([fetchPromise, timeoutPromise]);
          if (networkResponse && networkResponse.status === 200) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, networkResponse.clone());
            return networkResponse;
          }
          if (networkResponse && networkResponse.status >= 400) {
            // In case server returns 404/500, check if we have a valid cached shell
            const cache = await caches.open(CACHE_NAME);
            const cached = (await cache.match(request)) || (await cache.match('/auth/login')) || (await cache.match('/'));
            if (cached) {
              return cached;
            }
          }
          return networkResponse;
        } catch (_err) {
          // Network failed or offline: serve cached App Shell immediately
          const cache = await caches.open(CACHE_NAME);

          // 1. Try exact match
          const exactMatch = await cache.match(request);
          if (exactMatch) {
            return exactMatch;
          }

          // 2. Route to relevant cached App Shell
          if (url.pathname.startsWith('/auth/login') || url.pathname === '/') {
            const loginPage = (await cache.match('/auth/login')) || (await cache.match('/'));
            if (loginPage) {
              return loginPage;
            }
          }

          const appShell =
            (await cache.match('/app/passwords')) ||
            (await cache.match('/auth/login')) ||
            (await cache.match('/'));

          if (appShell) {
            return appShell;
          }

          return new Response(
            '<!doctype html><html><head><meta charset="utf-8"><title>Passly Offline</title></head><body><h2>Passly Offline App Shell</h2><p>Server is offline. Vault cache is available.</p></body></html>',
            {
              status: 200,
              headers: { 'Content-Type': 'text/html; charset=utf-8' }
            }
          );
        }
      })()
    );
    return;
  }

  // Static assets (CSS, JS, fonts, images): Cache-First with background revalidation
  const isStaticAsset = url.pathname.match(/\.(css|js|png|svg|ico|woff|woff2|ttf|eot)$/i);
  if (isStaticAsset) {
    event.respondWith(
      caches.open(CACHE_NAME).then(async (cache) => {
        // Strip cache busters query string if needed for matching
        const cachedResponse = (await cache.match(request)) || (await cache.match(url.pathname));
        const fetchPromise = fetch(request)
          .then((networkResponse) => {
            if (networkResponse && networkResponse.status === 200) {
              cache.put(request, networkResponse.clone());
            }
            return networkResponse;
          })
          .catch(() => null);

        return cachedResponse || (await fetchPromise);
      })
    );
  }
});
