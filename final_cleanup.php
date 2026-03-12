<?php
use Illuminate\Support\Facades\DB;

echo "Cleaning up all misplaced payroll data...\n";

// Move "others" to "professional_tax" for anyone who has others > 0 and PT = 0.
// This handles cases where "PF Tax" was saved to "others" before the mapping update.
$affectedPt = DB::table('employee_payroll')
    ->where('professional_tax', 0)
    ->where('others', '>', 0)
    ->update(['professional_tax' => DB::raw('others'), 'others' => 0]);

echo "Fixed $affectedPt Professional Tax records.\n";

// Move "epf" to "epf_employers_share"
$affectedEpf = DB::table('employee_payroll')
    ->where('epf_employers_share', 0)
    ->where('epf', '>', 0)
    ->update(['epf_employers_share' => DB::raw('epf'), 'epf' => 0]);

echo "Fixed $affectedEpf EPF records.\n";

echo "Final Cleanup Complete.\n";
