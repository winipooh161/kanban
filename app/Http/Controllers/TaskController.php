<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Column;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\NotificationController;

class TaskController extends Controller
{
    /**
     * Получение задачи
     */
    public function show(Task $task)
    {
        $task->load(['user', 'assignedTo']);
        return response()->json($task);
    }

    /**
     * Создание новой задачи
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'column_id' => 'required|exists:columns,id',
            'assigned_to_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|in:low,medium,high',
            'color' => 'nullable|string|max:7'
        ]);

        // Найти максимальный порядок в колонке
        $maxOrder = Task::where('column_id', $request->column_id)->max('order') ?? 0;

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'column_id' => $request->column_id,
            'user_id' => Auth::id(),
            'assigned_to_id' => $request->assigned_to_id,
            'due_date' => $request->due_date,
            'priority' => $request->priority ?? 'medium',
            'color' => $request->color,
            'order' => $maxOrder + 1
        ]);

        // Отправляем уведомление, если задача назначена пользователю
        if ($task->assigned_to_id && $task->assigned_to_id !== Auth::id()) {
            $notificationController = new NotificationController();
            $notificationController->sendTaskAssignedNotification($task->id, $task->assigned_to_id);
        }

        return response()->json($task);
    }

    /**
     * Обновление задачи
     */
    public function update(Request $request, Task $task)
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'column_id' => 'sometimes|exists:columns,id',
            'assigned_to_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|in:low,medium,high',
            'color' => 'nullable|string|max:7',
            'order' => 'sometimes|integer|min:0'
        ]);

        $oldAssignedToId = $task->assigned_to_id;
        $oldColumnId = $task->column_id;

        $task->update($request->all());
        
        $notificationController = new NotificationController();

        // Отправляем уведомление о назначении новому пользователю
        if ($task->assigned_to_id && $task->assigned_to_id !== $oldAssignedToId && $task->assigned_to_id !== Auth::id()) {
            $notificationController->sendTaskAssignedNotification($task->id, $task->assigned_to_id);
        }

        // Отправляем уведомление о перемещении задачи
        if ($task->column_id !== $oldColumnId) {
            $notificationController->sendTaskMovedNotification($task->id, $oldColumnId, $task->column_id);
        }
        
        return response()->json($task);
    }

    /**
     * Обновление позиции задачи (при перетаскивании)
     */
    public function updatePosition(Request $request)
    {
        $request->validate([
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|exists:tasks,id',
            'tasks.*.column_id' => 'required|exists:columns,id',
            'tasks.*.order' => 'required|integer|min:0',
        ]);

        $notificationController = new NotificationController();

        foreach ($request->tasks as $taskData) {
            $task = Task::find($taskData['id']);
            $oldColumnId = $task->column_id;

            Task::where('id', $taskData['id'])->update([
                'column_id' => $taskData['column_id'],
                'order' => $taskData['order']
            ]);

            // Отправляем уведомление о перемещении задачи между колонками
            if ($oldColumnId != $taskData['column_id']) {
                $notificationController->sendTaskMovedNotification($taskData['id'], $oldColumnId, $taskData['column_id']);
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Завершение задачи
     */
    public function complete(Request $request, Task $task)
    {
        // Находим колонку "Выполнено" или создаем её
        $completedColumn = Column::where('title', 'Выполнено')->first();
        
        if (!$completedColumn) {
            $maxOrder = Column::max('order') ?? 0;
            $completedColumn = Column::create([
                'title' => 'Выполнено',
                'order' => $maxOrder + 1,
                'color' => '#28a745'
            ]);
        }

        $oldColumnId = $task->column_id;
        $task->update(['column_id' => $completedColumn->id]);

        // Отправляем уведомление о завершении задачи
        $notificationController = new NotificationController();
        if ($task->user_id !== Auth::id()) {
            // Здесь можно добавить специальное уведомление о завершении задачи
            $notificationController->sendTaskMovedNotification($task->id, $oldColumnId, $completedColumn->id);
        }

        return response()->json(['success' => true, 'task' => $task]);
    }

    /**
     * Удаление задачи
     */
    public function destroy(Task $task)
    {
        $task->delete();
        return response()->json(['success' => true]);
    }
}
