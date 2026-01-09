const CACHE_NAME = 'enviro-hub-v3';
const OFFLINE_URL = '/offline';

const PRECACHE_ASSETS = [
    '/offline',
    '/manifest.json',
    '/favicons/favicon.ico',
    '/favicons/favicon.svg',
    '/favicons/favicon-96x96.png',
    '/favicons/icon-192.png',
    '/favicons/icon-512.png',
    '/favicons/apple-touch-icon.png'
];

// Install event - precache critical assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('Service Worker: Precaching assets');
                return cache.addAll(PRECACHE_ASSETS);
            })
            .then(() => self.skipWaiting())
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((name) => name !== CACHE_NAME)
                    .map((name) => caches.delete(name))
            );
        })
    );
});

// Fetch event - selective caching
self.addEventListener('fetch', (event) => {
    // Skip non-GET requests
    if (event.request.method !== 'GET') {
        return;
    }

    // Skip chrome-extension and other non-http(s) requests
    if (!event.request.url.startsWith('http')) {
        return;
    }

    const url = new URL(event.request.url);

    // Only cache static assets (CSS, JS, fonts, images)
    const isStaticAsset = /\.(css|js|woff2?|ttf|eot|svg|png|jpg|jpeg|gif|webp|ico)$/i.test(url.pathname);

    // Cache manifest and favicons
    const isCacheable = isStaticAsset ||
        url.pathname === '/manifest.json' ||
        url.pathname.startsWith('/favicons/') ||
        url.pathname === '/offline';

    if (isCacheable) {
        event.respondWith(cacheFirstStaticAssets(event.request));
    } else {
        // For everything else (HTML, API calls, data), always use network
        event.respondWith(
            fetch(event.request).catch(() => {
                // Only show offline page for navigation requests
                if (event.request.mode === 'navigate') {
                    return caches.match(OFFLINE_URL);
                }
                return new Response('Network error', { status: 408 });
            })
        );
    }
});

// Cache-first strategy for static assets
async function cacheFirstStaticAssets(request) {
    const cachedResponse = await caches.match(request);

    if (cachedResponse) {
        return cachedResponse;
    }

    try {
        const networkResponse = await fetch(request);

        if (networkResponse.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, networkResponse.clone());
        }

        return networkResponse;
    } catch (error) {
        console.log('Failed to fetch:', request.url, error);
        throw error;
    }
}

// Handle messages from the app
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }

    if (event.data && event.data.type === 'CLEAR_CACHE') {
        event.waitUntil(
            caches.delete(CACHE_NAME)
                .then(() => self.clients.matchAll())
                .then((clients) => {
                    clients.forEach((client) => {
                        client.postMessage({ type: 'CACHE_CLEARED' });
                    });
                })
        );
    }
});
