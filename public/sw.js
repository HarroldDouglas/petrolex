const CACHE_VERSION = '1.0.0';
const STATIC_CACHE = `isogaz-static-v${CACHE_VERSION}`;
const PAGES_CACHE = `isogaz-pages-v${CACHE_VERSION}`;
const IMAGES_CACHE = `isogaz-images-v${CACHE_VERSION}`;

// Only cache the offline page on install (other assets cached on-the-fly)
const PRECACHE_ASSETS = [
    '/offline.html',
    '/assets/images/logo/isogaz-no-bg.png'
];

// Routes that should never be cached
const NEVER_CACHE = [
    '/livewire',
    '/api/',
    '/logout',
    '/login',
    '/broadcasting',
    '/sanctum'
];

function shouldCache(url) {
    return !NEVER_CACHE.some(path => url.pathname.includes(path));
}

// Install: pre-cache essential assets only
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then(cache => cache.addAll(PRECACHE_ASSETS))
            .then(() => self.skipWaiting())
    );
});

// Activate: clean old caches
self.addEventListener('activate', event => {
    const validCaches = [STATIC_CACHE, PAGES_CACHE, IMAGES_CACHE];
    event.waitUntil(
        caches.keys()
            .then(keys => Promise.all(
                keys.filter(key => !validCaches.includes(key))
                    .map(key => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

// Fetch: Network-first for pages, Cache-first for static assets
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    if (event.request.method !== 'GET') return;
    if (!shouldCache(url)) return;

    // Static assets (CSS, JS, fonts) -> Cache first
    if (url.pathname.match(/\.(css|js|woff2?|ttf|eot)(\?.*)?$/)) {
        event.respondWith(
            caches.match(event.request).then(cached => {
                if (cached) return cached;
                return fetch(event.request).then(response => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(STATIC_CACHE).then(cache => cache.put(event.request, clone));
                    }
                    return response;
                });
            })
        );
        return;
    }

    // Images -> Cache first with network fallback
    if (url.pathname.match(/\.(png|jpg|jpeg|gif|svg|webp|ico)(\?.*)?$/)) {
        event.respondWith(
            caches.match(event.request).then(cached => {
                if (cached) return cached;
                return fetch(event.request).then(response => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(IMAGES_CACHE).then(cache => cache.put(event.request, clone));
                    }
                    return response;
                });
            })
        );
        return;
    }

    // Pages -> Network first with offline fallback
    if (event.request.headers.get('accept')?.includes('text/html')) {
        event.respondWith(
            fetch(event.request)
                .then(response => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(PAGES_CACHE).then(cache => cache.put(event.request, clone));
                    }
                    return response;
                })
                .catch(() => {
                    return caches.match(event.request)
                        .then(cached => cached || caches.match('/offline.html'));
                })
        );
        return;
    }
});

// Handle messages from the app
self.addEventListener('message', event => {
    if (event.data?.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
    if (event.data?.type === 'CLEAR_CACHE') {
        caches.keys().then(keys => keys.forEach(key => caches.delete(key)));
    }
});
