<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ColumnController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;

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
    Route::post('/tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
    
    // Маршруты для колонок
    Route::post('/columns', [ColumnController::class, 'store'])->name('columns.store');
    Route::put('/columns/{column}', [ColumnController::class, 'update'])->name('columns.update');
    Route::post('/columns/order', [ColumnController::class, 'updateOrder'])->name('columns.order');
    Route::delete('/columns/{column}', [ColumnController::class, 'destroy'])->name('columns.destroy');
    
    // Маршруты для push-уведомлений
    Route::get('/notifications/settings', function() {
        return view('notifications.settings');
    })->name('notifications.settings.page');
    Route::get('/notifications/settings/api', [NotificationController::class, 'getSettings'])->name('notifications.settings.get');
    Route::post('/notifications/subscribe', [NotificationController::class, 'subscribe'])->name('notifications.subscribe');
    Route::post('/notifications/unsubscribe', [NotificationController::class, 'unsubscribe'])->name('notifications.unsubscribe');
    Route::post('/notifications/settings', [NotificationController::class, 'updateSettings'])->name('notifications.settings');
    Route::post('/notifications/test', [NotificationController::class, 'sendTestNotification'])->name('notifications.test');
    Route::post('/notifications/{notificationId}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/{notificationId}/closed', [NotificationController::class, 'markAsClosed'])->name('notifications.closed');
});
if (app()->environment('production')) {
    URL::forceScheme('https');
}