<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "--- Duplicate P_ID in project_employee ---\n";
$dupes = DB::table('project_employee')
    ->select('p_id', DB::raw('count(*) as count'))
    ->groupBy('p_id')
    ->having('count', '>', 1)
    ->get();
print_r($dupes);

echo "\n--- Duplicate P_ID in service ---\n";
$dupesS = DB::table('service')
    ->select('p_id', DB::raw('count(*) as count'))
    ->groupBy('p_id')
    ->having('count', '>', 1)
    ->get();
print_r($dupesS);

echo "\n--- Count of records in each table ---\n";
echo "project_employee: " . DB::table('project_employee')->count() . "\n";
echo "service: " . DB::table('service')->count() . "\n";
echo "employee_payroll: " . DB::table('employee_payroll')->count() . "\n";
