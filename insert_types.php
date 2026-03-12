<?php
use Illuminate\Support\Facades\DB;

$existing = DB::table('employment_types')->pluck('employment_type')->toArray();
$needed = ['Deputation', 'Permanent', 'Full Time', 'Temporary'];
foreach ($needed as $type) {
    if (!in_array($type, $existing)) {
        DB::table('employment_types')->insert([
            'employment_type' => $type,
            'leave_period' => 1,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
echo "Employment types insertion complete.\n";
