<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProjectEmployee;

$query = ProjectEmployee::leftJoin("users", "users.id", "=", "project_employee.user_id")
    ->leftJoin("usertype_role","usertype_role.id","=","users.user_role")
    ->leftJoin("designations","designations.id","=","project_employee.designation_id")
    ->select('project_employee.id','project_employee.name','project_employee.p_id', 'users.id as user_actual_id');

$list = $query->take(5)->get();

echo "--- Global List Query Results ---\n";
foreach ($list as $row) {
    echo "ID: {$row->id}, Name: {$row->name}, P_ID: {$row->p_id}, UserID: {$row->user_actual_id}\n";
}
