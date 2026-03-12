<?php
use App\Models\PayItem;

$items = PayItem::with('slabs')->where('name', 'PF Tax')->get();
echo "--- PF TAX SLABS ---\n";
foreach ($items as $pi) {
    echo "Item ID: {$pi->id}\n";
    foreach ($pi->slabs as $s) {
        echo "  Slab: {$s->salary_from} - {$s->salary_to} -> {$s->amount}\n";
    }
}
