<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    $tables = ['designations', 'usertype_role'];
    foreach ($tables as $table) {
        echo "Columns in table $table:\n";
        $columns = Schema::getColumnListing($table);
        foreach ($columns as $column) {
            echo "- $column\n";
        }
        echo "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
