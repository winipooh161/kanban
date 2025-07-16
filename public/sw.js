const CACHE_NAME = 'kanban-v2';
const STATIC_CACHE_URLS = [
    '/',
    '/home',
    '/login',
    '/manifest.json',
    '/favicon.ico',
    '/icon-192x192.svg',
    '/icon-512x512.svg',
    '/offline.html'
];

// Установка Service Worker
self.addEventListener('install', event => {
    console.log('Service Worker installing...');
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('Caching static assets');
                return cache.addAll(STATIC_CACHE_URLS);
            })
    );
    self.skipWaiting();
});

// Активация Service Worker
self.addEventListener('activate', event => {
    console.log('Service Worker activating...');
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

// Обработка запросов
self.addEventListener('fetch', event => {
    // Только для GET запросов
    if (event.request.method !== 'GET') {
        return;
    }

    event.respondWith(
        caches.match(event.request)
            .then(response => {
                // Возвращаем кэшированную версию или загружаем из сети
                if (response) {
                    return response;
                }
                
                return fetch(event.request).then(response => {
                    // Проверяем, что ответ валидный
                    if (!response || response.status !== 200 || response.type !== 'basic') {
                        return response;
                    }

                    // Клонируем ответ для кэша
                    const responseToCache = response.clone();

                    caches.open(CACHE_NAME)
                        .then(cache => {
                            cache.put(event.request, responseToCache);
                        });

                    return response;
                });
            })
            .catch(() => {
                // Если запрос не удался, возвращаем офлайн-страницу для навигационных запросов
                if (event.request.destination === 'document') {
                    return caches.match('/offline.html');
                }
                // Для других типов запросов возвращаем базовый ответ
                return new Response('Офлайн-режим. Проверьте подключение к интернету.', {
                    headers: { 'Content-Type': 'text/plain; charset=utf-8' }
                });
            })
    );
});
