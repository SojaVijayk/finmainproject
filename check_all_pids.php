<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$employees = DB::table('project_employee')->get();
echo "--- Employee p_id list ---\n";
foreach ($employees as $emp) {
    echo "ID: {$emp->id}, Name: '{$emp->name}', Last: '{$emp->last_name}', P_ID: '{$emp->p_id}'\n";
}
