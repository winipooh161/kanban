class NotificationManager {
    constructor() {
        this.isSupported = 'serviceWorker' in navigator && 'PushManager' in window;
        this.subscription = null;
        this.publicVapidKey = 'BCU7efWE54GP-h47GrzRuGr3LeBkVY2oRqzvGHxXFPwn80Eq4eNC-_mUUrb45eDguxqiBQs4NircHx1OAIjlxTs';
        this.init();
    }

    // Инициализация менеджера уведомлений
    async init(publicKey) {
        this.publicKey = publicKey;
        
        if (!this.isSupported) {
            console.log('Push notifications are not supported');
            return false;
        }

        // Проверяем текущее разрешение
        const permission = await Notification.requestPermission();
        this.isEnabled = permission === 'granted';
        
        if (this.isEnabled) {
            await this.subscribe();
        }
        
        return this.isEnabled;
    }

    // Запрос разрешения на уведомления
    async requestPermission() {
        if (!this.isSupported) {
            throw new Error('Push notifications are not supported');
        }

        const permission = await Notification.requestPermission();
        this.isEnabled = permission === 'granted';
        
        if (this.isEnabled) {
            await this.subscribe();
        }
        
        return this.isEnabled;
    }

    // Подписка на push-уведомления
    async subscribe() {
        try {
            const registration = await navigator.serviceWorker.ready;
            
            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: this.urlBase64ToUint8Array(this.publicKey)
            });

            this.subscription = subscription;
            
            // Отправляем подписку на сервер
            await this.sendSubscriptionToServer(subscription);
            
            return subscription;
        } catch (error) {
            console.error('Failed to subscribe to push notifications:', error);
            throw error;
        }
    }

    // Отписка от push-уведомлений
    async unsubscribe() {
        if (this.subscription) {
            try {
                await this.subscription.unsubscribe();
                await this.removeSubscriptionFromServer();
                this.subscription = null;
                this.isEnabled = false;
                return true;
            } catch (error) {
                console.error('Failed to unsubscribe from push notifications:', error);
                throw error;
            }
        }
        return false;
    }

    // Отправка подписки на сервер
    async sendSubscriptionToServer(subscription) {
        try {
            const response = await fetch('/notifications/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    subscription: subscription.toJSON()
                })
            });

            if (!response.ok) {
                throw new Error('Failed to send subscription to server');
            }

            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error sending subscription to server:', error);
            throw error;
        }
    }

    // Удаление подписки с сервера
    async removeSubscriptionFromServer() {
        try {
            const response = await fetch('/notifications/unsubscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    subscription: this.subscription.toJSON()
                })
            });

            if (!response.ok) {
                throw new Error('Failed to remove subscription from server');
            }

            return await response.json();
        } catch (error) {
            console.error('Error removing subscription from server:', error);
            throw error;
        }
    }

    // Отправка тестового уведомления
    async sendTestNotification() {
        try {
            const response = await fetch('/notifications/test', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error('Failed to send test notification');
            }

            return await response.json();
        } catch (error) {
            console.error('Error sending test notification:', error);
            throw error;
        }
    }

    // Показ локального уведомления (fallback)
    showLocalNotification(title, options = {}) {
        if ('Notification' in window && Notification.permission === 'granted') {
            const notification = new Notification(title, {
                icon: '/icon-192x192.svg',
                badge: '/icon-192x192.svg',
                ...options
            });

            // Автоматически закрываем через 5 секунд
            setTimeout(() => {
                notification.close();
            }, 5000);

            return notification;
        }
        return null;
    }

    // Вспомогательная функция для конвертации ключа
    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    // Проверка статуса подписки
    async getSubscriptionStatus() {
        try {
            const registration = await navigator.serviceWorker.ready;
            const subscription = await registration.pushManager.getSubscription();
            
            this.subscription = subscription;
            this.isEnabled = subscription !== null;
            
            return {
                isSupported: this.isSupported,
                isEnabled: this.isEnabled,
                hasSubscription: subscription !== null
            };
        } catch (error) {
            console.error('Error checking subscription status:', error);
            return {
                isSupported: this.isSupported,
                isEnabled: false,
                hasSubscription: false
            };
        }
    }

    // Обновление настроек уведомлений
    async updateNotificationSettings(settings) {
        try {
            const response = await fetch('/notifications/settings', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(settings)
            });

            if (!response.ok) {
                throw new Error('Failed to update notification settings');
            }

            return await response.json();
        } catch (error) {
            console.error('Error updating notification settings:', error);
            throw error;
        }
    }
}

// Создаем глобальный экземпляр
window.notificationManager = new NotificationManager();

// Обработчик сообщений от Service Worker
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.addEventListener('message', event => {
        if (event.data && event.data.type === 'notification_action') {
            console.log('Received message from SW:', event.data);
            
            // Обрабатываем действия уведомлений
            switch(event.data.action) {
                case 'view_task':
                    if (event.data.data.task_id) {
                        // Прокручиваем к задаче или открываем модальное окно
                        const taskElement = document.querySelector(`[data-task-id="${event.data.data.task_id}"]`);
                        if (taskElement) {
                            taskElement.scrollIntoView({ behavior: 'smooth' });
                            taskElement.classList.add('highlight-task');
                            setTimeout(() => {
                                taskElement.classList.remove('highlight-task');
                            }, 3000);
                        }
                    }
                    break;
                case 'mark_completed':
                    // Обновляем интерфейс
                    window.location.reload();
                    break;
                default:
                    console.log('Unknown notification action:', event.data.action);
            }
        }
    });
}
