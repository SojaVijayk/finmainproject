<?php

namespace App\Models\PMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Traits\LogsActivity;


class TaskAssignment extends Model
{
    use HasFactory;
    use LogsActivity;
    protected static $recordEvents = ['created', 'updated', 'deleted'];

    protected $fillable = [
        'task_id',
        'user_id'
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}