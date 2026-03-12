<?php
use Illuminate\Support\Facades\DB;

$p = DB::table('employee_payroll')
    ->where('is_frozen', 1)
    ->orderBy('updated_at', 'desc')
    ->take(10)
    ->get();

echo "--- RECENT FROZEN PAYROLL RECORDS ---\n";
foreach ($p as $row) {
    echo "ID: {$row->id}, PID: {$row->p_id}, Month: {$row->paymonth}, Year: {$row->year}, PT: {$row->professional_tax}, Festival: {$row->festival_allowance}\n";
}
