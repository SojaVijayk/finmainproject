<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$count = DB::table('leave_request_details')->count();
echo "Total Leave Details: $count\n";

if ($count > 0) {
    $details = DB::table('leave_request_details')->take(10)->get();
    foreach ($details as $d) {
        echo "Date: {$d->date} | User: {$d->user_id} | Type: {$d->leave_type_id} | Dur: {$d->leave_duration} | Status: {$d->status}\n";
    }
} else {
    echo "No leave records found.\n";
}

$types = DB::table('leaves')->get();
foreach ($types as $t) {
    echo "ID: {$t->id} | Name: {$t->leave_type}\n";
}
