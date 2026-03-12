<?php
use Illuminate\Support\Facades\DB;

$pids = ['EMP-1773128004965', 'EMP-1770195835468', 'EMP-1771217797954'];

$emps = DB::table('project_employee')
    ->whereIn('p_id', $pids)
    ->get();

echo "--- PROJECT_EMPLOYEE RECORDS ---\n";
foreach ($emps as $e) {
    echo "PID: {$e->p_id}, Name: {$e->name}, ProjectID: {$e->project_id}, Status: {$e->status}\n";
}
