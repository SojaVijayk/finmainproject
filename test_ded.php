<?php
use Illuminate\Support\Facades\DB;

$masters = DB::table('deduction_masters')->orderBy('id', 'desc')->take(2)->get();
print_r($masters);
