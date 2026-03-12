<?php
use Illuminate\Support\Facades\DB;

$p = DB::table('employee_payroll')
    ->where('p_id', 'EMP-1773128004965')
    ->where('paymonth', 'March')
    ->where('year', 2026)
    ->first();

echo "--- CHECKING EMP-1773128004965 MARCH 2026 ---\n";
if ($p) {
    echo "PF: {$p->pf}, ProfTax: {$p->professional_tax}, Festival: {$p->festival_allowance}, EPF_Share: {$p->epf_employers_share}, Others: {$p->others}\n";
} else {
    echo "Record not found.\n";
}
