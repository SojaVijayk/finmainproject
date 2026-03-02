<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$payrolls = DB::table('employee_payroll')->get();
echo "--- Total Payroll Records: " . $payrolls->count() . " ---\n";
foreach ($payrolls as $p) {
    echo "ID: {$p->id}, P_ID: {$p->p_id}, Month: {$p->paymonth}, Year: {$p->year}, Net: {$p->net_salary}\n";
}
