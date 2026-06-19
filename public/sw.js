// Service Worker untuk Web Push Notification — Konsultasi Chat

self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (e) => e.waitUntil(clients.claim()));

self.addEventListener('push', (event) => {
    const data = event.data?.json() ?? {};

    event.waitUntil(
        self.registration.showNotification(data.title ?? 'Pesan Konsultasi', {
            body:      data.body ?? 'Anda memiliki pesan baru dari dokter.',
            icon:      '/img/favicon.png',
            badge:     '/img/favicon.png',
            tag:       'konsultasi-' + (data.token ?? 'default'),
            renotify:  true,
            vibrate:   [200, 100, 200],
            data:      { url: data.url ?? '/' },
        })
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const targetUrl = event.notification.data?.url ?? '/';

    event.waitUntil(
        clients
            .matchAll({ type: 'window', includeUncontrolled: true })
            .then((windowClients) => {
                for (const client of windowClients) {
                    if (client.url === targetUrl && 'focus' in client) {
                        return client.focus();
                    }
                }
                return clients.openWindow(targetUrl);
            })
    );
});
