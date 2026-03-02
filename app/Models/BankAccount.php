<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'account_holder_name','account_number', 'ifsc', 'branch', 'bank_name', 'bank_address',  'status', 'primary','entry_by','entry_at','verified_by','verified_at'];

}