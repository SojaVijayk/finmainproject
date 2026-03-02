<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$names = ['sraya', 'anjana'];
$output = "";

foreach ($names as $name) {
    $output .= "--- Checking Leaves for $name ---\n";
    $employees = DB::table('project_employee')->where('name', 'like', "%$name%")->get();
    foreach ($employees as $employee) {
        $output .= "PID: {$employee->p_id} | Name: {$employee->name}\n";
        $leaves = DB::table('leave_request_details')
            ->where('user_id', $employee->p_id)
            ->whereBetween('date', ['2026-02-01', '2026-02-28'])
            ->get();
        if ($leaves->isEmpty()) {
            $output .= "No leaves found in Feb 2026\n";
        }
        foreach ($leaves as $l) {
            $output .= "Date: {$l->date} | TypeId: {$l->leave_type_id} | Dur: {$l->leave_duration} | Status: {$l->status}\n";
        }
    }
}

file_put_contents('leave_debug.txt', $output);
echo "Output saved to leave_debug.txt\n";
