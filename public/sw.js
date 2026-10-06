const CACHE_NAME = 'absensi-pwa-shell-v2';
const OFFLINE_URL = new URL('offline.html', self.registration.scope).href;
const PRECACHE_URLS = [
  './offline.html',
  './manifest.json',
  './assets/css/material-dashboard.min.css',
  './assets/css/style.min.css',
  './assets/css/pwa-responsive.css',
  './assets/fonts/fonts.css',
  './assets/js/core/jquery-3.5.1.min.js',
  './assets/js/core/bootstrap.bundle.min.js',
  './assets/js/material-dashboard.js',
  './assets/img/favicon.png',
  './assets/img/pwa-icon-192.png',
  './assets/img/pwa-icon-512.png'
];

const ASSETS_PATH = new URL('assets/', self.registration.scope).pathname;

function isPublicAsset(request, url) {
  return request.method === 'GET'
    && url.origin === self.location.origin
    && url.pathname.startsWith(ASSETS_PATH)
    && /\.(?:css|js|png|jpe?g|gif|webp|svg|ico|woff2?|ttf|otf)$/i.test(url.pathname);
}

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(PRECACHE_URLS.map(path =>
      new Request(new URL(path, self.registration.scope), { cache: 'reload' })
    )))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys()
      .then(cacheNames => Promise.all(cacheNames
        .filter(name => name === 'absensi-cache-v1'
          || (name.startsWith('absensi-pwa-shell-') && name !== CACHE_NAME))
        .map(name => caches.delete(name))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', event => {
  const request = event.request;
  const url = new URL(request.url);

  if (request.method !== 'GET' || url.origin !== self.location.origin) {
    return;
  }

  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request).catch(() => caches.match(OFFLINE_URL))
    );
    return;
  }

  if (!isPublicAsset(request, url)) {
    return;
  }

  const cachedResponse = caches.match(request, { ignoreSearch: true });
  const cacheKey = new URL(request.url);
  cacheKey.search = '';
  cacheKey.hash = '';
  const refresh = fetch(new Request(request, { cache: 'reload' })).then(response => {
    if (response.ok && response.type === 'basic') {
      return caches.open(CACHE_NAME)
        .then(cache => cache.put(cacheKey.href, response.clone()).catch(() => undefined))
        .then(() => response);
    }

    return response;
  });

  event.waitUntil(refresh.catch(() => undefined));
  event.respondWith(cachedResponse.then(response => response || refresh));
});
