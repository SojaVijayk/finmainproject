<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$leaves = DB::table('leaves')->get();
foreach ($leaves as $l) {
    echo "ID: {$l->id} | Type: {$l->leave_type}\n";
}
