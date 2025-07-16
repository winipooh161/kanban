<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ColumnController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

// Маршруты для Kanban, доступные только аутентифицированным пользователям
Route::middleware(['auth'])->group(function () {
    // Маршрут для получения списка пользователей
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    
    // Маршруты для задач
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::post('/tasks/position', [TaskController::class, 'updatePosition'])->name('tasks.position');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    
    // Маршруты для колонок
    Route::post('/columns', [ColumnController::class, 'store'])->name('columns.store');
    Route::put('/columns/{column}', [ColumnController::class, 'update'])->name('columns.update');
    Route::post('/columns/order', [ColumnController::class, 'updateOrder'])->name('columns.order');
    Route::delete('/columns/{column}', [ColumnController::class, 'destroy'])->name('columns.destroy');
});
if (app()->environment('production')) {
    URL::forceScheme('https');
}