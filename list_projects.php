<?php
use Illuminate\Support\Facades\DB;

foreach(DB::table('projects')->get() as $pr) {
    $c = DB::table('project_employee')->where('project_id', $pr->id)->count();
    echo "Project ID: {$pr->id}, Name: [{$pr->name}], Employees: $c\n";
}
