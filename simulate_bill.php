<?php
use App\Models\PayItem;

$payItem = PayItem::where('name', 'PF Tax')->first();
$month = 'March';
$year = 2026;

$employees = \DB::table('employee_payroll')
    ->join('project_employee', 'project_employee.p_id', '=', 'employee_payroll.p_id')
    ->where('employee_payroll.paymonth', $month)
    ->where('employee_payroll.year', $year)
    ->select('employee_payroll.*', 'project_employee.name as employee_name')
    ->get();

echo "--- SIMULATING BILL GENERATION FOR $month $year ---\n";
foreach ($employees as $emp) {
    $cumulativeGross = (float)$emp->gross_salary;
    $calculatedAmount = 0;
    
    if ($payItem->is_slab_based && $payItem->slabs->isNotEmpty()) {
        $matched = false;
        $maxSlab = $payItem->slabs->sortByDesc('salary_to')->first();
        
        foreach ($payItem->slabs as $slab) {
            if ($cumulativeGross >= $slab->salary_from && $cumulativeGross <= $slab->salary_to) {
                $calculatedAmount = $slab->amount;
                $matched = true;
                break;
            }
        }
        
        if (!$matched && $cumulativeGross > $maxSlab->salary_to) {
            $calculatedAmount = $maxSlab->amount;
        }
    }
    
    echo "Employee: {$emp->employee_name}, PID: {$emp->p_id}, Gross: {$emp->gross_salary}, Calculated PT: $calculatedAmount\n";
}
