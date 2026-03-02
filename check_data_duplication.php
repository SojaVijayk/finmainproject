<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProjectEmployee;
use Illuminate\Support\Facades\DB;

$employees = ProjectEmployee::select('id', 'name', 'p_id')->get();
echo "--- Project Employees ---\n";
foreach ($employees as $emp) {
    echo "ID: {$emp->id}, Name: {$emp->name}, P_ID: {$emp->p_id}\n";
}

echo "\n--- Service Table (first 10) ---\n";
$services = DB::table('service')->get();
foreach ($services as $service) {
    echo "P_ID: {$service->p_id}, Status: " . ($service->status ?? 'N/A') . "\n";
}

echo "\n--- Payroll Table (first 10) ---\n";
$payrolls = DB::table('employee_payroll')->get();
foreach ($payrolls as $payroll) {
    echo "P_ID: {$payroll->p_id}, Month: {$payroll->paymonth}, Net: {$payroll->net_salary}\n";
}
