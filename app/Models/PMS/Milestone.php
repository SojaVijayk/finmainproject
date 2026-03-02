<?php

namespace App\Models\PMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Traits\LogsActivity;



class Milestone extends Model
{
    use HasFactory;
    use LogsActivity;

protected static $recordEvents = ['created', 'updated', 'deleted'];

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'start_date',
        'end_date',
        'weightage',
        'invoice_trigger',
        'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'invoice_trigger' => 'boolean',
    ];

    // Status constants
    const STATUS_NOT_STARTED = 0;
    const STATUS_IN_PROGRESS = 1;
    const STATUS_COMPLETED = 2;

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
    public function invoice()
{
    return $this->hasOne(Invoice::class);
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
        self::STATUS_NOT_STARTED => 'secondary',  // gray
        self::STATUS_IN_PROGRESS => 'warning',    // yellow
        self::STATUS_COMPLETED => 'success',      // green
    ][$this->status] ?? 'dark'; // fallback color
}

    public function getTaskCompletionPercentageAttribute()
    {
        $totalTasks = $this->tasks->count();
        if ($totalTasks == 0) return 0;

        $completedTasks = $this->tasks->where('status', Task::STATUS_COMPLETED)->count();
        return round(($completedTasks / $totalTasks) * 100, 2);
    }

    public function isAllTasksCompleted()
{
    $totalTasks = $this->tasks()->count();
    if ($totalTasks === 0) {
        return false; // No tasks means cannot complete
    }

    $completedTasks = $this->tasks()
        ->where('status', Task::STATUS_COMPLETED)
        ->count();

    return $totalTasks === $completedTasks;
}
}