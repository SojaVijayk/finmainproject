<?php
use App\Models\PayItem;

$items = PayItem::where('name', 'PF Tax')->get();
echo "--- PF TAX ITEM CHECK ---\n";
foreach ($items as $pi) {
    echo "ID: {$pi->id}, Slabs: " . $pi->slabs()->count() . "\n";
}
