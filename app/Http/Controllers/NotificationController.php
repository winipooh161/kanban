<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\PushSubscription;
use App\Models\NotificationSetting;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class NotificationController extends Controller
{
    private $webPush;

    public function __construct()
    {
        $this->middleware('auth');
        
        // Инициализируем WebPush с VAPID ключами
        $this->webPush = new WebPush([
            'VAPID' => [
                'subject' => config('app.url'),
                'publicKey' => config('webpush.vapid.public_key'),
                'privateKey' => config('webpush.vapid.private_key'),
            ]
        ]);
    }

    /**
     * Подписка на push-уведомления
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'subscription' => 'required|array',
            'subscription.endpoint' => 'required|string',
            'subscription.keys' => 'required|array',
            'subscription.keys.p256dh' => 'required|string',
            'subscription.keys.auth' => 'required|string',
        ]);

        $user = Auth::user();
        $subscriptionData = $request->input('subscription');

        // Сохраняем или обновляем подписку
        PushSubscription::updateOrCreate(
            [
                'user_id' => $user->id,
                'endpoint' => $subscriptionData['endpoint']
            ],
            [
                'p256dh_key' => $subscriptionData['keys']['p256dh'],
                'auth_token' => $subscriptionData['keys']['auth'],
                'user_agent' => $request->header('User-Agent'),
                'is_active' => true
            ]
        );

        // Создаем настройки уведомлений по умолчанию, если их нет
        NotificationSetting::firstOrCreate(
            ['user_id' => $user->id],
            [
                'task_assigned' => true,
                'task_due_soon' => true,
                'task_overdue' => true,
                'task_comments' => true,
                'task_moved' => false,
                'column_updated' => false,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Подписка на уведомления успешно оформлена'
        ]);
    }

    /**
     * Отписка от push-уведомлений
     */
    public function unsubscribe(Request $request)
    {
        $request->validate([
            'subscription' => 'required|array',
            'subscription.endpoint' => 'required|string',
        ]);

        $user = Auth::user();
        $endpoint = $request->input('subscription.endpoint');

        // Деактивируем подписку
        PushSubscription::where('user_id', $user->id)
            ->where('endpoint', $endpoint)
            ->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Подписка на уведомления отменена'
        ]);
    }

    /**
     * Обновление настроек уведомлений
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'task_assigned' => 'boolean',
            'task_due_soon' => 'boolean',
            'task_overdue' => 'boolean',
            'task_comments' => 'boolean',
            'task_moved' => 'boolean',
            'column_updated' => 'boolean',
        ]);

        $user = Auth::user();

        NotificationSetting::updateOrCreate(
            ['user_id' => $user->id],
            $request->only([
                'task_assigned',
                'task_due_soon', 
                'task_overdue',
                'task_comments',
                'task_moved',
                'column_updated'
            ])
        );

        return response()->json([
            'success' => true,
            'message' => 'Настройки уведомлений обновлены'
        ]);
    }

    /**
     * Получение настроек уведомлений
     */
    public function getSettings()
    {
        $user = Auth::user();
        $settings = NotificationSetting::where('user_id', $user->id)->first();

        if (!$settings) {
            $settings = NotificationSetting::create([
                'user_id' => $user->id,
                'task_assigned' => true,
                'task_due_soon' => true,
                'task_overdue' => true,
                'task_comments' => true,
                'task_moved' => false,
                'column_updated' => false,
            ]);
        }

        return response()->json($settings);
    }

    /**
     * Отправка тестового уведомления
     */
    public function sendTestNotification(Request $request)
    {
        $user = Auth::user();
        
        $this->sendNotificationToUser($user->id, [
            'type' => 'test',
            'title' => 'Тестовое уведомление',
            'body' => 'Это тестовое уведомление от Kanban Board',
            'data' => [
                'url' => '/home'
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Тестовое уведомление отправлено'
        ]);
    }

    /**
     * Отправка уведомления пользователю
     */
    public function sendNotificationToUser($userId, $notificationData)
    {
        $subscriptions = PushSubscription::where('user_id', $userId)
            ->where('is_active', true)
            ->get();

        if ($subscriptions->isEmpty()) {
            return false;
        }

        $settings = NotificationSetting::where('user_id', $userId)->first();
        
        // Проверяем, включен ли данный тип уведомлений
        if ($settings && isset($notificationData['type'])) {
            $settingKey = $notificationData['type'];
            if (isset($settings->{$settingKey}) && !$settings->{$settingKey}) {
                return false; // Этот тип уведомлений отключен
            }
        }

        foreach ($subscriptions as $subscription) {
            try {
                $webPushSubscription = Subscription::create([
                    'endpoint' => $subscription->endpoint,
                    'publicKey' => $subscription->p256dh_key,
                    'authToken' => $subscription->auth_token,
                ]);

                $payload = json_encode($notificationData);
                
                $this->webPush->sendOneNotification(
                    $webPushSubscription,
                    $payload
                );

            } catch (\Exception $e) {
                \Log::error('Failed to send push notification: ' . $e->getMessage());
                
                // Если подписка не валидна, деактивируем её
                if (strpos($e->getMessage(), '410') !== false) {
                    $subscription->update(['is_active' => false]);
                }
            }
        }

        return true;
    }

    /**
     * Отправка уведомления о назначении задачи
     */
    public function sendTaskAssignedNotification($taskId, $userId)
    {
        $task = \App\Models\Task::with(['assignedTo', 'column'])->find($taskId);
        
        if (!$task || !$task->assignedTo) {
            return false;
        }

        $notificationData = [
            'type' => 'task_assigned',
            'title' => 'Новая задача назначена',
            'body' => "Вам назначена задача: {$task->title}",
            'tag' => 'task-assigned',
            'data' => [
                'task_id' => $task->id,
                'task_title' => $task->title,
                'column_title' => $task->column->title,
                'notification_id' => uniqid(),
                'url' => "/home?task={$task->id}"
            ]
        ];

        return $this->sendNotificationToUser($task->assigned_to_id, $notificationData);
    }

    /**
     * Отправка уведомления о приближающемся дедлайне
     */
    public function sendTaskDueSoonNotification($taskId)
    {
        $task = \App\Models\Task::with(['assignedTo', 'user', 'column'])->find($taskId);
        
        if (!$task) {
            return false;
        }

        $dueDate = $task->due_date ? $task->due_date->format('d.m.Y') : 'не указан';
        
        $notificationData = [
            'type' => 'task_due_soon',
            'title' => 'Задача скоро завершается',
            'body' => "Задача \"{$task->title}\" завершается {$dueDate}",
            'tag' => 'task-due-soon',
            'requireInteraction' => true,
            'data' => [
                'task_id' => $task->id,
                'task_title' => $task->title,
                'due_date' => $dueDate,
                'notification_id' => uniqid(),
                'url' => "/home?task={$task->id}"
            ]
        ];

        // Отправляем уведомление назначенному пользователю и создателю задачи
        $userIds = array_filter([$task->assigned_to_id, $task->user_id]);
        $userIds = array_unique($userIds);

        foreach ($userIds as $userId) {
            $this->sendNotificationToUser($userId, $notificationData);
        }

        return true;
    }

    /**
     * Отправка уведомления о просроченной задаче
     */
    public function sendTaskOverdueNotification($taskId)
    {
        $task = \App\Models\Task::with(['assignedTo', 'user', 'column'])->find($taskId);
        
        if (!$task) {
            return false;
        }

        $notificationData = [
            'type' => 'task_overdue',
            'title' => 'Просроченная задача',
            'body' => "Задача \"{$task->title}\" просрочена",
            'tag' => 'task-overdue',
            'requireInteraction' => true,
            'data' => [
                'task_id' => $task->id,
                'task_title' => $task->title,
                'notification_id' => uniqid(),
                'url' => "/home?task={$task->id}"
            ]
        ];

        // Отправляем уведомление назначенному пользователю и создателю задачи
        $userIds = array_filter([$task->assigned_to_id, $task->user_id]);
        $userIds = array_unique($userIds);

        foreach ($userIds as $userId) {
            $this->sendNotificationToUser($userId, $notificationData);
        }

        return true;
    }

    /**
     * Отправка уведомления о перемещении задачи
     */
    public function sendTaskMovedNotification($taskId, $oldColumnId, $newColumnId)
    {
        $task = \App\Models\Task::with(['assignedTo', 'user', 'column'])->find($taskId);
        $oldColumn = \App\Models\Column::find($oldColumnId);
        
        if (!$task || !$oldColumn) {
            return false;
        }

        $notificationData = [
            'type' => 'task_moved',
            'title' => 'Задача перемещена',
            'body' => "Задача \"{$task->title}\" перемещена в колонку \"{$task->column->title}\"",
            'tag' => 'task-moved',
            'data' => [
                'task_id' => $task->id,
                'task_title' => $task->title,
                'old_column' => $oldColumn->title,
                'new_column' => $task->column->title,
                'notification_id' => uniqid(),
                'url' => "/home?task={$task->id}"
            ]
        ];

        // Отправляем уведомление назначенному пользователю и создателю задачи
        $userIds = array_filter([$task->assigned_to_id, $task->user_id]);
        $userIds = array_unique($userIds);

        foreach ($userIds as $userId) {
            $this->sendNotificationToUser($userId, $notificationData);
        }

        return true;
    }

    /**
     * Отметить уведомление как прочитанное
     */
    public function markAsRead(Request $request, $notificationId)
    {
        // Здесь можно реализовать логику отметки уведомления как прочитанного
        // Например, сохранить в базе данных или отправить аналитику
        
        return response()->json([
            'success' => true,
            'message' => 'Уведомление отмечено как прочитанное'
        ]);
    }

    /**
     * Отметить уведомление как закрытое
     */
    public function markAsClosed(Request $request, $notificationId)
    {
        // Здесь можно реализовать логику отметки уведомления как закрытого
        // Например, сохранить в базе данных или отправить аналитику
        
        return response()->json([
            'success' => true,
            'message' => 'Уведомление отмечено как закрытое'
        ]);
    }
}
