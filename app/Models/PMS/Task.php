<?php

namespace App\Models\PMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

use App\Traits\LogsActivity;

class Task extends Model
{
    use HasFactory;
     use LogsActivity;
    protected static $recordEvents = ['created', 'updated', 'deleted'];


    protected $fillable = [
        'milestone_id',
        'name',
        'description',
        'start_date',
        'end_date',
        'priority',
         'started_at',
         'completed_at',
         'total_minutes',
        'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
         'started_at'   => 'datetime',
    'completed_at' => 'datetime',
    ];

    // Priority constants
    const PRIORITY_LOW = 1;
    const PRIORITY_MEDIUM = 2;
    const PRIORITY_HIGH = 3;
     const PRIORITY_CRITICAL = 4;

    // Status constants
    const STATUS_NOT_STARTED = 0;
    const STATUS_IN_PROGRESS = 1;
    const STATUS_COMPLETED = 2;

    public function milestone()
    {
        return $this->belongsTo(Milestone::class);
    }

    public function assignments()
    {
        return $this->hasMany(TaskAssignment::class);
    }

    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'task_assignments', 'task_id', 'user_id');
    }
    public function comments()
{
    return $this->hasMany(TaskComment::class);
}

    public function getPriorityNameAttribute()
    {
        return [
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_MEDIUM => 'Medium',
            self::PRIORITY_HIGH => 'High',
              self::PRIORITY_CRITICAL => 'Critical',
        ][$this->priority] ?? 'Unknown';
    }

    public function getStatusNameAttribute()
    {
        return [
            self::STATUS_NOT_STARTED => 'Not Started',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
        ][$this->status] ?? 'Unknown';
    }
    public function getStatusBadgeColorAttribute()
{
    return [
        self::STATUS_NOT_STARTED => 'secondary', // gray
        self::STATUS_IN_PROGRESS => 'warning',     // yellow
        self::STATUS_COMPLETED => 'success',   // green
    ][$this->status] ?? 'dark'; // fallback
}
public function getPriorityBadgeColorAttribute()
    {
        return [
            self::PRIORITY_LOW => 'dark',
            self::PRIORITY_MEDIUM => 'primary',
            self::PRIORITY_HIGH => 'warning',
             self::PRIORITY_CRITICAL => 'danger',
        ][$this->priority] ?? 'Unknown';
    }
}
