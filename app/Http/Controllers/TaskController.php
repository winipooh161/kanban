<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Column;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $task->update($request->all());
        
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

        foreach ($request->tasks as $taskData) {
            Task::where('id', $taskData['id'])->update([
                'column_id' => $taskData['column_id'],
                'order' => $taskData['order']
            ]);
        }

        return response()->json(['success' => true]);
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
