<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Column;
use App\Models\Task;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard with Kanban board.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Получаем все колонки с задачами
        $columns = Column::with(['tasks' => function($query) {
            $query->with(['user', 'assignedTo'])->orderBy('order');
        }])->orderBy('order')->get();
        
        // Если колонок нет, создаем стандартные
        if ($columns->isEmpty()) {
            $defaultColumns = [
                ['title' => 'К выполнению', 'order' => 1, 'color' => '#f0f0f0'],
                ['title' => 'В процессе', 'order' => 2, 'color' => '#ffd700'],
                ['title' => 'Выполнено', 'order' => 3, 'color' => '#90ee90']
            ];
            
            foreach ($defaultColumns as $columnData) {
                Column::create($columnData);
            }
            
            $columns = Column::orderBy('order')->get();
        }
        
        return view('home', compact('columns'));
    }
}
