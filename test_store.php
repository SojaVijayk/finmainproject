<?php
use App\Models\PayItem;

$payItem = PayItem::where('name', 'PF Tax')->first();
$pId = 'EMP-1773128004965';
$m = 'March';
$y = 2026;
$amt = 1500.00;
$destColumn = 'professional_tax';

echo "Updating $pId for $m $y with $amt...\n";

\DB::table('employee_payroll')->updateOrInsert(
    ['p_id' => $pId, 'paymonth' => $m, 'year' => $y],
    [$destColumn => $amt, 'updated_at' => now()]
);

// Re-fetch to verify
$p = \DB::table('employee_payroll')->where('p_id', $pId)->where('paymonth', $m)->where('year', $y)->first();
echo "Updated PT: {$p->professional_tax}, Updated At: {$p->updated_at}\n";
