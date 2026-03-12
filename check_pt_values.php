<?php
use Illuminate\Support\Facades\DB;

$p = DB::table('employee_payroll')
    ->where('professional_tax', '>', 0)
    ->get();

echo "--- RECORDS WITH PROFESSIONAL_TAX > 0 ---\n";
foreach ($p as $row) {
    echo "ID: {$row->id}, PID: {$row->p_id}, Month: {$row->paymonth}, Year: {$row->year}, PT: {$row->professional_tax}\n";
}

$f = DB::table('employee_payroll')
    ->where('festival_allowance', '>', 0)
    ->get();

echo "\n--- RECORDS WITH FESTIVAL_ALLOWANCE > 0 ---\n";
foreach ($f as $row) {
    echo "ID: {$row->id}, PID: {$row->p_id}, Month: {$row->paymonth}, Year: {$row->year}, Festival: {$row->festival_allowance}\n";
}
