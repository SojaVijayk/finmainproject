<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$employees = DB::table('project_employee')->select('id', 'name', 'p_id')->get();
foreach ($employees as $emp) {
    echo "Employee ID: {$emp->id}, Name: {$emp->name}, P_ID: '{$emp->p_id}'\n";
    $service = DB::table('service')->where('p_id', $emp->p_id)->first();
    echo "  Service P_ID: '" . ($service->p_id ?? 'NOT FOUND') . "'\n";
    $payrolls = DB::table('employee_payroll')->where('p_id', $emp->p_id)->get();
    echo "  Payroll count: " . $payrolls->count() . "\n";
}
