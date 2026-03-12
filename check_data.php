<?php
use Illuminate\Support\Facades\DB;

$p = DB::table('employee_payroll')
    ->where('is_frozen', 1)
    ->orderBy('updated_at', 'desc')
    ->take(5)
    ->get();

echo "--- EMPLOYEE_PAYROLL (FROZEN) ---\n";
foreach ($p as $row) {
    echo "ID: {$row->id}, PID: {$row->p_id}, Month: {$row->paymonth}, Year: {$row->year}\n";
    echo "  TDS: {$row->tds}, PF: {$row->pf}, ProfTax: {$row->professional_tax}, Festival: {$row->festival_allowance}\n";
    echo "  Gross: {$row->gross_salary}, Net: {$row->net_salary}\n";
}

$dm = DB::table('deduction_masters')
    ->take(5)
    ->get();

echo "\n--- DEDUCTION_MASTERS ---\n";
foreach ($dm as $row) {
    echo "PID: {$row->p_id}\n";
    echo "  TDS flag: {$row->tds}, val: {$row->tds_value}, amt: {$row->tds_amount}\n";
    echo "  PF flag: {$row->pf}, val: {$row->pf_value}, amt: {$row->pf_amount}\n";
}
