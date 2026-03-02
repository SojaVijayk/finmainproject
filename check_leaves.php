<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$leaves = DB::table('leaves')->get();
foreach ($leaves as $l) {
    echo "{$l->id}: {$l->leave_type}\n";
}
echo "--- Leave Requests Statuses ---\n";
$statuses = DB::table('leave_requests')->select('status')->distinct()->get();
foreach ($statuses as $s) {
    echo "Status: {$s->status}\n";
}
