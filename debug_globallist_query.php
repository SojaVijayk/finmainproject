<?php

use App\Models\ProjectEmployee;
use Illuminate\Http\Request;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Attempting to run globalList query...\n";
    
    $list = ProjectEmployee::leftJoin("users", "users.id", "=", "project_employee.user_id")
    ->leftJoin("usertype_role","usertype_role.id","=","users.user_role")
    ->leftJoin("designations","designations.id","=","project_employee.designation_id")
    ->select('project_employee.id','project_employee.name','project_employee.last_name',
      'project_employee.mobile', 'project_employee.email', 'project_employee.status', 
      'project_employee.empId', 'project_employee.age', 'project_employee.dob', 'project_employee.date_of_joining', 'project_employee.address',
      'usertype_role.usertype_role as user_type','designations.designation')->get();

    echo "Query successful. Count: " . $list->count() . "\n";
    if ($list->count() > 0) {
        echo "First item sample: " . json_encode($list->first()) . "\n";
    }

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Available tables: \n";
    $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
    foreach($tables as $table) {
        foreach($table as $key => $value) echo $value . ", ";
    }
    echo "\n";
}
