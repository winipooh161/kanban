<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Column;
use App\Models\Task;
use App\Models\User;

class KanbanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем стандартные колонки
        $todoColumn = Column::create([
            'title' => 'К выполнению',
            'order' => 1,
            'color' => '#f0f0f0'
        ]);
        
        $inProgressColumn = Column::create([
            'title' => 'В процессе',
            'order' => 2,
            'color' => '#ffd700'
        ]);
        
        $doneColumn = Column::create([
            'title' => 'Выполнено',
            'order' => 3,
            'color' => '#90ee90'
        ]);
        
        // Получаем пользователя (если есть) или используем ID 1
        $user = User::first() ?? ['id' => 1];
        
        // Создаем примеры задач
        Task::create([
            'column_id' => $todoColumn->id,
            'user_id' => $user->id,
            'title' => 'Изучить Laravel',
            'description' => 'Изучить основы фреймворка Laravel',
            'due_date' => now()->addDays(7),
            'priority' => 'high',
            'order' => 0,
            'color' => '#e74c3c'
        ]);
        
        Task::create([
            'column_id' => $todoColumn->id,
            'user_id' => $user->id,
            'title' => 'Создать базу данных',
            'description' => 'Спроектировать и создать структуру БД для проекта',
            'due_date' => now()->addDays(3),
            'priority' => 'medium',
            'order' => 1,
            'color' => '#3498db'
        ]);
        
        Task::create([
            'column_id' => $inProgressColumn->id,
            'user_id' => $user->id,
            'title' => 'Разработать интерфейс',
            'description' => 'Создать пользовательский интерфейс для Kanban доски',
            'due_date' => now()->addDays(5),
            'priority' => 'medium',
            'order' => 0,
            'color' => '#2ecc71'
        ]);
        
        Task::create([
            'column_id' => $doneColumn->id,
            'user_id' => $user->id,
            'title' => 'Настроить проект',
            'description' => 'Инициализировать проект Laravel и настроить окружение',
            'due_date' => now()->subDays(1),
            'priority' => 'low',
            'order' => 0,
            'color' => '#9b59b6'
        ]);
    }
}
