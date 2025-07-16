// Упрощенная версия для страницы настроек уведомлений
class NotificationSettingsManager {
    constructor() {
        this.isSupported = 'serviceWorker' in navigator && 'PushManager' in window;
        this.subscription = null;
        this.publicVapidKey = 'BCU7efWE54GP-h47GrzRuGr3LeBkVY2oRqzvGHxXFPwn80Eq4eNC-_mUUrb45eDguxqiBQs4NircHx1OAIjlxTs';
        this.init();
    }

    async init() {
        if (!this.isSupported) {
            document.getElementById('notification-status').textContent = 'Push-уведомления не поддерживаются вашим браузером';
            return;
        }

        try {
            await this.registerServiceWorker();
            await this.checkExistingSubscription();
            this.setupUI();
            this.loadSettings();
        } catch (error) {
            console.error('Ошибка инициализации уведомлений:', error);
            document.getElementById('notification-status').textContent = 'Ошибка инициализации уведомлений';
        }
    }

    async registerServiceWorker() {
        const registration = await navigator.serviceWorker.register('/sw.js', {
            scope: '/'
        });
        
        console.log('Service Worker зарегистрирован:', registration);
        this.registration = registration;
    }

    async checkExistingSubscription() {
        this.subscription = await this.registration.pushManager.getSubscription();
        this.updateUI();
    }

    setupUI() {
        const enableBtn = document.getElementById('enable-notifications');
        const disableBtn = document.getElementById('disable-notifications');
        const testBtn = document.getElementById('test-notification');
        const settingsForm = document.getElementById('notification-settings-form');

        if (enableBtn) {
            enableBtn.addEventListener('click', () => this.enableNotifications());
        }

        if (disableBtn) {
            disableBtn.addEventListener('click', () => this.disableNotifications());
        }

        if (testBtn) {
            testBtn.addEventListener('click', () => this.sendTestNotification());
        }

        if (settingsForm) {
            settingsForm.addEventListener('submit', (e) => this.saveSettings(e));
        }
    }

    updateUI() {
        const enableBtn = document.getElementById('enable-notifications');
        const disableBtn = document.getElementById('disable-notifications');
        const status = document.getElementById('notification-status');

        if (this.subscription) {
            if (enableBtn) enableBtn.style.display = 'none';
            if (disableBtn) disableBtn.style.display = 'inline-block';
            if (status) status.textContent = 'Уведомления включены';
            status.className = 'text-success';
        } else {
            if (enableBtn) enableBtn.style.display = 'inline-block';
            if (disableBtn) disableBtn.style.display = 'none';
            if (status) status.textContent = 'Уведомления отключены';
            status.className = 'text-muted';
        }
    }

    async enableNotifications() {
        try {
            const permission = await Notification.requestPermission();
            
            if (permission !== 'granted') {
                alert('Необходимо разрешить уведомления в браузере');
                return;
            }

            this.subscription = await this.registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: this.urlBase64ToUint8Array(this.publicVapidKey)
            });

            await this.sendSubscriptionToServer(this.subscription);
            this.updateUI();
            
            console.log('Push уведомления включены');
            alert('Уведомления успешно включены!');
        } catch (error) {
            console.error('Ошибка при включении уведомлений:', error);
            alert('Ошибка при включении уведомлений');
        }
    }

    async disableNotifications() {
        try {
            if (this.subscription) {
                await this.subscription.unsubscribe();
                await this.removeSubscriptionFromServer(this.subscription);
                this.subscription = null;
                this.updateUI();
                console.log('Push уведомления отключены');
                alert('Уведомления успешно отключены!');
            }
        } catch (error) {
            console.error('Ошибка при отключении уведомлений:', error);
            alert('Ошибка при отключении уведомлений');
        }
    }

    async sendSubscriptionToServer(subscription) {
        const response = await fetch('/notifications/subscribe', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                subscription: subscription.toJSON()
            })
        });

        if (!response.ok) {
            throw new Error('Ошибка при отправке подписки на сервер');
        }
    }

    async removeSubscriptionFromServer(subscription) {
        await fetch('/notifications/unsubscribe', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                subscription: subscription.toJSON()
            })
        });
    }

    async sendTestNotification() {
        try {
            const response = await fetch('/notifications/test', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const result = await response.json();
            if (result.success) {
                alert('Тестовое уведомление отправлено!');
            } else {
                alert('Ошибка при отправке уведомления');
            }
        } catch (error) {
            console.error('Ошибка при отправке тестового уведомления:', error);
            alert('Ошибка при отправке уведомления');
        }
    }

    async loadSettings() {
        try {
            const response = await fetch('/notifications/settings/api');
            const settings = await response.json();

            document.getElementById('task_assigned').checked = settings.task_assigned;
            document.getElementById('task_due_soon').checked = settings.task_due_soon;
            document.getElementById('task_overdue').checked = settings.task_overdue;
            document.getElementById('task_comments').checked = settings.task_comments;
            document.getElementById('task_moved').checked = settings.task_moved;
            document.getElementById('column_updated').checked = settings.column_updated;
        } catch (error) {
            console.error('Ошибка при загрузке настроек:', error);
        }
    }

    async saveSettings(event) {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        const settings = {};
        
        // Получаем все чекбоксы
        const checkboxes = ['task_assigned', 'task_due_soon', 'task_overdue', 'task_comments', 'task_moved', 'column_updated'];
        
        checkboxes.forEach(name => {
            settings[name] = formData.has(name);
        });

        try {
            const response = await fetch('/notifications/settings', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(settings)
            });

            const result = await response.json();
            if (result.success) {
                alert('Настройки сохранены!');
            } else {
                alert('Ошибка при сохранении настроек');
            }
        } catch (error) {
            console.error('Ошибка при сохранении настроек:', error);
            alert('Ошибка при сохранении настроек');
        }
    }

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
}

// Инициализируем менеджер уведомлений после загрузки DOM
document.addEventListener('DOMContentLoaded', () => {
    window.notificationSettingsManager = new NotificationSettingsManager();
});
