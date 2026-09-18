self.addEventListener("install", (event) => {
    event.waitUntil(self.skipWaiting());
});

self.addEventListener("activate", (event) => {
    event.waitUntil(self.clients.claim());
});

// MVP без офлайна: всегда сеть, SW нужен только для installability.
self.addEventListener("fetch", (event) => {
    event.respondWith(fetch(event.request));
});
