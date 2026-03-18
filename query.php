<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$res = DB::select('SELECT * FROM projects LIMIT 1');
file_put_contents('projects.json', json_encode($res, JSON_PRETTY_PRINT));
echo "done!\n";
