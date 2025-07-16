<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'task_assigned',
        'task_due_soon',
        'task_overdue',
        'task_comments',
        'task_moved',
        'column_updated'
    ];

    protected $casts = [
        'task_assigned' => 'boolean',
        'task_due_soon' => 'boolean',
        'task_overdue' => 'boolean',
        'task_comments' => 'boolean',
        'task_moved' => 'boolean',
        'column_updated' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
