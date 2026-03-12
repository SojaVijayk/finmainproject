<?php
use Illuminate\Support\Facades\DB;

$res = DB::table('employee_payroll')
    ->join('project_employee', 'project_employee.p_id', '=', 'employee_payroll.p_id')
    ->leftJoin('deduction_masters', 'deduction_masters.p_id', '=', 'employee_payroll.p_id')
    ->where('employee_payroll.id', 48) // The one I manually set to 1500
    ->select('employee_payroll.*', 'deduction_masters.professional_tax as dm_pt_flag')
    ->first();

echo "PID: {$res->p_id}\n";
echo "PT Value: {$res->professional_tax}\n";
echo "DM PT Flag: {$res->dm_pt_flag}\n";
