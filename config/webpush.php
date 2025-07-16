<?php

return [
    /*
    |--------------------------------------------------------------------------
    | VAPID Configuration
    |--------------------------------------------------------------------------
    |
    | Voluntary Application Server Identification (VAPID) keys for push notifications.
    | Generate keys using: vendor/bin/web-push generate-vapid-keys
    |
    */
    'vapid' => [
        'subject' => env('VAPID_SUBJECT', 'mailto:admin@example.com'),
        'public_key' => env('VAPID_PUBLIC_KEY', 'BP8ZxB6_QLl1KqB-VYu8wjOzQkUqLjp2xLXVQN1_5wK7fZe6RNgkp8lUUv8YQTQ_9QnU_YvQTQs9'),
        'private_key' => env('VAPID_PRIVATE_KEY', 'example-private-key'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Types
    |--------------------------------------------------------------------------
    |
    | Configuration for different types of notifications
    |
    */
    'types' => [
        'task_assigned' => [
            'title' => 'Новая задача назначена',
            'icon' => '/icon-192x192.svg',
            'badge' => '/icon-192x192.svg',
            'vibrate' => [200, 100, 200],
            'actions' => [
                ['action' => 'view_task', 'title' => 'Посмотреть'],
                ['action' => 'mark_read', 'title' => 'Отметить прочитанным']
            ]
        ],
        'task_due_soon' => [
            'title' => 'Задача скоро завершается',
            'icon' => '/icon-192x192.svg',
            'badge' => '/icon-192x192.svg',
            'vibrate' => [200, 100, 200, 100, 200],
            'requireInteraction' => true,
            'actions' => [
                ['action' => 'view_task', 'title' => 'Посмотреть'],
                ['action' => 'extend_deadline', 'title' => 'Продлить срок']
            ]
        ],
        'task_overdue' => [
            'title' => 'Просроченная задача',
            'icon' => '/icon-192x192.svg',
            'badge' => '/icon-192x192.svg',
            'vibrate' => [300, 100, 300, 100, 300],
            'requireInteraction' => true,
            'actions' => [
                ['action' => 'view_task', 'title' => 'Посмотреть'],
                ['action' => 'mark_completed', 'title' => 'Завершить']
            ]
        ],
        'task_comment' => [
            'title' => 'Новый комментарий',
            'icon' => '/icon-192x192.svg',
            'badge' => '/icon-192x192.svg',
            'vibrate' => [200, 100, 200],
            'actions' => [
                ['action' => 'view_task', 'title' => 'Посмотреть'],
                ['action' => 'reply', 'title' => 'Ответить']
            ]
        ],
        'task_moved' => [
            'title' => 'Задача перемещена',
            'icon' => '/icon-192x192.svg',
            'badge' => '/icon-192x192.svg',
            'vibrate' => [200, 100, 200],
            'actions' => [
                ['action' => 'view_task', 'title' => 'Посмотреть']
            ]
        ],
        'column_updated' => [
            'title' => 'Колонка обновлена',
            'icon' => '/icon-192x192.svg',
            'badge' => '/icon-192x192.svg',
            'vibrate' => [200, 100, 200],
            'actions' => [
                ['action' => 'view_board', 'title' => 'Открыть доску']
            ]
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    |
    | Default notification settings for new users
    |
    */
    'default_settings' => [
        'task_assigned' => true,
        'task_due_soon' => true,
        'task_overdue' => true,
        'task_comments' => true,
        'task_moved' => false,
        'column_updated' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | TTL (Time To Live)
    |--------------------------------------------------------------------------
    |
    | How long notifications should be stored on push servers if device is offline
    | Value in seconds (86400 = 24 hours)
    |
    */
    'ttl' => env('WEBPUSH_TTL', 86400),

    /*
    |--------------------------------------------------------------------------
    | Urgency
    |--------------------------------------------------------------------------
    |
    | Priority of the notification. Can be 'very-low', 'low', 'normal', 'high'
    |
    */
    'urgency' => env('WEBPUSH_URGENCY', 'normal'),

    /*
    |--------------------------------------------------------------------------
    | Topic
    |--------------------------------------------------------------------------
    |
    | A topic is a string that can be used to replace pending notifications
    | with newer ones
    |
    */
    'topic' => env('WEBPUSH_TOPIC', null),
];
