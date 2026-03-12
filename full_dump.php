<?php
use Illuminate\Support\Facades\DB;

$p = DB::table('employee_payroll')
    ->where('p_id', 'EMP-1773128004965')
    ->where('paymonth', 'March')
    ->where('year', 2026)
    ->first();

echo "--- FULL DUMP OF EMP-1773128004965 MARCH 2026 ---\n";
if ($p) {
    print_r($p);
} else {
    echo "Record not found.\n";
}
