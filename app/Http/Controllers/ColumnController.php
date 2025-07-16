<?php

namespace App\Http\Controllers;

use App\Models\Column;
use Illuminate\Http\Request;

class ColumnController extends Controller
{
    /**
     * Создание новой колонки
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
        ]);

        // Найти максимальный порядок
        $maxOrder = Column::max('order') ?? 0;

        $column = Column::create([
            'title' => $request->title,
            'color' => $request->color ?? '#f0f0f0',
            'order' => $maxOrder + 1
        ]);

        return response()->json($column);
    }

    /**
     * Обновление колонки
     */
    public function update(Request $request, Column $column)
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'color' => 'nullable|string|max:7',
            'order' => 'sometimes|integer|min:0'
        ]);

        $column->update($request->all());
        
        return response()->json($column);
    }

    /**
     * Обновление порядка колонок
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'columns' => 'required|array',
            'columns.*.id' => 'required|exists:columns,id',
            'columns.*.order' => 'required|integer|min:0',
        ]);

        foreach ($request->columns as $columnData) {
            Column::where('id', $columnData['id'])->update(['order' => $columnData['order']]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Удаление колонки
     */
    public function destroy(Column $column)
    {
        $column->delete();
        return response()->json(['success' => true]);
    }
}
