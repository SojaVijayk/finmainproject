<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$data = DB::table('roles')->get();
echo "Data in roles:\n";
foreach ($data as $row) {
    print_r($row);
}
