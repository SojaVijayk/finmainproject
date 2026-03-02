<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceBankAccount extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'bank_name',
    'account_name',
    'account_number',
    'ifsc_code',
    'branch',
    'opening_balance',
    'current_balance',
    'is_active',
  ];

  public function transactions()
  {
    return $this->hasMany(BankTransaction::class);
  }
}
