<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$names = ['sraya', 'anjana'];

foreach ($names as $name) {
    echo "--- Checking $name ---\n";
    $employees = DB::table('project_employee')->where('name', 'like', "%$name%")->get();
    foreach ($employees as $employee) {
        echo "PID: {$employee->p_id} | Name: {$employee->name}\n";
        $services = DB::table('service')->where('p_id', $employee->p_id)->get();
        foreach ($services as $s) {
            echo "ID: {$s->id} | Pay: {$s->consolidated_pay} | Start: {$s->start_date} | End: {$s->end_date} | Status: {$s->status} | EmpType: {$s->employment_type}\n";
        }
    }
}
