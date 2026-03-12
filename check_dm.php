<?php
use Illuminate\Support\Facades\DB;

$dm = DB::table('deduction_masters')->where('p_id', 'EMP-1773128004965')->first();

echo "--- DEDUCTION MASTER DUMP FOR EMP-1773128004965 ---\n";
if ($dm) {
    print_r($dm);
} else {
    echo "No record found.\n";
}
