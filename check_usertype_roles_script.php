<?php
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$roles = DB::table('usertype_role')->get();
foreach($roles as $r) {
    echo $r->id . ': ' . $r->usertype_role . "\n";
}
