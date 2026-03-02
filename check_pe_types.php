<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$columns = DB::select('DESCRIBE project_employee');
foreach ($columns as $column) {
    if (in_array($column->Field, ['p_id', 'employee_code'])) {
        echo "Column: " . $column->Field . " | Type: " . $column->Type . "\n";
    }
}
