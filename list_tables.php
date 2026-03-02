<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $tables = DB::select('SHOW TABLES');
    echo "Tables in database:\n";
    foreach ($tables as $table) {
        $tableArray = (array) $table;
        echo array_values($tableArray)[0] . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
