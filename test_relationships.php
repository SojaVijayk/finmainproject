<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProjectEmployee;

$employees = ProjectEmployee::with(['service', 'payroll'])->take(3)->get();
foreach ($employees as $emp) {
    echo "--- Employee: {$emp->name} (ID: {$emp->id}, P_ID: {$emp->p_id}) ---\n";
    echo "Service P_ID: " . ($emp->service->p_id ?? 'NULL') . "\n";
    echo "Payroll Count: " . $emp->payroll->count() . "\n";
    foreach ($emp->payroll as $p) {
        echo " - Payroll Month: {$p->paymonth}, Year: {$p->year}, Net: {$p->net_salary}\n";
    }
    echo "\n";
}
