<?php
use Illuminate\Support\Facades\DB;

$p = DB::table('employee_payroll')->where('id', 48)->first();
if ($p) {
    echo "ID: {$p->id}, Month: {$p->paymonth}, Year: {$p->year}, Gross: {$p->gross_salary}, Net: {$p->net_salary}, PT: {$p->professional_tax}\n";
} else {
    echo "Record 48 not found.\n";
}
