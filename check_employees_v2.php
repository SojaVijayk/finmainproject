<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$names = ['sraya', 'anjana'];
$output = "";

foreach ($names as $name) {
    $output .= "--- Checking $name ---\n";
    $employees = DB::table('project_employee')->where('name', 'like', "%$name%")->get();
    foreach ($employees as $employee) {
        $output .= "PID: {$employee->p_id} | Name: {$employee->name}\n";
        $services = DB::table('service')->where('p_id', $employee->p_id)->get();
        foreach ($services as $s) {
            $output .= "ID: {$s->id} | Pay: {$s->consolidated_pay} | Start: {$s->start_date} | End: {$s->end_date} | Status: {$s->status} | EmpType: {$s->employment_type}\n";
        }
    }
}

file_put_contents('employee_debug.txt', $output);
echo "Output saved to employee_debug.txt\n";
