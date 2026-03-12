<?php
use Illuminate\Support\Facades\DB;
use App\Models\EmployeeDynamicDeduction;
use App\Models\DeductionMaster;

echo "Starting one-time migration of existing dynamic deductions to deduction_masters...\n";

$employees = EmployeeDynamicDeduction::select("p_id")->distinct()->get();

$dedmap = [
    'TDS' => 'tds', 'EPF' => 'epf', 'PF' => 'pf', 'LIC' => 'lic', 'EDLI' => 'edli',
    'TDS 192 B' => 'tds_192_b', 'TDS 194 J' => 'tds_194_j', 'PROFESSIONAL TAX' => 'professional_tax',
    'ESI EMPLOYER' => 'esi_employer', 'OTHER' => 'other'
];

foreach($employees as $emp) {
    $p_id = $emp->p_id;

    $masterData = [
        'tds' => 0, 'tds_value' => 0, 'tds_type' => 'amount', 'tds_amount' => 0,
        'epf' => 0, 'epf_value' => 0, 'epf_type' => 'amount', 'epf_amount' => 0,
        'pf' => 0, 'pf_value' => 0, 'pf_type' => 'amount', 'pf_amount' => 0,
        'lic' => 0, 'lic_value' => 0, 'lic_type' => 'amount', 'lic_amount' => 0,
        'edli' => 0, 'edli_value' => 0, 'edli_type' => 'amount', 'edli_amount' => 0,
        'tds_192_b' => 0, 'tds_192_b_value' => 0, 'tds_192_b_type' => 'amount', 'tds_192_b_amount' => 0,
        'tds_194_j' => 0, 'tds_194_j_value' => 0, 'tds_194_j_type' => 'amount', 'tds_194_j_amount' => 0,
        'professional_tax' => 0, 'professional_tax_value' => 0, 'professional_tax_type' => 'amount', 'professional_tax_amount' => 0,
        'esi_employer' => 0, 'esi_employer_value' => 0, 'esi_employer_type' => 'amount', 'esi_employer_amount' => 0,
        'other' => 0, 'other_value' => 0, 'other_type' => 'amount', 'other_amount' => 0,
    ];

    $deds = EmployeeDynamicDeduction::where('p_id', $p_id)->get();
    foreach($deds as $ded) {
        $dedName = $ded->deduction_name;
        $calcType = $ded->calculation_type;
        $col = $dedmap[strtoupper(trim($dedName))] ?? null;

        if ($col) {
            $isPercent = (strpos($calcType, 'percent') !== false);
            $masterData[$col] = 1; 
            $masterData[$col.'_type'] = $isPercent ? 'percentage' : 'amount';
            $masterData[$col.'_value'] = $isPercent ? ($ded->percentage ?? 0) : ($ded->amount ?? 0);
            $masterData[$col.'_amount'] = $ded->amount ?? 0;
        }
    }

    DeductionMaster::updateOrCreate(['p_id' => $p_id], $masterData);
}

echo "Migration complete.\n";
