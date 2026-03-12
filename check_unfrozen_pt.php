<?php
use Illuminate\Support\Facades\DB;

$p = DB::table('employee_payroll')
    ->where('professional_tax', '>', 0)
    ->where('is_frozen', 0)
    ->get();

echo "--- UNFROZEN RECORDS WITH PT > 0 ---\n";
echo "Count: " . $p->count() . "\n";
foreach ($p as $row) {
    echo "ID: {$row->id}, PID: {$row->p_id}, Month: {$row->paymonth}, Year: {$row->year}, PT: {$row->professional_tax}\n";
}
