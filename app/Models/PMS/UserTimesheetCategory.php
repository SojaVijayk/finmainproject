<?php

namespace App\Models\PMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserTimesheetCategory extends Model
{
    use HasFactory;

    protected $table = 'user_timesheet_categories';

    protected $fillable = [
        'user_id',
        'category_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(TimesheetCategory::class);
    }
}
