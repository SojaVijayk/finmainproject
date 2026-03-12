<?php
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$p = DB::table('employee_payroll')
    ->where('updated_at', '>=', Carbon::today())
    ->orderBy('updated_at', 'desc')
    ->get();

echo "--- PAYROLL UPDATED TODAY (" . Carbon::today()->toDateString() . ") ---\n";
if ($p->isEmpty()) {
    echo "No records updated today.\n";
} else {
    foreach ($p as $row) {
        echo "ID: {$row->id}, PID: {$row->p_id}, Month: {$row->paymonth}, Year: {$row->year}, Updated: {$row->updated_at}\n";
        echo "  PF: {$row->pf}, ProfTax: {$row->professional_tax}, Festival: {$row->festival_allowance}, EPF_Share: {$row->epf_employers_share}, Others: {$row->others}\n";
    }
}
