<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDynamicDeduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'p_id',
        'deduction_name',
        'calculation_type',
        'percentage',
        'base_amount',
        'amount'
    ];

    public function employee()
    {
        return $this->belongsTo(\App\Models\ProjectEmployee::class, 'p_id', 'p_id');
    }
}
