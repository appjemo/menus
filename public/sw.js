// JEMO Menus — Service Worker (offline para el Player)
// Objetivo: la pantalla nunca queda en negro aunque se caiga internet.
//  - Video (Google Cloud Storage): cache-first (se descarga completo y se sirve offline)
//  - Menú JSON (/play/{token}/menu): network-first con fallback a cache
//  - Página del Player (/play/{token}): network-first con fallback a cache
//  - pusher-js (CDN): cache-first (para reconectar al volver la red)

const VERSION = 'v1';
const PAGE_CACHE = `jemo-pages-${VERSION}`;
const MENU_CACHE = `jemo-menu-${VERSION}`;
const VIDEO_CACHE = `jemo-video-${VERSION}`;
const STATIC_CACHE = `jemo-static-${VERSION}`;

const CURRENT_CACHES = [PAGE_CACHE, MENU_CACHE, VIDEO_CACHE, STATIC_CACHE];

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        (async () => {
            const names = await caches.keys();
            await Promise.all(
                names.filter((n) => !CURRENT_CACHES.includes(n)).map((n) => caches.delete(n)),
            );
            await self.clients.claim();
        })(),
    );
});

// Cache-first sirviendo el archivo completo (para video cross-origin / opaco)
async function cacheFirstFull(request, cacheName) {
    const cache = await caches.open(cacheName);
    const key = new Request(request.url, { method: 'GET' });
    const cached = await cache.match(key);
    if (cached) {
        return cached;
    }
    try {
        // Pedimos el archivo COMPLETO (sin Range) en no-cors para poder cachearlo.
        const resp = await fetch(request.url, { mode: 'no-cors' });
        // status 0 = respuesta opaca (cross-origin), igual se puede cachear y reproducir
        if (resp && (resp.ok || resp.type === 'opaque')) {
            cache.put(key, resp.clone());
        }
        return resp;
    } catch (e) {
        return cached || Response.error();
    }
}

// Network-first con fallback a cache (para menú y página)
async function networkFirst(request, cacheName) {
    const cache = await caches.open(cacheName);
    try {
        const resp = await fetch(request);
        if (resp && resp.status === 200) {
            cache.put(request, resp.clone());
        }
        return resp;
    } catch (e) {
        const cached = await cache.match(request);
        if (cached) {
            return cached;
        }
        throw e;
    }
}

// Cache-first normal (para scripts estáticos del CDN)
async function cacheFirst(request, cacheName) {
    const cache = await caches.open(cacheName);
    const cached = await cache.match(request);
    if (cached) {
        return cached;
    }
    const resp = await fetch(request);
    if (resp && (resp.ok || resp.type === 'opaque')) {
        cache.put(request, resp.clone());
    }
    return resp;
}

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') {
        return;
    }

    let url;
    try {
        url = new URL(request.url);
    } catch (e) {
        return;
    }

    // Video en Google Cloud Storage → cache-first completo
    if (url.hostname === 'storage.googleapis.com') {
        event.respondWith(cacheFirstFull(request, VIDEO_CACHE));
        return;
    }

    // pusher-js desde el CDN → cache-first
    if (url.hostname === 'js.pusher.com') {
        event.respondWith(cacheFirst(request, STATIC_CACHE));
        return;
    }

    // Solo gestionamos rutas del Player de nuestro propio origen
    if (url.origin === self.location.origin && url.pathname.startsWith('/play/')) {
        // Endpoint del menú → network-first
        if (url.pathname.endsWith('/menu')) {
            event.respondWith(networkFirst(request, MENU_CACHE));
            return;
        }
        // Navegación a la página del Player → network-first
        if (request.mode === 'navigate') {
            event.respondWith(networkFirst(request, PAGE_CACHE));
            return;
        }
    }
    // El resto: comportamiento por defecto del navegador
});
