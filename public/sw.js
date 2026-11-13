/**
 * Paskola SIMS — Service Worker (PWA & Web Push Notification)
 * Version: 2.0.0
 */

const CACHE_NAME = 'paskola-cache-v2';
const PRECACHE_ASSETS = [
    '/offline.html',
    '/manifest.json',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/icons/badge-72.png',
];

// ─── 1. Install Event (Pre-caching offline essentials) ─────────────────────────
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS);
        }).then(() => self.skipWaiting())
    );
});

// ─── 2. Activate Event (Clean old caches & take control) ───────────────────────
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((name) => name !== CACHE_NAME)
                    .map((name) => caches.delete(name))
            );
        }).then(() => self.clients.claim())
    );
});

// ─── 3. Fetch Event (Network-first for pages with offline fallback) ────────────
self.addEventListener('fetch', (event) => {
    const request = event.request;

    // Hanya tangani metode GET
    if (request.method !== 'GET') return;

    // Untuk navigasi halaman (HTML): Coba jaringan dulu, jika gagal tampilkan offline.html
    if (request.mode === 'navigate' || request.headers.get('accept')?.includes('text/html')) {
        event.respondWith(
            fetch(request).catch(async () => {
                const cache = await caches.open(CACHE_NAME);
                const cachedResponse = await cache.match(request);
                if (cachedResponse) return cachedResponse;
                return cache.match('/offline.html');
            })
        );
        return;
    }

    // Untuk asset statis (ikon, font, css bawaan): Cache first, fallback ke network
    if (request.url.includes('/icons/') || request.url.includes('/manifest.json')) {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                if (cachedResponse) return cachedResponse;
                return fetch(request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const responseClone = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, responseClone));
                    }
                    return networkResponse;
                });
            })
        );
    }
});

// ─── 4. Push Notification Event ───────────────────────────────────────────────
self.addEventListener('push', (event) => {
    let data = {
        title: 'Paskola SIMS',
        body:  'Ada pengumuman atau pembaruan baru dari sekolah.',
        icon:  '/icons/icon-192.png',
        badge: '/icons/badge-72.png',
        url:   '/announcements',
        tag:   'paskola-notification',
    };

    if (event.data) {
        try {
            const received = event.data.json();
            data = { ...data, ...received };
        } catch (e) {
            data.body = event.data.text();
        }
    }

    const options = {
        body:               data.body,
        icon:               data.icon,
        badge:              data.badge,
        tag:                data.tag,
        renotify:           true,
        vibrate:            [100, 50, 100],
        requireInteraction: false,
        data: {
            url: data.url || '/announcements',
            time: Date.now(),
        },
        actions: [
            { action: 'open', title: 'Lihat Detail' },
            { action: 'close', title: 'Tutup' },
        ],
    };

    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

// ─── 5. Notification Click Event ──────────────────────────────────────────────
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    if (event.action === 'close') return;

    const targetUrl = event.notification.data?.url || '/announcements';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            // Jika ada tab yang sudah membuka aplikasi, arahkan & fokuskan
            for (const client of windowClients) {
                if (client.url.includes(self.location.origin) && 'focus' in client) {
                    client.navigate(targetUrl);
                    return client.focus();
                }
            }
            // Jika tidak ada tab terbuka, buka tab baru
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});
