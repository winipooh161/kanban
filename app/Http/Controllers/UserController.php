<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Получить список всех пользователей для назначения задач
     */
    public function index()
    {
        $users = User::select('id', 'name', 'email')->get();
        return response()->json($users);
    }
}
