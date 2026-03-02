<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectEmployee extends Model
{
    use HasFactory;

    protected $table = 'project_employee';

    protected $fillable = [
        'user_id',
        'p_id',
        'empId',
        'employee_code',
        'name',
        'last_name',
        'email',
        'mobile',
        'age',
        'dob',
        'address',
        'designation_id',
        'date_of_joining',
        'employment_type',
        'service_id',
        'employee_code',
        'status',
        'project_id',
        'documents',
        'bank_name',
        'account_no',
        'account_name',
        'branch',
        'ifsc_code'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->hasOne(Service::class, 'p_id', 'p_id')->where('status', 1);
    }

    public function services()
    {
        return $this->hasMany(Service::class, 'p_id', 'p_id')->orderBy('start_date', 'desc');
    }

    public function salary()
    {
        return $this->hasOne(Salary::class, 'p_id', 'p_id');
    }

    public function deduction()
    {
        return $this->hasOne(Deduction::class, 'p_id', 'p_id');
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }

    public function payroll()
    {
        return $this->hasMany(Payroll::class, 'p_id', 'p_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
