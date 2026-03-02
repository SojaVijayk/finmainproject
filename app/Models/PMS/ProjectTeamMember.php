<?php

namespace App\Models\PMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Traits\LogsActivity;



class ProjectTeamMember extends Model
{
    use HasFactory;

    use LogsActivity;

protected static $recordEvents = ['created', 'updated', 'deleted'];
    protected $fillable = [
        'project_id',
        'user_id',
        'role',
        'expected_time_investment_minutes',
        'cost_share'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getExpectedTimeInvestmentHoursAttribute()
    {
        return $this->expected_time_investment_minutes / 60;
    }

    public function setExpectedTimeInvestmentHoursAttribute($value)
    {
        $this->attributes['expected_time_investment_minutes'] = $value * 60;
    }
}
