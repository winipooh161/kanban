<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use App\Http\Controllers\NotificationController;
use Carbon\Carbon;

class CheckOverdueTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:check-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Проверяет просроченные задачи и отправляет уведомления';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $notificationController = new NotificationController();
        
        // Находим задачи, которые скоро завершаются (за 1 день)
        $dueSoonTasks = Task::whereNotNull('due_date')
            ->whereDate('due_date', Carbon::tomorrow())
            ->get();

        foreach ($dueSoonTasks as $task) {
            $notificationController->sendTaskDueSoonNotification($task->id);
            $this->info("Отправлено уведомление о приближающемся дедлайне для задачи: {$task->title}");
        }

        // Находим просроченные задачи
        $overdueTasks = Task::whereNotNull('due_date')
            ->whereDate('due_date', '<', Carbon::today())
            ->get();

        foreach ($overdueTasks as $task) {
            $notificationController->sendTaskOverdueNotification($task->id);
            $this->info("Отправлено уведомление о просроченной задаче: {$task->title}");
        }

        $this->info("Проверка завершена. Найдено {$dueSoonTasks->count()} задач с приближающимся дедлайном и {$overdueTasks->count()} просроченных задач.");
        
        return 0;
    }
}
