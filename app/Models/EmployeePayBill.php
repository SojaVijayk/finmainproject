<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeePayBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'pay_item_id',
        'project_id',
        'salary_id',
        'month',
        'year',
        'to_month',
        'to_year',
        'employment_type',
        'status'
    ];

    public function payItem()
    {
        return $this->belongsTo(PayItem::class);
    }

    public function project()
    {
        return $this->belongsTo(\App\Models\Project::class);
    }

    public function details()
    {
        return $this->hasMany(EmployeePayBillDetail::class);
    }
}
