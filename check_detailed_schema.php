<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = ['project_employee', 'service'];
$results = [];

foreach ($tables as $table) {
    // MySQL specific
    $columns = DB::select("DESCRIBE `$table`");
    $results[$table] = $columns;
}

echo json_encode($results, JSON_PRETTY_PRINT);
