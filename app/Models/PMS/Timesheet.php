<?php

namespace App\Models\PMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Traits\LogsActivity;




class Timesheet extends Model
{
    use HasFactory;
    use LogsActivity;
    protected static $recordEvents = ['created', 'updated', 'deleted'];

    protected $fillable = [
        'user_id',
        'date',
        'category_id',
        'project_id',
        'minutes',
        'description'
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(TimesheetCategory::class,'category_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    public function items()
{
    return $this->hasMany(TimesheetItem::class);
}


    public function getHoursAttribute()
    {
        return $this->minutes / 60;
    }

    public function setHoursAttribute($value)
    {
        $this->attributes['minutes'] = $value * 60;
    }

    public function getFormattedTimeAttribute()
    {
        $hours = floor($this->minutes / 60);
        $minutes = $this->minutes % 60;
        return sprintf('%dh %02dm', $hours, $minutes);
    }
}