<?php
use Illuminate\Support\Facades\DB;

$project_id = 1;
$month = 'February';
$year = 2026;

$frozenPayrolls = DB::table('employee_payroll')
    ->join('project_employee', 'project_employee.p_id', '=', 'employee_payroll.p_id')
    ->leftJoin('designations', 'designations.id', '=', 'project_employee.designation_id')
    ->leftJoin('service', 'service.p_id', '=', 'employee_payroll.p_id')
    ->leftJoin('deduction_masters', 'deduction_masters.p_id', '=', 'employee_payroll.p_id')
    ->where('project_employee.project_id', $project_id)
    ->where('employee_payroll.paymonth', $month)
    ->where('employee_payroll.year', $year)
    ->where('employee_payroll.is_frozen', 1)
    ->whereRaw('service.id = (SELECT MAX(id) FROM service WHERE service.p_id = employee_payroll.p_id)')
    ->select(
        'employee_payroll.*',
        'project_employee.name',
        'deduction_masters.professional_tax_value as dm_professional_tax_value',
        'deduction_masters.professional_tax_type as dm_professional_tax_type',
        'deduction_masters.professional_tax_amount as dm_professional_tax_amount'
    )
    ->get();

echo "--- SIMULATING DEDUCTION MASTER QUERY FOR $month $year ---\n";
echo "Count: " . $frozenPayrolls->count() . "\n";
foreach ($frozenPayrolls as $p) {
    echo "Employee: {$p->name}, PT: {$p->professional_tax}, Master PT: {$p->dm_professional_tax_amount}\n";
}
