<?php

namespace App\Models\PMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimesheetItem extends Model
{
    use HasFactory;

    protected $fillable = ['timesheet_id', 'item_name', 'hours', 'description'];

    public function timesheet()
    {
        return $this->belongsTo(Timesheet::class);
    }
}