<?php
use Illuminate\Support\Facades\DB;

$projectId = 3;
$query = DB::table('project_employee')
    ->leftJoin('service', 'project_employee.p_id', '=', 'service.p_id')
    ->leftJoin('employment_types', 'service.employment_type', '=', 'employment_types.id')
    ->leftJoin('salary', 'project_employee.p_id', '=', 'salary.p_id');

if ($projectId) {
    $query->where('project_employee.project_id', $projectId);
}

$employees = $query->select(
    'project_employee.p_id',
    'project_employee.name as employee_name',
    'project_employee.status as current_status',
    'project_employee.employment_type as pe_type',
    'service.employment_type as svc_type',
    'employment_types.employment_type as et_label',
    'salary.gross_salary as master_gross',
    'salary.basic_pay as master_basic',
    'salary.da as master_da',
    'service.consolidated_pay'
)->get();

$employees = $employees->unique('p_id')->values();

echo "Found " . $employees->count() . " employees.\n";

$pIds = $employees->pluck('p_id')->toArray();

$latestPayroll = DB::table('employee_payroll')
    ->whereIn('p_id', $pIds)
    ->where('is_frozen', 1)
    ->orderBy('year', 'desc')
    ->orderByRaw("CASE 
        WHEN paymonth = 'January' THEN 1 WHEN paymonth = 'February' THEN 2 
        WHEN paymonth = 'March' THEN 3 WHEN paymonth = 'April' THEN 4 
        WHEN paymonth = 'May' THEN 5 WHEN paymonth = 'June' THEN 6 
        WHEN paymonth = 'July' THEN 7 WHEN paymonth = 'August' THEN 8 
        WHEN paymonth = 'September' THEN 9 WHEN paymonth = 'October' THEN 10 
        WHEN paymonth = 'November' THEN 11 WHEN paymonth = 'December' THEN 12 
        END DESC")
    ->get()
    ->unique('p_id')
    ->keyBy('p_id');

foreach ($employees as $emp) {
    $lp = $latestPayroll->get($emp->p_id);
    $actual_salary = (float)($lp?->net_salary ?? 0);
    echo "Employee: {$emp->employee_name}, P_ID: {$emp->p_id}, Actual Salary: {$actual_salary}\n";
}
