<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "--- Service Table ---\n";
print_r(DB::select('DESCRIBE service'));

echo "\n--- Project Employee Table ---\n";
print_r(DB::select('DESCRIBE project_employee'));

echo "\n--- Sample Employee with Project ---\n";
$emp = DB::table('project_employee')
    ->leftJoin('projectdemo', 'projectdemo.id', '=', 'project_employee.project_id')
    ->select('project_employee.id', 'project_employee.name', 'project_employee.project_id', 'projectdemo.name as project_name')
    ->first();
print_r($emp);
