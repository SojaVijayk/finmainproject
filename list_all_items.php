<?php
foreach(\App\Models\PayItem::all() as $pi) {
    echo "ID: {$pi->id}, Name: [{$pi->name}], Type: {$pi->type}\n";
}
