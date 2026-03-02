<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    use HasFactory;

    protected $table = 'salary';

    protected $fillable = [
        'p_id',
        'paymonth',
        'year',
        'salary_start_date',
        'salary_end_date',
        'basic_pay',
        'da',
        'hra',
        'conveyance_allowance',
        'medical_allowance',
        'special_allowance',
        'other_allowance',
        'bonus',
        'overtime_pay',
        'attendance_bonus',
        'total_working_days',
        'days_worked',
        'lop_days',
        'gross_salary',
    ];

    public function employee()
    {
        return $this->belongsTo(ProjectEmployee::class, 'p_id', 'p_id');
    }
}
