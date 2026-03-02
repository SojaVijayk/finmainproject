<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = ['project_employee', 'service', 'employee_payroll'];
foreach ($tables as $table) {
    echo "--- Checking Table: $table ---\n";
    $dupes = DB::table($table)
        ->select('p_id', DB::raw('count(*) as count'))
        ->groupBy('p_id')
        ->having('count', '>', 1)
        ->get();
    
    if ($dupes->isEmpty()) {
        echo "No duplicate p_id found.\n";
    } else {
        echo "Found " . $dupes->count() . " duplicates!\n";
        foreach ($dupes as $d) {
            echo "P_ID: {$d->p_id} Count: {$d->count}\n";
        }
    }
}
