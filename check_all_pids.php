<?php
use Illuminate\Support\Facades\DB;

$p = DB::table('employee_payroll')
    ->where('p_id', 'EMP-1773128004965')
    ->orderBy('year', 'desc')
    ->orderByRaw("CASE 
        WHEN paymonth = 'January' THEN 1 WHEN paymonth = 'February' THEN 2 
        WHEN paymonth = 'March' THEN 3 WHEN paymonth = 'April' THEN 4 
        WHEN paymonth = 'May' THEN 5 WHEN paymonth = 'June' THEN 6 
        WHEN paymonth = 'July' THEN 7 WHEN paymonth = 'August' THEN 8 
        WHEN paymonth = 'September' THEN 9 WHEN paymonth = 'October' THEN 10 
        WHEN paymonth = 'November' THEN 11 WHEN paymonth = 'December' THEN 12 
        END DESC")
    ->get();

echo "--- ALL RECORDS FOR EMP-1773128004965 ---\n";
foreach ($p as $row) {
    echo "ID: {$row->id}, Month: {$row->paymonth}, Year: {$row->year}, Frozen: {$row->is_frozen}\n";
    echo "  PT: {$row->professional_tax}, Festival: {$row->festival_allowance}, Updated: {$row->updated_at}\n";
}
