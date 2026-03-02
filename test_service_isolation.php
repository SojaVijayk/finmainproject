<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Service;
use App\Models\ProjectEmployee;

// 1. Get two employees
$emp1 = ProjectEmployee::find(1);
$emp2 = ProjectEmployee::find(2);

echo "Initial State:\n";
echo "Emp 1 ({$emp1->name}) Service: " . ($emp1->service->consolidated_pay ?? '0') . "\n";
echo "Emp 2 ({$emp2->name}) Service: " . ($emp2->service->consolidated_pay ?? '0') . "\n";

// 2. Update Emp 1's service
$p_id1 = $emp1->p_id;
Service::updateOrCreate(['p_id' => $p_id1], ['consolidated_pay' => 12345.67]);
echo "\nUpdated Emp 1 service to 12345.67\n";

// 3. Re-fetch and check isolation
$emp1 = ProjectEmployee::find(1);
$emp2 = ProjectEmployee::find(2);
echo "\nFinal State:\n";
echo "Emp 1 ({$emp1->name}) Service: " . ($emp1->service->consolidated_pay ?? '0') . "\n";
echo "Emp 2 ({$emp2->name}) Service: " . ($emp2->service->consolidated_pay ?? '0') . "\n";
