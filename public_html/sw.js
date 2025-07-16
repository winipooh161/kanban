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

// Обработка push-уведомлений
self.addEventListener('push', event => {
    console.log('Push notification received:', event);
    
    let notificationData = {
        title: 'Kanban Board',
        body: 'У вас новое уведомление',
        icon: '/icon-192x192.svg',
        badge: '/icon-192x192.svg',
        tag: 'kanban-notification',
        data: {
            url: '/home'
        }
    };

    if (event.data) {
        try {
            const payload = event.data.json();
            notificationData = {
                ...notificationData,
                ...payload
            };
        } catch (e) {
            console.error('Error parsing push data:', e);
        }
    }

    const promiseChain = self.registration.showNotification(
        notificationData.title,
        {
            body: notificationData.body,
            icon: notificationData.icon,
            badge: notificationData.badge,
            tag: notificationData.tag,
            data: notificationData.data,
            requireInteraction: notificationData.requireInteraction || false,
            actions: notificationData.actions || []
        }
    );

    event.waitUntil(promiseChain);
});

// Обработка клика по уведомлению
self.addEventListener('notificationclick', event => {
    console.log('Notification clicked:', event);
    
    event.notification.close();

    // Определяем URL для открытия
    let urlToOpen = '/home';
    if (event.notification.data && event.notification.data.url) {
        urlToOpen = event.notification.data.url;
    }

    // Открываем или фокусируем окно приложения
    const promiseChain = clients.matchAll({
        type: 'window',
        includeUncontrolled: true
    }).then(windowClients => {
        // Ищем уже открытое окно с нашим приложением
        for (let i = 0; i < windowClients.length; i++) {
            const client = windowClients[i];
            if (client.url.includes(self.location.origin)) {
                // Если окно найдено, фокусируемся на нем и переходим на нужную страницу
                return client.focus().then(() => {
                    return client.navigate(urlToOpen);
                });
            }
        }
        // Если окно не найдено, открываем новое
        return clients.openWindow(urlToOpen);
    });

    event.waitUntil(promiseChain);
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
self.addEventListener('notificationclick', event => {
    console.log('Notification clicked:', event);
    
    event.notification.close();
    
    const action = event.action;
    const data = event.notification.data;
    
    let url = '/home';
    
    switch(action) {
        case 'view_task':
            if (data.task_id) {
                url = `/home?task=${data.task_id}`;
            }
            break;
        case 'view_board':
            url = '/home';
            break;
        case 'mark_read':
            // Отправляем запрос на сервер для отметки как прочитанное
            if (data.notification_id) {
                fetch(`/notifications/${data.notification_id}/read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
            }
            return;
        case 'mark_completed':
            if (data.task_id) {
                // Отправляем запрос на завершение задачи
                fetch(`/tasks/${data.task_id}/complete`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                url = '/home';
            }
            break;
        default:
            if (data.task_id) {
                url = `/home?task=${data.task_id}`;
            }
    }
    
    // Открываем или фокусируемся на существующем окне
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then(clientList => {
                // Проверяем, есть ли уже открытое окно
                for (let client of clientList) {
                    if (client.url.includes('/home') && 'focus' in client) {
                        client.postMessage({
                            type: 'notification_action',
                            action: action,
                            data: data
                        });
                        return client.focus();
                    }
                }
                // Если нет открытого окна, открываем новое
                if (clients.openWindow) {
                    return clients.openWindow(url);
                }
            })
    );
});

// Обработка закрытия уведомления
self.addEventListener('notificationclose', event => {
    console.log('Notification closed:', event);
    
    // Можно отправить аналитику о закрытии уведомления
    const data = event.notification.data;
    if (data.notification_id) {
        fetch(`/notifications/${data.notification_id}/closed`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
    }
});
