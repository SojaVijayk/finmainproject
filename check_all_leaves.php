<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$count = DB::table('leave_request_details')->count();
echo "Total leaves in DB: $count\n";

if ($count > 0) {
    $leaves = DB::table('leave_request_details')->take(20)->get();
    foreach ($leaves as $l) {
        echo "User: {$l->user_id} | Date: {$l->date} | Type: {$l->leave_type_id} | Dur: {$l->leave_duration} | Status: {$l->status}\n";
    }
}
