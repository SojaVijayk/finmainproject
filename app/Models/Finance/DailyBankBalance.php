<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyBankBalance extends Model
{
  use HasFactory;

  protected $fillable = [
    'finance_bank_account_id',
    'date',
    'opening_balance',
    'receipts',
    'payments',
    'closing_balance',
  ];

  protected $casts = [
    'date' => 'date',
    'opening_balance' => 'decimal:2',
    'receipts' => 'decimal:2',
    'payments' => 'decimal:2',
    'closing_balance' => 'decimal:2',
  ];

  public function bankAccount()
  {
    return $this->belongsTo(FinanceBankAccount::class, 'finance_bank_account_id');
  }
}
