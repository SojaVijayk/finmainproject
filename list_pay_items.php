<?php
use App\Models\PayItem;

$items = PayItem::all();
echo "--- ALL PAY ITEMS ---\n";
foreach ($items as $pi) {
    echo "ID: {$pi->id}, Name: [{$pi->name}], Type: {$pi->type}\n";
}
