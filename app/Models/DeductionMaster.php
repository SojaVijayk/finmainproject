<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeductionMaster extends Model
{
    use HasFactory;

    protected $fillable = [
        'p_id',
        'tds', 'tds_value', 'tds_type', 'tds_amount',
        'epf', 'epf_value', 'epf_type', 'epf_amount',
        'pf', 'pf_value', 'pf_type', 'pf_amount',
        'lic', 'lic_value', 'lic_type', 'lic_amount',
        'edli', 'edli_value', 'edli_type', 'edli_amount',
        'other', 'other_value', 'other_type', 'other_amount',
        'tds_192_b', 'tds_192_b_value', 'tds_192_b_type', 'tds_192_b_amount',
        'tds_194_j', 'tds_194_j_value', 'tds_194_j_type', 'tds_194_j_amount',
        'professional_tax', 'professional_tax_value', 'professional_tax_type', 'professional_tax_amount',
        'esi_employer', 'esi_employer_value', 'esi_employer_type', 'esi_employer_amount',
        'other_details',
        'status'
    ];

    public function employee()
    {
        return $this->belongsTo(\App\Models\ProjectEmployee::class, 'p_id', 'p_id');
    }
}
