<?php

use App\Models\Service;
use Illuminate\Support\Carbon;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new Service();
$service->start_date = Carbon::now();
$service->end_date = Carbon::now()->addYear();

// Force attributes to be set if not already
$service->setAttribute('start_date', Carbon::now());
$service->setAttribute('end_date', Carbon::now()->addYear());

$jsonArray = $service->toArray();

echo "Start Date: " . $jsonArray['start_date'] . "\n";
echo "End Date: " . $jsonArray['end_date'] . "\n";

if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $jsonArray['start_date'])) {
    echo "SUCCESS: Date format correct.\n";
} else {
    echo "FAILURE: Date format incorrect: " . $jsonArray['start_date'] . "\n";
}

$service->created_at = Carbon::now()->subDay();
$service->updated_at = Carbon::now();

if ($service->updated_at->gt($service->created_at)) {
    echo "SUCCESS: Comparison works.\n";
} else {
    echo "FAILURE: Comparison failed.\n";
}
