<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $table = 'employee_payroll';

    protected $fillable = [
        'p_id',
        'salary_id',
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
        'festival_allowance',
        'bonus',
        'overtime_pay',
        'attendance_bonus',
        'total_working_days',
        'days_worked',
        'lop_days',
        'pf',
        'employee_contribution',
        'employer_contribution',
        'epf_employers_share',
        'edli_charges',
        'eligible_salary_revised',
        'epf',
        'esi',
        'lic',
        'tds',
        'tds_192_b',
        'tds_194_j',
        'esi_employer',
        'lic_others',
        'loan_deduction',
        'gdf',
        'gpf',
        'others',
        'gross_salary',
        'total_deductions',
        'net_salary',
        'cl_days',
        'sl_days',
        'pl_days',
        'other_leave_days',
        'is_frozen',
    ];

    public function employee()
    {
        return $this->belongsTo(ProjectEmployee::class, 'p_id', 'p_id');
    }
}
