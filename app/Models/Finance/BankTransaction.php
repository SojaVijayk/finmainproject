<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class BankTransaction extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'finance_bank_account_id',
    'type',
    'amount',
    'balance_after',
    'transaction_date',
    'category',
    'description',
    'reference_id',
    'reference_type',
  ];

  protected $casts = [
    'transaction_date' => 'date',
  ];

  public function bankAccount()
  {
    return $this->belongsTo(FinanceBankAccount::class, 'finance_bank_account_id');
  }

  public function reference()
  {
    return $this->morphTo();
  }
}
