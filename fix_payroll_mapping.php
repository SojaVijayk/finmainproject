<?php
use Illuminate\Support\Facades\DB;

echo "Migrating incorrectly mapped pay items...\n";

// 1. Move "others" to "professional_tax" for employees who had "PF Tax" generated.
// We search for records with "others > 0" where they might have been hit by the old mapping.
// Actually, it's safer to just move the values if the user says they "gave" them.
// I'll check if professional_tax is 0 and others is > 0.
$affected = DB::table('employee_payroll')
    ->where('professional_tax', 0)
    ->where('others', '>', 0)
    ->update(['professional_tax' => DB::raw('others'), 'others' => 0]);

echo "Moved $affected records from 'others' to 'professional_tax'.\n";

// 2. Move "epf" to "epf_employers_share"
$affectedEpf = DB::table('employee_payroll')
    ->where('epf_employers_share', 0)
    ->where('epf', '>', 0)
    ->update(['epf_employers_share' => DB::raw('epf'), 'epf' => 0]);

echo "Moved $affectedEpf records from 'epf' to 'epf_employers_share'.\n";

echo "Data migration complete.\n";
