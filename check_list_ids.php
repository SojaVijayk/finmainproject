<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Project\ProjectEmployeeController;
use Illuminate\Http\Request;

$controller = new ProjectEmployeeController();
$response = $controller->globalList(new Request());
$data = json_decode($response->getContent(), true);

echo "--- Global List IDs ---\n";
foreach ($data['data'] as $row) {
    echo "ID: {$row['id']}, Name: {$row['name']}\n";
}
