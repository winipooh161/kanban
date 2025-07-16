<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- PWA -->
    <meta name="theme-color" content="#007bff">
    <link rel="manifest" href="/manifest.json">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="Kanban Board">
    <link rel="apple-touch-icon" href="/icon-192x192.svg">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    
    <style>
        .kanban-board {
            min-height: calc(100vh - 150px);
        }
        .kanban-column {
            min-width: 300px;
        }
        .task-card {
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        .task-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .task-actions {
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        .task-card:hover .task-actions {
            visibility: visible;
            opacity: 1;
        }
        .card-header {
            cursor: grab;
        }
        .card-header:active {
            cursor: grabbing;
        }
        .highlight-task {
            animation: highlight 3s ease-in-out;
        }
        @keyframes highlight {
            0% { background-color: #fff3cd; }
            50% { background-color: #ffeaa7; }
            100% { background-color: transparent; }
        }
        .notification-status {
            font-size: 0.8em;
            padding: 2px 6px;
            border-radius: 12px;
        }
        .notification-enabled {
            background-color: #d4edda;
            color: #155724;
        }
        .notification-disabled {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('notifications.settings.page') }}">
                                        <i class="fas fa-bell me-2"></i>Настройки уведомлений
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>
    
    @yield('scripts')
    
    <!-- Notifications Script -->
    <script src="/js/notifications.js"></script>
    
    <!-- PWA Service Worker -->
    <script>
        // VAPID публичный ключ (должен совпадать с серверным)
        const VAPID_PUBLIC_KEY = 'BP8ZxB6_QLl1KqB-VYu8wjOzQkUqLjp2xLXVQN1_5wK7fZe6RNgkp8lUUv8YQTQ_9QnU_YvQTQs9';
        
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(function(registration) {
                        console.log('ServiceWorker registration successful');
                        
                        // Инициализируем уведомления после регистрации SW
                        if (window.notificationManager) {
                            window.notificationManager.init(VAPID_PUBLIC_KEY);
                        }
                    })
                    .catch(function(err) {
                        console.log('ServiceWorker registration failed: ', err);
                    });
            });
        }
        
        // PWA install prompt
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            deferredPrompt = e;
        });
        
        // Функция для открытия настроек уведомлений
        function openNotificationSettings() {
            showNotificationModal();
        }
        
        // Показать модальное окно настроек уведомлений
        async function showNotificationModal() {
            const status = await window.notificationManager.getSubscriptionStatus();
            
            const modalHtml = `
                <div class="modal fade" id="notificationModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Настройки уведомлений</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span>Push-уведомления</span>
                                        <span class="notification-status ${status.isEnabled ? 'notification-enabled' : 'notification-disabled'}">
                                            ${status.isEnabled ? 'Включены' : 'Отключены'}
                                        </span>
                                    </div>
                                    <small class="text-muted">Получайте уведомления о новых задачах и изменениях</small>
                                </div>
                                
                                <div class="mb-3">
                                    <h6>Типы уведомлений:</h6>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="taskAssigned" checked>
                                        <label class="form-check-label" for="taskAssigned">
                                            Назначение новых задач
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="taskDueSoon" checked>
                                        <label class="form-check-label" for="taskDueSoon">
                                            Приближение дедлайна
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="taskOverdue" checked>
                                        <label class="form-check-label" for="taskOverdue">
                                            Просроченные задачи
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="taskComments" checked>
                                        <label class="form-check-label" for="taskComments">
                                            Комментарии к задачам
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="taskMoved">
                                        <label class="form-check-label" for="taskMoved">
                                            Перемещение задач
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                <button type="button" class="btn btn-primary" onclick="saveNotificationSettings()">Сохранить</button>
                                ${status.isEnabled ? 
                                    '<button type="button" class="btn btn-warning" onclick="disableNotifications()">Отключить</button>' :
                                    '<button type="button" class="btn btn-success" onclick="enableNotifications()">Включить</button>'
                                }
                                <button type="button" class="btn btn-info" onclick="testNotification()">Тест</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Удаляем старое модальное окно, если есть
            const existingModal = document.getElementById('notificationModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            // Добавляем новое модальное окно
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            // Показываем модальное окно
            const modal = new bootstrap.Modal(document.getElementById('notificationModal'));
            modal.show();
        }
        
        // Включить уведомления
        async function enableNotifications() {
            try {
                const enabled = await window.notificationManager.requestPermission();
                if (enabled) {
                    alert('Уведомления включены!');
                    document.getElementById('notificationModal').querySelector('.btn-close').click();
                } else {
                    alert('Не удалось включить уведомления. Проверьте настройки браузера.');
                }
            } catch (error) {
                console.error('Error enabling notifications:', error);
                alert('Ошибка при включении уведомлений');
            }
        }
        
        // Отключить уведомления
        async function disableNotifications() {
            try {
                await window.notificationManager.unsubscribe();
                alert('Уведомления отключены');
                document.getElementById('notificationModal').querySelector('.btn-close').click();
            } catch (error) {
                console.error('Error disabling notifications:', error);
                alert('Ошибка при отключении уведомлений');
            }
        }
        
        // Сохранить настройки уведомлений
        async function saveNotificationSettings() {
            const settings = {
                task_assigned: document.getElementById('taskAssigned').checked,
                task_due_soon: document.getElementById('taskDueSoon').checked,
                task_overdue: document.getElementById('taskOverdue').checked,
                task_comments: document.getElementById('taskComments').checked,
                task_moved: document.getElementById('taskMoved').checked
            };
            
            try {
                await window.notificationManager.updateNotificationSettings(settings);
                alert('Настройки сохранены!');
                document.getElementById('notificationModal').querySelector('.btn-close').click();
            } catch (error) {
                console.error('Error saving notification settings:', error);
                alert('Ошибка при сохранении настроек');
            }
        }
        
        // Тестовое уведомление
        async function testNotification() {
            try {
                await window.notificationManager.sendTestNotification();
                alert('Тестовое уведомление отправлено!');
            } catch (error) {
                console.error('Error sending test notification:', error);
                alert('Ошибка при отправке тестового уведомления');
            }
        }
    </script>
</body>
</html>
