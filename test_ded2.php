<?php
use Illuminate\Support\Facades\DB;

$edd = DB::table('employee_dynamic_deductions')->orderBy('id', 'desc')->take(5)->get();
echo "--- EMPLOYEE_DYNAMIC_DEDUCTIONS ---\n";
print_r($edd);

$dm = DB::table('deduction_masters')->orderBy('id', 'desc')->take(5)->get();
echo "\n--- DEDUCTION_MASTERS ---\n";
print_r($dm);
