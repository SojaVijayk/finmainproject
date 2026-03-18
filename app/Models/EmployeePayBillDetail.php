<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeePayBillDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_pay_bill_id',
        'p_id',
        'base_salary',
        'actual_salary',
        'total_period_salary',
        'adjusted_amount'
    ];

    public function employeePayBill()
    {
        return $this->belongsTo(EmployeePayBill::class);
    }

    public function employee()
    {
        return $this->belongsTo(\App\Models\ProjectEmployee::class, 'p_id', 'p_id');
    }
}
