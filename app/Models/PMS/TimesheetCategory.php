<?php

namespace App\Models\PMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class TimesheetCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_system'
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_timesheet_categories', 'category_id', 'user_id');
    }

    public function timesheets()
    {
        return $this->hasMany(Timesheet::class,'category_id');
    }
}