<?php
use Illuminate\Support\Facades\DB;

$p = DB::table('employee_payroll')
    ->orderBy('updated_at', 'desc')
    ->take(3)
    ->get();

echo "--- RECENT PAYROLL RECORDS ---\n";
foreach ($p as $row) {
    echo "ID: {$row->id}, PID: {$row->p_id}, Month: {$row->paymonth}, Year: {$row->year}, Updated: {$row->updated_at}\n";
    echo "  PF: {$row->pf}, ProfTax: {$row->professional_tax}, Festival: {$row->festival_allowance}, EPF_Share: {$row->epf_employers_share}, Others: {$row->others}\n";
}
