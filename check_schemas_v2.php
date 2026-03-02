<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

function describe($table) {
    echo "--- Table: $table ---\n";
    $cols = DB::select("DESCRIBE $table");
    foreach ($cols as $col) {
        echo sprintf("%-20s | %-20s | %-5s | %-5s | %-10s\n", 
            $col->Field, $col->Type, $col->Null, $col->Key, $col->Default);
    }
    echo "\n";
}

describe('service');
describe('project_employee');
describe('projectdemo');

echo "--- Relationships Test ---\n";
$emp = DB::table('project_employee')
    ->leftJoin('projectdemo', 'projectdemo.id', '=', 'project_employee.project_id')
    ->select('project_employee.id', 'project_employee.name', 'project_employee.project_id', 'projectdemo.name as project_name')
    ->first();
var_dump($emp);
