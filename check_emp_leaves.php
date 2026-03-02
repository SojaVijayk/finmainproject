<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$names = ['sraya', 'anjana'];

foreach ($names as $name) {
    echo "--- Checking Leaves for $name ---\n";
    $employees = DB::table('project_employee')->where('name', 'like', "%$name%")->get();
    foreach ($employees as $employee) {
        echo "PID: {$employee->p_id} | Name: {$employee->name}\n";
        $leaves = DB::table('leave_request_details')
            ->where('user_id', $employee->p_id)
            ->whereBetween('date', ['2026-02-01', '2026-02-28'])
            ->get();
        foreach ($leaves as $l) {
            echo "Date: {$l->date} | TypeId: {$l->leave_type_id} | Dur: {$l->leave_duration} | Status: {$l->status}\n";
        }
    }
}
