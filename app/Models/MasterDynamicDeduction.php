<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterDynamicDeduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'default_calculation_type',
        'default_percentage',
        'default_base_amount',
        'default_amount',
        'status'
    ];
}
