<?php
use Illuminate\Support\Facades\DB;

$projectId = 1;

echo "--- Project 1 Employees ---\n";
$all = DB::table('project_employee')->where('project_id', $projectId)->get();
foreach($all as $e) {
    echo "ID: {$e->p_id}, Name: {$e->name}\n";
}

echo "\n--- Frozen Employees in Project 1 ---\n";
$frozen = DB::table('project_employee')
    ->join('employee_payroll', 'project_employee.p_id', '=', 'employee_payroll.p_id')
    ->where('project_employee.project_id', $projectId)
    ->where('employee_payroll.is_frozen', 1)
    ->select('project_employee.p_id', 'project_employee.name', 'employee_payroll.paymonth', 'employee_payroll.year')
    ->distinct()
    ->get();

foreach($frozen as $e) {
    echo "ID: {$e->p_id}, Name: {$e->name} (Frozen: {$e->paymonth} {$e->year})\n";
}
