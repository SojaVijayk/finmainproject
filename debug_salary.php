<?php
// Simulation of the calculation logic found in the controllers and blade
$grossSalary = 10000;
$totalWorkingDays = 31;
$daysWorked = 31;
$arrear = 500;
$festivalAllowance = 1000;

$tds = 200;
$epf = 300;
$pf = 400;
$edli = 50;
$tds192b = 0;
$tds194j = 0;
$pt = 200;
$esi = 100;
$lic = 150;
$otherDed = 0;

// Logic from select-employees.blade.php (PHP part)
$proratedSalary = $grossSalary; // Assuming full month for simplicity
$computedGrossSalary = $proratedSalary + $arrear + $festivalAllowance;
echo "Computed Gross (PHP): $computedGrossSalary (Expected: 11500)\n";

$allDeductionAmts = $tds + $epf + $pf + $edli + $tds192b + $tds194j + $pt + $esi + $lic + $otherDed;
echo "Total Deductions (PHP): $allDeductionAmts (Expected: 1400)\n";

$computedNetSalary = $computedGrossSalary - $allDeductionAmts;
echo "Computed Net Salary (PHP): $computedNetSalary (Expected: 10100)\n";

// Logic from DeductionMasterController.php (storeDeductions)
$totalDeductionsStore = $tds + $epf + $pf + $edli + $tds192b + $tds194j + $pt + $esi + $lic + $otherDed;
$computedGrossStore = $proratedSalary + $arrear + $festivalAllowance;
$netSalaryStore = $computedGrossStore - $totalDeductionsStore;
echo "Store Net Salary: $netSalaryStore (Expected: 10100)\n";

// Logic from PayItemMasterController.php (storeBill)
$totalDeductionsBill = $tds + $epf + $pf + $edli + $tds192b + $tds194j + $pt + $esi + $lic + $otherDed;
$computedGrossBill = $proratedSalary + $arrear + $festivalAllowance;
$netSalaryBill = $computedGrossBill - $totalDeductionsBill;
echo "Bill Net Salary: $netSalaryBill (Expected: 10100)\n";

if ($computedNetSalary == 10100 && $netSalaryStore == 10100 && $netSalaryBill == 10100) {
    echo "CORRECT: Festival Allowance increases net salary.\n";
} else {
    echo "ERROR: Math inconsistency detected!\n";
}
