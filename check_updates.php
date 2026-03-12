<?php
use Illuminate\Support\Facades\DB;

$p = DB::table('employee_payroll')
    ->orderBy('updated_at', 'desc')
    ->take(10)
    ->get();

echo "--- MOST RECENT UPDATES TO EMPLOYEE_PAYROLL ---\n";
foreach ($p as $row) {
    echo "ID: {$row->id}, PID: {$row->p_id}, Month: {$row->paymonth}, Year: {$row->year}, PT: {$row->professional_tax}, Festival: {$row->festival_allowance}, Updated: {$row->updated_at}\n";
}
