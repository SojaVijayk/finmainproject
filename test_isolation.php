<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Payroll;
use App\Models\ProjectEmployee;

// 1. Update first employee's payroll
$p_id1 = 'EMP-1770195835468'; // soja ID 1
$payroll = Payroll::updateOrCreate(
    ['p_id' => $p_id1, 'paymonth' => 'February', 'year' => '2026'],
    ['net_salary' => 99999.99]
);
echo "Updated $p_id1 to 99999.99\n";

// 2. Fetch all and check isolation
$employees = ProjectEmployee::with('payroll')->take(3)->get();
foreach ($employees as $emp) {
    echo "Employee: {$emp->name} (P_ID: {$emp->p_id})\n";
    foreach ($emp->payroll as $p) {
        echo " - {$p->paymonth} {$p->year}: Net = {$p->net_salary}\n";
    }
}
