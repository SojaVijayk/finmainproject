<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = ['project_employee', 'service', 'employee_payroll'];
foreach ($tables as $table) {
    echo "--- Table: $table ---\n";
    $cols = DB::select("DESCRIBE $table");
    foreach ($cols as $col) {
        if ($col->Field == 'p_id') {
            print_r($col);
        }
    }
}
