<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deduction extends Model
{
    use HasFactory;

    protected $table = 'deduction';

    protected $fillable = [
        'p_id',
        'pf',
        'epf',
        'esi',
        'lic',
        'professional_tax',
        'tds',
        'loan_deduction',
        'gdf',
        'gpf',
        'others',
        'total_deductions',
    ];

    public function employee()
    {
        return $this->belongsTo(ProjectEmployee::class, 'p_id', 'p_id');
    }
}
